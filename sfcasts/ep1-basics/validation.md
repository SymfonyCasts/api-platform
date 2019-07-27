# Validation

There are a *bunch* of different ways that an API client can send bad data: they
might send malformed JSON... or send a blank `title` field... maybe because a
user forgot to fill in a field on the frontend. The job of our API is to respond
to *all* of these situations in an informative and consistent way so that errors
can be easily understood, parsed and communicated back to humans.

## Invalid JSON Handling

This is one of the areas where API Platform *really* excels. Let's do some
experimenting: what happens if we accidentally send some invalid JSON? Remove the
last curly brace.

Try it! Woh! Nice! This comes back with a *new* "type" of resource: a `hydra:error`.
If an API client understands Hydra, they'll instantly know that this response contains
error details. And even if someone has *never* heard of Hydra before, this is a
*super* clear response. And, *most* importantly, *every* error has the same structure.

The status code is also 400 - which means the client made an error in the request -
and `hydra:description` says "Syntax error". Without doing anything, API Platform
is already handling this case. Oh, and the `trace`, while maybe useful right now
during development, will *not* show up in the production environment.

## Field Validation

What happens if we just delete everything and send an *empty* request? Oh...
that's *still* technically invalid JSON. Try just `{}`.

Ah... *this* time we get a 500 error: the database is exploding because some of
the columns cannot be null. Oh, and like I mentioned earlier, if you're using
Symfony 4.3, you might already see a validation error instead of a database error
because of a new feature where validation rules are automatically added by reading
the Doctrine database rules.

But, whether you're seeing a 500 error, or Symfony is *at least* adding some basic
validation for you, the input data that's allowed is something *we* want to control:
I want to decide the *exact* rules for each field.

***TIP
Actually, the auto-validation was not enabled by default in Symfony 4.3, but may be in Symfony 4.4.
***

Adding validation rules is... oh, so nice. And, unless you're new to Symfony, this
will look *delightfully* boring. Above `title`, to make it required, add
`@Assert\NotBlank()`. Let's also add `@Assert\Length()` here with, how about,
`min=2` and `max=50`. Heck, let's even set the `maxMessage` to

> Describe your cheese in 50 chars or less

[[[ code('4d04c62b2b') ]]]

What else? Above `description`, add `@Assert\NotBlank`. And for price,
`@Assert\NotBlank()`. You could also add a `GreaterThan` constraint to make
sure this is above zero.

[[[ code('40a8181ab1') ]]]

Ok, switch back over and try sending *no* data again. Woh! It's awesome! The
 `@type` is `ConstraintViolationList`! That's one of the types that was described
by our JSON-LD documentation!

Go to `/api/docs.jsonld`. Down under `supportedClasses`, there's `EntryPoint` and
here is `ConstraintViolation` and `ConstraintViolationList`, which describes what
each of these types look like.

And the data on the response is really useful: a `violations` array where each
error has a `propertyPath` - so we know what field that error is coming from -
and `message`. So... it all just... works!

And if you try passing a `title` that's longer than 50 characters... and execute,
there's our custom message.

## Validation for Passing Invalid Types

Perfect! We're done! But wait... aren't we missing a bit of validation on the
`price` field? We have `@NotBlank`... but what's preventing us from sending *text*
for this field? Anything?

Let's try it! Set the price to `apple`, and execute.

Ha! It *fails* with a 400 status code! That's awesome! It says:

> The type of the price attribute must be int, string given

If you look closely, it's failing during the deserialization process. It's not
*technically* a validation error - it's a serialization error. But to the API client,
it looks just about the same, except that this returns an Error type instead of
a `ConstraintViolationList`... which probably makes sense: if some JavaScript
is making this request, that JavaScript should probably have some built-in
validation rules to prevent the user from *ever* adding text to the price field.

The point is: API Platform, well, really, the serializer, knows the types of your
fields and will make sure that nothing insane gets passed. It *knows* that price
is an integer from *two* sources actually: the Doctrine `@ORM\Column` metadata
on the field *and* the argument type-hint on `setPrice()`.

The only thing *we* really need to worry about is adding "business rules" validation:
adding the `@Assert` validation constraints to say that this field is required,
that field has a min length, etc. Basically, validation in API Platform works
*exactly* like validation in *every* Symfony app. And API Platform takes care of
the boring work of mapping serialization and validation failures to a 400 status
code and descriptive, consistent error responses.

Next, let's create a *second* API Resource! A User! Because things will get *really*
interesting when we start creating *relations* between resources.
