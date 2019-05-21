# Serializer

Coming soon...

Google for Symfony serializer.

Okay.

And you'll find a page called the serializer component. Api Platform, as you know, is
built on top of Symfony and the entire process of how it turns our cheese listing
object into JSON and JSON back into the cheese listing object is done by Symfonys
serializer. It uses Symfony serializer 100%. So if you understand Symfony serializer,
it's going to go a long way to knowing how to leverage a platform and we're not going
to go through and we're going to go through with the Symfony. So you realize you're a
little by little. But what I did want you to see here is this nice little diagram
that talks about the process. Um, I've taken your object and going into JSON and
taking your JSON and going into your object of going from an object to JSON is called
serializing and from your JSON and to back into your object called DC. Realizing now
in the process, when you start with your object, you go through a process called
normalizing. It takes your object and turns it into an array and then it's encoded
into JSON.

Great

to do the job of normalizing and d normalizing. There's an object called a
normalizer, specifically one called an object normalizer. The object normalizer is
basically the heart of the process and it controls how everything, um, how everything
works and the object normalizer works like this. It uses behind the scenes that you
used as a component of Symfonys called the property access components. Probably
access component is basically really good at using getter and setter methods to
access properties. In other words.

Okay.

When API platform tries to chair turn one of our

okay.

Objects into JSON.

Okay.

What the, what it does is it basically looks at all of the getter and setter methods.

Yeah.

So for example, because we have a, obviously [inaudible] reference our properties
directly, it sees that there's a get id method and so it turns that into an IB
property. It sees that there's a good title method. It turns that into a title
property. It's just that simple.

Okay.

And on input it does the same thing for the body. So it sees that there is a set
title method and so it knows that there can be a title key and what it takes in that
JSON, it's going to take whatever string we pass here and it's going to pass it to
set title. So it's kind of a cool way that your objects are corn being exposed into
your Api and the API clients are using your getters and setters to interact with
them. There's more to it, there's more super powers in that. That's basically what
goes on behind the scenes. So let's see this in action. Let's pretend the right now
that's um, as you can see when we, if we were to and end point, you can see that we
passed the description, which is uh, which is, uh, we passed the description. Let's
say that that um, let's say that in the database, the description is actually going
to contain some html.

But what do you want the user to only send? We want the user to actually send us
plain text description. And what we're gonna do is we're going to take, let's say if
it supports line breaks, we're going to add a line breaks through that. Basically
we're going to create, let me show you what I mean. Freedom about your API for a
second. Let's find our set description method. I'm going to copy this, I'm going to
paste it. I want to make a new one called set text description. And here we're going
say this air description = and l to VR discrepancy. So very simple thing where it's
like, hey, if you want to set a text description on the cheese listing, you can do
that and we will turn all of the uh, new lines into br elements and set that on the
description field.

Yeah,

if you go over here, as soon as we refresh, as soon as we refresh under the posts,
you can see, look at, we have a description field. We can also set a text
description, field input. But if you get it, you can see that there's still just
description field cause we have a center but we don't have a getter. In fact, you can
see this all the way in the bottom of our swag and I condition under our models. You
can see text description and we'd seen description.

Yeah,

of course you don't really want to have two fields there. Uh, what's it that we want
to force the user to use the text description one. So let's actually removes that
description.

Okay.

And then refresh.

Okay.

And we now have this desired result that on input they have to pass the text
description, but when they retrieved the resource, they're actually going to get the
description field, which will contain the, uh, the html. In fact, let's try this. So
Id one, let's go down here and actually do an edit for id one. And here for text
description, we'll do a strain that has a couple of line breaks in it and I'm only
going to update that one field. So we can do that by just passing the single field on
there. And then I'll execute. Cool. 200 Sashco to check this out. So on the Alto we
actually get the description back and now the description has those line breaks in
there.

Okay. Hi.

So the easiest way to start crafting your API is to control your gauges and et
center. So we're going to get more complicated than that. Another thing you might
notice is that we have this creative, that field, which is great, but it doesn't
really make sense to allow the users to set that. That should be set automatically.
So we don't really want a created that feel here. So perfect. So what we can do then
is let's find set created that, let's delete that and we'll just do normal good
object oriented programming, which says let's make a constructor function, public
function underscore,_construct. And inside here this->created that = new new date
time immutable. So now we go over and refreshing. Nice. You can see it's gone from
here,

but when we actually execute, it's still going to be returned as a field on our
things. So let's do one more thing. Let's actually create a custom field. So let's
say that in addition to, um, uh, custom output field, so let's say that in addition
to the creative that we actually want to, um, have a creative that shown as a string.
Somebody says like five seconds ago, this was credit two days ago, something like
that. So to help that, I'm going to say composer require Nez bought slash. Carbon.
This is just a little daytime utility. While I'm waiting for that, one thing I forgot
to do is I'm actually gonna take out my custom configuration for my good end point
just so that doesn't cause anything weird.

Okay?

And that will make our URL kind of go back to normal. There we go. That looks better.
Then back over here on composer. When that finishes perfect, I'm going to go back in
and find my gift. Created that method. And down here we'll say public function get
created at up go. And this is actually not return a string. And here I'm gonna Return
Carbon.

Okay,

instance, say this Arrow, get created apps, create a, this is that. And then we can
just say diff for humans. So just for adding Mag Kitter, I'm gonna flip back over and
refresh. You can see it down here first in the model, we now have a created at a go,
which is a read only field. By the way. You can also see here that text, the
description is a read only field and if I try it up here, there it is created, but
then are created at a go. Is there?

Yeah,

so his nicest it is to control things with the getter and setter methods. It is a
little bit weird that, for example, I don't have a set created that in here. Like
what if for some reason I'm application, I really did need a set created that
sometimes we didn't need to override the creative that method, but we don't want to
expose that our API, this is a super common use case and we're going to handle it
with serialization groups. That's next.