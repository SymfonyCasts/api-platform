# DTO Input Initializer Logic

The end goal is that this `$input` argument will be the `CheeseListingInput` object
that we create in our denormalizer... which eventually will be pre-filled with
data from the database.

Let's `dump($input)` so that, once this ready, we can *see* if we've accomplished
that.

## Calling the Core Denormalizer System

As I mentioned earlier, as soon as we created this class and filled in
`supportsDenormalization()`, we are now 100% responsible for denormalizing
`CheeseListingInput` objects... which means we actually *do* need to somehow
do the work of taking the array of data from the JSON, putting it onto the
`CheeseListingInput` and returning it.

But... we don't *really* want to do that work... because that's what the normal
denormalizer system does! Instead, we want to call the normal system, but pass
in *our* modified `$context`.

We actually had this same situation in an earlier tutorial with `UserNormalizer`
and we handled it with a - kind of - elaborate solution that allowed us to inject
the entire normalizer system, call `normalize()` again use a flag on the `$context`
to avoid recursion.

The *most* correct solution in `CheeseListingInputDenormalizer` would be to do
that same thing. But... I'm going to cheat. Deserialization is a bit simpler
than serialization... and we can get away with inject the specific, *one* denormalizer
that I know we need instead of injecting the *entire* denormalizer system and trying
to avoid recursion.

The denormalizer we need is called `ObjectNormalizer` and it's autowireable. On
top, create public function `__construct()` with `ObjectNormalizer` - make sure
you get the one from Symfony - `$objectNormalizer`. Then I'll hit Alt + Enter and
go to "Initialize properties" to create that property and set it.

Now, down in our code, return `$this->objectNormalizer->denormalize()` and pass it
the `$data` - because we still want to denormalize same array of data - `$type`
`$format`, but now pass it our shiny *new* `$context` so that *when* it denormalizes,
it will use our `$dto` instead of creating a new one.

Ok, let's try it! Hit "Execute" again. We still get a 500 error... which makes sense
because we haven't finished our job yet. What I want to see is if the dump worked:
I want to see if the `CheeseListingInput` object we created in the denormalizer
is now being passed into the `transform()` method.

Go over to the other profiler tab and hit "Latest". And... there it is! Oh, wait.
Ryan, you dummy! I should have done take off the `title` property from the JSON
so that we can see if the `title` that we're setting in the denormalizer is what
we see in the dump(). Try it again... 500 error... hit latest and... yes! *This*
is what we were looking for! It proves that *we* are now in control of creating
the `CheeseListingInput` object. We create the object, then, when the deserializer
does its job, it either leaves the property alone if we didn't send that field or
it overrides it if the user *did* send that field.

*Now* we are dangerous. Inside `CheeseListingInputDataTransformer`, once we've
finished pre-populating the data in the denormalizer, we will be able to safely
transfer *every* property onto `CheeseListing` because if a field was *not*
sent in the JSON, it will match what's already in the database.

## OBJECT_TO_POPULATE, CheeseListing & CheeseListingInput

By the way, if you're wondering why we can override `OBJECT_TO_POPULATE` to a
`CheeseListingInput` inside the denormalizer... but then `OBJECT_TO_POPULATE`
is supposed to still be a `CheeseListing` object inside of our data transformer...
that's... a good thing to wonder!

In reality, denormalizing into the input object and calling the data transformer
are two completely separate processes. And the two `$context` arrays - while nearly
identical - are two separate arrays in memory. So even though we modify the
`$context` inside the denormalizer, that does *not* modify the `$context` that's
passed to the data transformer.

Anyways, our last job is to finish the denormalizer: to populate the
`CheeseListingInput` with the data from the database. And... since this is pretty
boring, at the bottom, I'll paste in a new private function. You can copy this
from the code block on this page. Re-type the end of `CheeseListing` to get that
`use` statement.

This checks the `object_to_populate` key - I should probably use my constant there
instead - to see if there is an existing entity, which would happen on an update
operation. If there is *no* entity, which means this is a create operation, it
returns an empty DTO. But if there *is* a `CheeseListing`, it uses it to
pre-populate all the fields on the DTO.

Back up in `denormalize`, delete the new DTO stuff and say
`$context[AbstractNormalizer::OBJECT_TO_POPULATE]` equals `$this->createDto()`
and pass `$context`.

Let's try this! Back at the browser, go to the docs tab... and hit Execute. Let's
see... yes! It works! Price 5000!

We're still missing a few pieces related with validation, but we'll talk about
that soon.

Before we do, we currently have code for converting to and from entity and DTO
objects *all* of the place. Next, it's cleanup time!
