# Diving into the Normalizer Internals

When a `User` object is normalized - whether it's a single `User` object or a
collection of `User` objects - our new `UserNormalizer` class is now 100%
responsible for that process. Whenever the serializer needs to normalizer a `User`
object, it loops over all of the normalizers and stops when it finds the *first*
one whose `supportsNormalization()` method returns true. That's now *our*
normalizer.

And... what we're doing is simple: adding a custom group... then, because we
autowired the `ObjectNormalizer` - that's the "main" normalizer Symfony uses to
normalize objects - we're calling it and making *it* do all of the heavy lifting.
But we discovered in the last chapter that this isn't *quite* working like we want:
each `User` record is missing the JSON-LD fields. The embedded `cheeseListings`
data has them... but each user does not.

What's going on?

## The Many Core Normalizers

When you work with API Platform, the Symfony serializer has *many* normalizers.
I'll hit Shift+Shift and search for a class called `ItemNormalizer`. There are
a bunch of these - open the one in the `JsonLd` directory. This is one of those
normalizers. And, if you followed its parent class, you would find that it eventually
extends `ObjectNormalizer`. Basically, this class uses the *normal* functionality
of the parent `ObjectNormalizer` - that's the one that reads data off of our getter
methods - but then *adds* the JSON-LD fields - like `@context` and `@id`.

Actually, let's back up even further... and pretend like our custom `UserNormalizer`
doesn't exist. Out-of-the-box, when API Platform normalizes a single `User` object,
*many* normalizers are used. First, it loops over all of the normalizers and finds
the *one* normalizer that can turn a `User` object into an array of data. That's
normally handled by the JsonLd `ItemNormalizer`, which reads all the data from the
`User` object *and* adds a few more JSON-LD specific fields. Once this finishes,
the serializer *then* loops over the individual pieces of data - like the
`phoneNumber` string or the `cheeseListings` collection - and sends each of *these*
through the normalization process again, asking each normalizer if they support
this piece of data until it finds one that does. Or... if none do, it just uses
the value as the final value - that happens for simple scalar fields like
`phoneNumber`.

This creates a *super* powerful system. For example, another normalizer - the
`DateTimeNormalizer` - is responsible for normalizing any `DateTime` objects...
like if you had a `createdAt` property. It normalizes `DateTime` objects into a
string that can used in JSON or XML.

So each *piece* of data - from the top-level `User` down to each property... and
even further for related objects - is normalized by exactly *one* normalizer.

## Why is the JSON-LD Info Gone?

Cool! So then... back to our question: why are the JSON-LD fields missing? Well,
when we autowired the `ObjectNormalizer` and then called it... we're not *really*
calling the correct, core normalizer. Nope, instead of asking the serializer to
loop over *all* of the normalizers to find the correct one to use, we accidentally
autowired just one *specific* normalizer and are using it. Basically, instead of
using the `ItemNormalizer` that does all the `ObjectNormalizer` goodness and then
adds the JSON-LD fields, we're using the `ObjectNormalizer` directly. Hence... we
lost those fields!

This is a *long* way of saying that what we *really* want to do is modify the
`$context` and then send the `User` object back through the entire normalization
chain again so it can find the core normalizer that's *usually* responsible for
normalizing objects.

How do we do that? It's easy! Um, and... kinda tricky. It involves recursion...
well... hopefully *avoiding* recursion. Now that we understand what's going on,
let's fix it next.
