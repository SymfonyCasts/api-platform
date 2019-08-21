# Access Control & Voters

The access control system in API Platform instantly gives you a lot of power:
you can check for a simple role *or* write more complex logic and... it works!

What I *don't* like about it is that... it's a bit ugly. And... it can get even
uglier! What if I said that you should be able to update a `CheeseListing` if you
are the owner of that `CheeseListing` *or* an admin user can update *any*
`CheeseListing`. We could *maybe* add an `or` to the expression... and we might
need parentheses... That's not something I want to try. Instead, let's use a voter!

## Generating the Voter

Voters technically have nothing to do with API Platform... but they *do* work
*super* well as a way to keep your API Platform access controls clean and predictable.
Find your terminal and run:

```terminal
php bin/console make:voter
```

Call it `CheeseListingVoter`. I commonly have one voter per each entity or "resource"
that has some complex rules. This creates `src/Security/Voter/CheeseListingVoter.php`
If you're not familiar with the voters, they're a simple, but powerful way to
centralize your security logic.

## Updating access_control

Before we dive into the new class, go to `CheeseListing` first. Instead of saying
`is_granted('ROLE_USER') and previous_object.getOwner() == user`, simplify this
to `is_granted('EDIT', previous_object)`.

This deserves some explanation. The word `EDIT`... well... I just invented that.
We could use `EDIT` or `MANAGE` or `CHEESE_LISTNG_EDIT`... it's *any* word that
describes the "intention" of what access you want to check: I want to check to
see if the current user can "edit" this `CheeseListing`. This string will be
passed to the voter and, in a minute, we'll see how to use it. We're also passing
`previous_object` as the second argument. Thanks to this, the voter will *also*
receive the `CheeseListing` that we're deciding access on.

## How Voters Work

Here's how the voter system works: whenever you call `is_granted()`, Symfony loops
through all of the "voters" in the system and asks each one:

> Hey! Lovely request we're having, isn't it? Do you know how to decide
> whether or not the current user has `EDIT` access to this `CheeseListing` object?

Symfony itself comes with basically only two core voters: one that knows how to
decide access for any string that starts with `ROLE_` - so `is_granted()` calls
with `ROLE_USER` would be decided by that voter - and another that knows how to
decide access for the `IS_AUTHENTICATED_` strings, like `IS_AUTHENTICATED_FULLY`.

Now that we've created a class and made it extend Symfony's `Voter` base class,
our app has a *third* voter. This means that whenever someone calls `is_granted()`,
Symfony will call the `supports()` method passing `$attribute` - which will be the
string `EDIT`... or `ROLE_USER` and the `$subject` which will be the `CheeseListing`
object in this case.

## Coding the Voter

Our job is to answer the question: do we know how to decide access for this
`$attribute` and `$subject` combination? Or should another voter handle this?

To start, we're going to design our voter to decide access if the `$attribute` is
`EDIT` - and we may add other strings here later... like maybe `DELETE` - and if
`$subject` is an `instanceof CheeseListing`.

If anything else is passed - like `ROLE_ADMIN` - `supports()` will return false
and Symfony will know to ask a different voter.

*If* we return `true` from `supports()`, Symfony will call `voteOnAttribute()`
and pass us the same `$attribute` string - `EDIT` - the same `$subject` - the
`CheeseListing` object - and a `$token`, which contains the authenticated `User`
object. Our job in this method is simple: return true if the user should have
access or false if they should not.

Let's start by helping my editor: add `@var CheeseListing $subject` to hint to
it that `$subject` will *definitely* be a `CheeseListing`.

After this, the generated code has a switch-case statement - a nice example for
a voter that handles *two* different attributes for the same entity. I'll delete
the second case, but leave the switch-case statement in case we handle more later.
So, if `$attribute` is equal to `EDIT`, let's put our security business logic.
If  `$subject->getOwner() === $user`, return true! Access granted. Otherwise,
return false.

That's it! Oh, in case we make a typo and pass some *other* attribute to `is_granted()`,
the end of this function always return `false` to deny access. That's cool, but
let's make this mistake *super* obvious. Throw a big exception:

> Unhandled attribute "%s"

and pass that `$attribute`.

I love it! Our `access_control` is simple: `is_granted('edit', previous_object)`.
If we've done our job, this will call our voter and everything will work just like
before. And hey! We can check that by running out tests!

```terminal
php bin/phpunit --filter=testUpdateCheeseListing
```

Scroll up... yea! All green. And if we wanted to support some *other* attribute
later, maybe `is_granted('DELETE', previous_object)`, that'll be easy!

## Also allowing Admin Access

But, I had a different motivation originally for refactoring this into a voter:
I want to *also* allow "admin" users to be able to edit *any* `CheeseListing`.
For that, we'll check to see if the user has some `ROLE_ADMIN` role.

To check if a user has a role from inside a voter, we *could* call the `getRoles()`
method on our `User`... but that won't work if you're using the role hierarchy
system in `security.yaml`. A more robust option to see if the current user has
a role is to inject the `Security` service.

Add `public function __construct()` with one argument: `Security $secutity`. I'll
hit Alt + Enter -> Initialize Fields to create that property and set it

Inside `voterOnAttribute`, for the `EDIT` attribute, if
`$this->security->isGranted('ROLE_ADMIN')`, then return true.

I don't have a test for this... but you *could* add one on `CheeseListingResourceTest`
by creating a *third* user, giving them `ROLE_ADMIN`, logging in, then trying to
edit the cheese listing. Or you could *unit* test the voter itself if your logic
is getting pretty crazy.

Let's at least make sure we didn't break anything. Go tests go!

```terminal-silent
php bin/phpunit --filter=testUpdateCheeseListing
```

And... still green!

I love voters and this is the way *I* like to handle `access_control` in API Platform.
Sure, if you're just checking for a rule, no problem: use `is_granted('ROLE_ADMIN')`.
But if your logic gets *any* more complex, use a voter.

Next, our API *still* requires an API client to POST an *encoded* version of a
user's password when creating a new `User` resource. That's crazy! Let's learn
how to "hook" into the "saving" process so we can intercept the plain text password
and encode it.
