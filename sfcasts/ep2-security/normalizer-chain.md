# Normalizer Chain

Coming soon...

When we normalize a `User` object like TJ, in our case to JSON, whether that's a single
user object or it's a collection of user objects, we now have a `UserNormalizer` that
because of its supports, normalization, a method is taking control of normalizing the
object. So our class is now responsible for normalizing the object. And all we're
really doing is just kind of adding a custom group. And then because we have auto
wired the `ObjectNormalizer`, that's the main normalizer as part of a Symfony
serializer we're just actually calling it and offloading all of the work to it. So in
theory, other than this dynamic class here, we're not doing anything. We're just,
we're relying on the core serializer to do it stuff. But we discovered in the last
chapter that it's not quite working. Our individual results here are user results are
missing. Our JSON LD information, you can see it up here and the `cheeseListing`
record, but on the phone number on the `User` records themselves, they do not have the
JSON LD information. So in reality, in the core of API platform, there are many
normalizes. One of the normalizers is responsible. For example, two, uh, one
of the normalizes is the JSON+LD `ItemNormalizer` and it's responsible for taking our
objects which extends `ObjectNormalizer`. And what it basically does is it uses the
normal `ObjectNormalizer` functionality. That's basically something that's really good
at, uh, you know, reading our getters and set our methods, um, to uh, transform that
object into an array. And then it also extends it,

I'm going to show you, it then extends it by adding more information to it like the
app context and other things. And in fact, and in fact if you are normalizing a
single `User` object, uh, many normalizes are called because um, there uh, your main
`User` object is normalized

but then it actually normalizes each individual property and piece of data. So for
example, the `User` object, um, goes through the JSON LD `ItemNormalizer` which then
gets all the properties, but then the individual properties themselves on user, like
the `phoneNumber` using an `password`, they go through a normalization process also. And
so there are other normalize, there's like a date time normalizer that's really good
at taking `DateTime` objects and turning them into strings. Okay, so what's the
problem then? So in reality there is instead of just one normalizer in the system,
there's this whole collection of normalizes and every single time a piece of data is
normalized, a API platform loops over all of those normalized hours asks each one, do
you support normalizing this piece of data? And the first one that returns true from
`supportsNormalization()` will be called in only that one.

So by injecting the `ObjectNormalizer` here, what we're doing is we're not actually
normalizing our object of back through that same chain of normalizes. We're actually
picking one specific normalizer and sending our data through it. And that specific
normalizer is pretty good at the job, but it doesn't actually go into the JSON
[inaudible] stuff. So this is a long way of saying that what we really want to do is
we want to modify the context here and then we want to send our object back through
the entire normalization, uh, uh, chain. We want to basically start over from the
beginning. The way you do that is by adding a new interface to our object called
`NormalizerAwareInterface`.

What that map does that requires us to have as a `setNormalize()` our method and what
this tells the serializer is it says, it tells us serializer to actually call if the,
when the serializer sees this method right before a serialized something, it actually
calls `setNormalizer()` on our object in passes us the main chain of or abnormal lasers.
This doesn't make sense. Check it out. I'm actually going to instead of auto wiring
`ObjectNormalizer` I'm going to remove that and then I'm actually going to use
`NormalizerAwareTrait` and all that normalize or where trade has it is it actually
has a `setNormalizer()` method and it sets it on a protected property.

so the end result of this is that instead of `$this->normalizer` being a single object
normalizer this actually represents the entire chain of normalizes. So what we're
going to do is when our normalized method is called, where is she going to make a
modification to it and then pass it back through the entire chain so that I can go
through the normal normalization process.

But some of you may already see a problem with this. If we hit execute now, I'd have
to wait a second for it to hit recursion. 

> Maximum function nesting level of 256 reached, aborting!

So what's happening here is it's literally calling our normalized
method or changing the context and then we're passing and then where and they we're
calling normalize again on that same chain. Well guess what that chain does. That
chain, once again, calls, `supportsNormalization()` on this normalizer we return true.
And so it calls us again. So we're basically calling ourselves over and over and over
again, which is what's causing that recursion. What we need is we need a flag,
basically says, we only want this to be called once. So we need a flag that says only
called me one time.

So what we're specifically going to do is we're, you're going to add a little flag to
the context itself that says we've already been called. And then down in supports
normalization, we'll use the context to say we use the context so that the second
time `supportsNormalization()` called we will say that we do not support normalization.
The first thing though, even though so there's no context argument on supports
normalization, you can actually get that by adding another interface here. Xicana
remove the, uh, remove the `NormalizerInterface` and replace it with 
`ContextAwareNormalizerInterface`. I can do that because you see this extends the normal,
normalize your interface. The only difference is that down here we need to have an
extra argument and `array $context`.

so basically the way my implemented the interface, we now are past the context in our
`supportsNormalization()` method. Now up here, I'm going to create a 
`private const ALREADY_CALLED` and said that too. It can be anything else. 
Say `USER_NORMALIZER_ALREADY_CALLED`

Now down here, once work once we're called the first time, right before I restart the
chain again, we'll say `$context[self::ALREADY_CALLED] = true`
So we're setting this little flag in the context of right before we
call the `normalize()` method. Again, when we call the `normalize()` method, again, our
`supportsNormalization()` method will be called for the second time. So here if you do
something like if `isset($context[self::ALREADY_CALLED]` it means that we're being
called the second time and we can return `false`. That's going to allow the
normalization process to continue and execute the normal normalizer, uh, for the JSON
LD.

So in theory that will fix it. However, when we hit execute this same exact thing
happens and this is a little bit subtle. The fix for this is to find this 
`hasCacheableSupportsMethod()` and return `false`. Before I explain that, let's try it. I'll
go back up, hit execute and it works. Check this out. We have `phoneNumber` kind of
randomly because we're still returning a, a, a a random result from users. Owner
bought, we have our `@id` and `@type` stuff back. So it's using the normal JSON LD
normalizer. So this `hasCacheableSupportsMethod()` that that your normalizer, um,

when we ran make the, the maker command for this, it automatically had our class
implement this `CacheableSupportsMethodInterface`, which is technically optional and
that forces us to have this `hasCacheableSupportsMethod()` down here. Um, this is
just a performance thing. If you, if your `supportsNormalization()` method only relies
on the format and the class of the data, basically `$data instanceof User` is legal,
then you can return `true` from `hasCacheableSupportsMethod()` and it will only call
`supportsNormalization()` one time. But as soon as you rely on maybe like some data on
that object or the context, then you can't, you have to return false here so that it
calls our `supportNormalization()` every time. So this is an important thing to return.
`true`. And you can, if you, if you, uh, if you can't return `false`, it's not the end of
the world. And this actually fixes our problem. So next, let's actually finish this
properly determined security down here so we can actually make sure this really
works. Fixed some tests, and we're going to use this custom normalizer to add a
totally crazy custom field called, `$isMe` a boolean field that will add to our `User`
that will return `true` or false based on whether or not the `User` that a and is is
equal to the currently authenticated user. I kind of a hacky field, but sometimes
really useful.