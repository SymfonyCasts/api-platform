# Listeners & Accessing the Resource Objects

Coming soon...

But... I admit... I kinda cheated with this example. It was *really* easy because
the object that I needed to operate on was the currently-authenticated `User`
object. That made it *super* easy to grab that *one* object and set some data
on it.

But... what if object we need to set the data on is *not* a `User` object...
it's `CheeseListing` or something else. Where could we get that object from
inside of our listener?

is not the user object. It's just some entity. Where do we get the
entity from in this situation to answer that question, I'm actually going to go over
it in Google for APF platform events. We have not talked about much about the API
platform event system yet, but one of the things I want to show you is that it gets
to, is that a lot of what happens between an API platform behind the scenes is
actually done via normal event listeners.

So for example, this `ReedListener` here is actually responsible for calling the data
providers to actually get the data from the database. The `DeserealizeListener` is what
deserealizes the JSON and updates or creates your object. Then later about `ValidateListener`
`WriteListener` calls the data persisters and then there's some other ones.
Now, what I want you to notice is that both the readlistener and the deserializer
listener are listening on `kernel.request` that's the same event that we are
listening on and they will have a priority of four and two. Now, by default, when
you, when you create a subscriber, you can specify down here a priority since we
haven't. Our priority is zero. What that means is that we're actually being called
after these two listeners. Now that's really important because these listeners take
whatever data is being queried for.

Or maybe if this is a new item, the data that's being created and sets it as a
request attribute. So check this out. I'm actually going to go up here. And as an
experiment, I'm going to say `dd($event->getRequest()->attributes->get('data')`,
arrow, get data. That's the special key where API platform puts its data. Now, if we
spin over now and our refresh, the collections end point for users, you can see that
this is our page Nader object. The one we can loop over to get users. And if I go to
`/user/1.jsonld`, then it is an individual user object. So you can use this
data key off of the request attributes to get access to the items that you're working
with, uh, on this request. So I'll remove that `dd()`.

So this is really nice, but there's even another way that we could have loaded and
set the `$isMe` field. The only problem with this solution is that it's only going to
work inside of a request. This isn't going to work inside of a custom command instead
of our CLI, because there is no request. So this listener's never going to be called,
which for a security makes sense. There's if we're running a console command and not
going to be an authenticated user anyways, but in other cases, you might have a field
that you want that, that, uh, you need to calculate. And you do want that to be set
inside of your CLI. Also let's solve that with a custom doctrine, post load listener,
