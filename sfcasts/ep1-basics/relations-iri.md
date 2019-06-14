## Relations and IRIs

I just tried to create a `CheeseListing` by setting the `owner` property to 1: the
id of a real user in the database. But... it didn't like it! Why? Because in
API Platform and, commonly, in modern API development in general, we do *not* use
ids to refer to resources: we use IRIs. For me, this was strange at first... but
I quickly fell in *love* with this. Why pass around integer ids when URLs are
*so* much more useful?

Check out the response of the user we just created: like *every* JSON-LD response,
it contains an `@id` property... that isn't an id, it's an IRI! And *this* is what
you'll use whenever you need to refer to this resource.

Head back up to the `CheeseListing` POST operation and set `owner` to
`/api/users/1`. Execute that. This time... it works!

And check it out, when it transforms the new `CheeseListing` into JSON, the `owner`
property is that same IRI. *That* is why Swagger documents this as a "string"...
which isn't *totally* accurate. Sure, on the surface, `owner` *is* a string...
and that's what Swagger is showing in the `cheeses-Write` model.

But *we* know... with our *human* brains, that this string is special: it *actually*
represents a "link" to a related resource. And... even though Swagger doesn't
quite understand this, check out the JSON-LD documentation: at `/api/docs.jsonld`.
Let's see, search for owner. Ha! *This* is a bit smarter: JSON-LD knows that this
is a Link... with some fancy metadata to basically say that the link is to a
`User` resource.

The big takeaway is this: a relation is just a normal property, except that it's
represented in your API with its IRI. Pretty cool.

## Adding cheesesListings to User

What about the other side of the relationship? Use the docs to go fetch the
`CheeseListing` with id = 1. Yep, here's all the info, including the `owner` as
an IRI. But what if we want to go the other direction?

Let's refresh to close everything up. Go fetch the `User` resource with id 1.
Pretty boring: `email` and `username`. What if you *also* want to see what
cheeses this user has posted?

That's *just* as easy. Inside `User` find the `$username` property, copy the
`@Groups` annotation, then paste above the `$cheeseListings` property. But...
for now, let's *only* make this readable: just `user:read`. We're going to talk
about how you can *modify* collection relationships later.

[[[ code('71506814e0') ]]]

Ok, refresh and open the GET item operation for User. Before even trying this, it's
*already* advertising that it will *now* return a `cheeseListings` property, which,
interesting, will be an array of *strings*. Let's see what `User` id 1 looks like.
Execute!

Ah.. it *is* an array! An array of IRI strings - of *course*. By default, when
you relate two resources, API Platform will output the related resource as an IRI
or an *array* of IRIs, which is beautifully simple. If the API client needs more
info, they can make another request to that URL.

Or... if you want to avoid that extra request, you *could* choose instead to
*embed* the cheese listing data *right* into the user resource's JSON. Let's chat
about that next.
