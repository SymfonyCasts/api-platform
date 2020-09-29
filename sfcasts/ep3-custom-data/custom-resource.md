# Completely Custom Resource

Even though they have some custom fields, both of our API resources are truly bound
to the database. Ultimately we're querying for and saving to a specific entity
like `CheeseListing` or `User`.

## Custom Fields vs Custom Resource Class

But really, an API resource can be *any* object that pulls data from... anywhere!
So instead of adding un-persisted `$isMe` and `$isMvp` fields to `User`, we could
have created a totally different non-entity class - like `UserApiResource` - with
*exactly* the fields in our API.

And, the *way* we would do that is pretty similar to what we've seen: we would need
a custom data provider and data persister. The reason we *didn't* do that is that
if your underlying data source *is* a Doctrine entity, it's much easier to add
a few custom fields than to reinvent the wheel with a custom resource. After all,
with a Doctrine entity, we get things like pagination and filtering for free!

But sometimes... you *will* need to create a completely custom ApiResource class,
maybe because the data comes from some complex queries that join across multiple
tables... or maybe the data doesn't come from the database at all!

So here's our goal: we're going to create a new `DailyStats` API resource so that
we can have an endpoint - `/api/daily-stats` - that returns a collection of items
where each one contains site stats for a single day: things like total
visitors and most popular cheese listings. And we're going to pretend that the
data for these stats do *not* come from the database.

## Creating the API Resource Class

So... let's get started! Step one: create an API resource class that looks
*exactly* how we want our API to look... regardless of how the underlying data
looks or where it's coming from.

In the `Entity/` directory, create a new class called `DailyStats`. And yes,
I'm putting this in the `Entity/` directory even though this isn't going to be
a *Doctrine* entity. That's *totally* allowed and it's up to you if you like this
or not. If you *do* want to put API Resource classes somewhere else, then in
`config/packages/api_platform.yaml`, you'll need to tweak the config to tell
API Platform to look in that new spot. Easy peasy!

Back in the new class, for simplicity - and... because this class *will* stay
very simple - I'm going to use *public* properties: `public $date`,
`public $totalVisitors` and a `public $mostPopularListings` that will hold an
array of `CheeseListing` objects. If you're using PHP 7.4, you can also add
property *types*, which will *also* help API Platform's documentation. More
on that soon. Or, if you don't want this stuff to be public, use the normal
private properties with getters and setters: whatever you want.

To *officially* make this an API Resource class, above, add `@ApiResource`.

That's it! Spin back to the browser and refresh the documentation. Say hello to
our `DailyStats` resource! No... it won't magically work yet, but it *is*
already documented.

## Customizing the Resource URL

Now, I don't mean to be picky, but I don't love this `daily_stats` URL - dashes
are much more hipster. We can fix that by adding an option to the annotation:
`shortName="daily-stats"`.

When we refresh the docs now... that did it! But we could have *also* solved this
"Ryan hates underscores" problem in a more *global* way. Search for
"API Platform operation path naming" to find a spot on their docs that talks about
how the URLs are generated.

Whenever you have an entity - like `DailyStats` - there is a process inside API
Platform that converts the word `DailyStats` to `daily_stats` or `daily-stats`.
That's called the `path_segment_name_generator` and it's something you can customize.
It uses underscores by default, but there's a pre-built service that loves dashes.

Copy the example config. Then, remove the `shortName` option and in
`api_platform.yaml` paste that config anywhere. Back on our docs, let's try it!
Beautiful! The URL is *still* `/api/daily-stats`.

Next: let's make these operations actually *work*, starting with the
get collection operation. How do we do that? By adding a data provider... and a
few other details that are special when you working with a class that is *not*
an entity.
