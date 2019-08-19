# Backport the API Platform 2.5 Test Tools

To test our API, we're going to use PHPUnit and some other tools to make a request
into our API, get back a response, and assert different things about it, like the
status code, that the response is JSON and that the JSON has the right keys.

API platform recently introduced some really nice tools for doing all this. In fact
it was *so* recent that... they're not released yet! When I run:

```terminal
composer show api-platform/core
```

...you can see that I'm using Api Platform 2.4.5. These new features will come out
in version 2.5. So if you're already on version 2.5, you can skip to the end of this
video where we bootstrap our first test.

But if you're with me on 2.4.5... welcome! We're going to do some hacking and backport
those features manually into our app. If you downloaded the course code, you should
have a `tutorial/` directory inside. If you don't have this, try downloading
the code again to get it.

## Copying the new Test Classes

See this `Test/` directory? This contains a copy of all of those new testing classes.
The only difference is that I've changed their namespaces to use
`App\ApiPlatform\Test` instead of what they look like inside of ApiPlatform:
`ApiPlatform\Core\Bridge\Symfony\Bundle\Test`.

That's because we're going to move *all* of this directly into our `src/`
directory. Start by creating a new `ApiPlatform/` directory... and then copy
`Test/` and paste it there.

I'm also going to right click on the `tutorial/` directory and say "Mark as Excluded".
That tells PhpStorm to *ignore* those files... which is important so it doesn't
get confused by seeing the same classes in two places.

## Registering the new Test Client Service

Beyond the new classes, we need to make one other change. Open up `config/`
and create a new file: `services_test.yaml`. Thanks to its name, this file will
*only* be loaded in the `test` environment. Inside, add
`services:` and create a new service called `test.api_platform.client:` with
`class: App\ApiPlatform\Test\Client` and `arguments: ['@test.client']`. Also add
`public: true`.

[[[ code('ecef4a0542') ]]]

These two steps completely replicate what ApiPlatform will give you in version 2.5.
Well... unless they change something. I don't normally show unreleased features...
because they might change... but these tools are *so* useful, I just *had* to
include them. When 2.5 *does* come out, there could be a few differences.

## Bootstrapping the First Test

Ok, let's create our first test! Inside the `tests/` directory, create a
`functional/` directory and then a new class: `CheeseListingResourceTest`.

This isn't an official convention, but because we're *functionally* testing our
API... and because everything in API Platform is based on the API resource,
it makes sense to create a test class for each resource: we test the `CheeseListing`
resource via `CheeseListingResourceTest`.

Make this extend `ApiTestCase` - that's one of the new classes we just moved into
our app. If you're using API Platform 2.5, the namespace will be totally different -
it'll start with `ApiPlatform\Core`.

[[[ code('8d0bd36911') ]]]

The first thing I want to test is the POST operation - the operation that creates
a new `CheeseListing`. A few minutes ago, under `collectionOperations`, we added
an access control that made it so that you *must* be logged into use this. Oh...
and I duplicated the `collectionOperations` key too! The second one is overriding
the first... so let's remove the extra one.

Anyways, for our first test, I want to make sure this security *is* working.
Add `public function testCreateCheeseListing()`. And inside, make sure this all
isn't an elaborate dream with `$this->assertEquals(42, 42)`.

[[[ code('4ca7ed3bf9') ]]]

Ok! Run that with:

```terminal
php bin/phpunit
```

We're alive! Next, let's turn this into a *true* functional test against our API!
We'll also take care of some other details to make our tests shine.
