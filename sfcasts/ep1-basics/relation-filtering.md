# Filtering on Relations

Go directly to `/api/users/5.jsonld`. This user owns one `CheeseListing`... and
we've decided to embed the `title` and `price` fields instead of just showing
the IRI. Great!

Earlier, we talked about a really cool filter called `PropertyFilter`, which allows
us to, for example, add `?properties[]=username` to the URL if we *only* want
to get back that *one* field. We added that to `CheeseListing`, but not `User`.
Let's fix that!

Above `User`, add `@ApiFilter(PropertyFilter::class)`. And remember, we need to
manually add the `use` statement for filter classes: `use PropertyFilter`.

[[[ code('c818db1ea9') ]]]

And... we're done! When we refresh, it works! Other than the standard JSON-LD
properties, we *only* see `username`.

## Selecting Embedded Relation Properties

But wait there's more! Remove the `?properties[]=` part for a second so we can see
the full response. What if we wanted to fetch only the `username` property and
the `title` property of the embedded `cheeseListings`? Is that possible? Totally!
You just need to know the syntax. Put back the `?properties[]=username`. *Now*
add `&properties[`, but inside of the square brackets, put `cheeseListings`. Then
`[]=` and the property name: `title`. Hit it! Nice! Well, the `title` is empty
on this `CheeseListing`, but you get the idea. The point is this: `PropertyFilter`
kicks butt and can be used to filter embedded data without any extra work.

## Searching on Related Properties

Speaking of filters, we gave `CheeseListing` a *bunch* of them, including the ability
to search by `title` or `description` and filter by `price`. Let's add another one.

Scroll to the top of `CheeseListing` to find `SearchFilter`. Let's break this onto
multiple lines. 

[[[ code('7049bbb6e2') ]]]

Searching by `title` and `description` is great. But what if I want
to search by *owner*: find all the `CheeseListings` owned by a specific `User`? Well,
we can already do this a different way: fetch that user's data and look at
its `cheeseListings` property. But having it as a filter might be super useful. Heck,
then we could search for all cheese listings owned by a specific user *and* that
match some title! And... if users start to have *many* `cheeseListings`, we
might decide *not* to expose that property on `User` at all: the list might be
too long. The advantage of a filter is that we can get all the cheese listings
for a user in a paginated collection.

To do this... add `owner` set to `exact`.

[[[ code('dd301f85a4') ]]]

Go refresh the docs and try the GET endpoint. Hey! We've got a new filter box!
We can even find by *multiple* owners. Inside the box, add the *IRI* - `/api/users/4`.
You *can* also filter by `id`, but the IRI is recommended.

Execute and... yes! We get the *one* `CheeseListing` for that `User`. And the syntax
on the URL is *beautifully* simple: `?owner=` and the IRI... which only looks ugly
because it's URL-encoded.

## Searching Cheese Listings by Owner Username

But we can get even crazier! Add one more filter: `owner.username` set to `partial`.

[[[ code('1a2d66f266') ]]]

This is pretty sweet. Refresh the docs again and open up the collection operation.
Here's our new filter box, for `owner.username`. Check this out: Search for "head"
because we have a bunch of cheesehead usernames. Execute! This finds two cheese
listings owned by users 4 and 5.

Let's fetch all the users... just to be sure and... yep! Users 4 and 5 match that
username search. Let's try searching for this `cheesehead3` exactly. Put that in
the box and... Execute! Got it! The exact search works too. And, even though we're
filtering *across* a relationship, the URL is pretty clean:
`owner.username=cheesehead3`.

Ok just *one* more short topic for this part of our tutorial: subresources.
