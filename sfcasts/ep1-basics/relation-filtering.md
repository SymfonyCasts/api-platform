# Relation Filtering

Coming soon...

Yeah,

God directly to /API /users /five that JSON Ld, that's one of our users, that Owens
one cheese listing. One of the things we do with that, she's listening as we added a
really cool filter called a property filter which allowed us to do things like this
question mark properties = um, username. If we just wanted to get that, we don't have
that enabled right now and I think that's really useful feature. So let's add that.
So Bob user, we're going to add our first at API filtered and inside of here,
property filter ::class. And just like we had to do before, we're going to use
property filter on top manually. Cool. As soon as we did that one refresh we get that
superpower. But here's the really cool thing. I'm gonna tick off that question. Mark
Properties = for a second cause notice we in this, we set up this to have it in
embedded cheese listing. We actually see the data. Turns out that property filter
works for embedded data too. You just have to know the syntax. So in this case we can
say and properties left square bracket. Instead of the inside of here we actually put
cheese listings and then we do the open close = and let's say we just want to get the
title of that cheese listing, which in this case is actually empty and it works. So
property filter is is something we get for free even when we embed things.

All right, so speaking of filters, our cheese listing, we ended up bunch of filters.
We can search by title, description, price, lots of different things. Let's actually
add a couple of other ones. So I'm gonna Scroll up to the top here and probably the
most useful when we have here is a search filter allows us to search on title and
description. Actually going to break that onto multiple lines cause we are going to
add a little bit more here. So one of things that might be useful as it might be
useful for us to find all cheese listings that are owned by a specific user. Of
course we could fetch that user's data the other direction, but we might want to do
that. And actually we can here by adding owner. In this case we're going to use
exact, we want to match the exact owner so as soon as they go back we can refresh our
docs and not surprisingly when we try out our good end point, we have a new spot here
where we can actually do it in owner or we can actually find by multiple owners. And
the way this works is you do /API /users /four you actually use the eye, our eye. You
can use the id but that's deprecated. So if we try that, yes we get the one she's
listing for that specific user. And if you look at the way that syntax circus looks
is really nice, it's question mark owner's = and this looks ugly because it's URL
encoded but it's just the eye, our eye to that user. So super handy.

But you can even do more than that at another filter, say owner dot user name and set
this to partial. This is pretty sweet. So refresh the docs again, open up our
collection point and now we have an owner. That username thing here where, check this
out. Let's search for head because we have a bunch of cheese, head usernames, hit
execute, and that finds two cheese listings by users for and users five. And actually
let's there, let's make sure this works. Let's go back and look down here and look at
exactly what those two users look like. So users four and five are cheese had too,
and she's had three. So let's actually look for cheese had three specifically. So
backup in our filter. Let's look for owner. That username = cheese had three. It
should find just one results. And it does. And again, the way this looks in the URL
is super clean. It's just question mark, owner and an owner. Dot. Username = and then
what you're searching for. So it's very powerful that this filtering system, it works
just as well. Once you start diving into embedded objects.