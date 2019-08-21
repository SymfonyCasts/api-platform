# Api Tests & Assertions

Time to test our API! When someone uses our API for real, they'll use some sort
of HTTP client - whether it be in JavaScript, PHP, Python, whatever. So, no surprise
that to *test* our API, we'll do the exact same thing. Create a client object with
`$client = self::createClient()`.

[[[ code('147feb58bc') ]]]

This creates a, sort of, "fake" client, which is another feature that comes from
the API Platform test classes. I say "fake" client because instead of making
*real* HTTP requests to our domain, it makes them directly into our Symfony
app via PHP... which just makes life a bit easier. And, side note, this `$client`
object has the same interface as Symfony's new http-client component. So if you
like how this works, next time you need to make real HTTP requests in PHP, try
installing `symfony/http-client` instead of Guzzle.

## Making Requests

Let's do this! Make a request with `$client->request()`: make a `POST`
request to `/api/cheeses`.

How nice is that? We're going to focus our tests *mostly* on asserting security
stuff. Because we haven't logged in, this request will *not* be authenticated...
and so our access control rules *should* block access. Since we're *anonymous*,
that should result in a 401 status code. Let's assert that!
`$this->assertResponseStatusCodeSame(401)`.

[[[ code('820b4ba896') ]]]

That assertion is *not* part of PHPUnit: we get that - and a bunch of other nice
assertions - from API Platform's test classes.

Let's try this! Run the test:

```terminal
php bin/phpunit
```

## Deprecation Warnings?

Oh, interesting. At the bottom, we see deprecation warnings! This is a feature
of the PHPUnit bridge: if our tests cause deprecated code to be executed, it prints
those details after running the tests. These deprecations are coming from API Platform
itself. They're already fixed in the next version of API Platform... so it's
nothing we need to worry about. The warnings *are* a bit annoying... but we'll
ignore them.

## Missing symfony/http-client

Above all this stuff... oh... interesting. It died with

> Call to undefined method: Client::prepareRequest()

What's going on here? Well... we're missing a dependency. Run

```terminal
composer require symfony/http-client
```

API Platform's testing tools depend on this library. That "undefined" method
is a pretty terrible error...it wasn't obvious at all how we should fix this.
But there's already an issue on API Platform's issue tracker to throw a more
*clear* error in this situation. It should say:

> Hey! If you want to use the testing tools, please run
> `composer require symfony/http-client`

That's what we did! I also could have added the `--dev` flag... since we *only*
need this for our tests... but because I might need to use the `http-client`
component later inside my actual app, I chose to leave it off.

Ok, let's try those tests again:

```terminal-silent
php bin/phpunit
```

## Content-Type Header

Oooh, it failed! The response contains an error! Oh...cool - we automatically
get a nice view of that failed response. We're getting back a

> 406 Not acceptable

In the body... reading the error in JSON... is not so easy... but... let's see,
here it is:

> The content-type `application/x-www-form-urlencoded` is not supported.

We talked about this earlier! When we used the Axios library in JavaScript, I
mentioned that when you POST data, there are two "main" ways to format the data
in the request. The first way, and the way that *most* HTTP clients use by default,
is to send in a format called `application/x-www-form-urlencoded`. Your browser
sends data in this format when you submit a form. The second format - and the
one that Axios uses by default - is to send the data as JSON.

Right now... well... we're not actually sending *any* data with this request.
But if we *did* send some data, by default, this client object would format that
data as `application/x-www-form-urlencoded`. And... looking at our API docs,
our API expects data as JSON.

So even though we're not sending any data yet, the client is already sending a
`Content-Type` header set to `application/x-www-form-urlencoded`. API Platform
reads this and says:

> Woh, woh woh! You're trying to send me data in the wrong format! 406 status
> code to you!

The most straightforward way to fix this is to *change* that header. Add a third
argument - an options array - with a `headers` option to another array, and
`Content-Type` set to `application/json`.

[[[ code('1983fb3aaa') ]]]

Ok, try the tests again:

```terminal-silent
php bin/phpunit
```

This time... `400 Bad Request`. Progress! Down below... we see there was a syntax
error coming from some `JsonDecode` class. Of course! We're *saying* that we're sending
JSON data... but we're actually sending *no* data. Any empty string is technically
invalid JSON.

Add another key to the options array: `json` set to an empty array.

[[[ code('bcc9cdb2f7') ]]]

This is a really nice option: we pass it an array, and then the client will
automatically `json_encode` that for us and send that as the *body* of the request.
It gives us behavior similar to Axios. We're not sending any data yet... because
we shouldn't have to: we should be denied access *before* validation is executed.

Let's try that next! We'll also talk about a security "gotcha" then finish this
test by creating a user and logging in.
