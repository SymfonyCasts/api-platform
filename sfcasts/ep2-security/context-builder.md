# Context Builder

Coming soon...

Right now when you make a get request to get a, a collection of users or a single
user,

um,

both of those are normalizations of both of those are going to use the user colon,
Reid group. So you're going to get the email field, you're going to get the user name
field and the cheeses, things field and the cheeses, some field. But we want to do
now though is do something a little bit smarter where we are able to dynamically add
also another field, another group Admin, colon read. But only if the user's actually
in Eben. The way it goes is something called a context builder. So you remember when
you go through um, normalization or de normalization, you have something called a
context and that's a way to pass options to the serializer. Now like by far the most
common option is the groups. So we can do this, we can create a class that, that AK
platform will always call whenever it's building the context for serialization. And
then we can actually add a dynamic group to it. So Google for API, platform, context
builder, you'll find this serialization page here. If you scroll down a bit, it's
going to talk about changing the serialization context dynamically and it kind of
gives an example here. I am going to steal the uh, book context builder example. It
has here instead of source inside of the s inside a serializer directory though that
can be anywhere plus create a new pizzeria class and we're going to call this,

okay,

admin groups, context building. Cause the purpose of this is going to be able to add
that admin colon and read a group and also an admin colon, right group globally to
every resource if the is and avid user. So I'm going to start by just completely
pasting in the code here. And then the only thing we need to change is that this is
called Admin, that groups context builder. So here's how it works. Oh and then the
second part, now in a lot of cases in Symfony and API platform, just by creating a
class and making an implement some interface, boom, it's included in the system.
That's not the case where they context built in. You actually need some service
configuration for this. So go to config services.yaml and down here at the bottom,
we're actually gonna override our uh, class. So we're gonna say app /serializer
/Admin at groups, context builder. And then I'll look back into docs. Actually kind
of steal some stuff here. I'm actually going to copy these three lines that they have
and then paste them over here.

Then also change this, uh, arguments here to uh, admin groups. Context builder. This
is a bit confusing because it uses a slightly more advanced feature of Symfony
service container called service decoration. So the idea is that there is only one
service in the container that is responsible for building the context. And it's this
service here at API platform, serialize or context building. That's a core service
provided by API platform. So every time it needs to build a context, API pop from
calls that service and it builds the context. So the problem is we want to take
control of that now, but we don't want to replace the core functionality we want to
add to it. The way this is done in APAP platform is via service decoration. So what
this says here is this says that we have our new service called admin groups. Context
builder.

When we say it decorates API platform serialize or context builder, what that
actually says is it's actually going to replace that service. So as soon as we have
this, uh, uh, cohere, when API platform asks for the API platform serialize or
context builder, it's actually going to get our service. So it's kind of a way to
override the existing functionality. And of course we don't want to completely
override it. So what this decoration feature does is it allows us to reference
another service by the w that'll have the same id as our service dot enter. And what
this is going to be, what this weird string is going to refer to is it's actually
going to refer to the original context builders service. So if you look in our admin
groups, contacts builder, you're going to see that we implement serializer context
builder interface. And the first argument is a serializer context builder and a
phrase called decorated.

That is the core serializer context builders service. So we're being passed the core
service and what's, and the only method inside this class is called create from
requests. So one API platform a needs the context. Now it's going to call create from
requests on our service, but check out, the first thing that we do is we call
this->decorated->create from request, we call the core context builder. We pass it
all the same arguments and we let it build the contacts using its base. It's normal
functionality. That normal functionality we rely on that that normal functionality is
going to, for example, read the normalization context and de normalization context
off of our resource. So we do want that functionality. Then below this we can add any
extra functionality that we want.

So it's a really cool way to extend a core service, uh, but it is a bit more advanced
if you haven't seen it before. So here's what I'm gonna do. I'm actually gonna delete
the existing functionality here though it's pretty close to what we're going to do,
I'm going to say is admin equals. And then you'll notice that one of the other
services that when we keep copied in is called authorization checker interface. This
is a service that allows you to check whether or not a user has a role so far. Like
for example, in our voter when we wanted to do that, we were um, Ottawa wiring a
class called security. While security and add authorization checker interface are
basically both, both ways to do the exact same thing. You can use either of those
when you want to check to see if a user has access to something.

