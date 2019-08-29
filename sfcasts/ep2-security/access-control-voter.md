# Access Control & Voters

The access control system in API Platform instantly gives you a lot of power:
you can check for a simple role *or* write more complex logic and... it works!

But... it's also ugly. And... it can get even uglier! What if I said that a user
should be able to update a `CheeseListing` if they are the owner of the
`CheeseListing` *or* they are an admin user. We could... *maybe* add an `or` to
the expression... and then we might need parentheses... No, that's not something
I want to hack into my annotation expression. Instead, let's use a voter!

## Generating the Voter

Voters technically have nothing to do with API Platform... but they *do* work
*super* well as a way to keep your API Platform access controls clean and predictable.
Find your terminal and run:

```terminal
php bin/console make:voter
```

Call it `CheeseListingVoter`. I commonly have one voter for each entity or "resource"
that has complex access rules. This creates
`src/Security/Voter/CheeseListingVoter.php`.

[[[ code('913b156c8b') ]]]

## Updating access_control

Before we dive into the new class, go to `CheeseListing`. Instead of saying
`is_granted('ROLE_USER') and previous_object.getOwner() == user`, simplify
to `is_granted('EDIT', previous_object)`.

[[[ code('7e2dc8f472') ]]]

This... deserves some explanation. The word `EDIT`... well... I just invented that.
We could use `EDIT` or `MANAGE` or `CHEESE_LISTING_EDIT`... it's *any* word that
describes the "intention" of the "access" you want to check: I want to check to
see if the current user can "edit" this `CheeseListing`. This string will be
passed to the voter and, in a minute, we'll see how to use it. We're also passing
`previous_object` as the second argument. Thanks to this, the voter will *also*
receive the `CheeseListing` that we're deciding access on.

## How Voters Work

Here's how the voter system works: whenever you call `is_granted()`, Symfony loops
through all of the "voters" in the system and asks each one:

> Hey! Lovely request we're having, isn't it? Do you happen to know how to decide
> whether or not the current user has `EDIT` access to this `CheeseListing` object?

Symfony itself comes with basically two core voters. The first knows how to
decide access when you call `is_granted()` and pass it `ROLE_` something, like
`ROLE_USER` or `ROLE_ADMIN`. It determines that by looking at the roles on the
authenticated user. The second voter knows how to decide access if you call
`is_granted()` and pass it one of the `IS_AUTHENTICATED_` strings:
`IS_AUTHENTICATED_FULLY`, `IS_AUTHENTICATED_REMEMBERED` or
`IS_AUTHENTICATED_ANONYMOUSLY`.

Now that we've created a class and made it extend Symfony's `Voter` base class,
our app has a *third* voter. This means that, whenever someone calls `is_granted()`,
Symfony will call the `supports()` method and pass it the `$attribute` - that's
the string `EDIT`, or `ROLE_USER` - and the `$subject`, which will be the
`CheeseListing` object in our case.

## Coding the Voter

Our job here is to answer the question: do we know how to decide access for this
`$attribute` and `$subject` combination? Or should another voter handle this?

We're going to design our voter to decide access if the `$attribute` is `EDIT` -
and we may support other strings later... like maybe `DELETE` - *and* if
`$subject` is an `instanceof CheeseListing`.

[[[ code('e334a5fc6f') ]]]

If anything else is passed - like `ROLE_ADMIN` - `supports()` will return false
and Symfony will know to ask a different voter.

But if we return `true` from `supports()`, Symfony will call `voteOnAttribute()`
and pass us the same `$attribute` string - `EDIT` - the same `$subject` -
`CheeseListing` object - and a `$token`, which contains the authenticated `User`
object. Our job in this method is clear: return true if the user *should* have
access or false if they should *not*.

Let's start by helping my editor: add `@var CheeseListing $subject` to hint to
it that `$subject` will *definitely* be a `CheeseListing`.

After this, the generated code has a switch-case statement - a nice example for
a voter that handles *two* different attributes for the same object. I'll delete
the second case, but leave the switch-case statement in case we *do* want to
support another attribute later.

So, if `$attribute` is equal to `EDIT`, let's put our security business logic.
If  `$subject->getOwner() === $user`, return true! Access granted. Otherwise,
return false.

[[[ code('4692dd0779') ]]]

That's it! Oh, in case we make a typo and pass some *other* attribute
to `is_granted()`, the end of this function always return `false` to deny access.
That's cool, but let's make this mistake *super* obvious. Throw a big exception:

> Unhandled attribute "%s"

and pass that `$attribute`.

[[[ code('1c0c84b885') ]]]

I love it! Our `access_control` is simple: `is_granted('EDIT', previous_object)`.
If we've done our job, this will call our voter and everything will work just like
before. And hey! We can check that by running out test!

```terminal
php bin/phpunit --filter=testUpdateCheeseListing
```

Scroll up... all green!

## Also allowing Admin Access

But... I had a different motivation originally for refactoring this into a voter:
I want to *also* allow "admin" users to be able to edit *any* `CheeseListing`.
For that, we'll check to see if the user has some `ROLE_ADMIN` role.

To check if a user has a role from inside a voter, we *could* call the `getRoles()`
method on the `User` object... but that won't work if you're using the role hierarchy
feature in `security.yaml`. A more robust option - and my preferred way of doing
this - is to use the `Security` service.

Add `public function __construct()` with one argument: `Security $security`. I'll
hit Alt + Enter -> Initialize Fields to create that property and set it

[[[ code('5d5492c473') ]]]

Inside `voteOnAttribute`, for the `EDIT` attribute, if
`$this->security->isGranted('ROLE_ADMIN')`, return true.

[[[ code('7053e60f8b') ]]]

That's *lovely*. I don't have a test for this... but you *could* add one
in `CheeseListingResourceTest` by creating a *third* user, giving them
`ROLE_ADMIN`, logging in and trying to edit the `CheeseListing`. Or you could
*unit* test the voter itself if your logic is getting pretty crazy.

Let's at *least* make sure we didn't break anything. Go tests go!

```terminal-silent
php bin/phpunit --filter=testUpdateCheeseListing
```

All good.

I *love* voters, and this is the way I handle access controls in API Platform.
Sure, if you're just checking for a role, no problem: use `is_granted('ROLE_ADMIN')`.
But if your logic gets *any* more complex, use a voter.

Next, our API *still* requires an API client to POST an *encoded* version of a
user's password when creating a new `User` resource. That's crazy! Let's learn
how to "hook" into the "saving" process so we can intercept the plain text password
and encode it.
