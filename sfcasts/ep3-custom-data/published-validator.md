# Validating Who/When Can Publish

I have an idea! Let's complicate things!

We can't let *anyone* publish a `CheeseListing`: only the owner or an admin
should be able to do that. But we already have that covered thanks to the
`security` attribute on the `CheeseListing` `put` operation:

[[[ code('2cf8f66487') ]]]

It has `"security"="is_granted('EDIT', object)`, which activate a custom voter
that we created in the last tutorial: `CheeseListingVoter`:

[[[ code('447f463c1a') ]]]

This looks for that `EDIT` attribute and checks to make sure that the current
user is the owner of the `CheeseListing` *or* is an admin. So it's already
true that only the owner or an admin can even *hit* this `put` operation.

But now we have a *new* requirement: to prevent low-quality listings from being
added, our business team has told us that an owner can only publish a
`CheeseListing` if the `description` is *at least* 100 characters. If it's
shorter, publishing should *fail* with a 400 status code. Oh... but the business
people *also* said that this rule *should* not apply to admins: they should be
able to publish no matter *how* short the description is.

## Using Foundry State to Make a Longer Description

Um... ok! Let's start with a test to describe all this craziness. In
`CheeseListingResourceTest`, to make sure that our `testPublishCheeseListing()`
keeps working, we need to give `CheeseListing` a longer description.

If you look in `CheeseListingFactory` - `src/Factory/CheeseListingFactory` - by
default, the `description` is short:

[[[ code('1887693e4b') ]]]

But I've already added something called a "state method": `withLongDescription()`:

[[[ code('247ccda3c4') ]]]

If we call this on the factory, it will set the description to three paragraphs
via Faker... which will *definitely* be long enough.

In other words, inside the test, we can add `->withLongDescription()`
to make sure that the `CheeseListing` created will have a long enough `description`:

[[[ code('321c3bca80') ]]]

Thanks to this, once we add the new restrictions, this test *should* still pass.

## Testing the Validation Requirements

Below this, I'm going to paste in a new test method, which you can get from the
code block on this page:

[[[ code('beeb866932') ]]]

Let's walk through it real quick.

We start by creating a `CheeseListing` with a short `description`, logging
in as the owner of that listing, trying to publish it and checking for a 400 status
code:

[[[ code('7a293cac17') ]]]

Next, we log in as an admin user, do the same thing and check that this *does*
work:

[[[ code('9d0a22e11d') ]]]

I also have a test that logs back in as the normal user and changes something
*different*... just to make sure *normal* edits still work:

[[[ code('bdc28021ba') ]]]

Finally, the last two parts are for unpublishing. In our system, unpublishing
is something that *only* an admin can do. To test that, we log in as a normal
user, try to set `isPublished` to false and make sure it doesn't work:

[[[ code('4c229e9812') ]]]

Then we log in as an admin and make sure that it *does* work:

[[[ code('2999189361') ]]]

Phew! And yes, we could - and maybe should - break this test into smaller test
methods to make debugging easier. I'm being a bit lazy by combining them.

## Is this Security? Or Validation?

So how can accomplish all of this? Well first... is this a security check or a
validation check? Because sometimes, the difference between the two is blurry.

In theory, we *could* do something inside the `security` attribute. The expression
has a variable called `previous_object`, which is the object *before* it was
updated. So, we could compare the previous object's `isPublished` with the
object's `isPublished` and do different things. Or, to keep this readable, we could
even pass both the `object` and `previous_object` into a voter as an array. That
seems a little crazy to me... but it would probably work!

However, *I* tend to view things like this: security is best when you're trying to
*completely* prevent access to an operation. Validation is best when the
restrictions you need to apply are based on the *data* that's being sent, like
preventing a field from changing to some value. That's true even if those values
*depend* on the authenticated user.

In other words, we're going to create a custom validator! At your terminal, run:

```terminal
php bin/console make:validator
```

I'll call it: `ValidIsPublished`.

This generated two classes: a class that represents the "annotation" and also
a class that holds the actual validator logic. We can see both inside
`src/Validator/`.

Before we add any logic to either class, let's use the annotation in
`CheeseListing`. So, above the `CheeseListing` class, add `@ValidIsPublished()`:

[[[ code('6bef647fb3') ]]]

Now, the reason we're adding this above the *class* - and not above the
`$isPublished` property - is that our validator will need access to the entire
`CheeseListing` object so that it can check both the `isPublished` *and* `description`
properties. If we had put this above `isPublished`, then only *that* field would
be passed to us.

Let's try the test and see what the failure looks like right now. Over in the test
class, copy the new method name, and then run:

```terminal
symfony php bin/phpunit --filter=testPublishCheeseListingValidation
```

And... it had an *error*! Let's see what's going on:

> Constraint `ValidIsPublished` cannot be put on classes

Ah! By default, constraint annotations are only allowed to be placed above
*properties*, not above the class. To change that, inside the validator class,
we need to do two things. First, add `@Target()` and pass this `"CLASS"`:

[[[ code('c752dd42e4') ]]]

Second, I'll go to "Code"->"Generate" - or `Command`+`N` on a Mac - and select
"Override methods" to override `getTargets()`. Make this return:
`self::CLASS_CONSTRAINT`:

[[[ code('6f6d513a50') ]]]

*Now* our annotation can be put above a class.

## Grabbing the CheeseListing inside the Validator

This means that when our `CheeseListing` is validated, Symfony will call
`ValidIsPublishedValidator` and this `$value` will be the `CheeseListing`
*object*:

[[[ code('fcf7ba93a1') ]]]

Real quick: if you're not familiar with how custom validators work, by default
if I add `@ValidIsPublished`, then Symfony will look for a service called
`ValidIsPublishedValidator`. So these two classes are already connected via
this naming convention.

Let's start with a sanity check... right here: if not
`$value instanceof CheeseListing`, then throw a new `\LogicException` - though the
exact exception class doesn't matter - and say:

> Only `CheeseListing` is supported.

[[[ code('a4c3cc5616') ]]]

This is just to make sure that if we accidentally add `@ValidIsPublished`
above a *different* class, someone will yell at us. Now  `dd($value)` so we can
be absolutely sure that we know what this looks like:

[[[ code('271a09c4e9') ]]]

Back to the tests! Run:

```terminal-silent
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

And... Yes! The validator is called and our `CheeseListing` object has `isPublished`
set to `true` because this is the `CheeseListing` *after* the new data has been
deserialized into it. The failure is from our first test case: we currently
*allow* this item to be published, even though the description is short. Next,
let's prevent this. How? It's back to our "original data" trick from Doctrine.
