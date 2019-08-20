# Base Test Class full of Goodies

I *love* using functional tests to test my API. Even with our nice Swagger frontend,
it's *almost* just as easy to write tests than it is to try things manually. To
make writing tests *even* nicer, I have a few ideas.

Inside `src/`, create a new directory called `Test/`. Then, add a new PHP class:
`CustomApiTestCase`. Make this extend the `ApiTestCase` class that our test classes
have been using so far. If you're using API platform 2.5, the namespace will start
with `ApiPlatform\Core` namespace.

## Base Class with createUser() and logIn()

We're creating a new base class that all our functional tests will extend. Why?
Shortcut methods! There are a lot of tasks that we're going to do over and over
again, like creating users in the database & logging in. To save time... and honestly,
to make each test more readable, we can create reusable shortcut methods right
here.

Start with `protected function createUser()`. We'll need to pass this the
`$email` we want, the `$password` we want... and it will return the `User` object
after it saves it to the database.

Go steal the logic for all of this from our test class: grab everything from
`$user = new User()` the flush call. Paste this and, at the bottom, return the
`$user`. Oh, and we need to make a couple of things dynamic: use the `$email`
variable and `$password` - that will temporarily still be the encoded password...
but we're going to improve that in a minute. For the username, we can be clever:
grab the substring of the email from the zero position to wherever the `@` symbol
is located. Basically, use everything *before* the `@` symbol.

Before we use this, we're also going to need to log in inside... basically
every test. Add a `protected function logIn()`. To log in, we'll need to make
a request... which means we'll need that `Client` object. Add that as the first
argument then the same `string $email` and `string $password`, except that this
time `$password` will be the plain text password. We won't need to return anything.

Let's sharpen our code stealing skills once again by going back to our test, copying
these last two lines, pasting them, and making `$email` and `$password` dynamic.

Woo! Let's go shorten our code! Change the base class to use our new
`CustomApiTestCase`. Then, down here, replace *all* the user stuff with
`$this->createUser('cheeseplease@example.com')` and... aw... dang! I should
have copied that long password. Through the power of PhpStorm... undo.... copy...
redo... and paste as the second argument.

Replace the login stuff too with `$this->login()`, passing `$client` that same
`cheeseplease@example.com` email and the plain text password: `foo`.

Let's check things! Go tests go!

```terminal
php bin/phpunit
```

If we ignore those deprecation warnings... it *did* pass!

## Encoding the User Password

Ok, this feels good. Let's keep going! The weirdest thing *now* is probably that
we're passing around this long encoded password. It's not obvious that this is
an encoded version of the password `foo` and... it's annoying! Heck, I had to
undo 2 minutes of work earlier so I could copy it!

Let's do this properly. Replace that huge, encoded password string with just `foo`.

Next, inside of the base test class, remove the `$password` variable and replace
it with `$encoded =` and... hmm. We need to get the service out of
the container that's responsible for encoding the password. We can get that with
`self::$container->get('security.password_encoder')`. We also could have used
`UserPasswordEncoderInterface::class` as the service id - that's the type-hint
we use for autowiring. Now say `->encodePassword()` and pass the `$user` object
and then the plain text password: `$password`. Finish this with
`$user->setPassword($encoded)`.

Beautiful!

And this point... I'm happy! But I have *one* more shortcut idea. It'll probably
be *pretty* common for us to want to create a user and then log in immediately.
Let's make that easier! Add a `protected function createUserAndLogIn()`... which
needs the same three arguments as the function above: `$client`, `$email` and
`$password` and we'll return the `User`. Inside say `$user = $this->createUser()`
passing `$email` and `$password`, and then `$this->logIn()` with `$client`,
`$email`, `$password`. At the bottom, `return $user`.

Nice! Let's shorten things a *little* bit further: `$this->createUserAndLogIn()`.
Let's try it! Run:

```terminal
php bin/phpunit
```

All green!

Looking back at our test, the point of this test was really two things. First,
to make sure that if an anonymous users tries to use this endpoint, they'll
get a 401 status code. And second, once they *do* log in, they *do* have
access. Let's add that part!

Down here, make the *exact* same request as before... Except that *this* time
we *should* have access. Assert that we get back a 400 status code. Wait, why
400 and not 200 or 201? Well `400` because we're not actually passing real data...
and so this will fail validation: a 400 error. If you wanted to make this a bit
more realistic, you could pass *real* data here - like a `title`, `description`,
etc - and test that we get back a 201 successful status code.

Ok, let's try this!

```terminal-silent
php bin/phpunit
```

It works! Oh, but one last, *tiny* bit of cleanup. See this `headers` key? We
can remove that... and we have one more in the `CustomApiTestCase` that we can
also remove.

But wait... didn't we need this so that API Platform knows we're sending data
in the right format? Absolutely. But... when you pass the `json` option, the
Client automatically sets that header for us. To prove it, run the tests once
last time:

```terminal-silent
php bin/phpunit
```

Everything works perfectly.

Hey! This is a great setup! So let's get back to API Platform security stuff!
Right now, to edit a cheese listing, you simply need to be logged in. We need to
make that smarter: you should only be able to edit a cheese listing if you are
the *owner* of that cheese listing... and maybe also admin users can edit any
cheese listing.

Let's do that next and *prove* it works via a test.
