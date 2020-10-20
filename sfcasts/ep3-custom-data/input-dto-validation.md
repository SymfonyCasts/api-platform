# Input DTO Validation

One nice thing about input DTOs is that after our data transformer is called and
we return the final `CheeseListing`, that object is validated like normal. We saw
this: we submitted empty JSON to create a new `CheeseListing` and got back errors
like "the title should not be blank".

These are coming from the `@Assert` rules on `CheeseListing`. `CheeseListing` is
*still* validated.

But... this isn't the *only* way that validation can work. One complaint that you'll
sometimes here about Symfony's validator is that, for it to work, you need to allow
your entity to get into an invalid state. Basically, even though `$title` should
not be blank, we need to first *allow* a blank or null value to be set onto the
property so that it can *then* be validated.

This was at the root of a problem we had a minute ago. In `CheeseListingInput`,
we had to add some type-casting here to help us set invalid data onto `CheeseListing`
without causing a PHP error... so that it could *then* be validated.

## Moving the Constraints to the Input

Another option is to move the validation from the entity into the *input* class.
If we did that, then when we set the data onto this `CheeseListing` object, we would
*know* that the data is - in fact - valid.

So let's try this. Undo the typecasting in `CheeseListingInput` because once
we're done, we will know that the data *is* valid and this won't be necessary.

Next in `CheeseListing`, I'm going to move *all* of the `@Assert` constraints onto
our input. Copy the two off of `$title` and move those into `CheeseListingInput`.
We *do* need a `use` statement... but let's worry about that in a minute.

Copy the constraint from `$description`, move it, copy the one from
`$price` delete it and... also delete the constraint from `$description`. We
*could* also choose to *keep* these validation rules in our entity... which would
make sense if we used this class outside of our API and it needed to be validated
there.

Paste the constraint above `price` and... there's one more constraint above
`owner`: `@IsValidOwner()`. Copy it, delete it, and move it into the input.

That's it! To get the `use` statements, re-type the end of `NotBlank` and hit tab
to auto-complete it - that added the `use` statement on top - and do the same
for `IsValidOwner`.

Ok cool! All of the validation rules live here and we have *no* constraints
in `CheeseListing`.

## Validating the Input Class

But... unfortunately, API Platform does *not* automatically validate your input
DTO objects: it *only* validates the final API resource object. So we'll need to
run validation manually... which is both surprisingly easy *and* interesting
because we'll see how we can trigger API Platform's super nice validation error
response manually.

Inside of our data transformer, before we start transferring data, *this* is where
validation should happen. To do that, we need the validator! Add a public function
`__construct()` with a `ValidatorInterface` argument. But grab the one
from `ApiPlatform\`, *not* `Symfony\`. I'll explain why in a second. Call that
argument `$validator` and then I'll go to Alt + Enter and select "Initialize
properties" to create that property and set it.

Down in `transform()`, `$input` will be the object that contains the deserialized
JSON that we want to validate. Do that with
`$this->validator->validate($input)`.

That's it! The validator from API platform is a wrapper around Symfony's validator.
It wraps it so that it can *add* a few nice things. Let's check it out.

Hit Shift + Shift, look for `Validator.php`, include non project items and open
the Validator from API Platform. As I mentioned, this wraps Symfony's validator...
which it does so that it can add the validation groups from API Platform's config.

But *more* importantly, at the bottom, after *executing* validation, it gets back
these "violations" and throws a `ValidationException`. This is a special exception
that you can throw from anywhere to trigger the nice validation error response.

So... let's go see it! At the browser, hit Execute and... yeehaw! A 400 validation
error response! But *now* this is coming from validating our input object. The
input must  be *fully* valid before the data will be transferred to our entity.

So if you like this, do it! If you don't, leave your validation constraints on
your entity.

Next: it's time for our final topic! Right now, all of our resources use their
auto-increment database id as the identifier in the API. But in many cases,
you can make your life easier - or the life of a JavaScript developer who is *using*
your API - by using UUID's instead.
