# Why/When a Many Relation is IRI Strings vs Embedded

I want to show you something a bit *surprising* about *how* our custom API resource
is being serialized. To make things more realistic, add a `$stats2`, and let's
actually use 1000 visitors for day one - our site is at least *kind* of popular -
2000 for `$stats2` and set this to minus 1 days from now. Put that new `$stats2`
into the return statement:

[[[ code('87ddc77891') ]]]

Both of these `DailyStats` are missing one thing: we're passing
an empty array as the fourth argument, which is *supposed* to be a collection
of the most popular `CheeseListing` objects. I don't really care about the
"most popular" part, so let's just pass 5 random listings.

Create a public function `__construct()` and autowire `CheeseListingRepository`.
I'll hit `Alt`+`Enter` and go to "Initialize properties" to create that property
and set it:

[[[ code('f43ba1910b') ]]]

Before the stats, fetch some listings with
`$listings = $this->cheeseListingRepository->findBy()` and pass an empty
array of criteria, an empty array of "order by" and 5 to get a max of 5 listings:

[[[ code('caf2d8184b') ]]]

Use this to replace the empty array: `$listings` and `$listings`:

[[[ code('fc0d39c648') ]]]

## Embedded Serialization Versus Inline IRI

Easy enough! Let's see what it looks like! Back at the browser, refresh and...
no surprise! It works! Wait... it looks... weird. Each listing has `@id` and
`@type`... but no other fields. And... hmm. That actually makes sense.

In `DailyStats`, to serialize - or "normalize" - we're using a group called
`daily-stats:read`:

[[[ code('4df8d596f0') ]]]

Whenever API Platform sees an embedded object:

[[[ code('e5fae805fe') ]]]

It looks at that object - so `CheeseListing` - and looks to see if any of
*its* fields are in that same group. And there are *no* properties inside
`CheeseListing` that have the `daily-stats:read` group:

[[[ code('3d49aeeeff') ]]]

So... it makes sense that when we embed `CheeseListing` into `DailyStats`, none
of its fields are included.

But, hold on a minute. Usually when API Platform sees an embedded object... and
sees that *no* fields will be exposed on that object, instead of embedding it, it
uses its IRI.

So the question is: why is it *not* doing that? Why do we have an array of embedded
objects instead of an array of IRI strings?

## API Platform Doesn't know Your Collection Type

Before we answer that, let's *first* see how to *solve* it. Look closely at
the `$mostPopularListings` property:

[[[ code('2648441342') ]]]

From API Platform's perspective, it knows that this will hold an array,
but it does *not* know that it will hold an array of `CheeseListing` objects:

[[[ code('0b3afd761d') ]]]

And that *changes* how it behaves.

Ok, in truth, this `CheeseListing[]` doc *should* probably be enough to tell
API Platform that this is an array of `CheeseListing` objects:

[[[ code('095eeaa4b6') ]]]

But for some reason it doesn't read that syntax.

It's not important, but let's move the documentation to the property directly.
Say `@var array` then greater than, less than with `CheeseListing` inside:

[[[ code('e83aaed83e') ]]]

This syntax is... maybe a bit lesser-known than the `CheeseListing[]` syntax.
It comes from PSR-5 - a draft standard for PHP documentation.

As soon as we make this change... when we refresh... check it out! Suddenly
it *is* the array of IRI strings that we expected! If we added the `daily-stats:read`
group to any of the properties in `CheeseListing`, it *would* become an embedded
object, but since we haven't, we get the expected IRI strings.

Now... the *only* problem with the `array<CheeseListing>` syntax is that... PhpStorm
doesn't understand it. If we say `$this->mostPopularListings[0]->`... we get
zero auto-completion.

The workaround is to use both formats: this is an array of `CheeseListing` *or*
`CheeseListing[]`:

[[[ code('99ae61c775') ]]]

And *now* PhpStorm is happy. This isn't ideal, but hopefully we won't need both
formats in the future.

But... hold on. We just added documentation that *changed* the way API Platform
serialized our object. Why was that needed? Can't API Platform figure out that
this is an array of `CheeseListing` objects when we... pass it an array of
`CheeseListing` objects? Why was the extra documentation even needed?

Let's dive deeper into that question next.
