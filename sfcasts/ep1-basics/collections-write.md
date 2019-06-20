# Adding Items to a Collection Property

Use the docs to check out the `User` with id=2. When we *read* a resource, we
can decide to expose any property - and a property that holds a *collection*, like `cheeseListings`, is no different. We exposed that property by adding
`@Groups("user:read")` above it. And because this holds a collection of related
*objects*, we can *also* decide whether the `cheeseListings` property should be
exposed as an array of IRI strings *or* as an array of embedded objects, by
adding this same group to at least one property inside `CheeseListing` itself.

Great. New challenge! We can read the `cheeseListings` property on `User`... but
could we also *modify* this property?

For example, well, it's a bit of a strange example, but let's pretend that an admin
wants to be able to edit a `User` and make them the owner of some existing
`CheeseListing` objects in the system. You can *already* do this by editing a
`CheeseListing` and changing its `owner`. But could we also do it by editing a
`User` and passing a `cheeseListings` property?

Actually, let's get even a bit *crazier*! I want to be able to create a new
`User` *and* specify one or more cheese listings that this `User` should
own... all in one request.

## Making cheeseListings Modifiable

Right now, the `cheeseListings` property is not modifiable. The reason is simple:
that property *only* has the read group. Cool! I'll make that group an array
and add `user:write`.

[[[ code('16d4b0a91c') ]]]

Now, go back, refresh the docs and look at the POST operation: we *do* have
a `cheeseListings` property. Let's do this! Start with the boring user info:
email, password doesn't matter and username. For `cheeseListings`, this needs
to be an array... because this property *holds* an array. Inside, add just one
item - an *IRI* - `/api/cheeses/1`.

In a perfect world, this will create a new `User` and *then* go fetch the
`CheeseListing` with id `1` and change it to be owned by this user. Deep breath.
Execute!

It worked? I mean, it worked! A 201 status code: it created the new `User` and
that `User` now owns this `CheeseListing`! Wait a second... how *did* that work?

## Adder and Remover Methods for Collections

Check it out: we understand how `email`, `password` and `username` are handled:
when we POST, the serializer will call `setEmail()`. In this case, we're sending a
`cheeseListings` field... but if we go look for `setCheeseListings()`, it doesn't
exist!

Instead, search for `addCheeseListing()`. Ahhh. The `make:entity` command is smart:
when it generates a collection relationship like this, instead of generating a
`setCheeseListings()` method, it generates `addCheeseListing()` and
`removeCheeseListing()`. And the serializer is smart enough to use those! It sees
the one `CheeseListing` IRI we're sending, queries the database for that object,
calls `addCheeseListing()` and passes it as an argument.

The *whole* reason `make:entity` generates the adder - instead of just
`setCheeseListings()` - is that it lets us *do* things when a cheese listing is
added or removed. And that is *key*! Check it out: inside the generated code, it
calls `$cheeseListing->setOwner($this)`. *That* is the reason why the owner *changed*
to the new user, for this `CheeseListing` with id=1. Then... everything just saves!

Next: when we're creating or editing a user, instead of *reassigning* an existing
`CheeseListing` to a new owner, let's make it possible to create totally *new*
cheese listings. Yep, we're getting crazy! But this will let us learn even *more*
about how the serializer thinks and works.
