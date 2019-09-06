# Testing, Updating Roles & Refreshing Data

This test is failing... which is great! It proves that our end goal - *only* returning
the `phoneNumber` field to users authenticated as an admin - is totally *not*
working yet. Before we get it working, let's finish the test: let's make the user
an admin and assert that the `phoneNumber` field *is* returned.

First, "promote" this user to be an admin: call `$user->setRoles()` and pass this
an array with `ROLE_ADMIN` inside.

## Services are Recreated between Requests

Easy! But... we need to be careful with what we do next.

Let me explain: in the "real" world - when you're not inside a test - each request
is handled by a separate PHP process where all the service objects - like the
entity manager - are instantiated *fresh*. That's just... how PHP works: objects
are created during the request and then trashed at the end of the request.

But in our test environment, that's *not* the case: we're *really* making *many*,
sort of, "fake" requests into our app all within the *same* PHP process. This means
that, in theory, if we made a request that, for some reason, changed a property on
a service object, when we make the *next* request... that property would *still*
be changed. In the test environment, one request can *affect* the next requests.
That's *not* what we want because it's *not* how the real world works.

Not to worry: Symfony handles this automatically for us. Before each request with
the client, the client "resets" the container and recreates it from scratch. That
gives each request an "isolated" environment because each request will create
*new* service objects.

But it *also* affects the service objects that we use *inside* our test... and it
can be confusing. Stick with me through the explanation, and then I'll give you
some rules to live by at the end.

Check out the entity manager here on top. Internally, the entity manager keeps
track of all of the objects that it's fetched or persisted. This is called the
identity map. Then, when we call `flush()`, it loops over all of those objects,
finds the ones that have changed and runs any queries it needs.

Up here, this entity manager *does* have this `User` object in its identity map,
because the `createUserAndLogIn()` method just *used* that entity manager to
persist it. But when we make a request, two things happen. First, the *old*
container - the one we've been working with inside the test class so far, is
"reset". That means that the "state" of a lot of services is reset back to its
initial state, back when the container was originally created. And second, a
totally new container is created for the request itself.

This has two side effects. First, the "identity map" on this entity manager was
just "reset" back to empty. It means that it has *no* idea that we ever persisted
this `User` object. And second, if you called `$this->getEnityManager()` now, it
would give you the EntityManager that was just used for the last request, which
would be a different object than the `$em` variable. That detail is less important.

On a high level, you basically want to think of everything *above*
`$client->request()` as code that runs on one page-load and everything *after*
`$client->request()` as code that runs on a totally different page load. If the
two parts of this method really *were* being executed by two different requests,
I wouldn't expect the entity manager down here to be aware that I persisted this
`User` object on some previous request.

Ok, I get it, I lost you. What's going on behind the scenes is technical - it
confuses me too sometimes. But, here's what you need to know. After calling
`$user->setRoles()`, if we *just* said `$em->flush()`, nothing would save. The
`$em` variable was "reset" and so it doesn't know it's supposed to be "managing"
this `User` object.

Here's the rule to live by: after each request, if you need to work with an
entity - whether to read data or update data - do *not* re-use an entity object
you were working with from *before* that request. Nope, query for a new one:
`$user = $em->getRepository(User::class)->find($user->getId())`.

We would need to do the *same* thing if we wanted to *read* data. If we made a
`PUT` request up here to edit the user and wanted to assert that a field *was*
updated in the database, we should query for a *new* `User` object down here.
If we used the *old* `$user` variable, it would hold the old data, even though
the database *was* successfully updated.

## Logging in as Admin

So I'll put a comment about this: we're refreshing the user and elevating them
to an admin. Saying `->flush()` is enough for this to save because we've just
queried for this object.

[[[ code('113576a637') ]]]

Below, say `$this->logIn()` and pass this `$client` and... the same two arguments
as before: the email & password.

[[[ code('983a17dcbb') ]]]

Wait... why do we need to log in again? Weren't we already logged in... and didn't
we just change this user's roles in the database? Yep! Unrelated to the test
environment, in order for Symfony's security system to "notice" that a user's
roles were updated in the database, that user needs to log back in. It's a quirk
of the security system and hopefully one we'll fix soon. Heck, I *personally* have
a two year old pull request open to do this! I gotta finish that!

Anyways, *that's* why we're logging back in: so that the security system sees the
updated roles.

Finally, down here, we can do the same `$client->request()` as before. In fact,
let's copy it from above, including the `assertJsonContains()` part. But this
time, assert that there *should* be a `phoneNumber` field set to `555.123.4567`.

[[[ code('5549506e81') ]]]

Phew! Ok, we already know this will fail: when we make a `GET` request for a `User`,
it *is* currently returning the `phoneNumber` field: the test is failing on
`assertArrayNotHasKey()`.

## Dynamic Fields

So... now that we have this big, fancy test... how *are* we going to handle this?
How *can* we make some fields *conditionally* available?

Via... dynamic normalization groups.

When you make a request for an operation, the normalization groups are determined on
an operation-by-operation basis. In the case of a `User`, API Platform is
using `user:read` for normalization and `user:write` for denormalization. In
`CheeseListing`, we're customizing it even deeper: when you `get` a single
`CheeseListing`, it uses the `cheese_listing:read` and `cheese_listing:item:get`
groups.

That's great. But all of these groups are still static: we can't change them
on a user-by-user basis... or via *any* dynamic information. You can't say:

> Oh! This is an admin user! So when we normalize this resource, I want to
> include an *extra* normalization group.

But... doing this *is* possible. On `User`, above `$phoneNumber`, we're going to
leave the `user:write` group so it's *writable* by anyone with access to a write
operation. But instead of `user:read`, change this to `admin:read`.

[[[ code('52d4749e49') ]]]

That's a *new* group name that I... just invented. Nothing uses this group, so
if we try the test now:

```terminal-silent
php bin/phpunit --filter=testGetUser
```

It fails... but gets further! It fails on `UserResourceTest` line 68... it's
failing down here. We successfully made `phoneNumber` *not* return when we fetch
a user.

Next, we're going to create something called a "context builder", which will allow
us to *dynamically* add this `admin:read` group when an "admin" is trying to
normalize a `User` resource.
