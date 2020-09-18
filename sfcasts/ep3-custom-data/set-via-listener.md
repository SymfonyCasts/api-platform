# Set Via Listener

Coming soon...

We needed to add a custom field and that custom field requires a service to calculate
its value. Then, as I mentioned earlier, there are three solutions. First, you could
create a totally custom API resource class. That's not actually an entity. And we'll
talk about that later. Or you can create an output DTO, or you can do what we did at
a non persisted field to your entity. I like this last option because it's the least
nucleolar. If most of your fields come from the entity, creating a custom resources,
overkill and output DTS, which are really cool, calm with a few drawbacks. So that's
what we did. And then we used a data provider to set that field.

The point is the cleanest way to create a custom field is to not really make it a
custom field, make it a real normal API field on your API resource class. But there
are multiple ways to set that field. The one we saw was the pure API platform
solution B pro is that you can exactly customize this field for your API. The
downside is that if you used your user, your user object to do anything outside of
your API, the is me field, won't be set. So here's an alternate idea. What if we at a
normal, boring Symfony event listener, that's executed early during the request and
we set the ismy field from there on a high level and makes sense if you have a user
object and it has an enemy property, then we should be honest. We should set this as
early as we can during the request.

Once security has figured out who is authenticated. So first let's remove our current
solution and user data for sister I'll comment out the dataset is me and add a little
comment now handled in listener, and then over in user data provider, I'll do the
same thing coming out. The first is me call. And then the second is me call. All
right. So now I'm back to a broken state where the ismy field has never set. All
right. So to create an event listener, find your terminal and run Ben consult, make
subscriber. Why should create an event subscriber and I'll call this set is me on
current user subscriber. And for the event we actually want Colonel that request, or
actually that's sort of its old name. Its new name is this long. Is this class name
right here? So I'll paste that down here. Perfect.

All right, let's go check out the new class in source events, subscriber and
brilliant we're subscribing to this event. And now our methods can be called early on
when Symfony is running. So our job is fairly simple. We are going to find out who
find the authenticated user if there, and if there is one we're going to, we're going
to, going to call set is me on it. So we know that we're going to need a constructor
public function,_underscore construct a security security I'll hit alt enter and go
to initialize properties to create that property and set it then down in on request
events. The first thing I'm actually going to do is say, if not event->is master
requests, then just return. That's not that important, but if you've seen our
something, something tutorial on, uh, our deep dive tutorial, where we talk about sub
requests, we don't need to run this on a sub request.

Only on the master request. Then I'll say user = this->security arrow, get user and
upload some documentation above this to help out my editor. We know this will be in
our project, our user entity or no, of course, if there is no user authenticated,
then we will do nothing. But if there is, this means that we found our user, we're
going to say user arrow,->set is meat troop. So the cool thing about doctrine is that
we just set the ismy field on the authenticated user object. If API platform later
queries for that exact user, it's actually going to reuse this exact same object in
memory. So the fact that we changed the ismy here means it's going to be changed on
the object. That's eventually returned from doctrine. All right. So to see this is
working, let's actually run over and run Symfony PHP, Ben /PHP unit. And I'm going to
run tests functional you the entire user resource test.

And

Well, we have one failure.

That's right. Two failures. Let's not do that.

So to see if this is working, go over and I'm going to go back to AP /API /user that
JSON LD and got it is me true. Oh, didn't mean to do that. I think I went backwards.
What just happened well done right now, this means that we're going to be setting the
ismy field for the current user, but purposely not setting it for all other users. So
actually now in the user class, I'm going to default is me to false mean that, Hey,
if we did not set this, it simply means that it must mean that we are not actually
that user. And then down here in the get is me. This logic exception is no longer
needed. All right. So let's try this. I'll actually go over and refresh my end point.

Perfect. You can see as me as true on the individual end point. And if I refresh the
main one, it is me true. And then false on all the other ones. We got it. So one
thing you might be wondering inside the subscriber is, okay, this was really easy
because the object we want it to operate on was the user object. So I could very
easily fetch the user object and set date on it. But what if the custom field we're
trying to set is not the user object. It's just some entity. Where do we get the
entity from in this situation to answer that question, I'm actually going to go over
it in Google for APF platform events. We have not talked about much about the API
platform event system yet, but one of the things I want to show you is that it gets
to, is that a lot of what happens between an API platform behind the scenes is
actually done via normal event listeners.

So for example, this Reed listener here is actually responsible for calling the data
providers to actually get the data from the database. The DC realize listener is what
DC realizes the JSON and updates or creates your object. Then later about validated
listener, right? Listener calls the data persisters and then there's some other ones.
Now, what I want you to notice is that both the real listener and the D serializer
listener are listening on kernel. That request that's the same event that we are
listening on and they will have a priority of four and two. Now, by default, when
you, when you create a subscriber, you can specify down here a priority since we
haven't. Our priority is zero. What that means is that we're actually being called
after these two listeners. Now that's really important because these listeners take
whatever data is being queried for.

Or maybe if this is a new item, the data that's being created and sets it as a
request attribute. So check this out. I'm actually going to go up here. And as an
experiment, I'm going to say D D->event, arrow, get request, arrow, attributes,
arrow, get data. That's the special key where API platform puts its data. Now, if we
spin over now and our refresh, the collections end point for users, you can see that
this is our page Nader object. The one we can loop over to get users. And if I go to
/user /one that JSON LD, then it is an individual user object. So you can use this
data key off of the request attributes to get access to the items that you're working
with, uh, on this request. So I'll remove that DD.

So this is really nice, but there's even another way that we could have loaded and
set the ismy field. The only problem with this solution is that it's only going to
work inside of a request. This isn't going to work inside of a custom command instead
of our CLI, because there is no request. So this listener's never going to be called,
which for a security makes sense. There's if we're running a console command and not
going to be an authenticated user anyways, but in other cases, you might have a field
that you want that, that, uh, you need to calculate. And you do want that to be set
inside of your CLI. Also let's solve that with a custom doctrine, post load listener,

