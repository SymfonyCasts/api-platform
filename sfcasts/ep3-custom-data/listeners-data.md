# Core Listeners & Accessing the "Resource" Objects

I gotta admit... I kinda cheated with the last example. The logic in the listener
was *really* easy because the *only* object that we needed to operate on was the
currently-authenticated `User`. That made it *super* easy to grab that *one*
object and set some data on it.

But... what if the object we need to set the data on is *not* the current `User`
object... it's a `CheeseListing` or something else. For example, if the user goes
to `/api/cheeses/1.jsonld`, how can we get access to the `CheeseListing` object
with id 1 so that we can set data on it? Or even *more* interesting, if the user
goes to `/api/cheeses.jsonld`, how could we get access to *all* of the
`CheeseListing` objects that are about to be displayed so that we can set custom
data on *all* of them?

## The Core API Platform Listeners

To answer that question, search for "API Platform events". We haven't talked much
about the API Platform event system yet. But in reality, almost *everything*
that API Platform does is *actually* done by a listener behind the scenes.

For example, this `ReadListener` is what's responsible for calling the data
providers, which fetch the objects from the database. Then, the
`DeserializeListener` is what *deserializes* the JSON data on `POST`, `PUT` and
`PATCH` requests to update or create an object. Later, `ValidateListener` executes
validation and `WriteListener` calls the data *persisters*. There are a few other
listeners, but these do the majority of the work.

Now check this out: both `ReadListener` and `DeserializeListener` listen to
the `kernel.request` event - the *same* event that *we* are listening to! And
these have a priority of 4 and 2.

When you create a subscriber, you *can* specify a priority in the
`getSubscribedEvents()` method:

[[[ code('0885dfe23e') ]]]

Since we haven't, our priority is zero. That means that both `ReadListener` and
`DeserializeListener` are called *before* us.

That's important because after calling the data providers, `ReadListener` *stores*
that information on a request *attribute*. And if `DeserializeListener` creates
a *new* object, it does the same thing!

## Grabbing the Request "data" Attribute

Check this out, in our listener - as an experiment - add
`dd($event->getRequest()->attributes->get('data'))`:

[[[ code('3f480789ff') ]]]

That's the special key where API platform puts the "data" for the current API
request. When we spin over now and refresh the collections endpoint for users...
awesome! It's our `Paginator` object! We could loop over that to get access to
every `User` object that is *about* to be serialized.

And when we go to `/api/users/1.jsonld`, this dumps the *individual* `User` object.

So... this is awesome! At any point, we can grab the `data` key off of the request
attributes to get access to the item or *items* for the current API request.
*This* is how you could set a custom field for *any* entity inside a listener.

Remove that `dd()`:

[[[ code('806206ccbc') ]]]

I *really* like the listener solution! Though, it does have two downsides, which
may or may not be important. First, the event system isn't used in API Platform's
GraphQL support... so this won't work if you're using GraphQL. And second, if
you're writing some custom console commands, the `isMe` field will *never* be
set there... because there's no request and so no `RequestEvent`!

For the `isMe` field... that's probably fine... because *nobody* is the
currently-authenticated user in a console command anyways. But if you *did*
want a custom field to be populated everywhere - *even* in a console command - we
have one more solution: a Doctrine `postLoad` listener. Let's check that out next!
