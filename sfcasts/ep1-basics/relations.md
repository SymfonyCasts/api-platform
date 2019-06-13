# Relating Resources

We have a cheese resource and a user resource. Let's link them together! Ok,
the *real* problem we need to solve is this: each `CheeseListing` will be "owned"
by a single user, which is something we need to set up in the database but *also*
something we need to expose in our API: when I look at a `CheeseListing` resource,
I need to know which user posted it!

## Creating the Database Relationship

Let's set up the database first. Find your terminal and run:

```terminal
php bin/console make:entity
```

Let's update the `CheeseListing` entity and add a new `owner` property.
This will be a `relation` to the `User` entity... which will be a `ManyToOne`
relationship: every `CheeseListing` has one `User`. Should this new property be
nullable in the database? Say no: *every* `CheeseListing` *must* have an `owner`
in our system.

Next, it asks a *super* important question: do we want to add a new property
to `User` so that we can access and update cheese listings on it - like
`$user->getCheeseListings()`. Doing this is optional, and there are *two* reasons
why you might want it. First, if you think writing `$user->getCheeseListings()`
in your code might be convenient, you'll want it! *Second*, when you fetch a
`User` in our API, if you want to be able to see what cheese listings this user
owns as a property in the JSON, you'll *also* want this. More on that soon.

Anyways, say yes, call the property `cheeseListings` and say no to `orphanRemoval`.
If you're not familiar with that option... then you don't need it. And... bonus!
A bit later in this tutorial, I'll show you why and when this option *is* useful.

Hit enter to finish! As usual, this did a few things: it added an `$owner`
property to `CheeseListing` along with `getOwner()` and `setOwner()` methods.
Over on `User`, it added a `$cheeseListings` property with a `getCheeseListings()`
method... but *not* a `setCheeseListings()` method. Instead, `make:entity` generated
`addCheeseListing()` and `removeCheeseListing()` methods. Those will come in handy
later.

[[[ code('be811e5079') ]]]

[[[ code('b5e24fc8a2') ]]]

Let's create the migration:

```terminal
php bin/console make:migration
```

And open that up... *just* to make sure it doesn't contain anything extra. 

[[[ code('e9e9053212') ]]]

Looks good - altering the table and setting up the foreign key. Execute that:

```terminal
php bin/console doctrine:migrations:migrate
```

Oh no! It exploded!

> Cannot add or update a child row, a foreign key constraint fails

... on the `owner_id` column of `cheese_listing`. Above the `owner` property,
we set `nullable=false`, which means that the `owner_id` column in the table
*cannot* be null. But... because our `cheese_listing` table already has some rows
in it, when we try to add that new column... it doesn't know what value to use
for the existing rows and it explodes.

It's a *classic* migration failure. If our site were already on production,
we would need to make this migration fancier by adding the new column first
as nullable, set the values, then change it to not nullable. But because we're
not there yet... we can just drop all our data and try again. Run:

```terminal
php bin/console doctrine:schema:drop --help
```

... because this has an option I can't remember. Ah, here it is: `--full-database`
will make sure we drop *every* table, *including* `migration_versions`. Run:

```terminal
php bin/console doctrine:schema:drop --full-database --force
```

*Now* we can run *every* migration to create our schema from scratch:

```terminal
php bin/console doctrine:migrations:migrate
```

Nice!

## Exposing the Relation Property

Back to work! In `CheeseListing`, we have a new property and a new getter and setter.
But because we're using normalization and denormalization groups, this new stuff
is *not* exposed in our API.

To begin with, here's the goal: when we *create* a `CheeseListing`, an API client
should be able to specify *who* the owner is. And when we read a `CheeseListing`,
we should be able to *see* who owns it. That might feel a bit weird at first:
are we *really* going to allow an API client to create a `CheeseListing` and freely
choose *who* its owner is? For now, yes: setting the owner on a cheese listing
is *no* different than setting *any* other field. Later, once we have a real
security system, we'll start locking things down so that I can't create a
`CheeseListing` and say that someone else owns it.

Anyways, to make `owner` part of our API, copy the `@Groups()` off of `$price`...
and add those above `$owner`.

[[[ code('5cb6690fdc') ]]]

Let's try it! Move over and refresh the docs. But before we look at `CheeseListing`,
let's create a `User` so we have some data to play with. I'll give this an email,
any password, a username and... Execute. Great - 201 success. Tweak the data and
create one more user.

*Now*, the moment of truth: click to create a new `CheeseListing`. Interesting...
it says that `owner` is a "string"... which *might* be surprising... aren't we
going to set this to the integer id? Let's find out. Try to sell a block of unknown
cheese for $20, and add a description.

For owner, what do we put here? Let's see... the two users we just created had
ids 2 and 1. Okay! Set owner to `1` and Execute!

Woh! It *fails* with a 400 status code!

> Expected IRI or nested document for attribute owner, integer given.

It turns out that setting owner to the id is *not* correct! Next, let's fix this,
talk more about IRIs and add a new `cheeseListings` property to our `User` API
resource.
