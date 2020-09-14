# Validating Who/When Can Publish

I have an idea! Let's complicate things!

We can't just let *anyone* publish a `CheeseListing`, only the owner or an admin
should be able to do that. And we already have that covered thanks to the
`security` attribute on the `CheeseListing` `put` operation. It has
`"security"="is_granted('EDIT', object)`, which activate a custom voter that we
created the in last tutorial: `CheeseListingVoter`. This looks for that `EDIT`
attribute and checks to make sure that the current user is the owner of the
`CheeseListing` or an admin. So it's already true that only the owner or an
admin can even *hit* this `put` operation.

But now we have a *new* requirement: to prevent low-quality listings from being
added, our business team has told us that an owner can only publish a
`CheeseListing` if the `description` is *longer* than 100 characters. Of it's
shorter, publishing should *fail* with a 400 status code. Oh... but the business
people *also* said that this rule *should* not apply to admins: they can publish
no matter *how* short the description is.

## Using Foundry State to Make a Longer Description

Um... ok! So let's start with a test to describe all of this craziness. In
`CheeseListingResourceTest`, to make sure that our `testPublishCheeseListing()`
keeps working, we need to make sure that the `CheeseListing` has a long enough
description.

If you look in `CheeseListingFactory` - `src/Factory/CheeseListingFactory` - by
default, the `description` is short. But I've already added something called a
"state method": `withLongDescription()`. If we call this on the factory, it will
set the description to three paragraphs via Faker... which will *definitely*
be longer than 100 characters.

This is a *long* way of saying that, inside the test, we add say `->withLongDescription()`
to make sure that the object created the `CheeseListing` object created here is going to
have a long enough `description`. Now, once we add the new restrictions, *this*
test should still pass.

## Testing the Validation Requirements

Below this, I'm going to paste in a new test method, which you can get from the
code block on this page. Let's quickly walk through it to see what the goal is.

In the first case, we create a `CheeseListing` with a short `description`, log
in as the owner of that listing, and then expect a 400 status code. But if we
then log in as an admin user and do the same thing, this *will* work. We then
have one more test to log back in as the normal user and change something
*different*... just to make sure we didn't break anything.

This finishes with two tests for unpublishing: we log in as a normal user, try
to set `isPublished` to false and that should *not* work. But if we log in as an
admin and do the same, this *will* work with a 200 status code.

Phew! And yes, we could - and maybe should - break this test into smaller test
methods to help debugging. I'm being a bit lazy by combining them.

## Is this Security? Or Validation?

So how can accomplish all of this? Well first... is this a security check or a
validation check? Because sometimes, the difference between the two is blurry.

In theory, we *could* do something inside the `security` attribute. The expression
has a variable called `previous_object`, which is the object *before* it was
updated. So, we could compare the previous object's `isPublished` with the
object's `isPublished` and do different things. Or, to keep this readable, we could
even pass both the `object` and `previous_object` into a voter as an array. That
seems a little crazy to me... but it would probably work!

But *I* tend to view things like this: security is best when you're trying to
*completely* prevent access to an operation. But validation is best when the
restrictions you need to apply are based on the *data* that's being sent, like
preventing a field from changing to some value. That's true even if those rules
depend on authenticated user.

In other words, we're going to create a custom validator! At your terminal, run:

```terminal
php bin/console make:validator
```

I'll call it: `ValidIsPublished`.

This generated two classes: the class that represents the "annotation" and also
the class that holds the actual validator logic. We can see both inside
`src/Validator/`.

Perfect! Before we add any logic to either class, let's use the annotation in
`CheeseListing`. So, above the `CheeseListing` class, add `@ValidIsPublished()`.

Now, the reason we're adding this above the *class* - and not above the
`$isPublished` property - is that our validator will need access to the entire
`CheeseListing` so that it can check both the `isPublished` and `description`
properties. If we had put this above `isPublished`, then only *that* field would
be passed to us.

Let's try our test and see what our failure looks like right now. Over in the test,
copy the new method name, and then run:

```terminal
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

And... it had an error! Let's see what's going on:

> Constraint `ValidIsPublished` cannot be put on classes

Ah! By default, constraint annotations are only allowed to be placed above
*properties*, not above the class. To change that, inside the validator class,
we need to do two things. First, add `@Target()` and pass this `"CLASS"`. And second,
I'll go to Code -> Generate - or Command + N - and select "Override methods" to
override the `getTargets()` method. Make this return `self::CLASS_CONSTRAINT`.

*Now* or annotation can be put above a class.

## Grabbing the CheeseListing inside the Validator

This means that, when our `CheeseListing` is validated, Symfony will call
`ValidIsPublishedValidater` and this `$value` is going to be the `CheeseListing`
object.

Real quick: if you're not familiar with how custom validators work, by default
if I add `@ValidIsPublished`, then Symfony will look for a service called
`ValidIsPublishedValidator`. So these two classes are already connected via
a naming convention.

Let's start with a little sanity check... right here: if not
`$value instanceof CheeseListing`, then throw a new `\LogicException` - though the
type doesn't really matter - and say:

> Only  `CheeseListing` is supported.

This is just to make sure that if we accidentally add the `@ValidIsPublished`
above a *different* class, something will yell at us.

Anyways, let's `dd($value)` to absolutely make sure we know what it looks like.

Back to the tests! Run and...

```terminal-silent
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

Yes! The validator is called and our `CheeseListing` object has `isPublished`
set to `true`, because this is the `CheeseListing` *after* the new data has been
deserialized into it. The failure is from our first test case: we currently
*allowing* this item to be published, even though the description is short. Next,
let's prevent this. How? It's back to our "original data" trick from Doctrine.
