# Locking down the CheeseListing.owner Field

Inside `CheeseListing`, the `owner` property - that's the related `User` object
that owns this `CheeseListing` - is required in the database. *And*, thanks to
the `cheese:write` group, it's a *writable* field in our API.

In fact, even though I've forgotten to add an `@Assert\NotBlank` constraint to
this property, an API client *must* send this field when creating a new
`CheeseListing`. They can also *change* the `owner` by sending the field via a
PUT request. We even add some fanciness where, when you create or edit a
`CheeseListing`, you can modify the owner's username all at the same time.
This works because that property is in the `cheese:write` group... oops. I forgot
to change this group when we were refactoring - that's how it should look.
The `cheese:item:get` group means this field will be embedded when when we GET
a *single* `CheeseListing` - that's the "item operation" - and `cheese:write`
means it's writable when using *any* write operation for `CheeseListing`. That's
some crazy stuff we set up on our previous tutorial.

## Removing username Updatable Fanciness

But now, I want to simplify in two ways. First, I only want the `owner` property
to be set when we *creating* the `CheeseListing`, I don't want it to be
*changeable*. And second, let's get rid the fanciness of being able to edit the
`username` property via a `CheeseListing` endpoint. For that second part, remove
the `cheese:write` property from `username`. We can now also take off the
`@Assert\Valid()` annotation. This caused the `User` to be validated during
the `CheeseListing` operations. That was needed to make sure someone didn't
set the `username` to an invalid value while updating a `CheeseListing`.

## Making owner *only* Settable on POST

Now, how can we make the `owner` property settable for the POST operation but
*not* the PUT operation?

Open up `AutoGroupResourceMetadataFactory`. This monster automatically adds three
normalization groups in all situations. We can use this last one to include a field
*only* for a specific operation. Change `cheese:write` to `cheese:collection:post`.
That follows the pattern: "short name", colon, collection, colon, then the operation
name: `post`.

Congratulations, the `owner` an no longer be changed.

## Should Owner Be Set Automatically?

But... hold up. Isn't it kinda weird that we allow the API client to send the
`owner` field at all? I mean... shouldn't we instead *not* make `owner` part of
our API and then write some code to automatically set this to the
currently-authenticated user?

Um, maybe. Automatically setting the `owner` property *is* kinda nice... and
it would also make our API easier to use. We *will* talk about how to do this
later. But I don't want to completely remove `owner` from my API. Why? Well,
what if we created an admin interface where admin users could create cheese
listings on *behalf* of users. In that case, we *would* want the `owner` field
to be part of our API.

But.. hmm if we allow the `owner` field to be sent... we can't just allow
*anyone* to create a `CheeseListing` and set the `owner` to whoever they want.
Sure, maybe an admin user should be able to do this... but how can we prevent a
normal user from setting the `owner` to someone else?

## Two Ways to Protect a Writable Field

Backing up, if the behavior of the way a field can be *written* is dependent
on the authenticated user, you have two options. First, you could prevent some
users from writing to the field entirely. That's done by putting the property
into a special serialization group then dynamically adding that group either
in a context builder or in a custom normalizer. Or second, if the field should
be writable by *all* users... but the data that's *allowed* to be set on the field
depends on *who* is logged in, then the solution is validation.

So here's our goal: prevent the API client from POSTing an `owner` value that
is *different* than their own IRI... with an exception added for admin users:
they can set `owner` to anyone.

Let's codify this into a test first. Open `CheeseListingResourceTest`. Inside
`testCreateListing()`, we're basically verifying that you *do* need to be logged
in to use the operation. We get a 401 before we're authenticated... then after
logging in, we get a 400 status code because access will be granted, but it
will fail validation.

Let's make this test a bit more interesting! Create a new `$authenticatedUser`
variable set to who we're logged in as. Then create an `$otherUser` variable
set to... *another* user in the database.

Here's the plan: I want to make *another* POST request to `/api/users` with *valid*
data... except that we'll set the `owner` field to this `$otherUser`... a user
that we are *not* logged in as. Start by creating a `$cheeseData` array set to
some data: a `title`, `description` and `price`. These are the three required
fields other than `owner`.

Now, copy the request and status code assertion from before, paste down here and
set the `json` to `$cheeseData` *plus* the `owner` property set to `/api/users/`
and then `$otherUser->getId()`.

In this case, the status code should *still* be 400 once we've coded all of this:
passing the wrong owner will be a *validation* error. I'll add a little message
to the assertion to make it obvious while it's failing:

> not passing the correct owner

I like it! We're logging in as `cheeseplease@example.com`... then we're trying to
create a `CheeseListing` that's owned by a totally *different* user. This is the
behavior we want to prevent.

While we're here, copy these two lines again and change `$otherUser` to
`$authenticatedUser`. This *should* be allowed, so change the assertion to look
for the happy 201 status code.

You know the drill: once you've written a test, you get to celebrate by watching
it fail! Copy the method name, flip over to your terminal and run:

```terminal
php bin/phpunit --filter=testCreateCheeseListing
```

And... it fails!

> Failed asserting response status code is 400 - got 201 Created.

So we *are* currently able to create cheese listings and set the owner as a different
user. Cool! Next, let's prevent this with a custom validator.
