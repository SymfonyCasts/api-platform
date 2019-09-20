# Custom Validator

Here's the situation: all authenticated users should have access to create a
`CheeseListing`... and one of the fields that can be passed is `owner`. But
the data passed to the `owner` field may be valid or invalid depending on who
you're authenticated as. For a normal user, I'm supposed to set this to my own
IRI: if I try to set it to a *different* IRI, that should be denied. But for an
*admin* user, they *should* be allowed to set the IRI to anyone.

When the *value* of a field may be allowed or not allowed based on who is
authenticated, that should be protected via *validation*... which is why we're
expecting a 400 status code - not a 403.

## Generating the Custom Validator

Ok, so how can we make sure the `owner` field is set to the currently-authenticated
user? Via a *custom* validator. Find your terminal and kick things off with:

```terminal
php bin/console make:validator
```

Call it `IsValidOwner`. If you're not familiar with validators, each validation
constraint consists of *two* classes - you can see them both inside the
`src/Validator/` directory. The first class represents the *annotation* that we
will use to activate this... and it's usually empty, except for a few properties
that are typically public. Each public property will become an *option* that you
can pass to the annotation. More on that in a minute.

[[[ code('ec7dcc98bc') ]]]

The other class, which typically has the same name plus the word "Validator", is
what will be called to do the actual *work* of validation. The validation system
will pass us the `$value` that we're validating and then we can do whatever
business logic we need to determine if it's valid or not. If the value is invalid,
you can use this cool `buildViolation()` thing to set an error.

[[[ code('262efbe2e2') ]]]

## Using the Validation Constraint

To see this in practice, open up `CheeseListing`. The property that we need to
validate is `$owner`: we want to make sure that it is set to a "valid" owner...
based on whatever crazy logic we want. To activate the new validator, add
`@IsValidOwner()`. This is where we could customize the `message` option...
or *any* public properties we decide to put on the annotation class.

[[[ code('a8e3c75665') ]]]

Actually, let's change the *default* value for `$message`:

> Cannot set owner to a different user

[[[ code('f245466fd8') ]]]

Ok, now that we've added this annotation, whenever the `CheeseListing` object is
being validated, the validation system will *now* call `validate()` on
`IsValidOwnerValidator`. The `$value` will be the value of the `$owner` property.
So, a `User` object. It also passes us `$constraint`, which will be an instance
of the `IsValidOwner` class where the public properties are populated with any
options that we may have passed to the annotation.

## Avoid Validating Empty Values

The *first* thing the validator does is interesting... it checks to see if the
`$value` is, sort of, empty - if it's null. If it *is* null, instead of adding
a validation error, it does the opposite! It returns.. which means that, as far
as this validator is concerned, the value is valid. Why? The philosophy is that,
if you want this field to be *required*, you should add an *additional* annotation
to the property - the `@Assert\NotBlank` constraint. We'll do that a bit later.
That means that *our* validator only has to do its job if there *is* a value set.

## The setParameter() Wildcard

To see if this is working... ah, let's just try it! Sure, we haven't added any
logic yet... and so this constraint will *always* have an error... but let's make
sure we at least see that error!

Oh, and this `setParameter()` thing is a way for you to add "wildcards" to the
message. Like, if you set `{{ value }}` to the email of the `User` object, you
could reference that dynamically in your message with that same `{{ value }}`.
We don't need that... so let's remove it. Oh, and just to be *totally* clear, the
`$constraint->message` part is referencing the `$message` property on the annotation
class. So, we *should* see our customized error.

Let's try it! Go tests go!

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

If we scroll up... awesome! It's failing: it's getting back a 400 bad request
with:

> Cannot set owner to different user

Hey! That's our validation message! The failure comes from
`CheeseListingResourceTest` line 44. Once we use a *valid* `owner` IRI, validation
*still* fails because... of course... right now our new validator *always* adds
a violation.

Let's fix that next: let's add *real* logic to make sure the `$owner` is set to
the currently-authenticated user. *Then* we'll go further and allow admin users
to set the `$owner` to anyone.
