# Custom Resource Data Provider

For the `DailyStats` resource, to start, I *only* need the get collection endpoint:
we'll remove all the other operations for now.

## Limiting the Operations

We can do that by saying `itemOperations={}` and `collectionOperations={}` with
`get` inside:

[[[ code('3c106229bd') ]]]

Try the docs now. Yep! An API Resource with only *one* operation. Let's try
this operation to see what *currently* happens. I'll cheat and go directly to
`/api/daily-stats.jsonld`. Huh, it "sort of" works... but the results are empty.
This is because API Platform has *no* idea how to "load" "daily stats" and so...
it just returns nothing. How can we *teach* it to load `DailyStats` objects?
With a data provider of course!

## Creating the Data Provider

Inside the `DataProvider/` directory, create a new class called
`DailyStatsProvider`:

[[[ code('957a2f7479') ]]]

Let's keep this as simple as possible: implement `CollectionDataProviderInterface`
and `RestrictedDataProviderInterface` so so that we can support *only* the
`DailyStats` class:

[[[ code('ae6b1eca98') ]]]

Next, go to "Code"->"Generate" - or `Command`+`N` - on a Mac and select
"Implement Methods" to generate the two methods that we need:

[[[ code('aadaa52223') ]]]

For supports, it's easy: return `$resourceClass === DailyStats::class`:

[[[ code('a0568ae1b2') ]]]

For `getCollection()`... we're going to *eventually* load the data from a
JSON file. But to start, let's create a dummy object: `$stats = new DailyStats()`,
`$stats->date = new \DateTime()`, `$stats->totalVisitors = 100` and
we'll leave `$mostPopularListings` empty right now:

[[[ code('d19cce27eb') ]]]

At the bottom return an array with `$stats` inside:

[[[ code('d4cc64d8f4') ]]]

### You Need a "get" item Operation

Let's try it! Move over, refresh and... it works! I'm kidding. It's *almost*
that easy... but not quite. That's a weird error though:

> No item route associated with `DailyStats`.

Here's what's happening. Remember that JSON-LD always returns an `@id` property
with the IRI for the resource. For an item, the IRI is the URL to the *item* operation:
the URL you could go to, to fetch a *single* `DailyStats` resource.

The problem in *this* situation is that, inside `DailyStats`, we've specifically
said that we don't *want* any item operations:

[[[ code('b11751cab7') ]]]

This kind of confuses API Platform, which has a brief existential crises
before saying:

> How can I generate a URL to the item operation if there aren't any! Ah!

The easiest solution is to add the `get` `itemOperation`... and we *are* going to
do that later. But let's pretend that we *don't* want to use that workaround
because we *don't* want an item operation.

The solution is... well... it's *still* an ugly workaround. I'll paste in some
config... and then I need to add a `use` statement for this `NotFoundAction` class:

[[[ code('ce9be847ee') ]]]

So... this basically says:

> I *do* want a get item operation... but if anybody goes to it, I want to
> execute the `NotFoundAction` which will rudely return a 404 response.

So there *is* an item operation... but not really.

## Adding the identifier

*Anyways*, if we try it again... our next error!

> No identifiers defined for resource `DailyStats`.

This is *much* more understandable. Every resource needs to have a unique
identifier, which is used to generate the IRI. Usually this is an ID or UUID,
which we'll talk about later in the tutorial.

To give our `DailyStats` an identifier, we *could* add a `public $id` or
`public $uuid`... but we don't need to! Why? Because the `$date` is *already* a
unique identifier: we're only going to have one `DailyStats` per day:

[[[ code('e1c34a5fc5') ]]]

How do we tell API Platform to use this property as the identifier? In Doctrine,
it happens automatically thanks to the `@ORM\Id` annotation:

[[[ code('9dede90739') ]]]

When you're not in an entity - or if you want to use a field *other* than
the database id in an entity - you can add `@ApiProperty()` with `identifier=true`:

[[[ code('b4a75c49bd') ]]]

Cool! Let's refresh and celebrate! With... another error:

> `DateTime` could not be converted to string

Oh, duh! API Platform needs to convert our identifier into a *string*.
Since this is a `Datetime` object... that doesn't work.

And... that's ok! I have a better idea. Create a new
`public function getDateString()` that returns a `string`. Inside, return
`$this->date->format('Y-m-d')`:

[[[ code('c9711e7035') ]]]

Then, take the `ApiProperty` annotation and move it down here:

[[[ code('acb211e5a4') ]]]

Yea! That's allowed and it's *perfect*: our identifier is the *string* date.

Try it! And... oh! Boo - that's a Ryan error. I'll fix the typo and then...
victory! A *beautiful* JSON-LD Hydra response with one item inside. And check
out the `@id`: `/api/daily-stats/2020-09-17`. That's awesome!

Oh, right... but there are no actual *fields*. Let's fix that next and, more
importantly, look more closely at our docs. When your class is *not* an entity,
you need to do more work so that API Platform knows what *type* each field is.
And this is more than just for documentation: types affect how your API *behaves*.
