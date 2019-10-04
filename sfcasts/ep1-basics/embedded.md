# Embedded Relation

When two resources are related to each other, this can be expressed in two different
ways in an API. The first is with IRIs - basically a "link" to the other resource.
We can't see the data for the related `CheeseListing`, but if we need it, we could
make a second request to this URL and... boom! We've got it.

But, for performance purposes, you might say:

> You know what? I don't want to have to make one request to fetch user data
> and then one *more* request to get the data for each cheese listing they own:
> I want to get it all at once!

And *that* describes the *second* way of expressing a relationship: instead of just
returning a link to a cheese listing, you can *embed* its data right inside!

## Embedded CheeseListing into User

As a reminder, when we normalize a `User`, we include everything in the
`user:read` group. So that means `$email`, `$username` and `$cheeseListings`, which
is why that property shows up at all.

To make this property return *data*, instead of just an IRI, here's what you need
to do: go into the related entity - so  `CheeseListing` - and add this `user:read`
group to at least one property. For example, add `user:read` above `$title`...
and how about also above `$price`.

[[[ code('b1f0216d29') ]]]

Let's see what happens! We don't even need to refresh, just Execute. Woh! Instead
of an array of strings, it's now an array of *objects*! Well, this user only owns
*one* CheeseListing, but you get the idea. Each item has the standard `@type`
and `@id` *plus* whatever properties we added to the group: `title` and `price`.

It's beautifully simple: the serializer knows to serialize all fields in the
`user:read` group. It first looks at `User` and finds `email`, `username` and
`cheeseListings`. It *then* keeps *going* and, inside of `CheeseListing`, finds
that group on `title` and `price`.

## Relation Strings vs Objects

This means that each relation property may be a *string* - the IRI - *or* an
*object*. And an API client can tell the difference. If you get back an object,
you know it will have `@id`, `@type` and some other data properties. If you get
back a string, you know it's an IRI that you can use to go get the real data.

## Embedding User into CheeseListing

We can do the *same* thing on the other side of the relationship. Use the docs to
get the `CheeseListing` with `id = 1`. Yep! The `owner` property is a string. But
it *might* be convenient for the CheeseListing JSON to *at least* contain the
username of the owner... so we don't need to go fetch the *entire* User just to
display who owns it.

Inside `CheeseListing`, the normalization process will serializer everything in
the `cheese_listing:read` group. Copy that. The `owner` property, of course, already
has this group above it, which is why we see it in our API. Inside `User`, find
`$username`... and add `cheese_listing:read` to that.

[[[ code('2bd7945b33') ]]]

Let's try this thing! Move back over and... Execute! And... ha! Perfect!
It expands to an object and includes the `username`.

## Embedding Data Only on when GETing a Single Item

Does it work if we GET the *collection* of cheese listings? Try it out! Well...
ok, there's only *one* `CheeseListing` in the database right now, but of course!
It embeds the owner in the same way.

So... about that... new challenge! What if we want to embed the `owner` data when
I fetch a *single* `CheeseListing`... but, to keep the response from being gigantic...
we *don't* want to embed the data when we fetch the *collection*. Is that possible?

Totally! Again, for `CheeseListing`, when we normalize, we include everything in
the `cheese_listing:read` group. That's true regardless of whether we're GETting
the collection of cheese listings or just GETting a single item. *But*, *tons*
of things - including groups - can be changed on an operation-by-operation basis.

For example, under `itemOperations`, break the `get` operation configuration
onto multiple lines and add `normalization_context`. One of the tricky things
with the config here is that the top-level keys are lower camel case, like
`normalizationContext`. But deeper keys are usually snake case, like
`normalization_context`. That... can be a little inconsistent - and it's easy to
mess these up. Be careful.

Anyways, the goal is to *override* the normalization context, but *only* for this
*one* operation. Set this to the normal `groups` and another array. Inside, we're
going to say:

> Hey! When you are getting a *single* item, I want to include all of the
> properties that have the `cheese_listing:read` group like normal. But I *also*
> want to include any properties in a new `cheese_listing:item:get` group.

[[[ code('0eb08286dd') ]]]

We'll talk more about it later - but I'm using a *specific* naming convention for
this operation-specific group - the "entity name", colon, item or collection, colon,
then the HTTP method - `get`, `post`, `put`, etc.

If we re-fetch a single `CheeseListing`.... it makes no difference: we're including
a new group for serialization - yaaaay - but nothing is *in* the new group.

Here's the magic. Copy the new group name, open `User`, and above the
`$username` property, replace `cheese_listing:read` with `cheese_listing:item:get`.

[[[ code('9aec44a25c') ]]]

That's it! Move back to the documentation and fetch a *single* `CheeseListing`.
And... *perfect* - it *still* embeds the owner - there's the username. But
now, close that up and go to the GET *collection* endpoint. Execute! Yes!
Owner is back to an IRI!

These serialization groups *can* get a little complex to think about, but *wow*
are they powerful.

Next... when we fetch a `CheeseListing`, some of the owner's data is *embedded*
into the response. So... I have kind of a crazy question: when we're updating
a `CheeseListing`... could we *also* update some data on the owner by *sending*
embedded data? Um... yea! That's next.
