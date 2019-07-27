# Subresources

At some point, an API client... which might just be our JavaScript, will probably
want to get a list of all of the `cheeseListings` for a specific `User`. And...
we can already do this in two different ways: search for a specific owner here via
our filter... or fetch the specific `User` and look at its `cheeseListings` property.

If you think about it, a `CheeseListing` *almost* feels like a "child" resource
of a `User`: cheese listings *belong* to users. And for that reason, some people
might *like* to be able to fetch the cheese listings for a user by going to a URL
like this: `/api/users/4/cheeses`... or something similar.

But... that doesn't work. This idea is called a "subresource". Right now, each
resource has its own, sort of, base URL: `/api/cheeses` and `/api/users`. But it
*is* possible to, kind of, "move" cheeses *under* users.

Here's how: in `User`, find the `$cheeseListings` property and add `@ApiSubresource`.

[[[ code('4636f39c9e') ]]]

Let's go refresh the docs! Woh! We have a new endpoint!
`/api/users/{id}/cheese_listings`. It shows up in two places... because it's kind
of related to users... and kind of related to cheese listings. The URL is
*cheese_listings* by default, but that can be customized.

So... let's try it! Change the URL to `/cheese_listings`. Oh, and add the
`.jsonld` on the end. There it is! The collection resource for all cheeses that
are owned by this `User`.

Subresources are kinda cool! But... they're also a bit unnecessary: we *already*
added a way to get the collection of cheese listings for a user via the `SearchFilter`
on `CheeseListing`. And using subresources means that you have more endpoints to
keep track of, and, when we get to security, more endpoints means more access
control to think about.

So, use subresources if you want, but I don't recommend adding them *everywhere*,
there *is* a cost from added complexity. Oh, and by the way, there is a *ton*
of stuff you can customize on subresources, like normalization groups, the URL, etc.
It's all in the docs and it's pretty similar to the types of customizations we've
seen so far.

For our app, I'm going to remove the subresource to keep things simple.

And... we're done! Well, there is a *lot* more cool stuff to cover - including
security! That's the topic of the next tutorial in this series. But give yourself
a jumping high-five! We've already unlocked a *huge* amount of power! We can expose
entities as API resources, customize the operations, take *full* control of the
serializer in a *bunch* of different ways and a *ton* more. So start building
your gorgeous new API, tell us about it and, as always, if you have questions,
you can find us in the comments section.

Alright friends, seeya next time!
