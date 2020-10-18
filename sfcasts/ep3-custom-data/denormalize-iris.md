# Input DTO: Denormalizing IRI Strings

Status update: we created a `CheeseListingInput` class with all the properties
and groups that we need, we've configured `CheeseListing` to use that and we've
started creating a data transformer that will take the `CheeseListingInput` and
convert it into a `CheeseListing` when somebody sends JSON to a POST or PUT
endpoint.

## supportsTransformation()

In `supportsTransformation()`, we started by dumping `$data`, `$to` and `$context`.
When we look over here in a profiler... it's *slightly* different than what we
saw with our output transformer. This time, the data is an *array*: it's the
decoded JSON that we sent. The `$to` is the *target* class - `CheeseListing` -
and the `$context` is *also* interesting: it has an `input` key with class set
to `CheeseListingInput`.

Let's use this to fill in `supportsTransformation`. Start by checking if `$data`
is an `instanceOf CheeseListing`, which in our dump, it is *not*, then return
`false`. This would mean that the object has *already* been transformed.

To be honest, I'm not sure if this is needed... but it's shown on the docs. There
might be some case where the transformer is called multiple times.

The *real* important part is the `return` down here: we support this transformation
if `$to = CheeseListing::class` *and* `$context['input']['class']` - or `null` if
it's not set - equals `CheeseListingInput::class`.

The first part makes sense: we want to return true if we are converting *into* a
`CheeseListing`. And the second part isn't needed in *our* app because we know
that `CheeseListing` will *always* use `CheeseListingInput` as its input class.
But since you can have different input classes on different operations, the
`$context` tells us which input class should be used for *this* operation. We're
using that to double-check.

Anyways, let's see if this is working! Dump all three arguments again inside of
`transform()`. And, to avoid an error, return an empty `CheeseListing` object
at the bottom.

Now spin over to the browser - I'll leave the profiler open - and hit "Execute"
to try it again. Let's see:  it's the same 400 validation error as before...
which makes sense! We're returning a new, empty `CheeseListing`.

Back on the profiler tab, hit "Latest", which should take is to the profiler for
the *latest* request. Yep! This dump is coming from line 13. This *proves* our
`supportsTransformation` is working.

Let's rename `$object` to `$input` and add some PHPDoc: we know this will be a
`CheeseListingInput` object: the result of deserializing the JSON.

So... now our job is simple, right? Take this `CheeseListingInput` object, move
the data over onto a `CheeseListing` and return it. What could go wrong?

## Denormalizing IRIs into Objects

But... wait a second: check out the dump again in the profiler. The JSON *has*
been deserialized into this `CheeseListingInput` object, which is *cool*, but
look at the `owner` field. It's a string! I mean... that makes sense... as we
*are* passing this string in our JSON. But... does that mean we're supposed to
query for the `User` object manually using this string?

Nope! One of the jobs of the deserializer is to take each piece of data in the
JSON that we're sending and figure out what *type* it should be, like a string
`DateTime` object or `User` object. Then it does whatever work is needed to change
the raw data into that type - like creating a `DateTime` object from a date string.

And... *fortunately* API Platform comes with an `ItemNormalizer` whose job is to
change IRI strings into *objects* by querying the database... or... more accurately,
via the item data provider.

So why isn't that happening here? Why isn't the IRI string being changed into the
`User` object? Check out the `ClassListingInput` class and look at the `owner`
property. The the deserializer has *no* idea that this property is supposed to
hold a `User` object! So it doesn't know to work its magic!

Let's help it: above this at, `@var User`.

Head back to the browser, hit Execute and... once that finishes, go to the
Profiler, hit Latest and tada! The `owner` is now a `User` object! Adding proper
types to your properties is *very* important for deserialization.

Next, there's a small bug in the documentation that we actually just found a
way to work around. Let's see what it is and finish our data transformer.
