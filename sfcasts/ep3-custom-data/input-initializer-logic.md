# DTO Input Initializer Logic

The end goal is that this `$input` argument will be the `CheeseListingInput` object
that we create in our denormalizer... which eventually will be pre-filled with
data from the database.

Let's `dump($input)` so that, once this ready, we can *see* if we've accomplished
that.

## Calling the Core Denormalizer System

As I mentioned earlier, as soon as we created this class and filled in
`supportsDenormalization()`, we became 100% responsible for denormalizing
`CheeseListingInput` objects... which means we actually *do* need to somehow
take the array of data from the JSON, put it onto the `CheeseListingInput`
and return it!

But... pfff. We don't *really* want to do all that hard work... because that's
what the normal denormalizer system does. Nope, let's just *call* the normal
system, but pass in *our* modified `$context`.

We actually had this same situation in an earlier tutorial with `UserNormalizer`
and we handled it with a - kind of - elaborate solution that allowed us to inject
the entire normalizer system, call `normalize()` again and use a flag on the
`$context` to avoid recursion.

The *most* correct solution in `CheeseListingInputDenormalizer` would be to do
that same thing. But... I'm going to cheat. Denormalization is a bit simpler
than normalization... and we can get away with injecting the specific, *one*
denormalizer that I know we need instead of injecting the *entire* denormalizer
system and trying to avoid recursion.

The denormalizer we need is called `ObjectNormalizer` and it's autowireable. On
top, create public function `__construct()` with `ObjectNormalizer` - make sure
you get the one from Symfony - `$objectNormalizer`. I'll hit Alt + Enter and
go to "Initialize properties" to create that property and set it.

Now, down in our code, return `$this->objectNormalizer->denormalize()` and pass it
the `$data` - because we still want to denormalize the same array of data - `$type`
`$format`, but now pass our shiny *new* `$context` so that *when* it denormalizes,
it will *update* our `$dto` instead of creating a new one.

Ok, let's try it! Hit "Execute" again. We still get a 500 error... which makes sense
because we haven't finished our job yet. What I want to see is if the dump worked:
I want to see if the `CheeseListingInput` object we created in the denormalizer
is now being passed into the `transform()` method.

Go over to the other profiler tab and hit "Latest". And... there it is! Oh, wait.
Ryan, you dummy! I should have taken off the `title` field from the JSON
so that we can see if the `title` that we're setting in the denormalizer is what
we see in the dump(). Try it again... 500 error... hit Latest and... yes! *This*
is what we were looking for! It proves that *we* are now in control of creating
the `CheeseListingInput` object. We create the object, then, when the deserializer
does its job, it either leaves the property alone if we didn't send that field or
it *overrides* it if we *did* send that field.

*Now* we are dangerous. Inside `CheeseListingInputDataTransformer`, once we've
finished pre-populating the `$input` object from the database in the denormalizer,
we will be able to safely transfer *every* property onto `CheeseListing` because
if a field was *not* sent in the JSON, it will match what's already in the database.

## OBJECT_TO_POPULATE, CheeseListing & CheeseListingInput

By the way, if you're wondering why we can override `OBJECT_TO_POPULATE` to be a
`CheeseListingInput` inside the denormalizer... but then `OBJECT_TO_POPULATE`
is still apparently a `CheeseListing` object inside of our data transformer...
that's... a good thing to wonder!

In reality, denormalizing into the input object and calling the data transformer
are two completely separate processes. The two `$context` arrays - while nearly
identical - are two *separate* arrays in memory. So even though we modify the
`$context` inside the denormalizer, that does *not* modify the `$context` that's
passed to the data transformer.

## Pre-Populating from the Database

Anyways, let's finish the denormalizer by populating the `CheeseListingInput`
with the data from the database. And... since this is pretty boring, at the bottom,
I'll paste a new private function. You can copy this from the code block on this
page. Re-type the end of `CheeseListing` to get that `use` statement.

This checks the `object_to_populate` key - I should probably use the constant -
to see if there is an *existing* entity, which would happen for an update
operation. If there is *no* entity, which means this is a create operation, it
returns an empty DTO. But if there *is* a `CheeseListing`, it uses it to
pre-populate all the fields on the DTO.

Back up in `denormalize()`, delete the new DTO stuff and say
`$context[AbstractNormalizer::OBJECT_TO_POPULATE]` equals `$this->createDto()`
and pass `$context`.

Let's try it! Back at the browser, go to the docs tab... and hit Execute. Let's
see... yes! It works! Price 5000!

We're still missing a few pieces related to validation, but we'll talk about
those soon.

Before we do, we currently have code for converting to and from entity and DTO
objects... *all* over the place. Next, it's cleanup time!
