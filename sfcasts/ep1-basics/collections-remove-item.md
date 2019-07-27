# Removing Items from a Collection

Close up the POST operation. I want to make a GET request to the collection of users.
Let's see here - the user with id 4 has one `CheeseListing` attached to it - id 2.
Ok, close up that operation and open up the operation for `PUT`: I want to *edit*
that User. Enter 4 for the id.

First, I'm going to do something that we've already seen: let's *just* update the
`cheeseListings` field: set it to an array with one IRI inside: `/api/cheeses/2`.
If we did *nothing* else, this would set this property to... *exactly* what
it already equals: user id 4 already has this *one* `CheeseListing`.

But now, add *another* IRI: `/api/cheeses/3`. That already exists, but is owned
by another user. When I hit Execute.... pfff - I get a syntax error, because I left
an extra comma on my JSON. Boo Ryan. Let's... try that again. This time... bah! A
400 status code:

> This value should not be blank

My experiments with validation just came back to bite me! We set the `title` for
`CheeseListing` 3 to an empty string in the database... it's basically a "bad"
record that snuck in when we were playing with embedded validation. We could fix
that title.. or... just change this to `/api/cheeses/1`. Execute!

## The Serializer only Calls Adders for New Items

This time, it works! But, no surprise - we've basically done this! Internally,
the serializer sees the existing `CheeseListing` IRI - `/api/cheeses/2`, realizes
that this is already set on our `User`, and... does nothing. I mean, maybe it goes
and gets a coffee or takes a walk. But, it most definitely does *not* call
`$user->addCheeseListing()`... or really do anything. But when it sees the *new*
IRI - `/api/cheeses/1`, it figures out that this `CheeseListing` does *not* exist
on the `User` yet, and so, it *does* call `$user->addCheeseListing()`. That's why
adder and remover methods are so handy: the serializer is smart enough to *only*
call them when an object is *truly* being added or removed.

## Removing Items from a Collection

Now, let's do the *opposite*: pretend that we want to *remove* a `CheeseListing`
from this `User` - remove `/api/cheeses/2`. What do you think will happen? Execute
and... woh! An integrity constraint error!

> An exception occurred when executing UPDATE cheese_listing SET owner_id=NULL -
> column `owner_id` cannot be null.

This is cool! The serializer *noticed* that we *removed* the `CheeseListing` with
id = 2. And so, it *correctly* called `$user->removeCheeseListing()` and passed
`CheeseListing` id 2. Then, our generated code set the owner on that `CheeseListing`
to null.

Depending on the situation and the nature of the relationship and entities, this
might be *exactly* what you want! Or, if this were a ManyToMany relationship,
the result of *that* generated code would basically be to "unlink" the two objects.

## orphanRemoval

But in our case, we don't *ever* want a `CheeseListing` to be an "orphan" in
the database. In fact... that's exactly why we made `owner` `nullable=false` and
why we're seeing this error! Nope, if a `CheeseListing` is removed from a `User`...
I guess we really need to just delete that `CheeseListing` *entirely*!

And... yea, doing that is easy! All the way back up above the `$cheeseListings`
property, add `orphanRemoval=true`.

[[[ code('2595134de7') ]]]

This means, *if* any of the `CheeseListings` in this array suddenly... are *not*
in this array, Doctrine will delete them. Just, realize that if you try to
*reassign* a `CheeseListing` to another `User`, it will *still* delete that
`CheeseListing`. So, just make sure you only use this when that's *not* a use-case.
We've been changing the owner of cheese listings a bunch... but only as an example:
it doesn't *really* make sense, so this is perfect.

Execute one more time. It works... and *only* `/api/cheeses/1` is there. And if
we go *all* the way back up to fetch the collection of cheese listings... yea,
`CheeseListing` id 2 is gone.

Next, when you combine relations and filtering... well... you get some *pretty*
serious power.
