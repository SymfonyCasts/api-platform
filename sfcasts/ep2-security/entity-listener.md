# Entity Listener

Coming soon...

We decided to make the owner property a field when you create a cheese listing, which
was nice because it gives us some flexibility maybe to create an admin section in the
future where an admin user can create a cheese listing and assign it to any other
owner. In fact, in the validator is owner validator is valid owner validator. We even
allowed for this, we allowed an admin user to be able to set that owner value to
anything. So that's really nice. One downside is that this field is required. So for
the vast majority of the use cases when someone is just creating a cheese listing
under their own account, they need to make sure to pass their own owner. It's very
explicit, but we couldn't make this easier by making that optional and automatically
assigning it to the user though the authenticated user if it's not sent. So let's do
that. And first we'll do in our test will just be a small change here. So if you
look, we're kind of a, we have the data here with title, description, price. We are
sending a post request to /aps size cheeses and only sending up those fields. So
right now that's giving us a 400 air cause we're missing the owner field. Let's
change that to be a two Oh one let's say that no, if you send those three fields it
should work because we're going to automatically assign that owner property.

Yeah. The change we'll make right now is in cheese listing itself above the owner
property. We'll take off that assertion so we're not automatically setting the owner
yet obviously. So if we run the test we can see, yeah, awesome failure. Let's scroll
all the way up here. We're getting a 500 air because it's trying to insert the cheese
listing with an owner ID that is null. So there are a number of different ways to
solve this, but one really nice way is actually just to rely on a doctrine, a event
listener or a doctrine entity listener. Those are two different things that are
basically the same. So we don't even need to rely on an API platform feature here. We
can just say anytime a cheese listing is inserted into the database, let's
automatically set the owner property to the current user. If that property isn't
already set.

So in the source directory, let's create a doctrine directory though, like usual, it
doesn't matter where this lives. Then we're in credit new class here and we'll call
it Jeez listing set owner listener. This is going to be an entity listener. So this
is going to be a listener that um, this is gonna allow us to inside of here, say a
public function free persist and this will be past the cheese listing objects. So now
the way the entity entity listeners work is that you can add a public function inside
of this class for all the different events that you want to listen to, like pre
persist, pre update, uh, pre remove.

And then to tell doctrine that this listener exists, we need to go into our cheese
listing entity. And on top we're going to add a new annotation. I'll put it by my
other doctrine annotation. Actually, let's move that after an annotation at the
bottom. So we're not mixing the API filter API stuff with it and say at entity
listeners at RM /entity listeners, pass us an array. And here we're going to give it
the full class named that. So app /doctrines /and I'll go copy the CI's listing set
owner listener. So that's it for the basic setup because we've added this here,
doctrine is going to see that we have a pre persist method and it's going to
automatically call this method every time it persists, which means inserts a new
cheese listing.

All right, so the actual logic in here, um, we just need to be able to find the
currently authenticated user. So we know that's going to require us to add a
constructor and type into the security service and then do our usual alt enter
initialize fields to create that property and set it. Now down here, the first thing
I wanna say is if CI's listing->get owner, and we're just going to return if the
cheese listing already has an owner and we don't want to override that or set it, but
if this->security->get user. So if, um, there is no owner and the user is logged in,
then we will say, Jeez, listing->set owner, this->security arrow, get user. Here we
go. It's just that simple.

Well, if we run the test now it's doesn't work. It's Kinda close. Look here it says
argument count. Too few arguments to cheese listing set owner underscore, honest work
construct is zero past. So it looks like it. Doctrine is internally actually trying
to instantiate, um, our objects so it can call it pre persist on it. So the question
is, why is it trying to insert? You can actually see the code here. Its doctrine
itself is trying to instantiate that object, whereas in Symfony, the container is
supposed to instantiate all of our services. The question is, why is doctrine in
staging this object? Instead of asking, um, the, uh, the container for it? And the
reason for that is just kind of the way that that bundle works. In order to tell
doctrine to fetch this from the container, instead of trying to instantiate it
manually, we need a little bit of extra service configuration.

So when config services.yaml, we're going to add that class. We're going to override
the service definitions. We'll add /doctrine /and I'll go grab the cheese listing set
owner listener class again. And we need to do here is we just need to add a tag. The
tag is called doctrine dot or m. Dot. Entity listener. That's enough to basically
say, hey, this is an entity listener. So when you go, when you need it, grab it from
the container instead of instantiating it manually. And because it's grabbing it from
the container, that's actually gonna mean that our auto wiring works like normal.
Just when you run it again. Yes, we've got it. So we have the flexibility that we can
pass the owner property when we install, when we create a cheese listing. But if we
don't, it's going to fall back to whoever is currently logged in.