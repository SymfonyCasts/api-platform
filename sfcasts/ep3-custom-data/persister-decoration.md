# Data Persister Decoration

In the last course we heard of this thing called a user data persistent. Now let's
back up here real quick. Whenever you use a post or put endpoint after API platform,
DC realizes that data into an object. It needs to see that object somewhere. Usually
we think about saving an object to the database, but we could save an object
anywhere. APAP, Offerman doesn't care. Now so far, our two API resources CI's listing
and user are both doctrine entities, any pamphlet from as special support for doctor.
So without us doing anything, API platform has a core doctrine data per sister. So
whenever we make a post request or I put requests to a, to an API resource, that is a
doctrine entity, that core data presenter knows how to save that object database. So
normally we don't need to create these data. Persisters because there's already a
doctrine data per sister in the core that basically calls persist and flush for us.

But whenever you need to do something like right before or after an item is saved, a
database is persistent is the way to do it. We created a user data persistent because
we needed to encode the plain password and then set that on the password field before
we saved it. Now, when you create a data persister you of course need to implement
this data per sister interface. And then you add a supports method that says what
object or objects you support. Now, as soon as we add this one and said that we
supported user, it meant that we are now 100% responsible for persisting user
objects. We are no, we have, we're no longer using the core doctrine data persistent
for users. This is why we did our custom work up here. But then we call persist and
flush to prove this. I'm actually going to comment out the persist call here and
let's run one of our tests. So if I got to test functional user resource test, test
create user should have failed because the password is never going to get encoded. So
I'm going to copy that method name. Now I run Symfony run bin /speech unit dash
filter = test create user. And yes, it fails. It passed a second ago, but now you can
see that we get a 400 bad request to something internally exploded.

If we go on and put that for, to call back and run again, now it passes. So the fact
that we are now responsible entirely for persisting, this object is not that big of a
deal, but it would be even better. If we could do our custom work here and then just
call the core data per sister so that it can do its normal thing. And to do this,
we're going to use decoration. So instead of injecting the NC manager up here, I'm
just going to replace this with data per sister interface. Now call this decorated
data for sister a copy of that, and we'll rename our variable and our entity manager
property to be a decorated data for sister. So this is nice because down here instead
of Pentium manager, persistent flush, it's just this era of decorated data per sister
era persist data.

And then down here for remove, it's the same thing. It's now just one line.
This->decorated data per arrow, remove data beautiful. Now, if we try this and I'll
warn you don't try this, unless you have XD bug installed because we are going to get
horrible recursion. Ah, you can see maximum function, nesting level of 256 reached
recursion. All right. So let's figure out what's going on here now, obviously,
because we just said data per sister interface, we never told Symfony what to PA
which servers to pass us here. It must mean that this is being passed via auto
wiring. So let's go get a little bit more information about this persister so I'm
going to run it and Ben console, debug auto wiring and search for, for sister and
perfect. Okay. You can see this apparently is an instance of debug API platform, data
per sister. All right, let's do a little digging. I'm going to go in and run bin
console, debug container and pass that service ID.

And you can see that this is some survey traceable chain data persister. Now I'm
going to cut a few levels off. Now, if we don't get to that traceable chain data per
sister, a little bit, what you find out is that the data per sister is actually a
chain data presenter. What that means is that a data persister with a bunch of other
data persisters inside of it. So when, whenever something needs to be persisted API
platform, this one data per sister, but then that data processor loops over all of
the data persisters inside of it calls the supports method on it on each1. And as
soon as it finds one that returns to for sports, it calls that one.

So this is really cool. If you need the entire data per sister system, you can just
auto wire, this one service and then call whatever method you need on it. The problem
is that we are effectively calling ourselves again, API platform, originally calls
persist on that chain data per sister. It changed it for sister calls persist on us,
and then we call persistent back on the chain data persister. So instead of calling
the entire and injecting the entire data persisters system, the chain data persister
into ourselves, let's just call it the specific, uh, data persister that we want. The
one that's could normally use for entities to find this. Let's actually do a little
bit of digging summer on bin console, debug container, and then search that for
API_platforms. That's a filter to all the API platform services, and you can see that
there are a bunch of them and let's look inside here for doctrine.

