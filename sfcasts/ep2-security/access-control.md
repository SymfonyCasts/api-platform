# ApiResource access_control

There are two big parts to security in any app. First, how does your user authenticate?
How do they log in? Honestly, *that* is the *trickiest* part... and it has really
nothing to do with API Platform. We're authenticating via the `json_login` authenticator
and a session cookie. That's a great solution for many applications. But in the
bonus part 2 of the security tutorial, we'll talk about other types of applications
and solutions.

Regardless of how your users authenticate, step two of security - *authorization* -
will look the same. Authorization is all about denying access to read or perform
different operations... and this is enforced in a way that's *independent* of how
you log in. So even if the way clients of your API authenticate is *much*
different than what we're doing, all this authorization stuff will still be relevant.

## Denying access with access_control in security.yaml

When a user logs in - no matter *how* they authenticate or where your user data is
stored - your login mechanism assigns that user a set of roles. In our app, those
roles are stored in the database and we'll eventually let admin users modify them
via our API. The simplest way to prevent access to an endpoint is by making
sure the user has some role. And the *easiest* way to do that is via `access_control`
in `security.yaml`.

We could, for example, say that every URL that matches the `^/api/cheeses` regular
expression - so anything that *starts* with `/api/cheeses` - requires `ROLE_ADMIN`.
This is normal, boring Symfony security... and I love it!

## Using access_control on your ApiResource

`access_control` is great for some situations, but most of the time you'll need
more flexibility. In a traditional Symfony app, I typically add security to
my controllers. But... in API Platform... um... we don't have any controllers!
Ok, so instead of thinking about protecting each controller, we'll think about
protecting each API *operation*. Maybe we want this collection `GET` operation to
be accessible anonymously but we want to require a user to be authenticated in
order to `POST` and create a new `CheeseListing`.

Open up that entity: `src/Entity/CheeseListing.php`. We already have an
`itemOperations` key, which we used to remove the `delete` operation and also
to customize the normalization groups of `get`. We can do something
similar with a `collectionOperations` option. Start by setting this to
`get` and `post`.

[[[ code('4c7838622d') ]]]

If we stopped here, this would change *nothing*. Oh... except that I have
a syntax error! Silly comma! Anyways, API Platform adds two collection operations
by default - `get` and `post` - so we're simply repeating what it was already doing.
But *now* we can customize these operations.

For the `post` operation - that's how we create new cheese listings - we really
need the user to be authenticated to do this. Set `post` to `{}` with a new
`access_control` option inside. We're going to set this to a mini-expression:
`is_granted()` passing that, inside single quotes `ROLE_USER` - that's the role
that our app gives to *every* user.

[[[ code('ca94f235ae') ]]]

Let's try that! The web debug toolbar tells me that I'm *not* logged in right
now. Let's make a `POST` request... set the owner to `/api/users/6` - that's
my user id... though the value doesn't matter yet... nor do any of the others fields.
Hit Execute and... perfect! A `401` error:

> Full authentication is required to access this resource.

If we logged in, this would work.

Let's tighten up our `itemOperations` too. The `PUT` operations - editing a
`CheeseListing` - is an interesting case... we definitely need the user to
be logged in... but we *probably* also *only* want the "owner" of a `CheeseListing`
to be able to edit it. Let's just handle the first part now. I'll copy my
whole `access_control` config and paste. While we're here, let's also re-add the
delete operation... but maybe only admin users can do this. Check for some
`ROLE_ADMIN` role.

[[[ code('d5b381fa8a') ]]]

Go refresh the docs... yes! The DELETE operation is back! Notice that the docs
are... basically "static" as far as security is concerned: it documents the *whole*
API, including operations that you might not have access to: it doesn't magically
read your roles and hide or show different operations. That's done on purpose, but
I wanted to point it out.

Try the `PUT` endpoint... I think I have a `CheeseListing` with id 1 and... just
send the `title` field. Another 401!

Next, our security setup is about to get smarter and more complex. The goal is
to make sure that only the "owner" of a `CheeseListing` can update that
`CheeseListing`... and maybe also admin users. To *really* know that things are
working, I think it's time to bootstrap a basic system to functionally test our
API.
