# DTO Input "Initializer"

With an input DTO, the update process works like this. First ApiPlatform queries
for the `CheeseListing` entity. Second, the JSON is deserialized into a new
`CheeseListingInput` object. And third, our `transform()` method is called,
where we *take* that `CheeseListingInput` object's data and move it onto the
`CheeseListing`. To get the `CheeseListing` that was queried from the database,
we grab it from the `$context`.

The problem with this process is step 2. Because the deserializer always creates
a *new* `CheeseListingInput` with no data before putting the JSON data onto it,
if a property on the input is `null`, we don't know if it's `null` because the
user actually *sent* `null` for that field... or if the user simply *didn't* send
the field at all and we should ignore it.

To fix this, before step 2, we need to somehow prepare a `CheeseListingInput`
object that's populated with the current data from the database and tell the
serializer to deserialize the JSON onto *that* object instead of creating a
new one. If we did that, it would be safe to set everything from the input object
back onto the `CheeseListing` because if a field was *not* sent, we would
just be setting it to the original value from the database.

In ApiPlatform 2.6, you'll be able to do this via a new data transformer initializer.
Since that's not released yet, we'll do it ourselves.

## Custom Normalizer for the Input Class

How? We know that if you set the `OBJECT_TO_POPULATE` key on the `$context`, then
the deserializer will use *that* object instead of creating a new one. By leveraging
a custom denormalizer, we could hook into the denormalization process and set
the `OBJECT_TO_POPULATE` key to a pre-populated `CheeseListingInput` object
*right* before the JSON is deserialized.

If... that doesn't make sense yet, it's okay. Let's step through it piece by piece.

To start, in the `src/Serializer/Normalizer/` directory, create a new
`CheeseListingInputDenormalizer` class. This will be responsible for
*denormalizing* `CheeseListingInput` objects. Make it implement
`DenormalizerInterface` and also `CacheableSupportsMethodInterface`, which is a
performance thing. Then go to Code -> Generate - or Command + N a Mac - and select
"Implement Methods" to generate the three methods we need. I'll move
`hasCacheableSupportsMethod()` to the bottom because it's the least important.

As *soon* as we created this class, because it implements `DenormalizerInterface`,
the serializer will call `supportsDenormalization()` on *every* single piece of
data during denormalization. In `supportsDenormalization()`, we support
denormalizing a piece of data if its `$type` equals `CheeseListingInput::class`.

Thanks to this, we are now 100% responsible for denormalizing `CheeseListingInput`.
objects. Down in `hasCacheableSupportsMethod()` return true, which you should do
unless your `supportsDenormalization()` method uses the `$context` to make its
decision.

## The OBJECT_TO_POPULATE during Input Denormalization

Anyways, the serializer will now call `denormalize()` whenever it's trying to
denormalize a `CheeseListingInput`. Let's `dump()` the `$context` - that's the
last argument - so we can see what it looks like.

This won't work yet, but let's see what that dump looks like. At the browser -
this is the `put` operation - hit "Execute". And... error!

> Expected denormalized input to be an object

It's complaining because we're not returning anything from our `denormalize()`
method yet... but we *can* check out the dump. In another tab, I already have my
profiler open, click Latest. This takes me to the exception section. Go down
and click to open the Debug section.

Nice! This is the `$context` that's being passed to `denormalize`. And check this
out: it has an `object_to_populate` key set to the `CheeseListing` object. Well,
really, that should be no surprise: we saw that a few minutes ago. Inside our
data transformer, `OBJECT_TO_POPULATE` is set to the existing `CheeseListing`
object.

But... now that I think about it... the fact that this is set to a `CheeseListing`
object is kind of odd... because this process will ultimately deserialize the JSON
into a `CheeseListingInput` object. How does that work?

Internally, the serializer has a sanity check: if the `OBJECT_TO_POPULATE` is
*not* the same *type* as the object we're deserializing into, then it's ignored.
That's what's happening now: API Platform sets the existing `CheeseListing` onto
`OBJECT_TO_POPULATE`, but since we're not deserializing into that type of object,
it's ignored and a new `CheeseListingInput` is created.

But... we could *change* that key.

## Setting OBJECT_TO_POPULATE to the Input DTO

Inside the denormalizer, let's start with something simple:
`$dto = new CheeseListingInput()` and `$dto->title =` some hardcoded title.
Set *this* onto the context:
`$context[AbstractItemNormalizer::OBJECT_TO_POPULATE]` equals `$dto`.

We're not done yet... but if we passed *this* `$context` into the denormalizer
system, then it *should* deserialize the JSON onto our new object. And, whatever
we return from this method will eventually be passed as the `$input` to our data
transformer.

So next, let's finish this: by calling the denormalizer system to update the
`CheeseListingInput`, returning it from here, proving that it's passed to the
data transformer, then finally pre-filling it with database data. If you can't
see how all the pieces connect yet, you will soon.
