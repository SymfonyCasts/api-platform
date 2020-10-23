# Setting the UUID on POST

The UUID is now the identifier inside of our User resource. Awesome. But it
still works exactly like the old ID. What I mean is, only the *server* can set the
UUID. If we tried to send you UUID as a JSON field when creating a user, it would
be ignored.

How can I be so sure? Well, look at our `User`: `$uuid` is not settable *anywhere*.
It's not an argument on the constructor and there's no `setUuid()` method. Let's
change that.

## Setting the UUID in a Test

Let's describe the behavior we want in a test. In `UserResourceTest`, up to the
top and copy `testCreateUser()`. Paste that down here and call it
`testCreateUserWithUuid()`.

The *key* change we want to make is this: in the JSON, we're going to *pass* a
`uuid` field. For the value, go up and say `$uuid = Uuid` - the one from `Ramsey`
`::uuid4()`. Then below, send *that* as the `uuid`.

I technically could call `toString()`... but since the `Uuid` object has an
`__toString()` method, we don't need to. Then we assert that the response is
a 201 and... then we can remove the part that fetches the `User` object from the
database. Because... we know that the `@id` should be `/api/users/` and then
that `$uuid`. And, I'll take out the login part, only because we have that in
the other test.

So *this* is the plan: we send the `uuid`, it *uses* that `uuid`. Copy the name
of this method and let's make sure it fails:

```terminal
synfony php bin/phpunit --filter=testCreateUserWithUuid
```

It *does*. It completely ignores the UUID that we send and generates its own.

## Making the uuid Field Settable

So how *can* we make the UUID field settable? Well, it's really no different than
any other field: we need to put the property in the correct group and make sure
it's settable either through the constructor or via a setter method.

Let's think: we *only* want this field to be settable on *create*: we won't
want to allow anyone to *modify* it later. So we *could* add a `setUuid()` method,
but then we would need to be careful to configure and add the correct groups so
that it can be set on create but not edit.

But... there's a simpler solution: avoid the setter and instead add `$uuid` as an
argument to the constructor. Then, by the rules of object-oriented coding, it
will be settable on create but immutable after.

Let's do that: add a `UuidInterface $uuid` argument default it to null. Then
`$this->uuid = $uuid ?: Uuid:uuid4()`.

So if a `$uuid` argument *is*, we'll use that. If not, we generate a new one.
Oh, and we also need to make sure the UUID is actually writeable in the API.
Above the `$uuid` property, add `@Groups()` with `user:write`.

Ok, let's try the test again!

```terminal-silent
synfony php bin/phpunit --filter=testCreateUserWithUuid
```

This time... woh! It works. That's awesome. And the documentation for this
*instantly* looks perfect. Refresh the API homepage, open up the POST operation
for users, hit "Try it out" and... yep! It already shows a UUID example and it
understands that it is available for us to set.

## UUID String Transformed to an Objet?

But wait a second. How did that work? Think about it, if you look at our test,
we're sending a *string* in the JSON. But ultimately, on our `User` object, the
the constructor argument accepts a `UuidInterface` *object*, not a string. How
did that string become an object?

Remember: API platform - well really. Symfony's serializer - is really good at
reading your *types*. It notices that the type for `$uuid` is `UuidInterface`
and uses that to try to find a *denormalizer* that understands this type. And
fortunately, API Platform comes with a denormalizer that works with ramsey UUID's
out of the box. Yep, that denormalizer takes the string and turns it into a UUID
object so that it can *then* be passed to the constructor.

So... yay UUIDs! But, before we finish, there is one *tiny* quirk with UUID's.
Let's see what it is next and learn how to work around it.
