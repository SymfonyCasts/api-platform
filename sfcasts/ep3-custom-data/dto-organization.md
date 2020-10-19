# DTO Class Organization

It took some work - especially getting the update to work before API Platform 2.6 -
but our input & output DTO system is alive! Though... our logic for converting
from `CheeseListing` to input, input to `CheeseListing` and `CheeseListing` to
output is... not super organized. This code is all over the place. We can do
better.

There's no right or wrong way to exactly organize this kind of data
transformation code, but let's see what we can figure out. Start in
`CheeseListingInputDataTransformer`: this is where we go from `CheeseListingInput`
to `CheeseListing`. I'm going to put all of this transformation code into the
DTO classes themselves... because that's really their job: to be a temporary
holding place for data as it goes from one place to another.

## CheeseListingInput::createOrUpdateEntity

In `CheeseListingInput` create a new public function - `createOrUpdateEntity()` -
with a nullable `?CheeseListing $cheeseListing` argument and that returns a
`CheeseListing`. The reason this is *nullable* is because, inside our data
transformer, we may or may *not* have an existing `CheeseListing`.

Start inside of `CheesesListingInput` with a check for that: if *not*
`$cheeseListing`, then `$cheeseListing = new CheeseListing()`. And of course, this
is where we pass in the title, which is now `$this->title`.

For the rest of the logic, copy the setters from the transformer... then paste
them here. Oh, and change `$input` to `$this` on all the lines. At the bottom,
return `$cheeseListing`.

How nice is that? You could even *unit* test it now.

Back in the data transformer, let's use this. Copy the `$cheeseListing` context
line, delete the top, paste and add `?? null`.

At this point, `$cheeseListing` with either be a `CheeseListing` object or null.
Then, below, return `$input->createOrUpdateEntity()` and pass it `$cheeseListing`.

That is beautiful.

## CheeseListingInput::createFromEntity

Next, go to the denormalizer. This is where we go the other direction from a
`CheeseListing` - which might be null - *into* a `CheeseListingInput`.

Once again, let's put the logic inside`CheeseListingInput`, this time as a
public *static* function called `createFromEntity()` that accepts a nullable
`CheeseListing` arguments and returns `self`.

Let's go steal code from the denormalizer... copy the *center* of the code,
paste, and update the first `$entity` argument to `$cheseListing`. Delete the
`instanceof` check - we'll keep that in the denormalizer and update the last
`$entity` variables to `$cheeseListing`. Finally, return `$dto`.

Now, in the denormalizer, life is a lot simpler. Keep the first line that gets
the entity or sets it to `null`, delete the next part, keep the `instanceof`
check, but add if `$entity &&` at the beginning. So if we *do* have an entity
and it's somehow not a `CheeseListing`, we should panic.

At the bottom, return `CheeseListingInput::createFromEntity($entity)`.

I *love* that.

## CheeseListingOutput::createFromEntity()

Let's clean up *one* more spot. Open `CheeseListingOutputDataTransformer`.
This is where we go from `CheeseListing` to `CheeseListingOutput`. Let's move this
into `CheeseListingOutput`. Once again, it will be a static function public
static function `createFromEntity()` with a `CheeseListing` argument - we *know*
this will never be null - and the method will return `self`.

Go steal *all* the code from the output transformer... and paste them here. If
you want, you can change this to new `self()`... but nothing else needs to change.

Back in the transform it's as simple as
return `CheeseListingOutput::createFromEntity($cheeseListing)`.

Phew! It took a  bit of work there, but this feels nice. I'm having a nice time.

And since we *did* just change a lot of stuff, let's run our tests to make sure
we didn't break anything:

```terminal
symfony php bin/phpunit
```

And... it *always* amazes me when I don't make any typos.

The *one* part of the DTO system that we haven't talked about yet is how
validation happens. Do we validate the input object? Do we validate the entity
object? How does that work? Let's make our validation rock-solid next.
