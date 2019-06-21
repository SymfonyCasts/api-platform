# Creating Embedded Objects

Instead of assigning an existing `CheeseListing` to the user, could we create a
totally new one by embedding its data? Let's find out!

This time, we won't send an IRI string, we'll send an *object* of data. Let's
see... we need a `title` and... I'll cheat and look at the `POST` endpoint for
cheeses. Right: we need `title`, `price` `owner` and `description`. Set `price`
to 20 bucks and pass a `description`. But I'm not going to send an `owner` property.
Why? Well... forget about API Platform and just imagine you're *using* this API.
If we're sending a POST request to `/api/users` to create a new user... isn't it
pretty obvious that we want the new cheese listing to be owned by *this* new user?
Of course, it's our job to actually make this *work*, but this *is* how I would
*want* it to work.

Oh, and before we try this, change the `email` and `username` to make sure they're
unique in the database.

Ready? Execute! It works! No no, I'm totally lying - it's not *that* easy. We've
got a familiar error:

> Nested documents for attribute "cheeseListings" are not allowed. Use IRIs instead.

## Allowing Embedded cheeseListings to be Denormalized

Ok, let's back up. The `cheeseListings` field is *writable* in our API because
the `cheeseListings` property has the `user:write` group above it. But if we
did *nothing* else, this would mean that we can pass an array of IRIs to this
property, but *not* a JSON object of embedded data.

To allow *that*, we need to go into `CheeseListing` and add that `user:write` group
to all the properties that we want to allow to be passed. For example, we know
that, in order to create a `CheeseListing`, we need to be able to set `title`,
`description` and `price`. So, let's add that group! `user:write` above `title`,
`price` and... down here, look for `setTextDescription()`... and add it there.

[[[ code('0e4bac8f13') ]]]    

I *love* how clean it is to choose which fields you want to allow to be embedded...
but life *is* getting more complicated. Just keep that "complexity" cost in mind
if you decide to support this kind of stuff in your API

## Cascade Persist

Anyways, let's try it! Ooh - a 500 error. We're closer! And we know this error too!

> A new entity was found through the `User.cheeseListings` relation that was
> not configured to cascade persist.

Excellent! This tells me that API Platform *is* creating a new `CheeseListing`
and it *is* setting it onto the `cheeseListings` property of the new `User`. But
nothing ever calls `$entityManager->persist()` on that new `CheeseListing`, which
is why Doctrine isn't sure what to do when trying to save the User.

If this were a traditional Symfony app where I'm personally writing the code to
create and save these objects, I'd probably just find where that `CheeseListing`
is being created and call `$entityManager->persist()` on it. But because API
Platform is handling all of that for us, we can use a different solution.

Open `User`, find the `$cheeseListings` property, and add `cascade={"persist"}`.
Thanks to this, whenever a `User` is persisted, Doctrine will automatically
persist any `CheeseListing` objects in this collection.

[[[ code('455e97b909') ]]]

Ok, let's see what happens. Execute! Woh, it worked! This created a new `User`,
a new `CheeseListing` *and* linked them together in the database.

## But who set CheeseListing.owner?

But... how did Doctrine... or API Platform know to set the `owner` property on
the new `CheeseListing` to the new `User`... if we didn't pass an `owner` key in
the JSON? If you create a `CheeseListing` the *normal* way, that's totally required!

This works... *not* because of any API Platform or Doctrine magic, but thanks
to some good, old-fashioned, well-written code in our entity. Internally, the
serializer instantiated a new `CheeseListing`, set data on it and *then* called
`$user->addCheeseListing()`, passing that new object as the argument. And *that*
code takes care of calling`$cheeseListing->setOwner()` and setting it to `$this`
User. I *love* that: our generated code from `make:entity` and the serializer
are working together. What's gonna work? Team work!

## Embedded Validation

But, like when we embedded the `owner` data while editing a `CheeseListing`, when
you allow embedded resources to be changed or created like this, you need to pay
special attention to validation. For example, change the `email` and `username`
so they're unique again. This is now a valid user. But set the `title` of the
`CheeseListing` to an empty string. Will validation stop this?

Nope! It *allowed* the `CheeseListing` to save with no title, *even* though we
have validation to prevent that! That's because, as we talked about earlier,
when the validator processes the `User` object, it doesn't automatically cascade
down into the `cheeseListings` array and *also* validate those objects. You can
force that by adding `@Assert\Valid()`.

[[[ code('1ca01098e3') ]]]

Let's make sure that did the trick: go back up, bump the `email` and `username`
to be unique again and... Execute! Perfect! A 400 status code because:

> the `cheeseListings[0].title` field should not be blank.

Ok, we've talked about how to *add* new cheese listings to an user - either by
passing the IRI of an existing `CheeseListing` or embedding data to create a *new*
`CheeseListing`. But what would happen if a user had 2 cheese listings... and we
made a request to edit that `User`... and only included the IRI of *one* of those
listings? That should... *remove* the missing `CheeseListing` from the user, right?
Does that work? And if so, does it set that CheeseListing's `owner` to null? Or
does it delete it entirely? Let's find some answers next!
