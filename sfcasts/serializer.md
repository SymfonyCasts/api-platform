# Serializer

Coming soon...

Google for Symfony serializer.

Okay.

And you'll find a page called the Serializer Component. API Platform, as you know, is
built on top of Symfony and the entire process of how it turns our `CheeseListing`
object into JSON and JSON back into the `CheeseListing` object is done by Symfony
Serializer. It uses Symfony Serializer 100%. So if you understand Symfony Serializer,
it's going to go a long way to knowing how to leverage API Platform and we're not going
to go through and we're going to go through with the Symfony. So you realize you're a
little by little. But what I did want you to see here is this nice little diagram
that talks about the process. Um, I've taken your object and going into JSON and
taking your JSON and going into your object of going from an object to JSON is called
serializing and from your JSON and to back into your object called deserializing now
in the process, when you start with your object, you go through a process called
normalizing. It takes your object and turns it into an array and then it's encoded
into JSON.

Great

to do the job of normalizing and denormalizing. There's an object called a
Normalizer, specifically one called an Object Normalizer. The object normalizer is
basically the heart of the process and it controls how everything, um, how everything
works and the object normalizer works like this. It uses behind the scenes that you
used as a component of Symfonys called the PropertyAccess components. PropertyAccess
component is basically really good at using getter and setter methods to
access properties. In other words.

When API platform tries to chair turn one of our Objects into JSON. What the, what 
it does is it basically looks at all of the getter and setter methods.

So for example, because we have a, obviously can reference our properties
directly, it sees that there's a `getId()` method and so it turns that into an `id`
property. It sees that there's a `getTitle()` method. It turns that into a `title`
property. It's just that simple.

Okay.

And on input it does the same thing for the body. So it sees that there is a 
`setTitle()` method and so it knows that there can be a `title` key and what it takes in that
JSON, it's going to take whatever string we pass here and it's going to pass it to
`setTitle()`. So it's kind of a cool way that your objects are corn being exposed into
your API and the API clients are using your getters and setters to interact with
them. There's more to it, there's more super powers in that. That's basically what
goes on behind the scenes. So let's see this in action. Let's pretend the right now
that's um, as you can see when we, if we were to and endpoint, you can see that we
passed the `description`, which is uh, which is, uh, we passed the description. Let's
say that that um, let's say that in the database, the `description` is actually going
to contain some HTML.

But what do you want the user to only send? We want the user to actually send us
plain text description. And what we're gonna do is we're going to take, let's say if
it supports line breaks, we're going to add a line breaks through that. Basically
we're going to create, let me show you what I mean. Freedom about your API for a
second. Let's find our `setDescription()` method. I'm going to copy this, I'm going to
paste it. I want to make a new one called `setTextDescription()`. And here we're going
say `$this->description = nl2br($description);`. So very simple thing where it's
like, hey, if you want to set a text description on the `CheeseListing`, you can do
that and we will turn all of the uh, new lines into `<br>` elements and set that on the
`description` field.

if you go over here, as soon as we refresh, as soon as we refresh under the posts,
you can see, look at, we have a `description` field. We can also set a 
`textDescription`, field input. But if you get it, you can see that there's still just
`description` field cause we have a center but we don't have a getter. In fact, you can
see this all the way in the bottom of our swag and I condition under our models. You
can see `textDescription` and we'd seen `description`.

of course you don't really want to have two fields there. Uh, what's it that we want
to force the user to use the `textDescription` one. So let's actually removes that
`setDescription()`. And then refresh.

And we now have this desired result that on input they have to pass the 
`textDescription`, but when they retrieved the resource, they're actually going to get the
`description` field, which will contain the, uh, the HTML. In fact, let's try this. So
`id` 1, let's go down here and actually do an edit for id one. And here for 
`textDescription`, we'll do a strain that has a couple of line breaks in it and I'm only
going to update that one field. So we can do that by just passing the single field on
there. And then I'll execute. Cool. 200 status code to check this out. So on the Alto we
actually get the `description` back and now the `description` has those line breaks in
there.

So the easiest way to start crafting your API is to control your getters and setters
So we're going to get more complicated than that. Another thing you might
notice is that we have this `createdAt` field, which is great, but it doesn't
really make sense to allow the users to set that. That should be set automatically.
So we don't really want a `createdAt` field here. So perfect. So what we can do then
is let's find `setCreatedAt()` , let's delete that and we'll just do normal good
object oriented programming, which says let's make a constructor function 
`public function __construct()`. And inside here `$this->createdAt = new DateTimeImmutable()`
So now we go over and refreshing. Nice. You can see it's gone from
here,

but when we actually execute, it's still going to be returned as a field on our
things. So let's do one more thing. Let's actually create a custom field. So let's
say that in addition to, um, uh, custom output field, so let's say that in addition
to the `createdAt` we actually want to, um, have a creative that shown as a string.
Somebody says like five seconds ago, this was credit two days ago, something like
that. So to help that, I'm going to say 

```terminal
composer require nesbot/carbon
```

This is just a little datetime utility. While I'm waiting for that, one thing I forgot
to do is I'm actually gonna take out my custom configuration for my GET endpoint
just so that doesn't cause anything weird.

And that will make our URL kind of go back to normal. There we go. That looks better.
Then back over here on composer. When that finishes perfect, I'm going to go back in
and find my `getCreatedAt()` method. And down here we'll say 
`public function getCreatedAtAgo()`. And this is actually not return a `string`. 
And here I'm gonna `return Carbon::instance($this->getCreatedAt());` this is that. 
And then we can just say `->diffForHumans()`. So just for adding getter, 
I'm gonna flip back over and refresh. You can see it down here first in the model, 
we now have a `createdAtAgo`, which is a readonly field. By the way. You can 
also see here that text, the `description` is a readonly field and if I try it up
here, there it is `createdAt`, but then are `createdAtAgo`. Is there?

Yeah,

so his nicest it is to control things with the getter and setter methods. It is a
little bit weird that, for example, I don't have a setCreatedAt in here. Like
what if for some reason I'm application, I really did need a set created that
sometimes we didn't need to override the creative that method, but we don't want to
expose that our API, this is a super common use case and we're going to handle it
with serialization groups. That's next.