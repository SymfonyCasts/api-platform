# Subresources

Coming soon...

One of the things that you might need to know is what are all the `cheeseListings` for
a specific user and we just made that and that's already possible in a couple of
different ways. Uh, we just made it possible for us to search by a specific owner
here and that returns is all the cheese listings, a collection of that she has
listened for that owner or like we've had this entire time. We can just use the GET
end points. Let's look for `User` whose id is 5 and Jesus. Things as already a
property on there, but sometimes people want to have a URL that looks and want to be
able to do something like make a get request to `/api/users/4/cheese_listings`.

Basically what we have here minus `/cheeses`, that doesn't work, but that's something
that's very attractive and that is possible. It's called a sub resource. Right now we
have two resources and each resource kind of has its own base URL `/api/cheeses`
and `/api/users`. But it is possible to kind of move cheeses under users and it
works like this in `User`. Finally, `$cheeseListings`, property and add `@ApiSubresource`
as soon as you do that for your fresh with documentation, check it out. We actually
have now `/api/users/{id}/cheese_listings`. It shows up in two
spots cause it's kind of related to users and kind of related that she does as well.
It knows it as `cheese_listings`. That is something that you can configure.

So if I changed my [inaudible] over here to `cheese_listings` up and let's
actually add `.jsonld` on there to make it more interesting. There you go. We
now have a collection resource for all cheeses that are owned by this `User`. So it's
kind of a cool, it's kind of attractive thing, but I actually want, and I wanted to
show her to you, but I don't recommend doing this. The reason is that it just adds
more endpoints as the more to think about. And the other thing is we already had in
multiple ways to get this information as well. Adding this vanity way of getting it
is not necessarily a good idea. So I want you to see it as possible. It works well,
but I am actually going to remove it from there and also removed my use statement
just to clean things up a bit. All right guys, there's a lot more to talk about the
API Platform. Um, next tutorial we're going to talk about security, which is a huge
topic with API Platform.