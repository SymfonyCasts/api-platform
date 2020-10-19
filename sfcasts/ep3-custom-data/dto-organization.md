# Dto Organization

Coming soon...

It took a little bit of work, especially getting the update to work before ApiPlatform 2.6
 but our input output DTO system is alive, but our logic for converting
from `CheeseListing` to input and input and `CheeseListing` and `CheeseListing` output is
not super organized. There's kind of code that looks sort of like this all over the
place. So let's do some cleanup. There's no right or wrong way to exactly organize
this kind of data transformation code, but let's see what we configure out. Let's
start in `CheeseListingInputDataTransformer`. This is where we're going from
`CheeseListingInput` object into a `CheeseListing` entity object. I'm going to
actually put all of this transformation going into the DTO objects themselves classes
themselves, because that's really their job. So start again, `CheeseListingInput` I'm
going to create a new public function  `createOrUpdateEntity()` that takes eight
nullable `?CheeseListing $cheeseListing` argument and returns a `CheeseListing`. And the
reason this is nullable is because, uh, inside of our data transformer, um, you know,
we might already have a `CheeseListing` object or we might not have `CheeseListing` object.

So actually we're going to start inside of our `CheesesListingInput` with that statement.
I'll basically say, if not, `$cheeseListing`, if we were not past one and I'll say
`$cheeseListing = new CheeseListing()`. And of course this is where he passed in the
title. So now it's `$this->title`. Most of the rest of the logic inside of here is just
this stuff down here. So I'm gonna copy of these four setter statements, move those
into the, uh, input class and then change `$input` to `$this` on all of those cases. As
soon as we have that at the bottom, we can return `$cheeseListing`. So that's a really
nice function. You could even unit test that very easily. If you want it to over in
our data transformer now to use this, it's pretty simple. We can start to buy a copy
of this, a first cheese listing line here, and actually I'm going to, um, lead those
if statements and just say `$cheeseListing = $context[]`.

Object to populate `?? null`. So at this point, Jesus will
either be that `CheeseListing` object or it will be null. And then down here we can return
straight to `$input->createOrUpdateEntity()` and pass it `$cheeseListing`. That is
beautiful. All right. So next go to the denormalizer. This is where we go. The other
direction we go from from a `CheeseListing`, it's the object, which we may or may not
have one into a, an input object. So I'm once again to go put this inside of my
`CheeseListingInput` this time, it's actually be a static function. So public static
function `createFromEntity()`. So once again, take in a nullable `CheeseListing` and
it's going to return `self`.

Now I'll go steal some code from this other class. I'm basically going to steal kind
of the center section of the code here, move that, paste that into here, and then
update the entity here too. `$cheeseListing`. So if not, `$cheeseListing` return T to `$dto`,
and then I will delete this instance of Jack. We don't need that anymore. We know
we'll get a `CheeseListing`. We'll keep this check inside of the, uh, D normalizer and
down here, we just want to change this to cheese `$entity`, the `$cheeseListing` on these
five variables at the bottom, we can return `$dto`. Now, did you normalize their lives a
lot simpler? I'll keep this first line that gets the entity or sets it to know I'll
delete the next part. And then for the, um, if statement I'm actually going to
change, let's say if `$entity`, and it's not an instance that `CheeseListing` that way, we
don't throw an exception when there's no.

And then down here on the bottom, we can go straight to returning 
`CheeseListingInput::createFromEntity($entity)` We'll pass it, that entity. So again, really,
really satisfying end result that can be easily unit tested. All right, let's do one
more thing. This is actually in the, uh, `CheeseListingOutput`, a data transformer.
This is where we go from `CheeseListing` into H `CheeseListingOutput`. So I'll put this
code into `CheeseListingOutput`. Once again, it's gonna be a static function because
we are creating a new one of these. So pelvic static function `createFromEntity()`.

So exactly like we just had a second ago with a, this time, we know we'll have a
`CheeseListing` is we're printing an existing one and we will of course, return to
`self`. And it's out of here. I'll go steal the code from our output entirely. I'll
steal all of these lines of code, paste them in here. If you want, you can change
this to new self if you want it to, but that basically nothing else needs to change
on here and then transform. And our data transformer it's of course, as simple as
return `CheeseListingOutput::createFromEntity()` and we pass it. `$cheeseListing`
Phew. So a little bit of work there, but isn't that a nice system. If you're
going to use DTO is like this. I really like having these things centralized into our
DTO classes. All right. So I did just change a lot of stuff. So I'm just going to go
over here and run our tests

```terminal
symfony php bin/phpunit
```

because I know that I like to
make lots of mistakes and Hey, no mistakes this time. Awesome. So next one kind of
aspect of the input that we haven't talked about yet is how validation happens. Do we
validate the input object? Do we validate the entity object? How does that work? So
let's make our validation rock solid and next.

