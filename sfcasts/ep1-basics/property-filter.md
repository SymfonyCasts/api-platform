# PropertyFilter: Sparse Fieldsets

In just a few minutes, we've given our API clients the ability to filter by
published cheese listings and search by title and description. They may *also*
need the ability to filter by *price*. That sounds like a job for... `RangeFilter`!
Add another `@ApiFilter()` with `RangeFilter::class`. Let's immediately go up
and add the `use` statement for that - the one for the ORM. Then,
`properties={"price"}`.

[[[ code('4aaaac201a') ]]]

This filter is a bit nuts. Flip over, refresh the docs, and look at the GET
collection operation. Woh! We now have a *bunch* of filter boxes, for price between,
greater than, less than, greater than or equal, etc. Let's look for everything
greater than 20 and... Execute. This adds `?price[gt]=20` to the URL. Oh, except,
that's a search for everything greater than 20 cents! Try 1000 instead.

This returns just one item and, once again, it advertises the new filters down
inside `hydra:search`.

Filters are super fun. Tons of filters come built-in, but you can *totally* add
your own. From a high-level, a filter is basically a way for you to modify the
Doctrine query that's made when fetching a collection.

## Adding a Short Description

There's *one* more filter I want to talk about... and it's a bit special: instead
of returning less results, it's all about returning less *fields*. Let's pretend
that most descriptions are *super* long and contain HTML. On the front-end, we want
to be able to fetch a collection of cheese listings, but we're *only* going to display
a very *short* version of the description. To make that super easy, let's add a
new field that returns this. Search for `getDescription()` and add a new method
below called  `public function getShortDescription()`. This will return a nullable
string, in case description isn't set yet. Let's immediately add this to a
group - `cheese_listing:read` so that it shows up in the API.

[[[ code('aa0f8ed1b0') ]]]

Inside, if the `description` is already less than 40 characters, just return it.
Otherwise, return a `substr` of the description - get the first 40 characters, then
a little `...` at the end. Oh, and, in a real project, to make this better - you
should probably use `strip_tags()` on description before doing any of this so
that we don't split any HTML tags.

[[[ code('ed7c07d610') ]]]

Refresh the docs... then open the GET item operation. Let's look for cheese listing
id 1. And... there it is! The description was just *barely* longer than 40 characters.
I'll copy the URL, put it into a new tab, and add `.jsonld` on the end to see
this better.

At this point, adding the new field was *nothing* special. But... if some parts
of my frontend *only* need the `shortDescription`... it's a bit wasteful for the
API to *also* send the `description` field... especially if that field is *really*,
*really* big! Is it possible for an API client to tell our API to *not* return
certain fields?

## Hello PropertyFilter

At the top of our class, add another filter with `PropertyFilter::class`. Move
up, type `use PropertyFilter` and hit tab to auto-complete. This time, there's
only one of these classes.

[[[ code('c2673c06b2') ]]]

This filter *does* have some options, but it works perfectly well without doing
anything else.

Go refresh our docs. Hmm, this doesn't make any difference here... this isn't a
feature of our API that can be expressed in the OpenAPI spec doc.

But, this resource in our API *does* have a new super-power. In the other tab,
choose the exact properties you want with
`?properties[]=title&properties[]=shortDescription`. Hit it! Beautiful! We still
get the standard JSON-LD fields, but then we *only* get back those two fields.
This idea is sometimes called a "sparse fieldset", and it's a great way to allow
your API client to ask for *exactly* what they want, while still organizing
everything around concrete API resources.

Oh, and the user can't try to select *new* fields that aren't a part of our original
data - you can't try to get `isPublished` - it just doesn't work, though you *can*
enable this.

Next: let's talk about pagination. Yea, APIs *totally* need pagination! If we have
10,000 cheese listings in the database, we *can't* return *all* of them at once.
