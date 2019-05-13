# Our First ApiResource

Question: have you ever gone to the store and accidentally bought *too* much
cheese? Or maybe you have the opposite problem: you're hosting a big party and
you don't have *enough* cheese! This is our new billion dollar idea: a platform
where you can sell that extra chunk of Brie you never finished or buy a truck-load
of camembert from someone who go a little too excited at the cheese market. Yep,
it's what the world is asking for: a peer-to-peer cheese marketplace. We're calling
it: Cheese Whiz.

For the site, maybe we're going to make it an single-page application built in
React or Vue... or maybe it'll be a little more traditional with *some* JavaScript
in some parts. And maybe we'll even have a mobile app. It doesn't really matter
because the point is this: we need to expose our core functionality via an API.

## Generating the Entity

But to start: *forget* about the API and pretend like this is a normal, boring
Symfony project. Steps 1 is... hmm, probably do create some database entities.

Let's open up our `.env` file and tweak the `DATABASE_URL`. My computer uses
`root` with no password... and how about `cheese_whiz` for the database name.
You can also create a `.env.local` file and override `DATABASE_URL` there. Using
`root` and no password is pretty standard, so I like to add this to `.env` and
commit this as the default.

Cool! Next, at your terminal, run

```terminal
composer require maker --dev
```

to get Symfony's MakerBundle... so that we can be lazy and generate our entity.
When that finishes, run:

```terminal
php bin/console make:entity
```

Call the first entity: `CheeseListing`, which will will represent each "cheese"
that's for sale on the site. Hit enter and... oh! It's asking:

> Mark this class as an API Platform resource?

MakerBundle asks this *because* it noticed that API Platform is installed. Say
"yes". And before we add *any* fields, let's go see what this did. In my edit,r
yep! This created the usual `CheeseListing` and our `CheeseListingRepository`.
Nothing special there. Right now, the only property it has is `id`. So, what
did answering "yes" to the API Platform resource question give us? This tiny
annotation right here. The *real* question is: what does this give us? We'll
see that soon.

But first, let's add some fields. Let's see, each cheese listing probably needs
a `title`, `string`, `255`, not nullable, a `description`, which will be a big
text field, `price`, which I'll make an `integer` - this will be the price in
*cents* - so $10 would be 1000, `createdAt` as a `datetime` and an `isPublished`
boolean. Um... that's a good start! Hit enter to finish.

Congratulations! We have a *perfectly* boring `CheeseEntity` class: 7 properties
with getters and setters. Next, like always, generate the migration with:

```terminal
php bin/console make:migration
```

Oh! Migrations isn't installed yet! No problem - follow the recommendation:

```terminal
composer require migrations
```

But before we try generating it again, make sure you've created the database:

```terminal
php bin/console doctrine:database:create
```

And *now* run `make:migration`:

```terminal-silent
php bin/console make:migration
```

Let's go check that out to make sure there aren't any nasty surprises:

> CREATE TABLE cheese_listing...

yea! Looks good! Close that and run:

```terminal
php bin/console doctrine:migrations:migrate
```

## Say Hello to your API!

Brilliant! At this point, we have a *completely* traditional Doctrine entity
*except* for this one, `@ApiResource()` annotation. But *that* changes everything.
This tells API Platform that you want to *expose* this class as an API. What does
that mean *exactly*?

Refresh the `/api` page. Woh. Suddenly this saying that we have *five* new endpoints!
A `GET` endpoint to retrieve a collection of CheeseListings, a `POST` endpoint to
create a new one, `GET` to retrieve a single `CheeseListing`, `DELETE` to... ya
know... delete and `PUT` to update an existing `CheeseListing`.

And this isn't just documentation: these new endpoints *already* work. Let's
check them out next, say hello to something called JSON-LD and learn a bit about
how this magic works behind the scenes.