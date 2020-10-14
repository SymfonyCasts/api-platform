# Custom Filter apply()

Let's talk about how filter classes work internally. As we know, each data
provider is 100% responsible for taking filters into account and changing the data
it returns *based* on them. So, filtering happens inside *each* data provider, not
via some magic system that runs *after* them.

## How Filters work in Doctrine

Let's look at how this is done in the core Doctrine data provider. Hit Shift+Shift,
search for `doctrinedataprovider` and include non project items. There it is:
`CollectionDataProvider` from `Orm\`. Here is the `getCollection()` method.

The Doctrine data provider has a system called "collection extensions": these are
hook points that allow you to *modify* the query in any way you want. And... we
actually *created* one of these extensions in the last tutorial:
`CheeseListingIsPublishedExtension`. This modifies the query so that we don't
return unpublished listings, unless you're the owner of the listing or an admin.

## Doctrine's FilterExtension

Why are we talking about these extension classes? Because one of the *core*
Doctrine extensions is called `FilterExtension`.

Let's open it up: Shirt+Shift and look for `FilterExtension.php` making sure to
include all non-project items. Get the one from `Orm\`. I *love* this. It loops
over *all* of the filters that have been activated for this resource class, calls
`apply()` on each one and passes it the `QueryBuilder`!

Thanks to this, in `CheeseSearchFilter`, all *we* needed to do was extend
`AbstractFilter` and fill in the `filterProperty()` method. The `apply()` method
lives in `AbstractFilter`, which does some work and then ultimately calls
`filterProperty()`.

The point is: the Doctrine filter system works via a Doctrine *extension*, which
knows to call a method on each filter object.

## How / When the apply() Method is Called

But all of this stuff will *not* happen in our situation... because we are *not*
using the Doctrine data provider. However, because we made our filter implement the
`FilterInterface` from the core `Serializer\` namespace, API Platform *will* help
us a bit. How? By automatically calling our `apply()` method on *every*
request for an API resource where our filter has been activated.

What I mean is, in `DailyStats` we added `@ApiFilter(DailyStatsDateFilter::class)`.
Thanks to this, whenever we make a request to a `DailyStats` operation, API
Platform will automatically call the `DailyStatsDateFilter::apply()` method.
This works via a *context builder* in the core of API Platform that loops over all
of the filters for the current resource class, checks to see if they implement the
`FilterInterface` that we're using and, if they do, calls `apply()`.

So... this is huge! It means that API Platform is smart enough to automatically
call our filter's `apply()` method but *only* when needed. This means that we can
get down to work.

## Grabbing the Query Parameter

Our first job is to read the query parameter from the URL. And... hey! We get
the `Request` object as an argument! Schweet! Let's `dd($request->query->all())`.

Back at your browser, refresh and... there it is: the `from` query param.

Grab that with `$from = $request->query->get('from')`. And, if not `$from`,
it means we should do *no* filtering. Return without doing anything. After,
`dd($from)`.

Refresh now and... yay! We have a date string.

## Passing Info from the Filter to the Data Provider

So... what do we *do* with that string? I mean, we're not inside
`DailyStatsProvider` where we actually *need* this info: we're way over here in
the filter class.

The answer is that we're going top pass this info *from* the filter *to* the data
provider via the `$context`. Check it out: one of the arguments to `apply()` is
the `$context` array and it's passed by *reference*. That means we can modify it.

Head to the top of this class and add a new public constant, how about:
`FROM_FILTER_CONTEXT` set to `daily_stats_from`. This will be the key we
set on `$context`.

Before we do that, let's convert the string into a `DateTime` object:
`$fromDate = \DateTimeImmutable::createFromFormat()` passing
`Y-m-d` as the format and then `$from`.

We're using `createFromFormat()` because if the `$from` string is in an *invalid*
format, it will return `false`. We can use that to code defensively: if `$fromDate`,
then we know we have a valid date. Also add
`$fromDate = $fromDate->setTime()` and pass zero, zero, zero to normalize all
the dates to midnight. Finally, set this on the context:
`$context[self::FROM_FILTER_CONTEXT] = $fromDate`.

So the job of the `apply()` method in a custom, non-Doctrine filter is not
*actually* to *apply* the filtering logic: it's to *pass* some filtering
info into the context.

## Reading the Context in the Data Provider

And now, we're dangerous. Well... we're *almost* dangerous. If we can get access
to the `$context` from inside `DailyStatsProvider`, then we can read that key off
and set the from date. Unfortunately, we do *not* have the context yet. But
fortunately, we know how to get it!

Instead of `CollectionDataProviderInterface`, implement
`ContextAwareCollectionDataProviderInterface`. The only difference is that
`getCollection()` now has an extra `array $context = []` argument.

To start, let's `dd($context)` and see if the filter info is there.

Ok, refresh. And... we got it! The `daily_stats_from` *is* there! And if we take
the `from` query param off, it still works, but the key is gone.

Let's *finally* use this. Remove the `dd()` and, down here,
add `$fromDate = $context[DailyStatsDateFilter::FROM_FILTER_CONTEXT]` with a
`?? null` so that is defaults to null if the key doesn't exist. Then, if we have
a `$fromDate`, call `$paginator->setFromDate()` and pass it there.

Testing time! The query parameter should filter from 09-01. Refresh and... it
does! We only get *three* results starting from that date! If we take off the
query param... we get everything.

We just built a *completely* custom filter. Great work team! Next, in
`DailyStatsDateFilter`, if the `from` data is in an invalid format, we decided
to ignore it. But we could *also* decide that we want to return a 400 error instead.

Let's see how to do that *and* how we could even make that behavior configurable.
This will lead us down a path towards *true* filter enlightenment and uncovering
a hidden secret. Basically, we're going to learn even *more* about the power
behind filters.
