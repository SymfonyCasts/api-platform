# Output DTO Class

So far, every ApiResource class is either an entity - like `CheeseListing` and
`User` - or a completely custom class, like `DailyStats`. With an entity, we can
add complex custom fields with some work. We did this in `User` with our custom
`isMe` and `isMvp` fields:

[[[ code('2c2e92a426') ]]]

And of course, with a custom class like `DailyStats` that is *not* an entity...
we can do whatever we want! We get to make these classes look *exactly* like our
API. On the downside, these take more time to set up and we lose automatic features
like pagination and filtering.

But, like Jean-Luc Picard searching for a solution to an impossible situation,
there *is* a *third* option, which is kind of "in between" these two.

## Why DTO Classes?

In `CheeseListing`, the `input` fields look quite different from the *output*
fields. For example, the `isPublished` field is *writable*, but it's not
*readable*:

[[[ code('f80f685bb5') ]]]

And the description property is readable, but not writable:

[[[ code('c57ca58f05') ]]]

Well, it *is* writable, but via a different way: a `setTextDescription()` method:

[[[ code('06f83e9275') ]]]

We accomplished all of this by using smart serialization groups and some custom
methods. The upside is that... it's simple! All the logic is in one class. The
downside is that... well... it's not actually *that* simple. Our serialization
and deserialization rules are not *super* clear: you can't quickly look at
`CheeseListing` and figure out which fields are going to be readable or writable.

One solution to this is to have a separate class for your input and output.
Basically, we would transform a `CheeseListing` into another object and then
*that* object would be serialized and returned to the `User`. We can also do the
same thing for the *input*, which we'll talk about later.

This feature is called input and output DTO's, for data transfer objects. And I
*love* this approach... in theory. Implementing it is pretty clean and it gives
you a lot of flexibility. But it's also not a feature that is heavily used by the
core API Platform devs. And I found some quirks... some of which are already fixed.
I'll walk you through them along the way.

## Creating the Output Class

So how do we start? By creating a class that has the *exact* fields that should exist
when a `CheeseListing` is *serialized*. In `src/`, create a new directory called
`Dto/` and inside, a new PHP class called `CheeseListingOutput`:

[[[ code('50cafb26fc') ]]]

For now let's *just* have a `public $title` property:

[[[ code('f7faf0e28f') ]]]

I'm going to use public properties for a few reason. First, these classes should be
simple and are only used for our API. And second, if you're using PHP 7.4, you can
add *types* to the properties to guarantee they're set correctly.

Anyways, we'll add more properties later, but let's see if we can get this working.

To tell API Platform that we want to use this as the output class, we need to go
back to `CheeseListing` and, inside the `@ApiResource` annotation - it doesn't
matter where... but I like to put it on top - add `output=`, take off the quotes
and say `CheeseListingOutput::class`. Go above to add this `use` statement
manually: `use CheeseListingOutput`:

[[[ code('04c4bbec6d') ]]]

Ok, before we do *anything* else, let's try it! Find your browser and open a
new tab to `/api/cheeses.jsonld`. And... bah! Error because I always forget my
comma. Let's try that again. This time... it... works?

Hmm... it's the exact same result as before. Why? Because right now API Platform
is thinking:

> Hey! You told me that you wanted to use `CheeseListingOutput` as your
> output representation. That's great! But... how do I *create* that object
> from a `CheeseListing`?

Yep! Something needs to *transform* our `CheeseListing` objects into
`CheeseListingOutput` objects so that API platform can serialize them. What
does that? A data transformer! Let's create one next.
