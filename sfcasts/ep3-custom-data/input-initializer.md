# Input Initializer

Coming soon...

With an input DTO, like we have the process works like this first ApiPlatform queries
for the `CheeseListing` entity. This is specifically for a update. Second, the JSON is
deserialized into a new `CheeseListingInput` object. And finally, our `transform()`
method is called, which passes a set input object. So we can return the final 
`CheeseListing` object. Now, right now we're grabbing the cheese listing that was queried
from via the `$context`, which allows us to, uh, update an existing `CheeseListing`
object. If there is one, the problem is with the second step, because the 
deserializer always creates a new `CheeseListingInput` object with no data. If a input of
one of the properties of the input is no, we don't know if it's no, because the user
actually sent no for that field and the API and does one it's set to no on the entity
or if the user simply didn't send the field at all and we should be ignoring it to
fix this. What we want to do is right before step two, prepare a `CheeseListingInput`
object populated with the data from the database. We will then tell the serializer to
use that existing object to deserealizer the JSON onto, instead of using a new object,
then the input here would be an, would be an input that was initialized with
database.

Any JSON Fields overrode that data. And we would be to safe to set everything from
the input back into the `CheeseListing` and ApiPlatform. 2.6, you'll be able to do
this by implementing a new `DataTransformerInitializer` interface. Since that's not
released yet, let's do it ourselves. How will we know that if you set a, an object to
populate key on the context, the deserializer will use that object instead of
creating a new one. When it's deserializing by creating a custom denormalizer, we
could hook into the denormalization process and set the object to populate context D
context, the key to our prepopulated cheese listing input object that was populated
from the database. If it doesn't make complete sense yet that's okay. Let's step
through it piece by piece. All right, first in these `src/Serializer/Normalizer/`
directory, let's create a new `CheseeListingInputDenormalizer` or basically
going to do is create a new class that's responsible for de normalizing the cheeses
to input objects that we can modify the context before the process happens. This is
going to implement `DenormalizerInterface`.

and also `CacheableSupportsMethodInterface`, which
is a kind of a performance thing. I'll go to Code -> Generate or Command + N a Mac and go
to "Implement Methods" to implement the three methods that this needs for a hat. And
then I'll move `hasCasheableSupports()` to the bottom. That's just kind of the least
important. So as soon as we have this class, because it implements `DenormalizerInterface`
it's going to call `supportsDenormalization()` on every single piece of
data. When it's denormalizing anything in supports. We're going to say that we
support denormalizing, uh, anything where the `$type` is equal to `CheeseListingInput::class`
And as soon as you do that, we are now 100% responsible for denormalizing
`CheeseListingInput` objects down here and `hasCacheableSupportsMethod()` that we can return
true. That basically you can return true unless your, uh, `supportsDenormalization()`
method, uh, realizing the context, which requires a different interface. Alright, so
now it's going to call it `denormalize()`. Whenever it's trying to de normalize a, um, a
cheeses to input, I'm actually going to `dump()` the `$context`. That's the last argument
that's passed to us so we can see what that looks like.

All right. So let's try this out. It's not gonna work yet. We go over here and I'll
go back. This is my put end point, and I hit execute and, okay, perfect. As expected.
I get this expected de-normalized input to be an object. It's basically complaining
because we are not returning anything from our Dean normalized method yet, but we can
check up this dump. So in another tab, I already have my profiler open. I'm going to
hit latest to jump to the, that takes me to the exception. I'll go down here to debug
and perfect. This is the context that's being passed in the normalize. Now check this
out object. There is an object to populate key set to the `CheeseListing` object. That
should be no surprise. We just saw that a second ago, instead of our data transformer
object depopulate is set to the underlying `CheeseListing` object.

Now, the fact that this is set to `CheeseListing` object is kind of odd because
ultimately this process right now is going to deserealize the JSON into a 
`CheeseListingInput` object. So actually for that reason, there's a sanity check in the core
that sees that the object to populate is not actually the same as the type that we're
trying to deserealize into. And so it actually ignores the object depopulate right
now, that's actually why it's creating a new `CheeseListingInput` object every time, but we
could change this to be our `CheeseListingInput` object. Instead inside the denormalizer,
let's start with something simple. I'm gonna say `$dto = new CheeseListingInput()`, and
I'm going to say `$dto->title()`. I'm just gonna hardcore to title right now. So we can
kind of see what's happening in the system. So I'll say I am set in the, the
normalizer and down here, I'm going to say `$context`, Lasker bracket. And I use
`AbstractItemNormalizer::OBJECT_TO_POPULATE` equals `$dto`.

Now I'm not done with this method yet, but the goal is that now that I have given it
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

