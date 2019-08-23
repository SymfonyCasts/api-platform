# Resetting the Database Between Tests

One of the trickiest things about functional tests is controlling the database.
In my opinion, a *perfect* world is one where the database is completely empty
at the start of each test. That would give us a completely predictable state
where we could create whatever `User` or `CheeseListing` objects we want and
know *exactly* what's in the database.

Some people prefer to go a step further and load a predictable set of "fixtures"
data before each test - like a few users and some cheese listings. That's
fine, but it's not the approach I prefer. Why? Because if we can make the database
empty, then each test is forced to create whatever data - like users or cheese
listings - that it needs. That might sound bad at first... because... that's
more work! But the end result is that each test reads like a complete story:
we can see that we get a 401 status code, then we create a user, then we log in
with the user we just created. A nice, complete story.

## Installing Alice

So... how can we empty the database before each test? There are a few answers,
but one common one you'll see in the API Platform world is called Alice. Find
your terminal and install it with:

```terminal
composer require alice --dev
```

This will install `hautelook/alice-bundle`. What does that bundle actually do?
I've talked about it a few times in the past on SymfonyCasts: it allows you to
specify fixtures data via YAML. It's really fun actually and has some nice features
for quickly creating a set of objects, using random data and linking objects
to each other. It was the inspiration behind a fixture class that we created and
used in our [Symfony Doctrine](https://symfonycasts.com/screencast/symfony-doctrine/fixtures)
tutorial. The recipe creates a `fixtures/` directory for the YAML files and a new
command for loading that data.

## The ReloadDatabaseTrait

But... what does any of that have to do with testing? Nothing! It's just a way
to load fixture data and you can use it... or not use it. But AliceBundle has
an *extra*, unrelated, feature that helps manage your database in the test environment.

Back in our test class... once PHPStorm finishes reindexing we're going to use
a new trait: `use ReloadDatabaseTrait`.

[[[ code('d96699a06f') ]]]

That's it! *Just* by having this, before each test method is called, the trait
will handle emptying our database tables and reloading our Alice YAML fixtures...
which of course we don't have. So, it'll *just* empty the database.

Try it!

```terminal
php bin/phpunit
```

We see even *more* deprecation warnings now - the AliceBundle has a few deprecations
it needs to take care of - but it works! We can run it over and over again... it
*always* passes because the database *always* starts empty. This is a *huge* step
towards having dependable, readable tests.

## Removing the Logging Output

While we're here, we're getting this odd log output at the top of our tests. I
can tell that this is coming from Symfony... it's almost like each time an "error"
log is triggered in our code, it's being printed here. That's... ok... but why?
Symfony *normally* stores log files in `var/logs/test.log`, for the `test`
environment.

The answer is... because we never installed a logger! Internally, Symfony ships
with its *own* `logger` service so that if *any* other services or bundles want
to log something, it's available! But that logger is *super* simple... on purpose:
it's just meant as a fallback logger if nothing better is installed. Instead of
writing to a file, it logs errors to `stderr`... which basically means they get
printed to the screen from the command line.

Let's install a *real* logger:

```terminal
composer require logger
```

This installs Monolog. When it finishes... try running the tests again:

```terminal-silent
php bin/phpunit
```

And... no more output! *Now* the logs are stored in `var/logs/test.log`. If you
tail that file...

```terminal-silent
tail var/logs/test.log
```

there it is!

Next, I want to make *one* more improvement to our test suite before we get back
to talking about API Platform security. I want to create a base test class with
some helper methods that will enable us to move fast and write clean code in our
tests.
