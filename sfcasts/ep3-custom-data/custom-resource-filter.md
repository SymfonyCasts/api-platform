# Custom Filter for Custom Resources

Let's create one more custom filter: this time for `DailyStats`. Here's our mission:
to be able to add a `?from=` date on the URL. For example, if we go to
`/api/daily-stats.jsonld`, we see all the stats. But if we add `?from=2020-09-01`,
we want to *only* show stats on that day or later.

Let's do this! Start *basically* the same way as before: in the
`src/ApiPlatform/` directory, create a new PHP class called `DailyStatsDateFilter`:

[[[ code('f2ac23af28') ]]]

## Creating the Filter Class

For the search filter, we extended `AbstractFilter`:

[[[ code('e541ada1df') ]]]

but that class only works for *entity* resources. This time, implement `FilterInterface`. Now, be careful.
There are *multiple* `FilterInterface` in API platform. One is for the ORM, and
even inside the *main* part of API Platform, there are 2: one in `Serializer`
and one in `Api`. Get the one in `Serializer`:

[[[ code('a610fb3981') ]]]

This `FilterInterface` actually *extends* the other one from `Api\` but it has
*extra* integration that will make our life *much* easier.

Back in the new class, go to "Code"->"Generate" - or `Command` + `N` on a Mac - and
hit "Implement Methods" to add the two we need... which are *quite* similar to the
two methods we had before:

[[[ code('7958ee2dec') ]]]

The only difference is that the `apply()` method has different arguments
than `filterProperty()`... which makes sense because *that* method was *all*
about querying via Doctrine.

Before we start filling this in, go to `src/Entity/DailyStats.php` so we can *use*
the filter. So `@ApiFilter()`, hit tab to auto-complete that - and
`DailyStatsDateFilter::class`... but this time we need to manually add the `use`
statement: use `DailyStatsDateFilter`:

[[[ code('8346bcf2d6') ]]]

Now that we've *activated* the filter for this resource, let's jump straight into
`getDescription()`. This will be *exactly* like what we did in `CheeseSearchFilter`.
In fact, let's go steal our code! Copy the return statement from the other filter...
and paste it here. This time the query parameter will be called
`from`, keep `'property' => null`, type `string` is good, required `false` is
good, but tweak the `description`:

***TIP
In Api Platform 2.6 and higher, the `description` key doesn't live under `openapi`: it lives
on the same level as the other options.
***

> From date e.g. 2020-09-01

[[[ code('06a84cd9cb') ]]]

The `apply()` method is still empty, but this *should* be enough to see the filter
in the docs. Refresh that page, open the DailyStats collection operation and...
there it is!

## Adding the Filter Logic

But... how can we make this filter actually *work*? Well, forget about the filter
system for a minute. No matter *what* fanciness we do, it's 100% up to our data
provider to return the collection of data that will be shown.

What I mean is, we're *not* going to return this `DailyStatsPaginator` object and
then expect some *other* system to somehow magically filter those results:

[[[ code('aef2446387') ]]]

Nope, if we want `DailyStats` to have some filters, we need to handle that logic
*inside* `getCollection()` so that the items inside the paginator represent
the *filtered* items.

So before we really think about reading query parameters or integrating with
our filter class, let's *first* focus on making our `DailyStatsProvider` *able*
to filter the results by date.

And actually, most of the work of figuring out which results to return is done
by the paginator. So let's jump into `DailyStatsPaginator`.

Down here, `getIterator()` is where we use `StatsHelper` to fetch all of the
objects:

[[[ code('aa03fc36f8') ]]]

And, it *already* has a *type* of filtering: it returns a *subset* of the items
based on what *page* we're on.

And fortunately, if we jump into `src/Service/StatsHelper.php`, the `fetchMany()`
method already has a *third* argument called `$criteria`, which supports `from`
and `to` keys, which are both `DateTime` objects:

[[[ code('43236396d5') ]]]

Basically, we can *already* pass a `from` key on the 3rd argument to filter
*exactly* like we want. The boring work was done for us. Yay!

Ok, let's think: in order for `DailyStatsPaginator` to be able to return the
correct results, it needs to know what the `from` date is. To store that info,
add a property: a new private `$fromDate`. Above this, I'll document
that this will be a `\DateTimeInterface` or `null` if it's not set:

[[[ code('92b12ce377') ]]]

Then, at the bottom, go to "Code"->"Generate" - or `Command`+`N` on a Mac - select
setters, and generate the `setFromDate()` method. Oh, but I'll remove the nullable
type:

[[[ code('5f729a3366') ]]]

If you call this method... I'll assume that you *do* have a `DateTime` that
you want to use. We'll see *who* sets this in a minute.

But assuming that this *is* set, we can use it inside of `getIterator()`. Add
`$criteria` equals an empty array. Then, if we have `$this->fromDate`, say
`$criteria['from'] = $this->fromDate`:

[[[ code('1cf25e147a') ]]]

Finally, pass `$criteria` as that third argument to `fetchMany()`:

[[[ code('18aae86403') ]]]

This class is *ready*! Let's make sure it's working by going to
`DailyStatsProvider` and hard-coding a date temporarily. Let's see, remove the
return statement, add `$paginator =` and, at the bottom return `$paginator`. In
between add: `$paginator->setFromDate()`, with a new `\DateTime()` and `2020-08-30`:

[[[ code('711036f84d') ]]]

Let's see if it works! Back at the browser, I'll go over to my *other* tab. I
*do* have a `?from=`, but that query parameter is *not* being used yet: the
filtering should use the hardcoded date.

When we refresh... let's see. Yes! It returned only 5 results starting with the
hard-coded `08-30`! Awesome!

Oh, but I *do* see one, teenie-tiny bug: it says `hydra:totalItems` 30. That
should only say 5.

This number comes from our paginator object: the `getTotalItems()` method, which
calls `$this->statsHelper->count()`:

[[[ code('80d7e8f02f') ]]]

*Really*, the `count()` method should *also* allow an array of `$criteria`
to be passed so that it can do the *same* filtering that happens in `fetchMany()`.
But... I'll leave that for you as extra credit.

Next: this is good! We have the filtering logic working. But how can we fetch
the *real* data from the query parameter? Well... I sort of just answered my own
question! We could grab the `Request` object and read the query parameter directly.
But there's actually a cooler, more built-in way that involves our
`DailyStatsDateFilter` class.
