# Type Validation

Let's talk about validation. Even outside of DTO's, there are two layers of validation.
The first asks: is a piece of data even *settable* on a property. Like, is it a
valid data *type* for that property? And second, does it pass validation rules?

## Validation for Invalid Types

Let's start with that *first* type. Before even thinking about validation, we need
to ask: is it even possible for a piece of data to be *set* onto a property? For
example, pretend for a minute that we *don't* have an input class. In that case,
if a `price` field is sent in the JSON, it will be set via the `setPrice()` method.
And since the `int` argument is *not* nullable, if you tried to send a null `price`,
that value would *not* even be legal to set on this property.

When this happens, API Platform returns a 400 error. It's not a validation error
exactly... but it effectively means the same thing.

Let's see a real example. Inside `CheeseListeningInput`, all the properties are
public... and we're not using PHP 7.4 property types. This means that, technically,
we can set each property to *anything*. But, we *also* have this `@var`, which tells
API Platform that this is *supposed* to be an `int`. And while that documentation
wouldn't *normally* make any difference in how our code behaves, it *does* cause
something to happen in API Platform.

Move over refresh the documentation. Now go to the `POST` cheeses endpoint, click
"Try it out", and just send a `price` field set to, how about, `apple`.

Hit Execute. 400 error! It says:

> The type of the "price" attribute for class `CheeseListingInput` must be an
> int, string given.

*This* is cool. When the deserializer does its work, it *first* tries to figure
out what *type* the field should be, which we know it does in a number of different
ways, like reading Doctrine metadata, setter argument type-hints and even PHPDoc.
Then, for scalar types line the `int` price, if the field sent in the JSON is
*not* a valid type, API Platform throws this error.

## The Cons of Type Validation

I love this feature, but I *do* need to mention two things about it. First,
unfortunately, unlike true validation errors, the response doesn't contain a nice
list of *all* of the errors.

For example, if we sent *another* invalid field, instead of seeing *both* errors,
you would see just one: whichever one *happened* to be tried first. After fixing
that error, *then* you would see the next one. The response for these type errors
also isn't perfect: it doesn't tell you - in a machine-readable way - which field
the error comes from. True validation errors do a much better job.

This *is* something that the API Platform team would like to change so that it
can list *all* of the errors at once... but someone needs to do some work on
the Symfony serializer to make it possible. There *is* a pull request open to
do that.

The second thing I want to mention is about the error itself. The stack trace on
this error would *not* be shown on production, but the `hydra:description` *would*.
But notice: it mentions our internal class name!

Very quickly: this *only* happens when using input DTO's and it's a bug that has been
fixed and will be released in API Platform 2.5.8. Starting in that version, the
class name should *not* be in the error message.

The *big* point is: this whole idea of whether or not a piece of data can be
set on a property is something you just need to be aware of because these, sort
of, "sanity errors" are not quite as nice as true validation errors.

## Special Case for Input DTOs: Null Fields

And if you're using an input DTO, you need to be even *more* aware of this
question of: "can a field be set to a certain value?". Why?

Send a completely *empty* object to create a `CheeseListing`. This should return
a 400 error. But which kind? A type error like we just saw or a true validation
error?

The answer is that this should return a *true* validation error - saying that
some of the fields are required thanks to the `NotBlank` validation constraints.
When a field is *not* in the JSON, it's simply *not* ever set on the object. So
*not* sending a `price` field is different than sending a `price` field set to
`null`, which would *not* be allowed thanks to our PHPDoc.

But when we hit Execute... ah! 500 error! It says:

> Argument 1 passed to `setDescription()` must be of type string, null given

This is coming from over `CheeseListingInput` on line 66. Ah: because the
`description` field wasn't sent, `$this->description` is null... but then
`setDescription()` on `CheeseListing` does not *allow* null. The result is a
very not cool 500 error.

There are 2 solutions to this. First, you could add validation constraints to
your input class to guarantee that certain properties are not null. We're going
to talk about that in a few minutes.

Or, you can do some type-casting to avoid the errors. For example: we can cast
the description to a `(string)`, cast the price to an `(int)` And then, up here,
we can cast the title to a `(string)`.

*That* should fix the error. Now, if you're thinking:

> Wait a second! I still don't want `description` and `title` to be empty or the
`price` to be zero!

Me either! But check this out: At your browser, hit Execute again. And... surprise!
We see *true* validation errors! It says that `title` and `description` should not
be blank! So yes, even with an input class, validation *still* happens on our
final resource object. Oh, and why is there no error for `price`? That field
*does* have a `NotBlank` constraint, but it should probably have a `GreaterThan`
constraint to prevent it from being set to zero.

Anyways, we can see that validation *still* happens when we're using an input class.
How does that work? Can we move the validation to the input class? And should we?
That's next.
