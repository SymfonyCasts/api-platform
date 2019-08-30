# Adding the plainPassword Field

As a few of you have *already*, and *correctly* noticed... our `POST` operation
for `/api/users`... doesn't really work yet! I mean, it *works*... but, for the
`password` field, we can't POST the *plain* text password, we have to pass an
encoded version of the password... which makes no sense. We are *not* expecting
the users of our API to *actually* do this.

Great. So, how can we fix this? We know that the deserialization process sees
these `email`, `password` and `username` fields and then calls the setter methods
for each: `setPassword()`, `setUsername()` and `setEmail()`. That creates a
challenge because we need to use a *service* to encode the plain-text password.
And we can't access services from inside an entity.

Nope, we need some way to *intercept* the process, we need to be able to run
code *after* the JSON is deserialized into a `User` object, but *before* it's saved
to the database. One way to do this is via a Doctrine event listener or
entity listener, which are more or less the same thing. That's a fine option...
though things can get tricky when a user is updating their password. We talk
about that on an older
[Symfony 3 Security Tutorial](https://symfonycasts.com/screencast/symfony3-security/user-plain-password#forcing-user-to-look-dirty).

We're going to try a different approach - an approach that's more specific to
API Platform.

## Testing the POST User Endpoint

Before we get there, let's write a test to make sure this works. In the
`test/Functional/` directory, create a new `UserResourceTest` class. Make
this extend our nice `CustomApiTestCase` and use the `ReloadDatabaseTrait` so
the database gets emptied before each test.

[[[ code('e8a680adc2') ]]]

Because we're testing the POST endpoint, add
`public function testCreateUser()` with our usual start:
`$client = self::createClient()`.

[[[ code('91c26a2a57') ]]]

In this case... we don't need to put anything into the database before we start...
so we can jump *straight* to the request: `$client->request()` to make a `POST`
request to `/api/users`. And we of course need to send some data via the `json`
key. If we look at our docs... the three fields we need are `email`, `password`
and `username`. Ok: `email` set to `cheeseplease@example.com`, `username` set to
`cheeseplease` and, here's the big change, `password` set *not* to some crazy
encoded password... but to the plain text password. How about: `brie`. At the
end, toast to our success by asserting that we get this 201 success status code:
`$this->assertResponseStatusCodeSame(201)`.

[[[ code('f73990bd83') ]]]

But... this won't be enough to make sure that the password was *correctly* encoded.
Nope, to know for sure, let's try to login: `$this->logIn()` passing the
`$client`, the email and the password: `brie`.

[[[ code('01e5674cce') ]]]

That's all we need! The `logIn()` method has a built-in assertion. So if the password
is *not* correctly encoded, we'll know with a big, giant test failure.

Copy the `testCreateUser()` method name and let's go try it!

```terminal
php bin/phpunit --filter=testCreateUser
```

Failure! Yay! The login fails with:

> Invalid credentials.

Because the password is *not* being encoded yet.

## Adding plainPassword

Let's get to work. According to our test, we want the user to be able to POST
a field called `password`. But... the `password` property on our `User` is meant
to hold the *encoded* password... not the plain text password. We could, sort of,
use it for both: have API Platform temporarily store the plain text password on
the `password` field... then encoded it before the user is saved to the database.

But don't do that. First, it's just a bit dirty: using that *one* property for two
purposes. And second, I really, *really* want to *avoid* storing plain text
passwords in the database... which could happen if, for some reason, we introduced
a bug that caused our system to "forget" to encode that field before saving.

A better option is to create a new property below this called `$plainPassword`.
But this field will *not* be persisted to Doctrine: it exists *just* as temporary
storage. Make this *writable* with `@Groups({"user:write"})`... then *stop* exposing
the `password` field itself.

[[[ code('4a600af7af') ]]]

So, yes, this will temporarily mean that the POSTed field needs to be called
*plainPassword* - but we'll fix that in a few minutes with `@SerializedName`.

Ok, go to the Code -> Generate menu - or Command+N on a Mac - and generate the
getter and setter for this field. Oh... except I don't want those up here! I
want them *all* the way at the bottom. And... we can tighten this up a bit:
this will return a nullable string, the argument on the setter will be a string
and all of my setters return `self` - they all have `return $this` at the end.

[[[ code('e0d0a3d13c') ]]]

## eraseCredentials

Great! The new `$plainPassword` field is now a *writable* field in our API instead
of `$password`. The docs show this... the POST operation... yep! It advertises
`plainPassword`.

Before we talk about *how* we can intercept this POST request, read the `plainPassword`
field, encode it, and set it back on the `password` property, there's one *teenie*,
*tiny* security detail we should handle. If you scroll down in `User`... eventually
you'll find an `eraseCredentials()` method. This is something that `UserInterface`
forces us to have. After a successful authentication, Symfony calls this method...
and the idea is that we're supposed to "clear" any sensitive data that may be
stored on the `User` - like a plain-text password - *just* to be safe. It's not
*that* important, but as soon as you're storing a plain-text password on `User`,
even though it will *never* be saved to the database, it's a good idea to clear
that field here.

If we stopped now... yay! We haven't... really... done anything: we added this new
`plainPassword` property... but nothing is using it! So, the request would
ultimately explode in the database because our `$password` field will be null.

Next, we need to *hook* into the request-handling process: we need to run some
code *after* deserialization but *before* persisting. We'll do that with a data
persister.
