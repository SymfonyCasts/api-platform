# Normalizer & Completely Custom Fields

Our `UserNormalizer` is now totally set up. These classes are beautiful & flexible:
we can add custom normalization groups on an object-by-object basis. They're also
weird: you need to know about this `NormalizerAwareInterface` thing... and you need
to understand the idea of setting a flag into the `$context` to avoid calling yourself
recursively. But once you've got that set up, you're *gold*!

## Options for Adding Custom Fields

And if you look more closely... we're even *more* dangerous than you might
realize. The job of a normalizer is to turn an object - our `User` object - into
an array of data and return it. You can tweak which data is included by adding
more groups to the `$context`... but you could also add custom fields... right
here!

Well, hold on a minute. Whenever possible, if you need to add a custom field,
you should do it the "correct" way. In `CheeseListing`, when we wanted to add
a custom field called `shortDescription`, we did that by adding a
`getShortDescription()` method and putting it in the `cheese:read` group. Boom!
Custom field!

Why is this the *correct* way of doing it? Because this causes the field to be
seen & documented correctly.

But, there are two downsides - or maybe limitations - to this "correct" way of
doing things. First, if you have *many* custom fields... it starts to get ugly:
you might have a *bunch* of custom getter and setter methods *just* to support
your API. And second, if you need a *service* to generate the data for the custom
field, then you *can't* use this approach. Right now, I want to add a custom `isMe`
field to `User`. We couldn't, for example, add a new `isMe()` method to `User`
that returns true or false based on whether this `User` matches the
currently-authenticated user... because we need a service to know who is logged in!

So... since we can't add an `isMe` field the "correct" way... how *can* we add it?
There are two answers. First, the... sort of... "second" correct way is to use a
DTO class. That's something we'll talk about in a future tutorial. It takes more
work, but it *would* result in your custom fields being documented properly.
Or second, you can hack the field into your response via a normalizer. That's
what we'll do now.

## Adding Proper Security

Oh, but before we get there, I almost forgot that we need to make this
`userIsOwner()` method... actually work! Add a constructor to the top of this class
and autowire the `Security` service. I'll hit Alt -> Enter and go to
"Initialize Fields" to create that property and set it. Down in the method, say
`$authenticatedUser = $this->security->getUser()` with some PHPDoc above this
to tell my editor that this will be a `User` object or null if the user is
*not* logged in. Then, if `!$authenticatedUser`, return false. Otherwise, return
`$authenticatedUser->getEmail() === $user->getEmail()`. We could also compare the
objects themselves.

[[[ code('f89254b370') ]]]

Let's try this: if we fetch the collection of all users, the `phoneNumber` field
should *only* be included in *our* user record. And... no `phoneNumber`, no
`phoneNumber` and... yes! The `phoneNumber` shows up *only* on the third record:
the user that we're logged in as.

## Fixing the Tests

Oh, but this *does* break one of our tests. Run all of them:

```terminal
php bin/phpunit
```

Most of these will pass, but... we *do* get one failure:

> Failed asserting that an array does not have the key `phoneNumber` on
> UserResourceTest.php line 66.

Let's open that test and see what's going on. Ah yes: this is the test where we
check to make sure that if you set a `phoneNumber` on a `User` and make a GET
request for that `User`, you do *not* get the `phoneNumber` field back *unless*
you're logged in as an admin.

But we've now changed that: in addition to admin users, an authenticated user
will *also* see their own `phoneNumber`. Because we're logging in as
`cheeseplease@example.com`... and then fetching that same user's data, it
*is* returning the `phoneNumber` field. That's the correct behavior.

To fix the test, change `createUserAndLogin()` to just `createUser()`... and
remove the first argument. Now use `$this->createUserAndLogin()` to log in as a
totally *different* user. Now we're making a GET request for the `cheeseplease@example.com` 
user data but we're *authenticated* as this *other* user. 
So, we should *not* see the `phoneNumber` field.

[[[ code('55f893cb3d') ]]]

Run the tests again:

```terminal-silent
php bin/phpunit
```

And... all green.

## Adding the Custom isMe Field

Ok, back to our original mission... which will be *delightfully* simple: adding
a custom `isMe` field to `User`. Because `$data` is an array, we can add whatever
fields we want. Up here, I'll create a variable called `$isOwner` set to what we
have in the if statement: `$this->userIsOwner($object)`. Now we can use `$isOwner`
in the `if` *and* add the custom field: `$data['isMe'] = $isOwner`.

[[[ code('f61fdf399f') ]]]

Et voilà! Test it! Execute the operation again and... there it is: `isMe` false,
false and true! Just remember the downside to this approach: our documentation
has *no* idea that this `isMe` field exists. If we refresh this page and open the
docs for fetching a single `User`... yep! There's no mention of `isMe`. Of course,
you *could* add a `public function isMe()` in `User`, put it in the `user:read`
group, always return `false`, then *override* the `isMe` key in your normalizer
with the *real* value. That would give you the custom field *and* the docs.
But sheesh... that's... getting kinda hacky.

Next, let's look more at the `owner` field on `CheeseListing`. It's interesting:
we're currently allowing the user to *set* this property when they POST to create
a `User`. Does that make sense? Or should it be set automatically? And if
we *do* want an API user to be able to send the `owner` field via the JSON, how
do we prevent them from creating a `CheeseListing` and setting the `owner` to
some *other* user? It's time to see where security & validation meet.
