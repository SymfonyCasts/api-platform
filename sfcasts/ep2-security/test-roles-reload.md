# Testing, Updating Roles & Refreshing Data

This test is failing... which is great! It proves that our end goal - *only* returning
the `phoneNumber` field to users authenticated as an admin - is *not* working yet.
Before we get it working, let's finish the test: let's make the user an admin and
assert that the `phoneNumber` field *is* returned.

First, let's "promote" this user to be an admin: call `$user->setRoles()` and
pass this an array with `ROLE_ADMIN` inside.

## Services are Recreated between Requests

Easy! But... we need to be careful with what we do next. Each time we make a request
into the client, the test system *boots* Symfony's container and shuts it down after.
Let me explain: in the "real" world - when you're not inside a test - each request
is handled by a separate PHP process where all the services - like the entity manager -
are instantiated *fresh*. That's just... how PHP works: objects are during the request
then trashed at the end of the request.

But in our test environment, that's *not* the case: we're *really* making *many*,
sort of, "fake" requests into our app all within the same PHP process. This means
that, in theory, if we changed a property on a service during one request, that
would *affect* the next request. That's *not* what we want because it's *not* how
the real world works.

Not to worry: Symfony handles this automatically for us. Before each request with
the client, the container "resets" the container recreates it from scratch. That
gives each request an "isolated" environment.

But it *also* affects the services that we use *inside* our test... and it can
be confusing. Stick with me through the explanation, and then I'll give you some
rules to live by at th end.

Check out the entity manager here on top. Internally, the entity manager keeps
track of all of the objects that it's fetched or persisted. Then, we we call
`flush()`, it loops over all of them, finds the ones that have changed and runs
any queries it needs.

Up here, this entity manager *is* aware of this `User` object, because the
`createUserAndLogIn()` method just used the entity manager to persist it.
But when we make a request, two things happen. First, the *old* container - the
one we've been working with inside the test class so far, is "reset". That means
that the "state" on a lot of the services is reset back to its initial state,
back when the container was originally created. And second, a totally new container
is created for the request itself.

This has two side effects. First, this "identity" map on this entity manager object
was just "reset" back to empty. It means that is has *no* idea that we ever persisted
this `User` object. And second, if you called `$this->getEnityManager()` now, it
would give you the EntityManager that was just used for that request, which would
be a different object than the `$em` variable.

On a high level, you basically want to think of everything *above*
`$client->request()` as code that runs on one page-load and everything *after*
`$client->request()` as code that runs on a totally different page load. If the
two parts of this method really *were* being executed by two different requests,
I wouldn't expect the entity manager down here to be aware that I persisted this
`User` object on some previous request.

Ok, I get it, I lost you. What's going on behind the scenes is confusing - it
confuses me too. But, here's what you need to know. After calling `$user->setRoles()`,
if we *just* said `$em->flush()`, nothing would save. The `$em` variable was "reset"
and so doesn't know it's supposed to be "managing" this `User` object. If you

So, after each request, if you need to work with an entity - whether to read data
or update data - do *not* re-use an entity object from *before* that request.
Nope, query for a new one: `$em = getRepository(User::class)->find($user->getId())`.

Again, we would need to do the *same* thing if we wanted to *read* data. If we
made a `PUT` request up here to edit the user and wanted to assert that a field
*was* updated in the database, we should query for a *new* `User` object down
here. If we used the *old* `$user` variable, it would hold the old data, even
if the database *was* successfully updated.

## Logging in as Admin

So I'll put a comment about this: we're refreshing the user and elevating them
to an admin. Saying `->flush()` is enough for this to save because we've just
queries for this object.

Below, say `$this->logIn()` and pass this `$client` and... the same two arguments
as before: the email & password.

Wait... why do we need to login again? Weren't we already logged in... and didn't
we just change this user's roles in the database? Yep! Unrelated to the test
environment, in order for Symfony's security system to "notice" that a user's
roles were updated in the database, that user needs to log back in. It's quirky
of the security system and hopefully one we'll fix soon. Heck, *I* personally have
a two year old pull request open to do this! I gotta finish that!

Anyways, *that's* why we're logging back in: so that the security system sees the
updated roles.

Finally, down here. we can do the same `$client->request()` as before. In fact,
let's copy it from above, including the `assertJsonContains()` part. But this
time, assert that there *should* be a `phoneNumber` field set to `555.123.4567`.

Phew! Ok, we already know this will fail when we make a `GET` requests for a `User`,
it *is* currently returning the `phoneNumber` field: the test is failing on
`assertArrayNotHasKey()`.

## Dynamic Fields

So... now that we have this big, fancy test... how *are* we going to handle this?
How *can* we make some fields *conditionally* available?

Normalization groups. *Dynamic* normalization groups.

When you make a request for an operation, the groups are normally determined on
an operation-by-operation basis. In the case of a `User`, API Platform is
using `user:read` for normalization and `user:write` for denormalization. In
`CheeseListing`, we're customizing it even deeper: when you `get` a single
`CheeseListing`, it uses the `cheese_listing:read` and `cheese_listing:item:get`
groups.

That's great. But all of these groups are still static: we can't change them
on a user-by-user basis... or via *any* dynamic information. You can't say:

> Oh! this is an admin user! So when we normalize this `User`, I want to
> include an *extra* normalization group.

But... doing this *is* possible. On `User`, above `$phoneNumber`, we're going to
leave the `user:write` group so it's *writable* by anyone with access to a write
operation. But instead of `user:read`, change this to `admin:read`.

That's a *new* group name that I... just invented. Nothing uses this group, so
if we try the test now:

```terminal
php bin/phpunit --filter=testGetUser
```

it fails... but gets further! It fails on `UserResourceTest` line 68... it's
failing down here. We successfully made `phoneNumber` *not* return when we fetch
a user.

Next, we're going to create something called a "context builder", which will allow
us to *dynamically* add this `admin:read` group when an "admin" is trying to
normalize a `User` resource.
