# Collections Write

Coming soon...

We know that if we want to, we can expose collections as a property. So for example,
if I look at user id to

we've decided that it's cheese listings are going to be exposed and we did that the
same way as every other property. We just add an at group user corn read so that it
was exposed. My question now is can we also modify the cheese listings property on a
user? For example, if I wanted to change, it's a little bit of a weird example, but
let's pretend that you can change who owns a cheese listing. If I wanted to change
who own this cheese listing when I could do is go up here to the put end point for
that. She's listing and actually change its owner to be some, uh, okay.

Okay.

Can you just owner to be some other different user, but could we do it in the other
direction? Could we actually create, here's what, here's my goal and it's kind of a
strange example. I want to create a new user. And when I do, and I want to specify a
cheese listing that that new user should own immediately. Right now the cheese
listings property is not modifiable on a user. And that's very simple. It's because
we're missing the group here. She's listening is readable, but it's not rideable. So
that's easy enough to fix. I'll make that group and ray and why the use user colon

right

now, we'll go back and refresh your docs. If you look at the post end point. Now we
do have a cheese listing his property. All right, so check this out. Let's create a
new user right here.

Okay,

I'll fill in all the boring information, pastor doesn't matter, username. And then
for cheese listings, this is going to be an array and when I want to do here is
actually pass an array with one item in it. /Api /cheeses. /one

okay.

And so in a perfect world that's going to happen here is it's going to create this
new user and immediately take this cheese listing one and make it owned by this user.
So it's going to change who owns that cheese. It's a bit of a strange example. Um,
but it is something that you're going to want that you might want to do in some
cases. All right. Plus try it.

Okay.

Execute and it worked out to one status code. It created a new user in this new user.
Now owns this cheese. Wait a second. How did that work? What I mean is we understand
that one email, password and username. Our Past, what's going to happen internally,
it's going to cap, it's going to call the set email property. But in this case when
he passed cheese listings, if we go and look in user and look for a set cheese
listings, there isn't a set cheese listings method. Instead the serializer works.
Bye.

Okay.

Instead search for ad. She's listing when we use the make entity command and whenever
you have a collection like this, instead of making a set cheese listings and adds an
adder and a remover and the serializer component is able to take advantage of those.
So internally, what happens when it sees this one cheese listing here at queries the
database for that cheese listing and then it calls add cheese listing in passes that
cheese listing here.

Okay.

And the really important thing is that inside this method, the inside the generated
code, it calls cheese listing set owner this. So this is the reason why the cheese
listing id one here, uh, why it's owner changed from its original owner to this user
here. And then everything saves. So there's a few pieces are generated. Code just
takes care of everything, but there's a fee, there are a few pieces to get this
working the way you want it to. All right, so let's try something else. Instead of
assigning an existing Jesus thing here, could we create a new one? So let's go. So
it's actually change this here. Instead of passing a string, I'm going to pass an
array and then I'll pass in the three fields that we need for each. It's listing,
which I can remember by going and looking at. You need title, price owner
description. But I'm not going to use owner. All right down here for price, about 20
bucks and for description.

Okay.

And we'll fill that in there. So the idea here is that on also update my email. You
need to update my email, me use names of, there's something unique idea here is we we
create a new user and we'd credit cheese listing at the exact same time. When we hit
execute, oh it doesn't work. It says nested documents for attribute cheese listings
are not allowed use I our eyes instead, the section air we saw earlier and the fixes
the same by default. If we only add above the cheese things property, we added the
user colon right here, which means that this property is technically modifiable but

okay

to actually allow us to add pass individual properties instead of just an IRI, we
need to go into cheese listing and add that user colon right to all to any of the
properties that we want to allow to be passed here. So for example, we know that in
order to create cheese listing, we need at least the title, description and price. So
I'm going to go up here and pass.

Okay.

User right above title,

above

price. And down here I'll look for a set text description. We'll also pass it there.
You can see all this stuff gets a little more complicated when you want to allow this
level of complexity, which is why I don't recommend doing it unless you actually need
it. So now when we execute, Ooh, it's closer 500 air. It says a new entity was found
through the relationship user that cheese listings that was not configured to cascade
persist operations. If you've used doctrine for awhile, you probably recognize this
air and maybe we even saw it in the previous course. Behind the scenes, it is
creating a new cheese listing. It's setting up on the cheese listings, but nothing is
persisting it. If this were a traditional Symfony application, we might just be able
to persist that in our code. But in this case, we want it to, the fix here is
actually to go into user, find the cheese, the in property and say cascade = persist.
So whenever a user is persisted, automatically persist. Any of the cheese listening
objects in this array.

