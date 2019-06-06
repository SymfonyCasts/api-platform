# Filtering & Searching

We're doing awesome! We understand how to expose a class as an API resource, we
can choose which operations we want, and have full control over the input and
output fields, including some "fake" fields like `textDescription`. There's a lot
more to know, but we're doing great!

So what else does *every* API need? I can think of a few things, like pagination
and validation. We'll talk about both of those soon. But what about filtering?
Your API client - which might just be *your* JavaScript code, isn't going to
*always* want to fetch *every* single `CheeseListing` in the system. What if you
need the ability to only see published listings? Or what if you have a search
on the front-end and need to find by title? These are called "filters": ways to
see a "subset" of a collection based on some criteria. And API Platform comes
with a *bunch* of them built-in!

## Filtering by Published/Unpublished

Let's start by making it possible to *only* return *published* cheese listings.
Well, in a future tutorial, we're going to make it possible to *automatically*
hide unpublished listings from the collection. But, for now, our cheese
listing collection returns *everything*. So let's *at least* make it possible
for an API client to *ask* for only the published ones.

At the top of `CheeseListing`, activate our *first* filter with `@ApiFilter()`.
Then, choose the *specific* filter by its class name: `BooleanFilter::class`...
because we're filtering on a *boolean* property. Finish by passing the
`properties={}` option set to `"isPublished"`.

[[[ code('92b37a881a') ]]]

Cool! Let's see what this did! Refresh! Oh... what it *did* was break our app!

> The filter class `BooleanFilter` does not implement `FilterInterface`.

It's not *super* clear, but that error means that we forgot a `use` statement.
This `BooleanFilter::class` is referencing a specific *class* and we need a `use`
statement for it. It's... kind of a strange way to use class names, which is
why PhpStorm didn't autocomplete it for us.

No problem, at the top of your class, add `use BooleanFilter`. But... careful...
most filters support Doctrine ORM *and* Doctrine with MongoDB. Make sure to choose
the class for the ORM.

[[[ code('e5579433be') ]]]

Ok, *now* move over and refresh again.

We're back to life! Click "Try it out". Hey! We have a little `isPublished` filter
input box! If we leave that blank and execute... looks like 4 results.

Choose `true` for `isPublished` and try it again. We're down to two results! And
check out how this works with the URL: it's still `/api/cheeses`, but with a
*gorgeous* `?isPublished=true` or `?isPublished=false`. So *just* like that, our
API users can filter a collection on a boolean field.

Oh! Also, down in the response, there's a new `hydra:search` property. OoooOOO.
It's a bit techy, but this is *explaining* that you can now search using
an `isPublished` query parameter. It also gives information about which
property this relates to on the resource.

## Text Searching: SearchFilter

How else can we filter? What about searching by text? On top of the class, add
another filter: `@ApiFilter()`. This one is called `SearchFilter::class` and
has the same `properties` option... but with a bit more config. Say `title` set
to `partial`. There are also settings to match on an `exact` string, the `start`
of a string, `end` or a string or on `word_start`.

Anyways, *this* time, I remember that we need to add the `use` statement manually.
Say `use SearchFilter` and auto-complete the one for the ORM.

[[[ code('1986d6da79') ]]]

Oh, and before we check this out, I'll click to open `SearchFilter`. This lives
in a directory called `Filter` and... if I double click it... hey! We can see
a *bunch* of other ones: `ExistsFilter`, `DateFilter`, `RangeFilter`,
`OrderFilter` and more. These are all documented - but you can also jump
right in and see how they work.

*Anyways*, go refresh the docs, open the `GET` collection operation and click to
try it. *Now* we have a `title` filter box. Try... um... `cheese` and... Execute.

Oh, magnificent! It adds `?title=cheese` to the URL... and matched three of our
four listings. The `hydra:search` property now contains a second entry advertising
this new way to filter.

If we want to be able to search by another property, we can add that too:
`description` set to `partial`.

This is easy to set up, but this type of database search is still pretty basic.
*Fortunately*, while we won't cover it in this tutorial, if you need a truly
robust search, API Platform can integrate with Elasticsearch: exposing your
Elasticsearch data as readable API resources. That's pretty freaking cool!

Let's check out *two* more filters: a "range" filter, which will be *super* useful
for our price property and another one that's... a bit special. Instead of
filtering the number of results, it allows an API client to choose a subset
of *properties* to return in the result. That's next.
