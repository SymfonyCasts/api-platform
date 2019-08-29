# Custom Normalizer

Coming soon...

We now know how to add dynamic groups. We added this admin colon is Admin, Colon Reid
group have a phone number is only added. It's dynamically added via our context
builder if the users and Admin. So if the user has role Admin, we always add either
admin reader, Admin, right? So now I have a really cool way to run around and hide or
include fields or not include fields based on that admin role. But what about, but
one of the shortcomings of a context builder is that it's global. It doesn't allow us
to change groups on an object. So what if but there's a limitation in the context
builder.

Okay,

but the phone number field, what if we also wanted the owner of this user, the user
itself to be able to see the phone number but nobody else wants to do that. We could,
you might think, okay, well let's add another a

group here

called owner colon reed. You say, okay, well we'll do as well. We'll add this group
dynamically. Um, if the current user is trying to, if the a is trying to read this
object. But the problem is if you look in the context builder, we're not past which
object we're currently working with.

Complex builders are not the way to dynamically add or remove groups on an object by
object basis. They're just a way to do it on it, on a global or a class by class
basis. So to do this, we need another strategy and we, what we need is a custom
normalizer. So check this out, let's go over and we'll use make serializer
normalizer. Let's create one called user normalizer. So the idea is that whenever in
user object is changed and JSON, it goes through a normalizer and there's already a
core normalizer that takes care of that, we're now going to hook into that process so
that we can change the groups that are being used for normalization.

So if checkout source serializer normalizer, we have our new user normalizer and this
works similar to the context builder. We have a method down here called supports
normalization. In this, to put this time, we're actually past these specific object
that's being normalized. If we return true from supports normalization, it says that
we normalize this object. And so then we the normalization logic inside of normalize.
And notice the way it's set up now is where we've actually, um, we're auto wiring the
object normalizer that's the kind of the main normalizer that's used in the
serializer. And so right now this normalizer is basically just offloading all the
work to it. So in our case, let's make data, uh, for supports normalization or return
data instance of user. So if the users being s normalized, um, we should be called
then when this returns true it we'll call normalized. So we know the only time
normalize is going to be called is if object is the instance of user. So let's
actually add some PHP doc above this that says that object will be a user. And then
on top above this with you, something like if this->user is owner, that's a method
we'll create in a second. We'll pass this object

[inaudible]

context cause no surpassed B context. We can modify the context. So love square
bracket groups and then we will add to that owner colon. Read in this case, this is a
user normalizer. It's not used for de normalization. So we can always use owner Colin
Reed. I'm also assuming there's a group's key here already because we've built it in
our system. We're always specifying groups. So that key will already be initialized
for the select user is owner. I'll go down here and we'll just make a private
function user is owner. That takes a user object, it turns a boule. And just for now
I'm gonna just return brand zero 10 is granted five. We'll put some real security
logic in here, but for now we'll just, uh, return randomly.

Okay,

cool. And that's it. This is one of those cases where just by implementing normalize
your interface, a API platform is going to see our normalizer and start using it. So
I've just been over here right now I am anonymous, so that actually means I don't
even have access to a fetch the user objects. So let's go back to my front end here
and we'll log in as castle lover@example.com password Fu. This is just a, a user I
created earlier.

[inaudible].

So let's refresh this page. I'm actually going to insert a new user into the database
just to make sure. So I'll say a

good

dude@example.com pass her food. Good. It is, no has listings and yeah, let's put a
phone number, execute and perfect two oh one so I'm going to copy that email so we
can immediately go log in as that user. So I'll go to our front end log in as good to
do an example like on password and food. Cool. We're authenticated and if I go back
to /API, you can see down here we are authenticated. So if we get the collection of
users right now execute. You can see down here the first user, there is no phone
number, second user, there's no phone number and the third user has no phone number.
So let's try that again. That could've just been by chance. If I try it again. Yes.
Now you can see phone numbers showed up on two of them. So phone number is randomly
showing up. This is great.

So by using a normalizer, we are very easily able to add dynamic groups so that we
can hide or show fields on an object by object basis. And we can do the same thing
for a d normalize it. There's not a make serializer d normalizer you'd have to write
it by hand, but it's the same process, except if you look closely, something bad just
happened. Okay. We're missing the JSON LD information from our users. Like, okay.
Yeah. We have the JSON LD stuff on top ad id and even the, uh, the cheese listings
here has them. But before a second ago, each individual user had an id on it and also
an ad type. So if someone actually killed our JSON LD information from this, let's
talk about why next. Learn more about normalizes and fix it.