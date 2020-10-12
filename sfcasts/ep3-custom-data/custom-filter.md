# Custom Filter, getDescription() & properties

On `CheeseListing`, thanks to the `SearchFilter` and also `BooleanFilter` and
`RangeFilter`, when we use the collection end point, there are a number
of different ways that we can filter, like searching the `title`, `description`
and `owner` fields. That's pretty cool!

Except, we can only search by *one* field at a time. Like, we can search for
something in the `title` *or* something in the `description` but we can't, for
example, say `?search=` and have that automatically search across multiple fields.

But hey! That's no huge issue: when the built-in filters aren't enough, just make
your *own*. Creating custom filters is both fun and... weird! Let's go!

## Creating the Filter Class

Over in `src/`, how about in `ApiPlatform`, create a new PHP class called
`CheeseSearchFilter`. As usual, this will need to implement an interface or
extend some base class. In this case, we need to extend `AbstractFilter`. Make
sure you get the one from Doctrine `ORM` - it's actually impossible to see which
one we have here. So I'll randomly guess and... got it! If you chose the wrong
one, you'll have to delete your project and start over. Or... just delete this
*one* line and manually say `use AbstractFilter` to get the right one.

It turns, out creating a filter is *different* based on the underlying data
source. So if your underlying data source is Doctrine ORM, creating a filter
will look one way. But if your underlying data source is, for example,
`ElasticSearch` - which is something API Platform has built-in support for - then
a custom filter will look different. And if you have a *completely* custom API
resource like `DailyStats`, creating a custom filter looks even *another* way.

We'll talk about how to create a custom filter for `DailyStats` in a few minutes.

When we extend `AbstractFilter`, as you can see, we need to implement a couple of
methods. Go to Code -> Generate - or Command + N on a Mac - and select "Implement
Methods" to generate the two methods we need.

## Checking out Core Filters: PropertyFilter

Filters are ultimately two pieces: they're the logic that *does* the filtering,
which is `filterProperty()`, and then the logic that *describes* how the filter
works, which is used in the documentation. That's the job of `getDescription()`.

Let's start there. To help fill in this method, let's cheat and look at some core
classes. Hit Shift+Shift and look for `PropertyFilter.php` and make sure to
include non-project items. Open that up.

As a reminder, `PropertyFilter` is an odd filter that is used to return less
*fields* on each result. That's different than *most* filters whose job is to
return less *items* in the collection.

Over in `PropertyFilter`, search for `getDescription()`.

Excellent! So what `getDescription()` is supposed to return is what query parameters
can be used to activate this filter and, below each one, a bunch of array keys
that are ultimately used to generate the documentation.

Nobody likes random arrays like this, and honestly, the best way to see what
keys we should fill in is by checking out these core classes.

## Checking out Core Filters: SearchFilter

Close this filter and let's open one more: hit Shift+Shift and look for
`SearchFilter.php` and make sure to include non-project items,

In this case, `getDescription()` actually lives in this `SearchFilterTrait`.
Hold Command or Control and click to open up that.

This returns the *exact* same structure, except that it's a bit more complex.
It says `$properties = $this->getProperties();` and loops over those to create
the same array structure we saw earlier for each property.

In `CheeseListing`, when you use `SearchFilter`, you can pass it a `properties`
option that says:

> I want this `SearchFilter` to work on all 4 of these properties

What that effectively does is create *four* different possible query parameters
that you can use. And so the `getDescription()` method returns an array with
4 items in it.

Close up those core classes.

## The $this->properties Property

Now because we're extending `AbstractFilter`, one of the properties we magically
have access to is called `$this->properties`. Let's `dd()` that here:
`dd($this->properties)` so we can see what that is.

Back at the browser, let's open a new tab and go to `/api/cheeses.json.ld`. But
we won't hit the dump yet. Why? Because we haven't told API Platform that we want
our `CheeseListing` to *use* this filter.

## Using the Filter on the Resource

But we know how to do that. Back in `CheeseListing`, we've done this before
with other filters. Anywhere in here, add `@ApiFilter()` with
`CheeseSearchFilter::class`. Unfortunately, PhpStorm doesn't auto-complete that
for me, so I'll copy the class name and add it on top manually:
`use App\ApiPlatform\CheeseSearchFilter`.

As *soon* as we do this, when we refresh the endpoint, *even* though I don't have
any query parameters in the URL... it *does* hit `getDescription()`! Why? Because
in JSON-LD Hydra, the filters documentation is part of the response.

Apparently, `$this->properties` is null. Here is where and when `$this->properties`
comes into play. Often - and as we've seen - you can pass *which* properties you
want a filter to operate on.

For example, if we wanted *our* filter to be configurable, we could say
`properties =` and pass it `price` or an array of fields.

As *soon* as we have this, when we refresh, we get `price` in the array! So if
you want the *properties* on your filter to be configurable, *that* is the purpose
of `$this->properties`.

Now, in *our* case, we're creating this filter *entirely* for a single class for
*our* application. And so, we *don't* need to worry about making it configurable.
Remove the `properties` option from the annotation... and I'm going to completely
ignore the `$this->properties` and do whatever I want.

Next, we *now* know enough to fill in `getDescription()` for our filter. Once
we've done that, let's bring it to life!
