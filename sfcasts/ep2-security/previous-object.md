# ACL & previousObject

Via the `access_control` on the `PUT` operation, we were able to make sure that
only the *owner* of this `CheeseListing` can edit it. If you aren't the owner,
access denied! We assert that in our test.

Now... I'm going to *trick* the security system! We're logged in as
`user2@example.com` but the `CheeseListing` we're trying to update is owned by
`user1@example.com`... which is why we're getting the `403` status code.

Right now, we've configured the serialization groups to allow for the `owner`
field to be *updated* via the PUT request. That might sound odd, but it could
be useful for admin users to be able to do this. But... this complicates things
*beautifully*! Let's try changing the `owner` field to `/api/users/` then
`$user2->getId()`.

[[[ code('1d8f7a61eb') ]]]

Clearly, this should *not* be allowed: the user that *doesn't* own this `CheeseListing`
is trying to edit it and... make themselves the owner! Naughty!

But... try the test:

```terminal-silent
php bin/phpunit --filter=testUpdateCheeseListing
```

It fails! We expected a `403` status code but got `200`! What?

I mentioned earlier that when a request comes in, API Platform goes through
three steps in a specific order. First it deserializes the JSON and updates the
`CheeseListing` object. Second it applies our `access_control` security and *third*
it executes our validation rules.

See the problem? By the time API Platform processes our `access_control`, this
`object` has been updated! Its owner has *already* been changed! I mean, it hasn't
been updated in the database yet, but the object in memory has the *new* owner.
This causes access to be granted. Gasp!

## Hello previous_object

There are two solutions to this depending on your API Platform version.

In API Platform 2.4 - that's our version - instead of `object`, use `previous_object`.
Very simply: `previous_object` is the `CheeseListing` *before* the JSON is
processed and `object` is the `CheeseListing` *after* the JSON has been deserialized.

In API Platform 2.5, you'll do something different: use the new `security` option
instead of `access_control`. It's just that simple: `security` and `access_control`
work identically, except that `security` runs *before* the object is updated from
the posted data. There's also another option called `security_post_denormalize`
if you want to run a security check *after* deserialization. In that case, the
`object` variable is the *updated* object.

Phew! For us on API Platform 2.4, as soon as we change to `previous_object`...
it should work! Try the test:

[[[ code('b700f1f4a3') ]]]

```terminal-silent
php bin/phpunit --filter=testUpdateCheeseListing
```

Scroll up... all better!

## access_control on User

Now that we've got a rock-solid set of `access_control` for `CheeseListing`, let's
repeat this for `User`... because we don't have *any* access control stuff here now.

Start by saying `itemOperations={}`. For the `get` operation... let's steal an
`access_control` from `CheeseListing`. Let's see... to be able to fetch a single
User, let's say that you need to at *least* be logged in. So, `ROLE_USER`.

[[[ code('416a905390') ]]]

For the `put` operation, you're probably going to need to be logged in and...
you should probably only be able to update your *own* record. Use
`is_granted('ROLE_USER') and object == user`.

[[[ code('4d7372ad7e') ]]]

In this case, because we're not checking a specific property, we can safely
use `object` instead of `previous_object`: you can send data to change a specific
property... but not the entire object.

Finally, for `delete`, let's say that you can only delete a `User` if you're an
admin: `access_control` looking for `ROLE_ADMIN`.

[[[ code('7c9c254ad8') ]]]

Cool! Next, `collectionOperations`! For `get`, let's say that you need to be
logged in... and for `post`, for *creating* a `User`... hey, that's registration!
Put nothing here: this must be available to anonymous users.

[[[ code('5e3d4984a5') ]]]

Very nice! We could create some tests for this, but now that we're getting
comfortable... and because these access rules are still *fairly* simple, I'll
skip it and test once manually.

Go refresh the docs to do that. And... syntax error! Wow, I'm *super* lazy with
my commas. Try it again. My web debug toolbar tells me that I am *not* logged in.
So if we try the GET collection operation... 401 status code. Perfect!

## Top (Resource) Level accessControl

Until now, we've been adding the access control rules on an operation-by-operation
basis. But you can also add rules at the *resource* level. Add `accessControl`...
this time with a *capital* C - the top-level options are camel case. A few of
our operations require `ROLE_USER`... so, if we want to, we could say
`accessControl="is_granted('ROLE_USER')"`.

[[[ code('68a96421d8') ]]]

This becomes the *default* access control that will be used for all operations
*unless* an operation overrides this with their *own* `access_control`. This
means that we don't need to repeat `access_control` on the `get` collection or
`get` item operations. But! We *do* now need to set `access_control` on the `post`
operation to look for `IS_AUTHENTICATED_ANONYMOUSLY`. We're overriding the
default access control and making sure that *anyone* can access this operation.

[[[ code('a4c57775cb') ]]]

Using the resource-level versus operation-level access control is a matter of
taste... and resource-level controls fit better on some resources than others.

Let's make sure this works... open the `POST` operation, send an empty body and
500 error? Let's see... bah! Another annotation mistake. I like annotations... but
I'll admit, they *can* get a bit big with API Platform... and apparently my comma
key is broken today.

Let's execute that operation again and... got it! A 400 error: this value should
not be blank.

Next, let's *also* making it possible for an admin user to be able to edit
*any* `CheeseListing`. We *could* push our `access_control` logic further...
but it's probably time to talk about voters.
