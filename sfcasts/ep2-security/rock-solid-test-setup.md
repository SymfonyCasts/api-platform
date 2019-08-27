# Base Test Class full of Goodies

I *love* using functional tests for my API. Even with the nice Swagger frontend,
it's *almost* faster to write a test than it is to try things manually... over
and over again. To make writing tests *even* nicer, I have a few ideas.

Inside `src/`, create a new directory called `Test/`. Then, add a new class:
`CustomApiTestCase`. Make this extend the `ApiTestCase` that our test classes
have been using so far. If you're using API platform 2.5, the namespace will start
with the `ApiPlatform\Core` namespace.

[[[ code('77ac3b4c7f') ]]]

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
`$user = new User()` to the flush call. Paste this and, at the bottom, return
`$user`. Oh, and we need to make a couple of things dynamic: use the `$email`
variable and the `$password` variable. This will temporarily *still* be the
*encoded* password... but we're going to improve that in a minute. For the
username, let's be clever! Grab the substring of the email from the zero position
to wherever the `@` symbol is located. Basically, use everything *before* the `@` symbol.

[[[ code('35110b839d') ]]]

We're also going to need to log in from... basically every test. Add a
`protected function logIn()`. To accomplish this, we'll need to make a request...
which means we need the `Client` object. Add that as the first argument followed
by the same `string $email` and `string $password`, except that *this*
time `$password` will be the plain text password. We shouldn't need to return anything.

Let's sharpen our code-stealing skills once again by going back to our test, copying
these last two lines, pasting them, and making `$email` and `$password` dynamic.

[[[ code('a9fb736af3') ]]]

Woo! Time to shorten our code! Change the test class to extend our shiny new
`CustomApiTestCase`. Below, replace *all* the user stuff with
`$this->createUser('cheeseplease@example.com')` and... aw... dang! I should
have copied that long password. Through the power of PhpStorm... undo.... copy...
redo... and paste as the second argument.

Replace the login stuff too with `$this->login()`, passing `$client` that same
`cheeseplease@example.com` and the plain text password: `foo`.

[[[ code('7b99972f9e') ]]]

Let's check things! Go tests go!

```terminal
php bin/phpunit
```

If we ignore those deprecation warnings... it passed!

## Encoding the User Password

Ok, this feels good. What else can we do? The weirdest thing *now* is probably that
we're passing this long encoded password. It's not obvious that this is an
encoded version of the password `foo` and... it's annoying! Heck, I had to
undo 2 minutes of work earlier so I could copy it!

Let's do this properly. Replace that huge, encoded password string with just `foo`.

Now, inside the base test class, remove the `$password` variable and replace
it with `$encoded =` and... hmm. We need to get the service out of
the container that's responsible for encoding passwords. We can get that with
`self::$container->get('security.password_encoder')`. We also could have used
`UserPasswordEncoderInterface::class` as the service id - that's the type-hint
we use normally for autowiring. Now say `->encodePassword()` and pass the
`$user` object and then the plain text password: `$password`. Finish this with
`$user->setPassword($encoded)`.

[[[ code('d88d514154') ]]]

Beautiful!

And this point... I'm happy! Heck, I'm thrilled! But... I *do* have *one* more
shortcut idea. It'll be *pretty* common for us to want to create a user and then
log in immediately. Let's make that easy! Add a
`protected function createUserAndLogIn()`... which needs the same three arguments
as the function above: `$client`, `$email` and `$password`... and we'll return
the `User`. Inside say `$user = $this->createUser()` with `$email` and `$password`,
then `$this->logIn()` with `$client`, `$email`, `$password`. At the bottom,
`return $user`.

[[[ code('7dce438cf7') ]]]

Nice! Now we can shorten things a *little* bit more: `$this->createUserAndLogIn()`.

[[[ code('dab01c8d4a') ]]]

Let's try it! Run:

```terminal
php bin/phpunit
```

All green!

Looking back at our test, the purpose of this test was really two things. First,
to make sure that if an anonymous users tries to use this endpoint, they'll
get a 401 status code. And second, that an *authenticated* user *should* have
access. Let's add that second part!

Make the *exact* same request as before... except that *this* time we
*should* have access. Assert that we get back a 400 status code. Wait, why
400 and not 200 or 201? Well `400` because we're not actually passing real data...
and so this will fail validation: a 400 error. If you wanted to make this a bit
more useful, you could pass *real* data here - like a `title`, `description`,
etc - and test that we get back a 201 successful status code.

[[[ code('cffe4d3b2f') ]]]

Let's try this!

```terminal-silent
php bin/phpunit
```

It works! Oh, but one last, *tiny* bit of cleanup. See this `headers` key? We
can remove that... and we have one more in `CustomApiTestCase` that we can
also remove.

[[[ code('4a93093578') ]]]

But wait... didn't we need this so that API Platform knows we're sending data
in the right format? Absolutely. But... when you pass the `json` option, the
Client automatically sets the `Content-Type` header for us. To prove it, run
the tests one last time:

```terminal-silent
php bin/phpunit
```

Everything works perfectly!

Hey! This is a great setup! So let's get back to API Platform security stuff!
Right now, to edit a cheese listing, you simply need to be logged in. We need to
make that smarter: you should only be able to edit a cheese listing if you are
the *owner* of that cheese listing... and maybe also admin users can edit any
cheese listing.

Let's do that next and *prove* it works via a test.
