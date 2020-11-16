# UUID as a API Identifier

We have a `$uuid` property on `User` and it *is* being set:

[[[ code('40abf8abf9') ]]]

But it's completely *not* part of our API yet.

## Testing for the UUID

Before we change that, let's write a test that describes the behavior we expect.
Open up `tests/Functional/UserResourceTest.php` and find `testCreateUser()`:

[[[ code('8523e7b854') ]]]

After creating a `User`, our API *serializes* that `User` and returns it in the
response. Once we've changed to use the UUID, we'll expect the `@id` property
on that response to be `/api/user/{uuid}` instead of the auto-increment ID.

Let's check for that! Start by querying for the `User` object that was just created.
We can do that with  `$user = UserFactory::repository()->findOneBy()` and pass it
the email that we used up here. Below that, do a sanity check that the user *does*
exist: `$this->assertNotNull($user)`:

[[[ code('db2a7ac996') ]]]

In order to check that the `@id` is correct, we need to know what random
UUID the user was just assigned. Now that we have the `User` object from the database,
we can say `$this->assertJsonContains()`, pass an array and assert that `@id` should
be `/api/users/` and then `$user->getUuid()`:

[[[ code('8c017617dd') ]]]

Oh, except we don't have a `getUuid()` method yet!

## The UUID Object and UuidInterface

No problem - let's add it! Over in `User`, down at the bottom, go to
"Code"->"Generate" - or `Command`+`N` on a Mac - and generate the getter:

[[[ code('aaf306656b') ]]]

We don't need a setter.

Oh! Apparently this will return a `UuidInterface`.... though I'm not sure why it
used the *long* version here. I'll shorten that, re-type the end and auto-complete
it so that PhpStorm adds the `use` statement on top:

[[[ code('3c7a98d94a') ]]]

The `uuid` property will store in the database as a string in MySQL. But in PHP,
this property holds a `Uuid` *object*. That's not *too* important... just be aware
of it. Over in the test, to get the string, we can say `->toString()`:

[[[ code('43fbf38373') ]]]

Though, really, that's not needed because the `Uuid` object has an `__toString()`
method.

Let's try this! Copy the method name, find your terminal and run:

```terminal
symfony php bin/phpunit --filter=testCreateUser
```

And... *excellent*. We're looking for the UUID, but it still uses the *id*.

## Using the UUID as the API Identifier

So how *do* we tell API Platform to use the `uuid` property as the "identifier"?
It's actually pretty simple! And we talked about it before.

Go to the top of `User`. Every API Resource needs an *identifier*. And when you
use Doctrine, API Platform assumes that you want the database id as the identifier.
To tell it to use something different, add `@ApiProperty()` with `identifier=false`:

[[[ code('81505434ac') ]]]

That says:

> Hey! Please don't use this as the identifier.

Then, above `uuid`, add `@ApiProperty()` with `identifier=true`:

[[[ code('47bf244880') ]]]

That's it! Try the test again:

```terminal-silent
symfony php bin/phpunit --filter=testCreateUser
```

And... got it! But if we run *all* of the tests:

```terminal-silent
symfony php bin/phpunit
```

## Fixing all the Places we Relied in the Id

Ah... things are *not* so good. It turns out that we were relying on the *id* as the
identifier in a *lot* of places. Let's see an example. In `testCreateCheeseListing()`,
we send the `owner` field set to `/api/users/1`. But... that is *not* the IRI of
that user anymore! The IRI of *every* user just changed!

Let's fix some tests! Start inside `UserResourceTest` and search for
`/api/users/`. Yep! To update a user, we won't use the id anymore, we'll use
the *uuid*. In `testGetUser()`, it's the same to *fetch* a user. Change one more
spot at the bottom:

[[[ code('a4d84c785d') ]]]

Over in `CheeseListingResourceTest`, search for the same thing: `/api/users/`.
Then we'll change a few more spots. Like, when we're *setting* the `owner` property,
this needs to use the UUID. I'll keep searching and fix a few more spots:

[[[ code('ba6083d012') ]]]

Let's see if we found everything! Run the tests now:

```terminal-silent
symfony php bin/phpunit
```

And... green! We just switched to UUID's! Woo!

But... part of the point of changing to a UUID was that it would be nice to allow
our API clients - like JavaScript - to set the UUID *themselves*. Let's make that
possible next.