So his admin = this->authorization checker->is granted and we'll say role admin. That
will be our goto role that admin users have. And then down here you can say if
context error groups and is Admin, this is when we are going to add our groups to the
context. Now why am I checking for context groups? We don't really need to do this,
but in theory, if, um, whatever resource we were working with didn't have any groups
on it, then we don't want to add more groups because that would actually, um, when it
doesn't have any groups, that means serialize everything. So if we added groups, um,
it would actually make it serialize less, if that makes sense. It's not really
applicable to our case because I always like to have a normalization context and de
normalization context on my resources. So context groups is always going to be set in
our case at this point.

So as in here we can say context groups and then we'll add a new group to that that's
equal to, and you might think you're, I'm just gonna say admin read, but we can
actually be smarter than that. This grant from crate request is going to be called
when the um, object is being, um, serialized and also be serialized. And that's what
this normalization flag here is about. If, if this is being created for the purposes
of normalization for turning something into JSON, for example, then this will be
true. If this is being used for d serialization for DC, realizing JSON back into an
object, this'll be false. So we can actually say, if this is normalization, then
we're going to add in read else. We're going to add admin colon, right? So we can
also have a, a nice flag that allows us to only set certain fields and that's it.
I'll even get rid of this resource class thing here because we are applying this to
all of our resources. And by the way, you can actually have, um, as many, uh, context
builders as you want. I keep talking about how this decorated object is API
platforms, core context builder, but actually you can decorate this 10 times and
you'd actually have 10 layers. We're actually calling the next most inner decorated
and that it calls the next most enter and then it calls the next most entered. So it
can get even a bit more complicated, but for the most, but that's not really a detail
that you need to worry about. Yeah.

So anyways, let's go over and run our tests. So then [inaudible] that's just filter =
test get user. And this time

it passes. So just as a reminder, we have just checked that a normal user will not
get the phone number field. But as soon as we set ourselves to rural admin and make a
request, we do get that phone number. Yes. So now we're dangerous because one of the
original things we wanted to do is we wanted to make the roles field writeable only
by admins. And we can do that now. So before I change this here, let's actually
update our tests for this. So we'll go up to test update user and let's pretend here
that with our JSON

and in addition to user name, we also pass a rolls field. So we're gonna try to do is
we're gonna try to add role admin here, but notice the word as long. Then as a normal
user, we are not an Admin user. So this actually will be ignored. It will basically
be like the user. It's not a validation error in APM platform. It's just if you send
a field that is not part of the, uh, something that you can de normalize, it's just
going to be ignored. So down here to really be sure at the bottom, but I'll say is
eem = this arrogant entity manager and was query for that. Freshman user users will
say user = e m->get repository.

Okay.

User ::class, fine use Arrow, get id. And I'll put a little documentation button
there that says that this will be a user object. And then we'd say assert equals. And
what we expect actually is for rolling user to be what's inside of user Arrow. Get
roles. And just as a reminder, the reason for that is when a we create a user and an
empty user, the roles property is going to be empty by default. But if you look down
at the get rules property, the way you've made it for the security system is we
always add at least role user. So if it's empty in the database, when we call get
roles that we'll come back with roll user in it. So that's why we're checking down
here for just role user, but not role admin because that's not in our on our group.
So finally with this for around this test right now, all copying the test update
user, PHP bin /PHP unit dash filter = test update user. It's going to fail because we
still have that role. Is property open to everyone. But now if it changes to Admin
Colin, right

where you run it again and I scroll up, yes, it passes. Now one last little note I
want to talk about with all this cool dynamic group stuff is that like I mentioned
earlier, you're not going to see of this dynamic rules stuff us show up inside of
your documentation. So there's not like a different read model down here that says
that you get the, like if you look at the, uh, the get endpoint for a single user,
you're going to see, you're going to get email, you're going to get, it says you're
gonna get back email user name, and she's listening. And even if I were logged in as
an admin that wouldn't imagine the change, those are static. So as far as the
documentation is concerned, the only thing that's taken to account are the, um, the
normalization and de normalization groups that are actually above your class. It
doesn't call your context builder for that. Um, and that's done on purpose, but it's
something that you need to be aware about. Uh, you're going to, basically, your admin
is going to have access to fields that aren't documented.