All right. So dr. Metadata factory area here, APAP platform doctrine, ORM and data
persister, that's probably the one we want. So I actually going to type in 105 here
to get more information on it. And yes, you can see as a class call data per sister.
And if we want, we can hit Shift + Shift over here, search data resistor, make sure
you include non-project items. And we can load the one from APAP platform doctrine.
Here. It is the data per sister, which it looks a lot like we'd expect is some
persists and some flushes inside of it. So the point is we don't want to call the,
when we call, we don't want to actually inject the entire data persister system. We
just want to inject the normal one that we use the doctrine system. And we now know
that that service ID for that is APAP platform doctrine, ORM data.

Persister sorry. I kind of copy that now to override which service Symfony is
injecting. We can specifically configure this service. So we're going to go to config
services diagonal then down here at the bottom, I'll say app /data per sister is the
directory /user data per sister. And here I use it and fine. And we will say that
argument name is decorated data per sister. So we'll say dollar sign, decorated data
for sister and set that to ASP and then the service ID. So now Symfony knows to
inject this specific object instead of the entire system. As that argument, this time
when we run our test again, yes, it passes not, this is pretty deep in, in kind of
digging and Symfony, but this is some of the things we're going to be doing in this
tutorial. So you really understand what's going on now notice this isn't service
decoration. When you service decoration before with our context builder service. And
we said, decorates this other service. It's a very similar idea because both are
technically a decoration on an object oriented level, but the way they're done in
Symfony is a little bit different.

Now, first let me talk, let me, let's talk about the context of build their systems.
So I might actually open up that class, which is app serializer, admin groups,
context builder. Now notice at first, this is a, this implements serializer context
builder, and this is a class w we use to actually add conditional groups based on the
request now known as it implements this interface. And we also inject a decorated
thing. So on a high level, this is class decoration and in user data persister, we
are also doing class declaration, but the way we configure this in Symfony is a
little bit different because the way these systems work is a little bit different
inside of API platform.

So check this out, run bin console, debug container, and then search for context. I
want to find that, uh, inside of here, you're going to find something called API
platform, serializer or context builder. That is the one context builder service in
the system. That's ABM arms, core context, builder service. So when he needs to build
the context, this is the service that it uses. So I'm actually going to copy this and
rerun this with Ben console, debug container paste, that service a and then say, dash
dash show dash arguments. Perfect. I'm noticed. And he named me this class is not
something like chain, context builder, or something like that. Serialize or filter
context builder. And then inside of it, check this out. One of the arguments that
it's passed is actually our app serializer admin groups, context builder. Now I know
this may be a little bit confusing, but here's, what's going on with the co with the
context builder service and API platform.

There's only one of those services. And instead of being a chain context builder,
that just loops over all the context builders inside of it, there's simply only one.
So if you want to add your own context builder, the only way to do that is to replace
the core one. And that's actually the job of service decoration, which API platform
relies on in several different places. What this basically says is I want to, I
wanted to have this service replace the core context, builder service, but I want to
take whatever that is and inject it inside of us.

What you ultimately get is a bit of a Russian doll situation where you actually have
the outer one in this case, it's called serializer filter context builder, but then
it does its work and then calls our admin groups context builder. And if you actually
run that same debug container command on our service, you'll see that our service has
actually passed another service called with the same name called dot enter. And if we
actually look at what that service looks like, that one is actually another one
called serializer context builder. It's confusing to see this way, but we're seeing
is that that first class of the outer one, then it calls ours. Then it calls see,
realize their context builder. And, and that's how it creates a chain. Now, this is
different than the date of her, but because the date of her sisters have that chain
on the outside.

So we didn't need to actually replace the main data. Persister all we need to do was
just add our own data for sister to that chain. And then if we did want to call, uh,
for do want new, if we didn't want to call one of the other data for sisters, we can
inject it just like normal. So for me, this is actually a better design. This makes
our life a lot easier. The, uh, service decorations, a lot more of a confusing thing
to kind of think about in your head. Um, but fortunately, in this case, the data
resistors are a lot simpler. So that's why you have these two different approaches,
which are very similar, but you kind of approach them slightly different. That's
still confusing. You let us know and we'll try to clear it up. Alright, next, our
user data for sister allows us to do action before safe. What if we want to do
something only when an object is created or only one of the object is updated or only
on a specific operation once find out how to do that next.
