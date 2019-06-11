# Removing Items from a Collection

So again, all possible, it just gets a little
more complicated. All right, so let's try one more thing. So let's go ahead and close
up our `POST` end point. I'm gonna make a get request to get the collection of users.
So let's see here. Let's play with `User` id = 4 here. It has one `CheeseListing` a
attached to it, which is a cheese 2. So I'm gonna close up the collection end point
and let's actually, let's open up the `PUT` end point. So here's what I want to do.
Let's say that you have, this is actually a much more normal situation, sort of let's
say that we want to update `User` id = 4 and we want to add a new `CheeseListing` to it.
Like there's the cheese listings already saved to the database. We want to assign it
to this user. So what we can do here is under cheese listings, we already know that
we can pass new objects or IRI is here swinging past `/api/cheeses/2` that's the
existing,

`CheeseListing` for this `User`. If I look at user id for their attached to cheeses too.
And then let's also though add `/api/cheeses/3`

Which is currently owned by `User` id = 5. So when I execute this time, oh, I get a
syntax error because I have an extra comma. Let's try that again this time. Oh, of
course this value should not be blank. So is because I allowed `CheeseListing` a three
to have a blank title. So now when we're trying to modify it and it's actually trying
to validate that now what I wanted in this case, it's because we allowed a bad record
in our database. Let's try a different value. Let's actually try reassigning um,
cheeses. One. There we go. This time it works. You can see that we now have these two
cheeses, cheeses assigned to it and this works the same way. It's simply and noticed
that this, uh, cheese too was a new one. It called `addCheeseListing()` with that
`CheeseListing` id one and that changed the owner.

Cool. So now let's go back and say, all right, we want to do that, but let's actually
get rid. Now we want a remove operation. We're going to remove the `CheeseListing` two
from this user for, so let me execute this time. Whoa. We get an error and integrity
can occurred. An exception occurred when executing update cheese_listing set owner to
null a column `owner_id` cannot be null. So this is really cool because the serializer
notice that we removed the `CheeseListing` with id = 2. And so what it did is actually
past that. `CheeseListing` with id two to remove `CheeseListing` and
`removeCheeseListings()` thanks to our generated code. It actually sets the owner to know on that
`CheeseListing`. In some cases, depending on your application and what you're trying
to accomplish, sometimes that's exactly what you want. Sometimes if you remove a
something like a `CheeseListing` from a `User`, you want it to be sort of orphaned,
allow it to be known in the database. But in our case, we don't ever want to
`CheeseListing` to be a orphan of the database. And so instead what we really want is we
probably want, if a `CheeseListing` removed from a `User`, we probably want that
`CheeseListing` to be deleted.

So to do that, that's cool. All the way back up to the `$cheeseListings` his property
and we can add `orphanRemoval=true`

What that means is that if any of these `CheeseListing` s uh, stop having their owner
assigned to them have their owner nullified or even changed, I needed to look that
up then that `CheeseListing` is going to be deleted. So if we get execute this time, it
works. And you can see `/api/cheeses/1` is the only one there.
And if we go all the way back up and try look, `CheeseListing` to is gone. And
actually while we're here, let's fix our broken cheese, the database. So I'm actually
going to update `CheeseListing` 3

And sat in its title to something real well, execute that. Nevermind on that last
part. So this question is have a super complicated, it's possible, but you really got
to know what you're doing.
