# Security Logic in the Validator

Our custom validator *is* being used... but it doesn't have any *real* logic yet:
it *always* add a "violation". Meaning, this validator *always* fails. Let's fix
that: let's fail if the `owner` - that's the `$value` we're validating - is being
set to a `User` that's *different* than the currently-authenticated user.

To figure out who's logged in, add `public function __construct()` and autowire
our favorite `Security` service. I'll hit Alt + Enter and go to "Initialize fields"
to create that property and set it.

[[[ code('6ae8481fd3') ]]]

## Making sure the User Is Authenticated

For the logic itself, start with `$user = $this->security->getUser()`. For this
particular operation, we've added security to *guarantee* that the user will be
authenticated. But just to be safe - or in case we decide to use this validation
constraint on some other operation - let's double-check that the user *is* logged
in: if `!$user instanceof User`, add a violation.

[[[ code('22cfdf1ffe') ]]]

I could hardcode the message... but to be a bit fancier - and make it configurable
each time we use this constraint - on the annotation class, add a public property
called `$anonymousMessage` set to:

> Cannot set owner unless you are authenticated

[[[ code('f5345cd21d') ]]]

Back in the validator, do the same thing as below: `$this->context->buildViolation()`,
then the message - `$constraint->anonymousMessage` - and `->addViolation()`. Finally,
return so the function doesn't keep running: set the validation error and exit.

[[[ code('fcdd5ccd19') ]]]

## Checking the Owner

At this point, we know the user is authenticated. We *also* know that the `$value`
variable *should* be a `User` object... because we're expecting that the `IsValidOwner`
annotation will be set on a property that contains a `User`. But... because I
*love* making mistakes... I might someday accidentally put this onto some *other*
property that does *not* contain a `User` object. If that happens, no problem!
Let's send future me a clear message that I'm doing something... well, kinda silly:
if `!$value instanceof User`, `throw new \InvalidArgumentException()` with

> @IsValidOwner constraint must be put on a property containing a User object

[[[ code('8726f0a5be') ]]]

I'm not using a violation here because this isn't a user-input problem - it's a
programmer bug.

*Finally*, we know that both `$user` and `$value` are `User` objects. All we
need to do now is compare them. So if `$value->getId() !== $user->getId()`, we
have a problem. And yes, you could also compare the objects themselves instead
of the id's.

Move the violation code into the if statement and... we're done! 

[[[ code('2b53bd7e33') ]]]

If someone sends the `owner` IRI of a different `User`, boom! Validation error! 
Let's see if our test passes:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

And... it does!

## Allowing Admins to do Anything

The *nice* thing about this setup is that the `owner` field is *still* part
of our API. We did that for a very specific reason: we want to make it possible
for an *admin* user to send an `owner` field set to *any* user. Right now, our
validator would *block* that. So... let's make it a *little* bit smarter.

Because we already have the `Security` object autowired here, jump
straight to check for the admin role: if `$this->security->isGranted('ROLE_ADMIN')`,
then return. That will prevent the *real* owner check from happening below.

[[[ code('bac1faa2ed') ]]]

## Requiring Owner

A few minutes ago, we talked about how the validator starts by checking to see if
the `$value` is null. That would happen if an API client simply *forgets* to send
the `owner` field. In that situation, our validator does *not* add a violation.
Instead, it returns... which basically means:

> In the eyes of this validator, the value *is* valid.

We did that because *if* you want the field to be required, that should be done
by adding a *separate* validation constraint - the `NotBlank` annotation. The
fact that we're *missing* this is a bug... and let's prove it before we fix it.

In `CheeseListingResourceTest`, move this `$cheesyData` up above one more
request. 

[[[ code('c786aa416d') ]]]

This request is the one that makes sure that, after we log in, we *are*
authorized to use this operation: we get a 400 status code due to a validation
error, but not the 403 that we would expect if we were denied access.

Now, pass `$cheesyData` to that operation. We should *still* get a 400 response:
we're passing *some* data, but we're still missing the `owner` field... which
*should* be required.

[[[ code('0217c5d5f6') ]]]

However, when we run the test:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

It explodes into a big, giant 500 error! It's trying to insert a record into
the database with a `null` `owner_id` column.

To fix this, above `owner`, add the missing `@Assert\NotBlank()`. 

[[[ code('dac0b99379') ]]]

The value must now *not* be blank *and* it must be a valid owner. Try the test again:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

It's green! Next, allowing `owner` to be part of our API is great for admin users.
But it's kind of inconvenient for *normal* users. Could we *allow* the `owner` field
to be sent when creating a `CheeseListing`... but automatically set it to the
currently-authenticated user if the field is *not* sent? And if so, how? That's
next.
