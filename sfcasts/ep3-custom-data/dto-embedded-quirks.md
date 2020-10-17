# DTO Quirks: Embedded Objects

To see another, kind of, "quirk" of DTO's, go to `/api/users.jsonld`. Oh, this
tells me to log in. Ooooook. I'll go to the homepage, hit log in, and... excellent.
Close that tab and refresh again.

Check out the embedded `cheeseListings` field. That's... no right. An embedded
object... with only the `@id` field?

We know that if none of the fields on a related object will be serialized, then
API Platform should return an array of IRI *strings* instead of embedding the
objects.

## readableLink on Embedded Objects

This is a bug in how the `readableLink` for properties is calculated when you have
a DTO. I've actually *fixed* this bug... but I need to finish that
[pull request](https://github.com/api-platform/core/pull/3696).

Specifically, in the `User` class, if we search for `getPublishedCheeseListings()`,
this is the method that gives us the `cheesesListings` property. But because
`CheeseListing` uses a DTO, it doesn't calculate `readableLink` correctly. Remember:
`readableLink` is calculated by checking to see if the embedded object -
`CheeseListing` has any properties with the same normalization groups as `User`.
But... since `CheeseListing` isn't *actually* the object that will ultimately be
serialized... it should *really* check to see if `CheeseListingOutput` has any
fields with the `user:read` group.

*Anyways*, one way to fix this is just to force it. We can say `@ApiProperty`
with `readableLink=false`.

Now, when we move over and refresh... that will force it to use the IRI strings.
So... this is another quirk to be aware of, but hopefully it will get fixed sometime
soon.

## IRI String Problem with Multiple Output Classes

By the way, the problem of an object being *embedded* when it should be an IRI
string gets a bit worse if you multiple output classes. Like, if `User` *also*
had a `UserOutput` with a `cheeseListings` field... even adding `readableLink=false`
wouldn't help. If you have this situation, you can check out a conversation about
it [in the comments](https://symfonycasts.com/screencast/api-platform-extending/collections-readable-link#comment-5111958463).

## Re-Embedding some Fields

Anyways, I'm going to remove the `readableLink`. Why? Because originally, before
we started with all this output stuff, we *were* actually embedding the
`CheeseListing` data on `User` because we *were* including a couple of fields.

Check it out: in `CheeseListing`, go down to the `title` property. We put this
in the `user:read` group... and we did the same on `price`. We did that because
we wanted these two fields to be embedded when serializing a `User`.

The reason that wasn't happening *now* is... well... because I forgot to add these
in `CheeseListingOutput`. Let's fix that: above `title`, add `user:read` and then
also add `user:read` to `price`.

Let's check ot! Refresh now. *That* is how it looked before.

## Cleaning Up CheeseListing!

So... hey! We switched to an output DTO! And we're now getting *same* output we
had before! Yes, there *were* a few little quirks along the way, but overall, it's
a really clean process. This output class holds the fields that we actually want
to serialize and the data transformer gives us a simple way to create this object
from a `CheeseListing`

So let's celebrate! If you bring the pizza, then *I'll* clean up the
`CheeseListing` class. Because, it *no* longer needs anything related to
serializing because this object is *no* longer being serialized!

Search `:read` to find things we an remove. Remove `cheese:read` and `user:read`
from `title`, but keep the `write` groups because we *are* still *deserializing*
into this object when creating or updating cheese listings.

Then, down on `description`, remove `@Groups` entirely, for price. remove the two
read groups, and also remove `cheese:read` above `owner`.

Finally, down on `getShortDescription()`, we can remove method entire! Sure, if
you're calling it from somewhere else in your app, keep it. But we're not. Also
remove `getCreatedAtAgo()`.

*This* is a nice benefit of DTO's: we can slim down entity class a focus it on
just being an entity that persist data. The serialization logic is somewhere else.

Let's make sure I didn't make any silly mistakes: move on, refresh the users
endpoint and... bah! The `cheeseListings` property became an array of IRIs! This
is, once again, a case where `readableLink` is not being calculated correctly.
Now that we've removed the groups from `CheeseListing`, API Platform incorrectly
thinks that `User` and `CheeseListing` don't have any overlapping normalization
groups... but in reality, `CheeseListingOutput` *does*.

Re-add the `@ApiProperty` but this time say `readableLink=true` because we *do*
want to force an embedded object.

When we refresh now... yes! It's back to an embedded object. Also try
`/api/cheeses.jsonld`... that looks good, and let's run the tests one last time:

```terminal
symfony php bin/phpunit
```

And... yes! They *do* pass. With output DTO's, you need to be a bit more careful,
though some - but not all - of these quirks have already been fixed or will be
soon. The important thing to keep in mind is that DTO's are *not* serialized in
exactly the same way as ApiResource classes. So code carefully.

Next: let's talk about using an *input* DTO.
