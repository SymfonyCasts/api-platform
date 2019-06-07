# Collections Write

Coming soon...

We know that if we want to, we can expose collections as a property. So for example,
if I look at `User` id = 2

we've decided that it's `cheeseListings` are going to be exposed and we did that the
same way as every other property. We just add an `@Groups("user:read")` so that it
was exposed. My question now is can we also modify the `cheeseListings` property on a
`User`? For example, if I wanted to change, it's a little bit of a weird example, but
let's pretend that you can change who owns a `CheeseListing`. If I wanted to change
who own this `CheeseListing` when I could do is go up here to the put end point for
that. She's listing and actually change its owner to be some, uh, okay.

Can you just owner to be some other different `User`, but could we do it in the other
direction? Could we actually create, here's what, here's my goal and it's kind of a
strange example. I want to create a new `User`. And when I do, and I want to specify a
`CheeseListing` that that new `User` should own immediately. Right now the 
`cheeseListings` property is not modifiable on a user. And that's very simple. It's because
we're missing the group here. `CheeseListing` is readable, but it's not writable. So
that's easy enough to fix. I'll make that group and ray and why the use `"user:write"`
now, we'll go back and refresh your docs. If you look at the post end point. Now we
do have a `cheeseListings` property. All right, so check this out. Let's create a
new `User` right here.

I'll fill in all the boring information, pastor doesn't matter, username. And then
for cheese listings, this is going to be an array and when I want to do here is
actually pass an array with one item in it. `/api/cheeses/1`

And so in a perfect world that's going to happen here is it's going to create this
new `User` and immediately take this `CheeseListing` one and make it owned by this user.
So it's going to change who owns that cheese. It's a bit of a strange example. Um,
but it is something that you're going to want that you might want to do in some
cases. All right. Plus try it.

Execute and it worked out to one status code. It created a new `User` in this new user.
Now owns this cheese. Wait a second. How did that work? What I mean is we understand
that one `email`, `password` and `username`. Our Past, what's going to happen internally,
it's going to cap, it's going to call the set email property. But in this case when
he passed `cheeseListings`, if we go and look in user and look for a `setCheeseListings()`, 
there isn't a `setCheeseListings()` method. Instead the serializer works By

Instead search for `addCheeseListing()` when we use the `make:entity` command and whenever
you have a collection like this, instead of making a `setCheeseListings()` and adds an
adder and a remover and the serializer component is able to take advantage of those.
So internally, what happens when it sees this one `CheeseListing` here at queries the
database for that cheese listing and then it calls `addCheeseListing()` in passes that
`CheeseListing` here.

And the really important thing is that inside this method, the inside the generated
code, it calls `$cheeseListing->setOwner($this)`. So this is the reason why the 
`CheeseListing` id = 1 here, uh, why it's owner changed from its original owner to this user
here. And then everything saves. So there's a few pieces are generated. Code just
takes care of everything, but there's a fee, there are a few pieces to get this
working the way you want it to. All right, so let's try something else. Instead of
assigning an existing `CheeseListing` here, could we create a new one? So let's go. So
it's actually change this here. Instead of passing a string, I'm going to pass an
array and then I'll pass in the three fields that we need for each. It's listing,
which I can remember by going and looking at. You need `title`, `price` `owner`
`description`. But I'm not going to use `owner`. All right down here for price, about 20
bucks and for description.

And we'll fill that in there. So the idea here is that on also update my email. You
need to update my `email`, me `username` of, there's something unique idea here is we we
create a new `User` and we'd credit `CheeseListing` at the exact same time. When we hit
execute, oh it doesn't work. It says nested documents for attribute cheeseListings
are not allowed use IRIs instead, the section error we saw earlier and the fixes
the same by default. If we only add above the cheese things property, we added the
`user:right` here, which means that this property is technically modifiable but

to actually allow us to add pass individual properties instead of just an IRI, we
need to go into `CheeseListing` and add that `user:write` to all to any of the
properties that we want to allow to be passed here. So for example, we know that in
order to create `CheeseListing`, we need at least the `title`, `description` and `price`. So
I'm going to go up here and pass.

`user:write` above `title`, above `price` and down here I'll look for a 
`setTextDescription()`. We'll also pass it there.

You can see all this stuff gets a little more complicated when you want to allow this
level of complexity, which is why I don't recommend doing it unless you actually need
it. So now when we execute, Ooh, it's closer 500 error. It says a new entity was found
through the relationship `User#cheeseListings` that was not configured to cascade
persist operations. If you've used doctrine for awhile, you probably recognize this
error and maybe we even saw it in the previous course. Behind the scenes, it is
creating a new `CheeseListing`. It's setting up on the `cheeseListings`, but nothing is
persisting it. If this were a traditional Symfony application, we might just be able
to persist that in our code. But in this case, we want it to, the fix here is
actually to go into `User`, find the `$cheeseListings` property and say `cascade={"persist"}`.
So whenever a user is persisted, automatically persist. Any of the `CheeseListing`
objects in this array.

And now if we try it one more time, yes it works. We now have a new user and it
created a new cheese in the background. And the reason that we didn't have to pass an
owner property here, because notice usually when you create a new cheese listing, you
need to pass `owner`. We did not need to pass `owner` down here, but not because of any
API Platform magic very simply because internally after this new `CheeseListing`
object was created, the serializer called `addCheeseListing()`. And of course inside of
here it takes care of co setting the `owne`r to `$this` object. So there's no API
Platform, a serializer magic that is automatically setting the `$owner` on this 
`CheeseListing`. It's just kind of good code that's taken care it. All right. Let's try one
more thing here. But like last time when you do allow, I'm kind of editing, uh,

Editing embedded resources in this way, you need to think about validation. So for
example, if I change this achieves had three and then I'm going to just make the
title blank and it executes. That does work. You can see down here I allowed it to
save with the empty `title`. That's because as we mentioned earlier, that a validation,
we validate the `User` object, it doesn't automatically cascade and validate the 
`$cheeseListings`. You can do that by adding `@Assert\Valid()`. Now if we go back up
there, I'll bump my email and using them again and then execute.

Yes, we get the 400 status code, `cheeseListings[0].title`
This value should not be blank. So again, all possible, it just gets a little
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