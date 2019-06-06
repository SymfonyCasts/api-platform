# The Serializer

Google for Symfony serializer and find a page called
[The Serializer Component](https://symfony.com/doc/current/components/serializer.html).

API Platform is built on top of the Symfony components. And the *entire* process
of how it turns our `CheeseListing` object into JSON... and JSON back into a
`CheeseListing` object, is done by Symfony's Serializer! If we understand how *it*
works, we're in business!

And, at least on the surface, it's beautifully simple. Check out
the diagram that shows how it works. Going from an object to JSON is called
serialization, and from JSON back into an object called deserialization. To do
that, internally, it goes through a process called normalizing: it *first* takes
your object and turns it into an array. And *then* it's encoded into JSON or
whatever format.

## How Objects are Turned into Raw Data

There are actually a *bunch* of different "normalizer" classes that help with this
job - like one that's really good at converting `DateTime` objects to a string
and back. But the *main* class - the one at the *heart* of this process - is called
the `ObjectNormalizer`. Behind the scenes, *it* uses another Symfony component
called `PropertyAccess`, which has one superpower: if you give it a property name,
like `title`, it's really good at finding and using getter and setter methods
to access that property.

In other words, when API platform tries to "normalize" an object into an array,
it uses the getter and setter methods to do that!

For example, it sees that there's a `getId()` method, and so, it turns that into an
`id` key on the array... and eventually in the JSON. It does the same thing for
`getTitle()` - that becomes `title`. It's just that simple!

When we *send* data, it does the same thing! Because we have a `setTitle()` method,
we can send JSON with a `title` key. The normalizer will take the value we're sending,
call `setTitle()` and pass it!

It's a simple, but neat way to allow your API clients to interact with your object,
your API resource, using its getter and setter methods. By the way, the
PropertyAccess component also supports public properties, hassers, issers, adders,
removers - basically a bunch of common method naming conventions in addition to
getters and setters.

## Adding a Custom "Field"

Anyways, now that we know how this works, we're *super* dangerous! Seriously!
Right now, we're able to send a `description` field. Let's pretend that this property
can contain HTML in the database. But most of our users don't really understand
HTML and, instead, just type into a box with line breaks. Let's create a new,
*custom* field called `textDescription`. If an API client sends a `textDescription`
field, we'll convert the new lines into HTML breaks before saving it on the
`description` property.

How can we create a totally new, custom input field for our resource?
Find `setDescription()`, duplicate it, and name it `setTextDescription()`. Inside,
say, `$this->description = nl2br($description);`. It's a silly example, but even
forgetting about API Platform, this is good, boring, object-oriented coding: we've
added a way to set the description *if* you want new lines to be converted to
line breaks.

[[[ code('0b6a7fea41') ]]]

But *now*, refresh, and open up the POST operation again. Woh! It says that we can
*still* send a `description` field, but we can *also* pass `textDescription`! But
if your try the GET operation... we still *only* get back `description`.

That makes sense! We added a *setter* method - which makes it possible to *send*
this field - but we did *not* add a *getter* method. You can also see the new field
described down in the models section.

## Removing "description" as Input

But, we *probably* don't want to allow the user to send both `description`
*and* `textDescription`. I mean, you *could*, but it's a little weird - if the
client sent both, they would bump into each other and the last key would win because
its setter method would be called last. So, let's remove `setDescription()`.

Refresh now. I love it! To create or update a cheese listing, the client will send
`textDescription`. But when they fetch the data, they'll always get back `description`.
In fact, let's try it... with id 1. Open the PUT operation and set `textDescription`
to something with a few line breaks. I *only* want to update this *one* field,
so we can just remove the other fields. And... execute! 200 status code and...
a `description` field with some line breaks!

By the way, the fact that our input fields don't match our output fields is
*totally* ok. Consistency *is* super nice - and I'll show you soon how we can
fix this inconsistency. But there's no rule that says your input data needs to
match your output data.

## Removing createdAt Input

Ok, what else can we do? Well, having a `createdAt` field on the output is great,
but it probably doesn't make sense to allow the client to send this: the server
should set it automatically.

No problem! Don't want the `createdAt` field to be allowed in the input? Find the
`setCreatedAt()` method and remove it. To auto-set it, it's back to good,
old-fashioned object-oriented programming. Add `public function __construct()` and,
inside, `$this->createdAt = new \DateTimeImmutable()`.

[[[ code('bb8e91b1ff') ]]]

Go refresh the docs. Yep, it's gone here... but when we try the GET operation,
it *is* still in the output.

## Adding a Custom Date Field

We're on a roll! So let's customize one more thing! Let's say that, in addition
to the `createdAt` field - which is in this ugly, but standard format - we *also*
want to return the date as a string - something like `5 minutes ago` or `1 month ago`.

To help us do that, find your terminal and run:

```terminal
composer require nesbot/carbon
```

This is a handy DateTime utility that can easily give us that string. Oh, while
this is installing, I'll go back to the top of my entity and remove the custom
`path` on the `get` operation. That's a cool example... but let's *not* make our
API weird for no reason.

[[[ code('b458601aa9') ]]]

Yep, that looks better.

Back at the terminal.... done! In `CheeseListing`, find `getCreatedAt()`, go below
it, and add `public function getCreatedAtAgo()` with a `string` return type. Then,
`return Carbon::instance($this->getCreatedAt())->diffForHumans()`.

[[[ code('bf8573e66d') ]]]

You know the drill: *just* by adding a getter, when we refresh... and look at
the model, we have a *new* `createdAtAgo` - *readonly* field! And, by the way,
it *also* knows that `description` is readonly because it has no setter.

Scroll up and try the GET collection operation. And... cool: `createdAt` *and*
`createdAtAgo`.

As *nice* as it is to control things by simply tweaking your getter and setter methods,
it's not *ideal*. For example, to prevent an API client from setting the `createdAt`
field, we *had* to remove the `setCreatedAt()` method. But, what if, *somewhere*
in my app - like a command that imports legacy cheese listings - we *do* need to
manually set the `createdAt` date? Let's learn how to control this with
serialization groups.
