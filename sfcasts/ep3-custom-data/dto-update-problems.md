# Input DTO Update Problems

Our `CheeseListingInput` DTO now works to create *new* listings, but it does *not*
work for updates. The reason is simple: our data transformer always creates *new*
`CheeseListing` objects. What we *really* need to do is query for an existing
`CheeseListing` object from the database if this is an update.

And... doing this as pretty easy! When we make a put request - or really, *any*
item operation - API Platform *already* queries for the underlying `CheeseListing`
entity object. We just need to use *that* in our transformer. How? API Platform
puts that `CheeseListing` into the *context*.

## Fetching the Entity from the Context

So... where *does* API Platform put the `CheeseListing` object after it queries
for it? Check it out: if `isset($context[])` and look for a special key:
`AbstractItemNormalizer::OBJECT_TO_POPULATE`. Oh, but let me fix my syntax.

This is actually a feature that's part of Symfony's serializer. Normally, if you
deserialize some JSON into an object, the serializer will create a *new* object.
But if you want it to update an *existing* object, you need to pass that object
on this key in the context when deserializing. The serializer will notice it and
use it.

In the case of an input DTO, the `OJBECT_TO_POPULATE` is *actually* the underlying
`CheeseListing` object. So we can say `$cheeseListing = ` and I'll copy that long
`$context` and paste it here. Else, copy and move up the
`$cheeseListing = new CheeseListing()` line.

That's it! Notice this means that the title *can't* be updated: if the user sent
that field on a PUT request, we *can't* change it on the final `CheeseListing`
because the *only* way to *set* that on a `CheeseListing` is via the constructor.
There is no `setTitle()` method.

And... that was actually *already* the case before! The `title` field was *never*
used on update. API Platform does a great job of making our API work like our
classes work.

## Update Problem: Not Overriding Existing Data

Anywho, let's try this. Back at the docs, I still have the POST endpoint open.
Copy all the data I sent. And, let's see... this created a new cheese listing with
the id `53`. Close up this operation, open  the put operation, hit "Try it out",
put 53 for the ID and, in the box, paste the fields. Let's change the price to
be 5,000.

Hit Execute. And... ah! 500 error!

> owner_id Cannot be null

Figuring this out requires a bit of digging. Look over at `CheesesListingInput`.
The owner is in the `cheese:collection:post` group. Thanks to our denormalization
groups, this means it can only be set on an *create*. This means that, even though
we're sending `owner`, it's being ignored.

Ok... so why is that a problem? That *is* the behavior we want! The issue is that
when the serializer deserializes the JSON into the `CheeseListingInput`, the
`owner` property will be `null`... and then we pass that `null` value onto
`$cheeseListing->setOwner()`. When API Platform tries to save the `CheeseListing`
with no owner... error!

But... let's back up: there's a bigger problem that's causing this. In reality,
when we're passed the `CheeseListingInput` object, if a property on it is null,
we don't really know if that field is null because the user explicitly *sent*
`null` for a field - like `title: null` - or if they simply omitted the field.

And... that's important! If a field is is *not* sent on an update, then it means
the field should *not* be changed: we should use the value from the database.

Ideally, this `CheeseListingInput` object would *first* be *initialized* using the
data from the `CheeseListing` that's currently in the database. And *then* the
JSON would be deserialized onto it. If we did this, any fields that were *not*
send in the JSON would *remain* at their original value.

But... that does *not* happen and it means that we don't have enough information
in this function to figure out how to handle null fields.

## The new DataTransformerInitializerInterface

This is actually a missing feature in API Platform. Well, it was until we talked
to the API Platform team about it... and then they added the feature. You can see
it as [pull request 3701](https://github.com/api-platform/core/pull/3701) and it
should be available in API Platform 2.6.

Here's how it's going to work: you'll add a new interface to your data
transformer: `DataTransformerInitializerInterface`, which will force us to have
an `initialize()` method.

As soon as we have this, API Platform will call it *before* our transform method.
Our job will be to grab the `OBJECT_TO_POPULATE` off of the `$context` and use it
to create and initialize the data on a `CheeseListingInput`. Then, when
API Platform calls `transform()`. it will pass us an input object that is
pre-filled with data.

If you're using API Platform 2.6 and have any questions, let us know in the
comments. But since 2.6 hasn't been released yet, let's implement this feature
ourselves next by leveraging a trick inside a custom normalizer. That's next.
