# Embedded Write

Here's an interesting question: if we fetch a single `CheeseListing`, we can see
that the `username` comes through on the `owner` property. And obviously, if we,
edit a specific `CheeseListing`, we can *totally* change the owner to a different
owner. Let's actually try this: let's *just* set `owner` to `/api/users/2`.
Execute and... yep! It updated!

That's great, and it works pretty much like a normal, scalar property. But...
looking back at the results from the GET operation... here it is, if we can *read*
the `username` property off of the related owner, instead of changing the owner
entirely, could we *update* the current owner's username *while* updating
a `CheeseListing`?

It's kind of a weird example, but editing data *through* an embedded relation *is*
possible... and, at the very least, it's an *awesome* way to *really* understand
how the serializer works.

## Trying to Update the Embedded owner

Anyways... let's just try it! Instead of setting owner to an IRI, set it to
an object and try to update the `username` to `cultured_cheese_head`. Go, go, go!

And... it doesn't work:

> Nested documents for attribute "owner" are not allowed. Use IRIs instead.

So... is this possible, or not?

Well, the *whole* reason that `username` is embedded when serializing a `CheeseListing`
is that, above `username`, we've added the `cheese_listing:item:get` group, which
is one of the groups that's used in the "get" item operation.

The same logic is used when *writing* a field, or, denormalizing it. If we want
`username` to be writable while denormalizing a `CheeseListing`, we need to put
it in a group that's *used* during denormalization. In this case, that's
`cheese_listing:write`.

Copy that and paste it above `username`.

[[[ code('6c066eceac') ]]]

As *soon* as we do that - because the `owner` property already has this group -
the embedded `username` property can be written! Let's go back and try it: we're
still trying to pass an *object* with `username`. Execute!

## Sending New Objects vs References in JSON

And... oh... it *still* doesn't work! But the error is fascinating!

> A new entity was found through the relationship `CheeseListing.owner` that was
> not configured to cascade persist operations for entity User.

If you've been around Doctrine for awhile, you might recognize this strange error.
Ignoring API Platform for a moment, it means that something created a *totally*
new `User` object, *set* it onto the `CheeseListing.owner` property and then tried
to save. But because nobody ever called `$entityManager->persist()` on the new
`User` object, Doctrine panics!

So... yep! Instead of querying for the existing owner and *updating* it, API Platform
took our data and used it to create a totally *new* `User` object! That's not what
we wanted at all! How can we tell it to *update* the existing `User` object instead?

Here's the answer, or really, here's the simple rule: *if* we send an array of
data, or really, an "object" in JSON, API Platform assumes that this is a new
object and so... creates a new object. If you want to *signal* that you instead
want to update an *existing* object, just add the `@id` property. Set it to
`/api/users/2`. Thanks to this, API Platform will query for that user and
*modify* it.

Let's try it again. It *works*! Well... it *probably* worked - it looks successful,
but we can't see the username here. Scroll down and look for the user with id 2.

There it is!

## Creating new Users?

So, we *now* know that, when updating... or really creating... a `CheeseListing`,
we can send embedded `owner` data *and* signal to API Platform that it should update
an existing `owner` via the `@id` property.

And when we *don't* add `@id`, it tries to create a *new* `User` object...
which didn't work because of that persist error. But, we can *totally* fix that
problem with a cascade persist... which I'll show in a few minutes to solve a
different problem.

So wait... does this mean that, in theory, we *could* create a brand new `User`
while editing a `CheeseListing`? The answer is.... yes! Well... *almost*. There
are 2 things preventing it right now: first, the missing cascade persist, which
gave us that big Doctrine error. And second, on `User`, we would also need to
expose the `$password`  and `$email` fields because these are both required in
the database. When you start making embedded things writeable, it honestly adds
complexity. Make sure you keep track of what and what is *not* possible in your
API. I *don't* want users to be created accidentally while updating a `CheeseListing`,
so this is perfect.

## Embedded Validation

But, there is *one* weird thing remaining. Set `username` to an empty string.
That shouldn't work because we have a `@NotBlank()` above `$username`.

Try to update anyways. Oh, of course! I get the cascade 500 error - let me put
the `@id` property back on. Try it again.

Woh! A 200 status code! It looks like it worked! Go down and fetch this user... with
id=2. They have no username! Gasp!

This... is a bit of a gotcha. When we modify the `CheeseListing`, the validation
rules are executed: `@Assert\NotBlank()`, `@Assert\Length()`, etc. But when the
validator sees the embedded `owner` object, it does *not* continue down into that
object to validate *it*. That's *usually* what we want: if we were *only* updating
a `CheeseListing`, why should it *also* try to validate a related `User` object
that we didn't even modify? It shouldn't!

But when you're doing *embedded* object updates like we are, that changes: we *do*
want validation to continue down into this object. To force that, above the `owner`
property, add `@Assert\Valid()`.

[[[ code('157e4fdbfd') ]]]

Ok, go back, and... try our edit endpoint again. Execute. Got it!

> owner.username: This value should not be blank

Nice! Let's go back and give this a valid username... just so we don't have a
bad user sitting in our database. Perfect!

Being able to make modifications on embedded properties is pretty cool... but it
*does* add complexity. Do it if you need it, but also remember that we can update
a `CheeseListing` and a `User` more simply by making two requests to two endpoints.

Next, let's get even *crazier* and talking about updating *collections*: what happens
if we start to try to modify the `cheeseListings` property directly on a `User`?
