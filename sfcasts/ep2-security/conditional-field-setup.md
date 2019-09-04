# Conditional Field Setup

So far, we've been talking about granting or denying access to something entirely.
In `CheeseListing`, the most complex case was when we used a voter to deny access
to the PUT operation unless you are the owner of this `CheeseListing` or an admin
user.

But there are several other ways that you might need to customize access to your
API. For example, what if we have a field that we only want *readable*, or maybe
*writable* by certain *types* of users? A great example of this is in `User`: the
`$roles` field. Right now, the `$roles` field is *not* part of our API at all:
*nobody* can change this field via the API.

That certainly make sense... for *most* API users. But what if we create an admin
section and we *do* need this field to be editable by admin users? How can we do
that?

We'll get there. For now, let's add this to the API for *all* users by adding
`@Groups("user:write")`. This creates a *huge* security hole... so we'll come
back to this in a few minutes and make sure that *only* admin users can write
to this field.

[[[ code('ebad546aaf') ]]]

## Adding a phoneNumber Field

Let me give you another example: suppose each `User` has a `phoneNumber` field.
We want that field to be *writeable* by anyone that can *write* to that `User`.
But, for privacy reasons, we *only* want this field to be *readable* by admin
users: we don't want to expose this info when a normal API client fetches data
for a user.

Let's get this set up, then talk about how to make the field *conditionally* part
of our API depending on who is authenticated. To add the field, run:

```terminal
php bin/console make:entity
```

Update the `User` entity, add a new `phoneNumber` field that's a string, length,
how about `50` and say "yes" to `nullable`: the field will be optional.

[[[ code('ad2aa717ab') ]]]

Cool! We now have a nice new `phoneNumber` property in `User`. Generate the migration
with:

```terminal
php bin/console make:migration
```

Let's double-check that migration file... and... it looks good: it adds the
`phone_number` field but nothing else. 

[[[ code('0ced3fbeae') ]]]

Run it with:

```terminal
php bin/console doctrine:migrations:migrate
```

That updates our *normal* database. But because our test environment uses a
*different* database, we *also* need to update that too. Instead of worrying about
migrations on the test database, update it with:

```terminal
php bin/console doctrine:schema:update --force --env=test
```

Now that the field is in the database, let's expose it to the API. I'll steal
the `@Groups()` from above and put this in `user:read` and `user:write`.

[[[ code('d7f7eba888') ]]]

Ok! This is a perfectly boring field that is readable and writable by everyone who
has access to these operations.

## Testing the Conditional Behavior

Before we jump into making that field more dynamic, let's write a test for the
behavior we want. Add a new `public function testGetUser()` and start with the
normal `$client = self::createClient()`. Create a user & log in with
`$user = $this->createUserAndLogin()`, email `cheeseplease@example.com`, password
`foo` and... I forgot the first argument: `$client`.

[[[ code('a29f3b3e84') ]]]

That method creates a *super* simple user: with just the `username`, `email`
and `password` fields filled in. But this time, we *also* want to set the
`phoneNumber`. We can do that manually with `$user->setPhoneNumber('555.123.4567')`,
and then saving it to the database. Set the entity manager to an `$em` variable -
we'll need it a few times - and then, because we're *updating* the `User`, all we
need is `$em->flush()`.

[[[ code('0fb30d8723') ]]]

In this test, we're *not* logged in as an admin user: we're logged in by the user
that we're *fetching*. Our goal is for the API to return the `phoneNumber` field
*only* to admin users. It's a little weird, but, for now, I don't even want users
to be able to see their *own* phone number.

Let's make a request and assert that: `$client->request()` to make a `GET` request
to  `/api/users/` and then `$user->getId()`. To start, let's do a sanity check:
`$this->assertJsonContains()` to make sure that the response contains the
the `username` field set to `cheeseplease`.

[[[ code('a6b4c94f10') ]]]

But what we *really* want assert is that the `phoneNumber` field is *not* in the
response. There's no fancy assert for this so... we'll do it by hand. Start with
`$data = $client->getResponse()->toArray()`.

[[[ code('85cbcdc1a2') ]]]

This handy function will see that the response is JSON and automatically
`json_decode()` it into an array... or throw an exception if something went wrong.
Now we can use `$this->assertArrayNotHasKey('phoneNumber', $data)`.

[[[ code('f72da6a60a') ]]]

Boom! That's enough to make the test fail... because that field *should* be in
the response right now. Copy the `testGetUser` method name and... try it:

```terminal
php bin/phpunit --filter=testGetUser
```

Yay! Failure!

> Failed asserting that array does not have the key `phoneNumber`.

Next, let's finish the *second* half of the test - asserting that an admin user
*can* see this field. Then, we'll discuss the strategy for making the `phoneNumber`
field *conditionally* available in our API.
