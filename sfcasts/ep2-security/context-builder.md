# Context Builder: Dynamic Fields/Groups

Here's the goal: add logic to our new context builder so that, if the
currently- authenticated user has `ROLE_ADMIN`, an extra `admin:output` group
is *always* added during normalization.

Delete the existing code... though it's *pretty* close to what we're
going to do. Then say `$isAdmin =`. When we copied this class, in addition to
the "decorated" context builder, it came with a *second* argument:
`AuthorizationCheckerInterface`. This is a service that allows us to check whether
or not a user has a role.

But wait... when we needed to do that in our voter, we autowired a different
service via the `Security` type-hint. Well... these are both ways to do the
*exact* same thing: use whichever you like. Yep, we can say
`$isAdmin = $this->authorizationChecker->isGranted('ROLE_ADMIN')`. Then, if
`$context['groups']` and `$isAdmin`... we should add the extra group!

[[[ code('35221d59d6') ]]]

But... why am I checking *if* `$context['groups']`? Well, first, I should probably
be checking if `isset($context['groups'])`. And second... it doesn't really matter
for us. In *theory*, if you had a resource with *no* groups configured, it would
mean the serializer should serialize *all* fields. In that situation, we wouldn't
want to add the `admin:output` group because it would actually cause *less* fields
to be serialized. But because I like to *always* specify normalization and
denormalization groups, this isn't a real situation: `$context['groups']` will
*always* have something in it at this point.

Add the new group with `$context['groups'][] = 'admin:read'`. Right? Well, it's
not *that* simple. This `createFromRequest()` method is called both when the object
is being serialized to JSON - so when it's being "normalized" - *and* when the
JSON is being *deserialized* to the object - when it's being "denormalized".
That's what this normalization flag here is telling us.

Cool! We can say, *if* the object is being normalized, add `admin:read`, else,
add `admin:write`.

[[[ code('28bca08618') ]]]

We're done! I'll even remove this `$resourceClass` thing. That tells us the *class*
of the object that's being serialized or deserialized... which we don't need
because we're adding these groups to *every* resource.

## Side Note: You can Decorate Multiple Times

Side note: we've only created *one* context builder so far, but it's legal to create
as *many* as you want. In the last chapter, I talked about how our new service
"decorates" and "replaces" the core context builder. But you could repeat this
3 times - each time "decorating" that same core service. You can do this because
Symfony is smart: it will create 3 layers of decoration. This `$decorated` property
*might* be the "core" service... or it could just be the *next* decorated service...
which itself would call the *next*, until the core one is eventually called.

If that didn't make sense, don't sweat it. My point is: if you want to control
the context in multiple ways, you could smash all that logic into one context
builder or create *several*. The service config for each will look identical to
the one we created.

Let's head over and run our tests:

```terminal
php bin/phpunit --filter=testGetUser
```

This time... it passes! That test proves that a normal user will *not* get the
`phoneNumber` field.... but as soon as we give that user `ROLE_ADMIN` and make
*another* request, `phoneNumber` *is* returned! Mission accomplished!

## Making "roles" Writeable by only an Admin

And now we can fix the *huge* security problem I created a few minutes ago: instead
of allowing *anyone* to set the `$roles` property on `User`, only *admin* users
should be able to do this.

Before we make that change, let's tweak our test to check for this. In
`testUpdateUser`, let's *also* try to pass a `roles` field set to `ROLE_ADMIN`.
Because we're *not* logged in as an admin user, once we've finished our work,
the `roles` field should simply be ignored. It won't cause a validation error...
it just won't be processed at all.

[[[ code('81a229b776') ]]]

To make sure it's ignored, at the bottom, say `$em = $this->getEntityManager()`
and then query for a fresh user from the database:
`$user = $em->getRepository(User:class)->find($user->getId())`. I'll put some
PHPDoc above this to tell PhpStorm that this will be a `User` object. Finish
with `$this->assertEquals()` that we expect `['ROLE_USER']` to be returned from
`$user->getRoles()`.

[[[ code('28daf65670') ]]]

Why `ROLE_USER`? Because even if the `roles` property is empty in the database...
the `getRoles()` method *always* returns *at least* `ROLE_USER`.

Let's make sure this fails. Copy `testUpdateUser` and run that test:

```terminal
php bin/phpunit --filter=testUpdateUser
```

It... yes - fails! Our API *does* let us write to the `roles` property. How do we
fix this? You probably already know... and it's *gorgeously* simple. Change
the group to `admin:write`.

[[[ code('8b61dd88c5') ]]]

Run the test again:

```terminal-silent
php bin/phpunit --filter=testUpdateUser
```

This time... it passes! Context builders are *awesome*.

## Context Builders & Documentation

Though... they do have one downside: the dynamic groups are *not* reflected anywhere
in the documentation. Refresh the docs... then open the GET operation for a single
`User`. The docs say that this will return a `User` model with `email`, `username`
and `cheeseListings` fields. It does *not* say that `phoneNumber` will be returned.
And even if we logged in as an admin user, this won't change - there would *still*
be no mention of `phoneNumber`. The docs are *static*. If an admin user makes this
request, that JSON *will* contain a `phoneNumber` field, but it won't say that in
the docs.

Next, we're going to do a crazy experiment. Because we're following tight naming
conventions for our groups - like `cheese_listing:output`, `cheese_listing:input`
and even operation-specific groups like `cheese_listing:item:get`, could we use
a context builder to *automatically* set these groups... so we don't need to
manually manage them via annotations? The answer is... yes. But things get
*really* interesting if you want your docs to reflect this.
