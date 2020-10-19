# DTO Input Initializer Logic

Coming soon...

Now, we're not done with this method yet, but the goal is that now that I have given it
an initialized `CheeseListingInput` of an object, it will use this object. It will DC
realized that JSON into this object and whatever I am, whenever I'm passing here is
actually, what's going to be passed to me as the input in the transformer. So it will
call my Dean advisor first, we'll initialize an object, and then it will be passed
here. So I'm actually going to `dump($input)` here so that we can, uh, once we have this
fully working, we can actually see if that's the case. All right. So, as I mentioned
earlier, as soon as we, um, created this class and filled in our `supportsDenormalization()`
method, we are now 100% responsible for denormalizing `CheeseListingInput`
objects, which means we actually need to do the work here of, of taking the
JSON and creating the object with it and returning it.

Of course. So basically what we really want to do here is we want to basically call
the core normal denormalizer system, but use our modified context. We actually had
the same problem earlier with our user normalizer and we showed up kind of a
elaborate solution that allowed us to inject the, the entire normalizer system call
normalize again, and then use this little flag here to avoid recursion. Now, the most
correct solution in the DCF denormalizer is actually to do that same thing, but
decent realizing that destabilization is a bit simpler and we can actually get away
with just injecting an object normalizer directly, which is the, uh, the object
that's really good at de normalizing objects. So instead of injecting the entire
system and needing to avoid recursion, we're going to inject these specific, uh, the
specific normalizer that I know is going to be used in the case of a `CheeseListingInput`,
and just call that directly.

So up here, great public function `__construct()` with ObjectNormalizer and
make sure you get the one from Symfony `ObjectNormalizer $objectNormalizer` that is
Ottawa horrible. And then I'll go to I'll have Alt + Enter and go to "Initialize properties"
properties to create that property and set it now down here, all we need to do is
say, return `$this->objectNormalizer->denormalize()`, and we will pass it the `$data`,
cause they're still, we're still gonna be de-normalized in the same array of data
from JSON, the `$type` that would the `$format`, but now pass it our new `$context` so that
when a denormalizes that JSON is going to do normalize it into this DTO object here,
instead of creating a brand new one, okay, let's try this go over. Now, we'll go back
to our documentation and I'll hit execute again.

And we still got a 500 air, which I totally, which I totally we're just totally I'm
expected still. Cause we're not quite done with our job, but I want to see is if the
dump worked, I want to see if the `CheeseListingInput` object we created here is now being
passed into our `transform()` method. So I'll go over to my other tabs of the profiler
hit latest. And yes, there it is. You can see it. Oh, it says cheese like, Oh, you
know what? I should've done take off the `title` property, my bad. That will make it
more interesting. So we can actually see if that's being modified. So we get 500
error again, let me hit latest. And yes, this is what we're looking for. This proves
that we are actually now in control of creating the `CheeseListingInput` object. Then
the JSON has DC realized onto it and then either leaves the property alone. If we
didn't send that field or overrides it like we saw a second ago, which means we're
dangerous because now inside of our `CheeseListingInputDataTransformer`, we're safe to
transfer every single property onto `CheeseListing` because if it set by the user, it
will match what was already set in the database.

By the way, if you're wondering why we can override object to populate, to be
`CheeseListingInput` inside the inside the denormalizer, but then object depopulate is
supposed to be a cheesy missing entity object inside of our data transformer. It's a
little confusing, but those are actually two completely separate processes. And the
context is mostly shared, but it's actually cloned between them. So even though we
modify the context inside of our denormalizer, uh, that does not modify the context
that's used ultimately for our data transformer. Anyways, our last job is actually
just a finish denormalizer instead of creating a brand new `CheeseListing` object,
we want to populate this from the `CheeseListing`, uh, in the database. If there is
one to do to help with that, it's pretty boring. I'm gonna go down to the bottom of
this method and paste any new private function.

You can copy this from the code block on this page, I'll read type the GN cheese
listing to get that used statement. So basically it checks that object to populate. I
could probably use my constant there instead, but it looks to see if the, uh, the
entity exists that creates the DTO. And if there wasn't no entity, because this is a
new operation, it just returns the empty DTO like before. But if there isn't a D if
there is a, uh, entity object `CheeseListing`, then it pre-populates all the fields
from the DTO to be set though. So we ended up with a nice DTO at the end of this. So
we can use this up here simply by deleting new DTO stuff and saying `$context`, object
depopulate = `$this->createDto()` and pass that the `$context`.

Okay, let's try this. What we're hoping is that so far, we've tried this when we've
tried to set these couple of fields, we have the error that owner ID cannot be known
a database, and that's because it's been creating a new `CheeseListing` and put
object, which has a no owner, even there's one set of database. So now for the
cheeses to input, we'll get the owner from the, um, database. And even though we're
not setting it in the JSON, it will use that existing value. That's when I hit
execute. Yes, it works exhibit price 5,000. It actually did update. Phew. We are
still missing a little bit of a couple of pieces here with validation. If you left
some of these fields blank, but we'll talk about that pretty soon. But first we
currently have code a little bit all over the place for converting, uh, our entity
into our DTO and our DTO into an entity. Let's clean this up a little bit next.
