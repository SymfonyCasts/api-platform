# Bootstrapping a Test Suite

Our security requirements are about to get... pretty complicated. And so, instead
of testing all of that manually and hoping we don't break anything, I think it's
time to take a few minutes to get a nice functional testing system set up.

Spin over to your terminal and run:

```terminal
composer require test --dev
```

This will download Symfony's test-pack, which most importantly comes with the
PHPUnit bridge: a small Symfony component that wraps around PHPUnit.

When that finishes... yep - we'll put our tests in the `tests/` directory and *run*
PHPUnit via `php bin/phpunit`. That's a bit different than if you installed
PHPUnit directly and you'll see why in a minute. That file -  `bin/phpunit` was
added by the recipe. And... if you run `git status`, you'll see that the recipe
also added a `phpunit.xml.dist` file, which holds sensible default config for
PHPUnit itself.

## Configuring the PHPUnit Version

Open that file up. One of the keys here is called `SYMFONY_PHPUNIT_VERSION`,
which at the time I recorded this is set to 7.5 in the recipe. You can leave
this or change it to the latest version of PHPUnit, which for me is 8.2

***TIP
PHPUnit 8.2 requires PHP 7.2. If you're using PHP 7.1, stay with PHPUnit 7.5.
***

[[[ code('416e2842e6') ]]]

But wait... why are we controlling the version of PHPUnit via some variable in
an XML file? Isn't PHPUnit a dependency in our `composer.json` file? Actually...
no! When you use Symfony's PHPUnit bridge, you *don't* require PHPUnit directly.
Instead, you tell *it* which version you want, and *it* downloads it in the background.

Check it out. Run:

```terminal
php bin/phpunit
```

This downloads PHPUnit in the background and installs its dependencies.
That's not really an important detail... but it might surprise you the
first time you run this. Where *did* it download PHPUnit? Again, that's not an
important detail... but I *do* like to know what's going on. In your `bin/`
directory, you'll *now* find a `.phpunit/` directory.

## Customizing the test Database

After downloading PHPUnit, that `bin/phpunit` script *does* then execute our tests...
except that we don't have any yet. Before we write our first one, let's tweak
a few other things.

The recipe added another interesting file: `.env.test`. Thanks to its name, this
file will *only* be loaded in the `test` environment... which allows *us* to create
test-specific settings. For example, I like to use a different database for my
tests so that running them doesn't mess with my development database.

[[[ code('ae2f44f22c') ]]]

Inside `.env`, copy the `DATABASE_URL` key, paste it in `.env.test` and add a little
`_test` at the end.

[[[ code('cb72af0955') ]]]

Cool, right? One of the gotchas of the `.env` system is that the `.env.local`
file is *normally* loaded last and allows us to override settings for our local
computer. That happens in *every* environment... except for `test`. In the `test`
environment, `.env` & `.env.test` are read, but *not* `.env.local`. That little
inconsistency exists so that everyone's test environment behaves the same way.

Anyways, now that our test environment knows to use a different database, let's
create that database! Run `bin/console doctrine:database:create`. But since we
want this command to execute in the `test` environment, also add `--env=test`:

```terminal-silent
php bin/console doctrine:database:create --env=test
```

Repeat that same thing with the `doctrine:schema:create` command:

```terminal-silent
php bin/console doctrine:schema:create --env=test
```

Perfect! Next, Api Platform has some *really* nice testing tools which... aren't
released yet. Boo! Well... because I'm impatient, let's backport those new tools
into our app and get our first test running.
