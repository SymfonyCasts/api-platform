# Embedded

Coming soon...

When had a relationship and an API. There's really two ways for your API to
express that. The first one is be a m IRIs which is, which is what you're seeing
here. We don't actually see the data for the related `CheeseListing`. We just
see the IRI. If the use, what if we need more information about what this
cheese is, what we just, our API client can just take that you were all make an
extra request and boom, they have that information. The second way. Now for
performance purposes you might shoot, you might say, you know what? I want to
actually embed that `CheeseListing` data right here instead of having the IRI and
that's the second way that you can do it.

All right, now we're looking at the data for a specific user, so as a reminder,
whenever we normalize a `User`, we include everything in the `user:read` group, so
that means `$email`, `$username` and `$cheeseListings`, which is why that property shows up
at all. Now if you want, instead of the IRI is showing up here, if you actually want
to embed some data, what you need to do is go into the related entity. So 
`CheeseListing` and we need to add this `user:read` group to at least one property over
there. So for example, let's say that we want to show the day the `title` field. So
I'll add `user:read` up on `$title` and let's also add `user:read` on `$price`.
All right, so we'll go over here. Not even gonna refresh just to execute and yes,
check that out. Instead of this being an array of strings, it's now an array of
objects. There's only one object in this case, but if we had multiple cheeses, things
they would just show up below here and it includes the title and the price. So this
is something that's cool about JSON-LD. When you have relations, the relations can be
strings or they can be objects. As an API client, you can tell the difference. If you
get back an object here, you know it's going to have an `@id` and `@type` and its
properties. If this is a string, you know it's an IRI that you can use to go get more
information about it.

So we can do the same thing on the other side of the relationship as well. So let's
say that we're going to get `CheeseListing` with `id = 1` and in here right now we have
these `string` owner so it might be really convenient for us to at least have the user
name of the owner automatically right there. So once again, `CheeseListing` when we
render `CheeseListing` a uses everything with the `cheese_listing:read` class. So
I'm going to copy of that. Um, that of course includes, this is already on the owner
property, which points to our user. So over on `User`, let's find `$username` here and
let's just add `cheese_listing:read` to that.

All right, so if we try the single one here, yes, it works great. It expands now to
an object and has the username and includes the username and the same as going a
work. If we do the collection and point up here. So let's get all of our cheese
listings, which right now is only one and you can see that it embeds the owner right
there. So the last cool thing you can do is you can actually do this on an operation
by operation standpoint. Like if we had lots of lots of cheese listings, it might be
that when you're getting a collection of `CheeseListing`, you don't usually need the
user data. Just having an owner is enough. But you do want the extra owner data when
you are fetching a single `CheeseListing`.

So check out how we can do this. Again, on our `CheeseListing` right now, it's pretty
simple. When we normalize, we include everything in the `cheese_listing:read`
a group, which means when we get a single item or when we get a collection of items,
but all the different operations can be configured. So under `itemOperations`. One
of the things that you can do is you can add your own, get everything in a organized
spot. Here you can add your own `normalization_context`. Now notice, one of the tricky
things here is sometimes when you have top level keys and it'll be called
`normalizationContext`, Campbell case later wouldn't. Then it's
an option. It's `normalization_context`. So watch out for that. Here we can say
this is equal to and I'm just like normal st `groups`. You go to another ray inside of
here. What we're going to say is, hey, when you are fetching a single, getting a
single item, I want you to include all of the properties and `cheese_listing:read`
like normal. But I also want you to include the items in a new `cheese_listing:item:get`
this is something we're going to expand on later, but when you
need operation specific, a normalization groups, I like to use this pattern of kind
of the class name, `:item` or `:collection`, `:` and then the method name. So `get`
`post`, `put`.

Now if we did that right now if we fetch a single `CheeseListing` which is what we're
doing right here,

It makes no difference. Uh, we're now serializing all properties in these two groups,
but nothing is in this extra group. However, if I copy that, we can now go over to
our `User` and instead of including the `$username` property on all whenever `cheese_listing:read`
 used, we use it just when `cheese_listing:item:get` is used. So
now when you flip back over to your documentation, if we try to fetch a single cheese
listing, it still works the same. It's still has an embedded owner with a user name
showing up. But if you close that up and execute the collection end points, so 
`/api/cheeses`, now it goes back to just pointed to the IRI is not imbedded. So it can get
a little bit confusing to keep all of these serialization groups straight. But it's a
super powerful idea.