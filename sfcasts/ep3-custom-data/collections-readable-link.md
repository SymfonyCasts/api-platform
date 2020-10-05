# Collection "Types" and readableLink

Something a bit odd just happened: in order for API Platform to correctly serialize
the `mostPopularListings` collection, we had to explicitly *tell* it what was
inside the collection. Why?

To learn what's going on, let's look at another example. Inside `User`, we have
a `cheeseListings` property, which is *writable* in our API, but isn't *readable*.
There is also a `getPublishedCheeseListings()` method, which *is* part of the API
and we actually gave it the `cheeseListings` name.

Let's put in our lab coats and do an experiment! Science! Start by removing the
`SerializedName` annotation. We're still going to expose this method, but it will
use its natural name: `publishedCheeseListings`. Then, up on the `cheeseListings`
property add `user:read` to *also* expose this.

Let's see what it looks like! Head over to `/api/users.jsonld` and... pff. I got
logged out. Let me log back in. The problem is that *sometimes* I play around a
bit between videos and mess myself up like this.

*Anyways*... cool! Each `User` now has `cheeseListings` and `publishedCheeseListings`
properties and they're *both* embedded objects. The reason *why* is that the
`$title` and `$price` properties in `CheeseListing` have the `user:read` group.

Let's remove those temporarily. Go into `CheeseListing` and take `user:read` off
of `$title` and `user:read` off of `$price`.

Thanks to this change, when API Platform goes to serialize these two array fields,
it will realize that there are no embedded properties and return an array of IRI
strings.

But... surprise! When we refresh, `cheeseListings` *is* an array of IRI strings,
but check out `publishedCheeseListings`! It's still an array of embedded objects!
Other than the fact that `publishedCheeseListings` may have less items in it, these
two fields return the *same* thing! And yet, they're being serialized in
different ways!

## Property Metadata for Collections

Here's the deal. We know that API Platform collects a lot of metadata about
each property, like its type and whether it's required. And it gets that from
many different sources like Doctrine metadata and our own PHPDoc.

And because collecting all of this can take time, it caches it. Now,
API Platform is really good in `dev` mode at knowing *when* it needs to *rebuild*
that cache. Like, if we add more PHPDoc to a property, it rebuilds. And so, even
though it's caching all of this, we don't *really* notice it.

And this metadata collection process happens *before* API Platform starts handling
*any* request, which means the metadata is built *purely* by looking at our *code*.
Right now, when it looks at the `cheeseListings` property, it knows that
this is an array of `CheeseListing` objects thanks to the *Doctrine* annotations.

But it does *not* know that `getPublishedCheeseListings()` returns a collection
of `CheeseListing` objects. It *does* know that its a `Collection`... but not
what's *inside* that `Collection`.

Why is this a problem? Well, whenever API platform serializes a collection,
before it even *starts*, it asks its own metadata: what is this a collection *of*?
If the "thing" that's being serialized is a collection of objects that are an API
Resource class - like the `cheeseListings` property - then it calls *one* set of
code that knows how to handle this. But if it's an array of *anything* else - which
is what happens down in `getPublishedCheeseListings()` since it doesn't
know *what's* inside this collection, then it runs a *different* set of code with
different behavior.

This isn't a problem very often - especially if you're relying on Doctrine
metadata - but whenever you have a collection field, you should think:

> Does API Platform know what this is a collection *of*?

For `getPublishedCheeseListings()`, we already know the solution. Add `@return`
`Collection<CheeseListing>`.

Try it! Refresh the endpoint and... we get an array of IRI strings in *both* cases.

## The readableLink Option

Now, you *can* actually control this behavior directly... with an option
that - honestly - makes my head spin a little bit. Instead of allowing API Platform
to figure out if a property should be an embedded object or an IRI string, you can
force it with `@ApiProperty({readableLink=true})`.

Refresh now. Yep! This forces it to be an embedded object. `readableLink` is an
internal option that's set on *every* API field, and it's *normally* determined
automatically. API Platform sets it by looking to see if there are
intersecting normalization groups between `User` and `CheeseListing`. Basically
it says:

> Hey! I can see that this property will hold an array of `CheeseListing` objects.
> Let's see if any of the `CheeseListing` properties are in the `user:read` group.
> If there are *any*, set `readableLink` to false to force it to be embedded.

By using the `@ApiProperty` annotation, we're overriding this and taking control
ourselves.

Now, `readableLink` is *super* weird... at least for me - I can't *quite* wrap
my mind around it. The name almost seems backwards: `readableLink=true` says
that you want to embed and `readableLink=false` says to use an IRI link... though
I've seen some odd behavior in some cases. If you have any questions, let us know
down in the comments.

Ok, let's undo *everything*: take off `readableLink`, but leave the `@var` because
that's actually helpful. Put back the `@SerializedName()` and, on the `cheeseListings`
property, remove `user:read`. Back in `CheeseListing`, I'll undo to re-add the
`user:read` groups.

Go over and refresh to make sure things are back to normal.

Next, let's get back to our custom `DailyStats` API Resource. We've implemented
the collection operation, now let's add the get "item" operation so that we can
fetch stats for a *single* day.
