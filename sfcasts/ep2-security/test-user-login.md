# Logging in Inside the Test

We've added two options when we make the request: a `Content-Type` header, to tell
API Platform that any data we send will be JSON-formatted, and a `json` option set
to an empty array which is enough to *at least* send some valid JSON in the body
of the request.

Because, before we added the `json` option, we got this error: a 400 Bad Request
with... some details in the body that indicate we sent *invalid* JSON.

## Deserialization Before Security

But... wait a second. *Assuming* our `access_control` security is set up correctly...
shouldn't we get denied access *before* API Platform tries to deserialize the JSON
data we're sending to the endpoint?

This is a little bit of a security gotcha... and even as I'm recording this, it
looks like API Platform may change how this works in their next version. Progress!

When you send data to an endpoint API platform does the following things in
this order. First, it deserializes the JSON into whatever resource object we're
working with - like a `CheeseListing` object. Second, it applies the
security access controls. And *third* it applies our validation rules.

Do you see the problem? It's subtle. If API Platform has any problems deserializing
the JSON into the object, the user of our API will see an error about that... even
if that user doesn't have access to perform the operation. The JSON syntax error
is one example of this. But there are other examples, like if you send a
badly-formatted date string to a date field, you'll get a normalization error about
this... even if you don't have access to that operation.

This is probably not a *huge* deal in most cases, but it *is* possible for a user
to get *some* details about how your endpoints work... even if that user don't
have access to them. Of course... they still can't *do* anything with those
endpoints... but I *do* want you to be aware of this.

But... at this *very* moment, there's a pull request open on API Platform to
rename `access_control` to something else - probably `security` - and to change
the behavior so that security runs *before* deserialization. In other words,
if this *does* concern you, it's likely to not behave like this in the future.

Ok, but now that we *are* sending valid JSON, let's see if the test passes! Run:

```terminal
php bin/phpunit
```

And... we've got it! Green!

## Creating the User in the Database

We've proven that you *do* need to log in to execute this operation. So now...
let's log in and make sure it works!

To do that, we *first* need to put a user in the database. Cool! We got this:
`$user = new User()` and then fill in the email `setEmail()` and username
with `setUsername()`. The only other field that's required on the user is the
`password`. Remember, that field is the *encoded* password. For now, let's cheat
and generate an encoded password manually. Find your terminal and, once again, run:

```terminal
php bin/console security:encode-password
```

Let's pass this `foo` and... it gives me this giant, encoded password string.
Copy that, and paste it into `setPassword()`.

[[[ code('78b1a0d48b') ]]]

The `User` object is ready! To save this to the database, it's the same as being
inside our code: we need to get the entity manager, then call persist and flush
on it. But, *normally*, to get the entity manager - or any service - we use
autowiring. Tests are the *one* place where autowiring doesn't work... because
you're, sort of, "outside" of your application.

Instead, we'll fetch the services from the container by their *ids*. Try this:
`$em = self::$container` - a parent class sets the container on this nice property -
`->get()` and the service id. Use `doctrine` then say `->getManager()`.

You can *also* use the type-hint you use for autowiring as the service id. In
other words, `self::$container->get(EntityManagerInterface::class)` would work
super well. And actually... it's probably a bit simpler than what I did.

Anyways, now that we have the entity manager, use the famous: `$em->persist($user)`
and  `$em->flush()`.

[[[ code('3f19fa0dce') ]]]

## POST to Login

Hey! We've got a user in the database! To test if an *authenticated* user can create
a cheese listing... um... how can we authenticate as this user? Well, because we're
using traditional session-based authentication... we just need to log in! Make
a `POST` request to `/login`. I'll keep the header, but this time we *will* send
some JSON data: `email` set to `cheeseplease@example.com` and
`password => 'foo'`.

[[[ code('d1ee2f4eac') ]]]

And we should probably assert that this worked. Copy the response status code
assertion, paste it down here, and check that this returns 204... because 204 is
what we decided to return from `SecurityController`.

[[[ code('db8de20e2c') ]]]

We're not *quite* yet making an authenticated request to create a new
`CheeseListing`... but let's check our progress! Find your terminal and run:

```terminal
php bin/phpunit
```

Got it! Woo! We're now logged in and ready to start making *authenticated* requests.

Except... if you've done functional tests before... you might see a problem. Try
running the tests again:

```terminal-silent
php bin/phpunit
```

Explosion!

> Duplicate entry `cheeseplease@example.com`

coming from the database. *The* most annoying thing about functional tests is
that you need to control what's in the database... including what might be
"left over" in the database from a previous test. This is nothing specific to
API Platform... though the API Platform team *does* have some tools to help
with this.

Next, let's guarantee that the database is in a clean state before each test is
executed.