Hmm. Okay.

And now if we try it one more time, yes it works. We now have a new user and it
created a new cheese in the background. And the reason that we didn't have to pass an
owner property here, because notice usually when you create a new cheese listing, you
need to pass owner. We did not need to pass owner down here, but not because of any
API platform magic very simply because internally after this new cheese listing
object was created, the serializer called add cheese listing. And of course inside of
here it takes care of co setting the owner to this object. So there's no API
platform, a serializer magic that is automatically setting the owner on this cheese
listing. It's just kind of good code that's taken care it. All right. Let's try one
more thing here. But like last time when you do allow, I'm kind of editing, uh,

yeah.

Editing embedded resources in this way, you need to think about validation. So for
example, if I change this achieves had three and then I'm going to just make the
title blank and it executes. That does work. You can see down here I allowed it to
save with the empty title. That's because as we mentioned earlier, that a validation,
we validate the user object, it doesn't automatically cascade and validate the cheese
listings. You can do that by adding at assert. Slash. Valid. Now if we go back up
there, I'll bump my email and using them again and then execute.

Yes, we get the 400 status code, cheese listings, left square bracket, zero dot
title. This value should not be blank. So again, all possible, it just gets a little
more complicated. All right, so let's try one more thing. So let's go ahead and close
up our post end point. I'm gonna make a get request to get the collection of users.
So let's see here. Let's play with user id for here. It has one cheese listing a
attached to it, which is a cheese too. So I'm gonna close up the collection end point
and let's actually, let's open up the put end point. So here's what I want to do.
Let's say that you have, this is actually a much more normal situation, sort of let's
say that we want to update user id for and we want to add a new cheese listing to it.
Like there's the cheese listings already saved to the database. We want to assign it
to this user. So what we can do here is under cheese listings, we already know that
we can pass new objects or IRI is here swinging past /API /cheeses /two that's the
existing,

okay.

She's listing for this user. If I look at user id for their attached to cheeses too.
And then let's also though add /API /cheeses /three

okay.

Which is currently owned by user id five. So when I execute this time, oh, I get a
syntax error because I have an extra comma. Let's try that again this time. Oh, of
course this value should not be blank. So is because I allowed cheese listing a three
to have a blank title. So now when we're trying to modify it and it's actually trying
to validate that now what I wanted in this case, it's because we allowed a bad record
in our database. Let's try a different value. Let's actually try reassigning um,
cheeses. One. There we go. This time it works. You can see that we now have these two
cheeses, cheeses assigned to it and this works the same way. It's simply and noticed
that this, uh, cheese too was a new one. It called add cheese listing with that
cheese listing id one and that changed the owner.

Cool. So now let's go back and say, all right, we want to do that, but let's actually
get rid. Now we want a remove operation. We're going to remove the cheese listing two
from this user for, so let me execute this time. Whoa. We get an air and integrity
can occurred. An exception occurred when executing update cheese listing set owner to
null a column owner id cannot be null. So this is really cool because the serializer
notice that we removed the cheese listing with ID two. And so what it did is actually
past that. She's listed with id two to remove cheese listings and remove cheese
listings thanks to our generated code. It actually sets the owner to know on that
cheese listing. In some cases, depending on your application and what you're trying
to accomplish, sometimes that's exactly what you want. Sometimes if you remove a
something like a cheese listing from a user, you want it to be sort of orphaned,
allow it to be known in the database. But in our case, we don't ever want to cheese,
listen to be a orphan of the database. And so instead what we really want is we
probably want, if a cheese listings removed from a user, we probably want that cheese
listing to be deleted.

So to do that, that's cool. All the way back up to the cheese listing his property
and we can add orphan removal.

True.

What that means is that if any of these cheese listings s uh, stop having their owner
assigned to them have their own or nullified or even changed, I needed to look that
up then that she's listing is going to be deleted. So if we get execute this time, it
works. And you can see [inaudible] /a bias. Let's cheeses /one is the only one there.
And if we go all the way back up and try look, cheese listening to is gone. And
actually while we're here, let's fix our broken cheese, the database. So I'm actually
going to update cheese listing three

yeah.

And sat in its title to something real well, execute that. Nevermind on that last
part. So this question is have a super complicated, it's possible, but you really got
to know what you're doing.