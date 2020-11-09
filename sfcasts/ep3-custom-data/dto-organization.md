# DTO Class Organization

It took some work - especially getting the update to work before API Platform 2.6 -
but our input & output DTO system is alive! Though... our logic for converting
from `CheeseListing` to input, input to `CheeseListing` and `CheeseListing` to
output is... not super organized. This code is all over the place. We can do
better.

There's no right or wrong way to organize this kind of data transformation code,
but let's see what we can figure out. Start in `CheeseListingInputDataTransformer`:
this is where we go from `CheeseListingInput` to `CheeseListing`. I'm going to put
all of this transformation code into the DTO classes themselves... because
that's really their job: to be a helper class for data as it goes from one place
to another.

## CheeseListingInput::createOrUpdateEntity

In `CheeseListingInput` create a new public function - `createOrUpdateEntity()` -
with a nullable `?CheeseListing $cheeseListing` argument and this will return a
`CheeseListing`:

[[[ code('6f42720a12') ]]]

The reason this is *nullable* is because, inside the data transformer, we may
or may *not* have an existing `CheeseListing`.

Start inside of `CheeseListingInput` with a check for that: if *not*
`$cheeseListing`, then `$cheeseListing = new CheeseListing()`. And of course, this
is where we pass in the title, which is now `$this->title`:

[[[ code('e806e7757d') ]]]

For the rest of the logic, copy the setters from the transformer... then paste
them here. Oh, and change `$input` to `$this` on all the lines. At the bottom,
return `$cheeseListing`:

[[[ code('b2ad42201f') ]]]

How nice is that? You could even *unit* test this!

Back in the data transformer, to use this, copy the `$cheeseListing` context
line, delete the top section, paste and add `?? null`:

[[[ code('b068c9a024') ]]]

At this point, `$cheeseListing` with either be a `CheeseListing` object or null.
Finish the method with return `$input->createOrUpdateEntity($cheeseListing)`:

[[[ code('63566598b2') ]]]

That is beautiful.

## CheeseListingInput::createFromEntity

Next, go to the denormalizer. This is where we go the other direction - from a
`CheeseListing` - which might be null - *into* a `CheeseListingInput`.

Once again, let's put the logic inside `CheeseListingInput`, this time as a
public *static* function - called `createFromEntity()` - that accepts a nullable
`CheeseListing` argument and returns `self`:

[[[ code('09c1291706') ]]]

Go steal code from the denormalizer... copy the *center* section, paste, and
update the first `$entity` argument to `$cheeseListing`:

[[[ code('46fac8cbd9') ]]]

Delete the `instanceof` check - we'll keep that in the denormalizer - and update
the last `$entity` variables to `$cheeseListing`. Finally, return `$dto`:

[[[ code('baf0664a43') ]]]

Back in the denormalizer, life is a lot simpler! Keep the first line that gets
the entity or sets it to `null`, delete the next part, keep the `instanceof`
check, but add if `$entity &&` at the beginning:

[[[ code('854c887cde') ]]]

So if we *do* have an entity and it's somehow not a `CheeseListing`... we should
panic.

At the bottom, return `CheeseListingInput::createFromEntity($entity)`:

[[[ code('3ea94eaf6c') ]]]

I *love* that.

## CheeseListingOutput::createFromEntity()

Let's clean up *one* more spot. Open `CheeseListingOutputDataTransformer`.
This is where we go from `CheeseListing` to `CheeseListingOutput`. Let's move this
into `CheeseListingOutput`. Once again, it will be static:
`public static function createFromEntity()` with a `CheeseListing` argument - we *know*
this will never be null - and the method will return `self`:

[[[ code('a56d2b71fa') ]]]

Go steal *all* the code from the output transformer... and paste it here. If
you want, you can change this to new `self()`... but nothing else needs to change:

[[[ code('4992e7c27c') ]]]

Back in the transform, it's as simple as return
`CheeseListingOutput::createFromEntity($cheeseListing)`:

[[[ code('954d7e8a2c') ]]]

Phew! It took some work, but this feels nice. I'm having a nice time.

Since we *did* just change a lot of stuff, let's run our tests to make sure
we didn't break anything:

```terminal
symfony php bin/phpunit
```

And... it *always* amazes me when I don't make any typos.

The *one* part of the DTO system that we haven't talked about yet is how
validation happens. Do we validate the input object? Do we validate the entity
object? How does that work? Let's make our validation rock-solid next.
