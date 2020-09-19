# Adding & Populating the Custom Field

Let's ignore *all* of this data provider stuff for a minute and just pretend that
we want to add a *nice*, normal field to our API. Simple! In `User`, add a new
property - `private $isMe` - and put it in the `user:read` group.

The only difference between this property and the other properties is that I'm
*not* going to add `@ORM\Column` because I *don't* want to store this field in
the database.

Down at the bottom of this class, go to Code->Generate - or Command + N on a
Mac - and select getters and setters for the `isMe` field. Oh, but let's
improve these: add the boolean type-hint on the argument and a `bool` return
type.

So... that's actually all we need to make this part of our API! Open a new tab and
go to `/api` to check out the documentation. On the get endpoint, click to look
at the schema. There it is! An `isMe` boolean field.

To be even *cooler* - which is *always* my goal - back up on the property... there
it is... we can add more docs:

> Returns true if this is the currently authenticated user

That's nice because API Platform will automatically use this in the docs. When
we look at the schema now on the user operation... there it is!

In a minute, we're going to *set* this field in `UserDataProvider`. But we *do*
have a, sort of, strange situation, because - if we ever called the `isMe` field
*outside* of an API call where the data provider is called... the `isMe` field
won't be set!

Let's be *extra* cautious. Down in the getter, if `$this->isMe` is null, it
means it simply hasn't been set. Throw a new `LogicException`:

> The `isMe` field has not been initialized.

## Setting Custom Data in the Data Provider

Let's *finally* set this field in `UserDataProvider`. My guess is that the
`getCollection()` method will return an array of users... but let's actually check
that. Add `$users =`, `dd($users)` and, at the bottom, return `$users`.

Back at the browser, find the original tab and refresh. Oh! It's *not* an array
of users! It's a Paginator object with a Doctrine Paginator inside! So... this obviously
isn't an array, but the `Paginator` object *is* iterable: we *can* *loop* over it
like an array and then update the `isMe` field on each item.

Above this line, let's add some documentation to help my editor: I'll advertise
that `$users` is an array of `User` objects... which is actually a lie... but when we
loop over it, we *will* get `User` objects.

Now, do that loop: `foreach ($users as $user)`. And inside say
`$user->setIsMe()` - yay for auto-completion - and set this to true to start.

Let's see if it shows up! Move over, refresh and... yes! Every record has `isMe: true`.

## Setting isMe Based on the Current User

Setting this to the *correct* value is probably the *easiest* part of the whole process.
Start by adding a second argument to the constructor - `Security $security` - so
we can get the authenticated user. I'll hit Alt+Enter and go to Initialize Properties
to create that property and set it.

Now, before the loop, set `$currentUser = $this->security->getUser()` and set
`isMe` with a simple `$currentUser === $user`.

I love it! Try this one last time and... nice! The first one has `isMe: true` and
then `isMe` is false for the others.

We did it! Oh, except that this only works for the collection endpoint. Try going
to `/api/users/1.jsonld`. Yep!

> The isMe field has not been initialized

That makes sense: we only added the logic to the *collection* data provider, not
the item data provider, which is still being done by the core *Doctrine* item provider.
If we want to output the field here, we need to do a little bit more work.

Let's do that next and also find one other - kind of surprising - spot where
we *also* need to set this field.
