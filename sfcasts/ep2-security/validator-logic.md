# Security Logic in the Validator

Our custom validator *is* being used... but it doesn't have any *real* logic yet:
it *always* add a "violation". Meaning, this validator *always* fails. Let's fix
that: let's fail is the `owner` - that's the `$value` we're validating - is being
set to a `User` that's *different* than the currently-authenticated user.

To figure out who's logged in, add `public function __construct()` and autowire
our favorite `Security` service. I'll hit Alt + Enter and go to "Initialize fields"
to create that property and set it.

## Making sure the User Is Authenticated

For the logic itself, start with `$user = $this->security->getUser()`. For this
particular operation, we've added security to *guarantee* that the user will be
authenticated. But just to be safe - or in case we decide to use this validation
constraint for some other operation - let's double-check that the user is logged
in: if `!$user instanceof User`, then add a violation.

I could hardcode the message... but to be a bit fancier - and make it configurable
each time we use this constraint, on the annotation class, add a public property
called `$anonymousMessage` set to:

> Cannot set owner unless you are authenticated

Back in the validator, do the same thing as below: `$this->context->buildViolation()`,
then the message - `$constraint->anonymousMessage` - and `->addViolation()`. Then
return so the function doesn't keep running: set the validation error and exit.

## Checking the Owner

At this point we know the user is authenticated. We *also* know that the `$value`
variable *should* be a `User` object... because we're expecting that the `IsValidOwner`
annotation will be set on a property that contains a `User`. But... because I
*love* making mistakes... I might someday accidentally put this onto some *other*
property that does *not* container a `User` object. If that helps, no problem!
Let's send future us a clear message that we're doing something... kinda silly:
if `!$value instanceof User`, then `throw new \InvalidArgumentException()` with

> @IsValidOwner constraint must be put on a property containing a User object

I'm not using a violation here because this isn't a user-input problem - it's a
programmer bug.

*Finally*, we know that both `$user` and `$value` are both `User` objects. All we
need to do know is compare them. So if `$value->getId() !== $user->getId()`, then
we have a problem. And yes, you could also compare the objects themselves instead
of the id's.

Move the violation code into the if statement and... we're done! If someone sends
the `owner` IRI of a different `User`, boom! Validation error! Let's see if our
test passes:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

And... it does!

## Allowing Admins to do Anything

The *nice* thing about this setup is that the `owner` field is *still* part
of our API. We did that for a very specific reason: we want to make it possible
for an *admin* user to send an `owner` field set to *any* user. Right now, our
validator would *block* that. So... let's make it a *little* bit smarter.

Because we already have a `Security` object autowired into the class, we can jump
straight to check for the admin role: of `$this->security->isGranted('ROLE_ADMIN')`,
then return. That will prevent the *real* owner check from happening below.

## Requiring Owner

A few minutes ago, we talked about how the validator starts by checking to see if
the `$value` is null. That would happen if an API client simply *forgets* to send
the `owner` field. When that happens, our validator doesn't add a violation. Instead,
it returns... which basically means:

> In the eyes of this validator, the value *is* valid.

We did that because *if* you want the field to be required, that should be done
by adding a *separate* validation constraint - the `NotBlank` annotation. The
fact that we're *missing* this is a bug... and let's prove it before we fix it.

In `CheeseListingResourceTest`, let's move this `$cheesyData`, up above one more
request. This request is the one that makes sure that after we log in, we *can*
use this operation: we will get a 400 status code due to a validation error, but
not the 404 that would be expected if you were denied access.

Now, pass `$cheesyData` to that operation. We should *still* get a 400 response:
we're passing *some* data, but this should return a validation error due to the
missing `owner` field.

But when you run the test:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

It's a big, giant 500 error! It's trying to insert that record into the database,
which is failing because the `owner_id` column is required.

To fix this, above `owner`, add the missing `@Assert\NotBlank()`. The value must
now *not* be blank *and* it must be a valid owner. Try the test now:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

It's green! Next, allowing `owner` to be part of our API is great for admin users.
But it's also inconvenient for normal users. Could we *allow* the `owner` field
to be sent when creating a `CheeseListing`... but automatically set it to the
currently-authenticated user if it's missing? And if so, how can we do that? That's
next.
