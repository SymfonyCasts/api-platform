# Test Setup

Coming soon...

Because our security requirements are about to get more complicated. Instead of just
continuing to test things manly. With our interface here, I want to bootstrap a
little bit of a test suite and our application using funk functional tests to test
your API is just one of the absolute best use cases, so spin over to your terminal
and run 

```terminal
composer require test --dev
```

 up that almost all Sudanese test pack,
which comes with the simple PHP unit bridge, which is a little wrapper around PHP
unit and it also installs a couple of other things. Perfect.

as you see down here, we'll put the test and the test directory and then actually run
PHP and or on `php bin/phpunit`. That was a new file that was actually added by the
recipe because simple PHP unit is a little wrapper around PHP unit. It's also added a
couple of other files, so if you're on get status, you can see of course the updated
the standard files and I added a `phpunit.xml.dist` file which holds
some configuration about PHP unit itself. One of the things that you may not may or
may not realize is that one of the keys in here is actually says which version of PHP
want to use at this moment. It's a 7.5 um, you can leave that alone or if you want to
use the absolute latest version of PHP and you can use 8.2 the reason we control this
here, and you might expect us to control this maybe in `composer.json`, usually if you
install a PHP unit, you'd have some entry inside of here that says what version of
PHP PHP unit you're using. But with the simple PHP in a library that was installed,
the way you actually run it is by saying `bin/phpunit`.

```terminal-silent
php bin/phpunit
```

Yeah,

and in the background, this calls the simple being at library and it installs PHP and
if for you it's not really that important in detail, it's just that it installs PHP
in it in an isolated environment so that it doesn't clash with any of your libraries
dependencies. You can actually see this in your `bin/` Directory. You'll suddenly have a
little  `.phpunit/` directory. And that literally just a

downloaded it right there. So that's actually what it's executing. So right now as
you can see it, we don't have any tests graded. Of course. Uh, before we write our
first test, I'm actually gonna set up a couple of other things. One of the other
files that the recipe and it was `.env.test`. This is a file that's
obviously, that's only going to be read in the test environment. So we can make tests
specific changes here. So one of the ones that I'm going to make is I'm actually
going to copy my daddy database URL from `.env` and paste it in here and add a
little `_test` on the bottom. So now I'm using a database that's specific to my test
environment.

Now what are the Gotchas? Um, then I watched you to realize that when you're testing
and Symfony is if you have a `.env.local` file. I don't have one, but if you have
one that's actually not read in a test environment, you'll need to put all of your
test environment specific overrides into `.env.test`. All right, so now that
we have this, let's bootstrap that database. So except 

```terminal
php bin/console doctrine:database:create --env=test
```

But since we want that to happen in the test environment, we'll do
`--env=test` and then I will rerun that with that can `schema:create`

```terminal-silent
php bin/console doctrine:schema:create --env=test
```
 
to get the dirt Davies Schema. Perfect. Um, now when we do function on tests or the API
platform, what we're basically going to be doing is making real http API requests to
our API, getting back a response and asserting different things about it, like the
status code that the response is JSON and the, that JSON has the right keys, API
platform has some really nice tools for doing this. But as at the moment of
this tutorial, they're not released yet. They will come out in API Platform 2.5 and
right now we are using API platform.

Okay.

Hey back off from 2.4.5 you can see if you're on 

```terminal
composer show api-platform/core
```

So if you're using version 2.5 you don't need to do the next few steps.
Cause what I'm gonna do is I've actually, if you downloaded the course code, then you
should have a `tutorial/` directory inside of your application. If you don't have this,
you can just download the course code and steal it from there instead of your Ivan
`Test/` Directory. And this is actually all of the new um, testing functions, a features
from a platform version 2.5. The only difference is that internally I've changed all
their namespaces to be using `App\` instead of the normal `ApiPlatform\Core\Test`.
And that's because we are going to in our `src/` directory create a,

a `ApiPlatform/` directory. And then I am going to copy that `Test/` directory and paste
it into there. So I'm basically backporting all this into our, our code. I'm also
going to right click on my `tutorial/` directory and say mark has excluded just Oh PHP
storm doesn't get confused when it sees classes in both places. And then the one
other change that you need to make a to backport those changes is in your `config/`
directory. Ron Ace creative `services_test.yaml` file inside hearsay
`services:` and then say `test.api_platform.client:` that

`class: App\ApiPlatform\Test\Client`, `arguments: ['@test.client']`

and say `public: true`. So those two steps, they're creating this 
`services_test.yaml` file and copying things into your source API Bot from `Test/` directory, not
something you'll need to do on API platform 2.5 because that's already done for you
in the core. We're just trying to hack this functionality back so that we can do it.
I don't love doing this because this functionality could change before it's actually
released, but these tools are so useful that I really want to show how to use them so
this is more realistic. All right, finally, we're ready to create our tests. Some of
the `tests/` directory. Let's create a `functional/` directory for these functional tests
and it's not there. I'm going to create a `CheeseListingResourceTest` class.

So I'm kind of falling in convention here that is made, it's not official, but I
think it makes sense to create my functional tests and the functional directory. And
then what you're really doing here is you're functionally testing your resources. So
my `CheeseListing` resource, that's why I've named a `CheeseListingResourceTest`. Make
this extend `ApiTestCase`. And again, if you're using API platform from 2.5 you should
find AP test case in here but it'll have a different namespace because it's coming
from `ApiPlatform\Core` and just to see if things are working

Now the first test that we actually want to do here is I'm actually going to test

that the uh, post and point for creating a new cheese listing works and maybe also
that you actually need to be logged in in order to use that. Cause you'll remember in
the last chapter, one of the things that we did is under our collection operations,
we actually added a `"post" = { "access_control"="is_granted('ROLE_USER')" }` . And
actually when we did this collection operations here, I should have put it up here, I
actually have it duplicated. So our collection operations down there is overriding
this one up here. That was a mistake on my part. I'll delete that. Extra collection
operations. That was just being overwritten down here anyways.

Okay,

so names, we want to test this post end point. So instead of here, I'm going to say
`public function testCreateCheeseListing` and to make sure things are working,
we'll say `$this->assertEquals(42, 42)` and we should be good. So football weren't run that with

```terminal
php bin/phpunit
```

 and we've got it. All right, so this is the basic
test setup. Next, let's actually turn this into a functional test. Let's learn about
a couple other things that we need to get set up to get this really working well.