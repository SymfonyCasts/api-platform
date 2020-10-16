# DTO Data Transformer

To get an output DTO class to... ya know... actually *work*, we need to write
code that converts our `CheeseListing` object into a `CheeseListingOutput` object
so that API Platform can serialize it. The "thing" that does that is called a data
transformer.

## Creating the Data Transformer Class

Let's create one in the `src/` directory: add a new directory called `DataTransformer`
for organization and a class inside called `CheeseListingOutputDataTransformer`.

As usual with a class that hooks into part of API Platform, this needs to implement
an interface. In this situation, it's - surprise! - `DataTransformerInterface`!
Inside the class, go to Code -> Generate - or Command + N on a Mac - and select
"Implement Methods" to generate the two we need.

## The Data Transformer System

So here's how this works: the data transformer system exists *entirely* to
support the input and output DTO classes that we're working with. Whenever API
Platform is about to serialize an object, it checks to see if that resource has
an *output* DTO - which we configured in CheeseListing's `@ApiResource` annotation.
If it *does*, it loops over every data transformer in the system and calls
`supportsTransformation()`.

It basically asks each one:

> Hey! I apparently need to transform a `CheeseListing` into a
> `CheeseListingOutput`. Do... you know how to do that?

And thanks to auto-configuration, because our new class implements
`DataTransformerInterface`, it's instantly part of that system. In other words,
our `supportsTransformation()` method should now be called!

## supportsTransformation()

To prove it is, lets `dd($data)` and `$to`.

Now, move over and refresh the endpoint. There it is! API Platform is passing us
the `CheeseListing` and for the `$to` argument, it's asking:

> Do you know how to convert this `CheeseListing` into `CheeseListingOutput`?

And we do! For `supportsTransformation()`, return
`$data instanceof CheeseListing` *and* `$to === CheeseListingOutput::class`.

That second part might seem unnecessary... since, in our app, a `CheeseListing` will
*always* have `CheeseListingOutput` as its output class. But technically, you can
configure a different `output` class on an operation-by-operation basis. So,
we're checking it to be safe.

As soon as one of the data transformers returns `true` from
`supportsTransformation()`, API Platform will call `transform()` so that we can
do our work. To make sure that's happening, `dd($object)` and `$to`.

When we move over and refresh... yes! It dumps the exact same thing.

## transform()

Back in `transform()`, *we* know that `$object` will be a `CheeseListing` object.
Let's rename `$object` to `$cheeseListing` and then, above this, add PHPDoc to tell
my editor that this will be a `CheeseListing` object.

Ok: our job in `transform()` is pretty simple: return a `CheeseListingOutput`
object. Let's do this as *simply* as we can: `$output = new CheeseListingOutput()`.
And then, the only field we have right now is `title`. Populate that with
`$output->title = $cheeseListing->getTitle()`. At the bottom, `return $output`.

Let's do this! Move back over, refresh and... um... it kind of works? We *are*
getting results... but each one only has `@id`?

I have two questions about this. First... what happened to `@type`? Each item
usually has *at least* `@id` and `@type`. So where is `@type` hiding? We'll talk
about why that's missing a bit later.

## Normalization Groups Still Apply to DTO's!

But before that, my second question is: why don't we see the `title` field? *That*
has a simpler answer: normalization groups.

On `CheeseListing`, we set a `normalizationContext` with `groups` set to
`cheese:read`. Thanks to the output DTO, what's *actually* being serialized *now*
is a `CheeseListingOutput` object. But, the normalization groups *still* apply.

In other words, in `CheeseListingOutput`, we need to add that group above any
properties that we want to serialize. Above `title`, say `@Groups()`, go copy the
group name, and paste it here: `cheese:read`.

Now when we try it... sweet! We have a `title` field!

Next: let's add the rest of the properties we need to `CheeseListingOutput` and
see how all of this looks in our documentation. Because... similar to
`DailyStats`, since this is *not* an entity, we're going to need to do a bit more
work to help API Platform understand the *types* of each property.
