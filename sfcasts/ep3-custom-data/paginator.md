# Custom Paginator

When an API resource is a Doctrine entity like `CheeseListing`, life is good!
We get *tons* of things for free, like pagination, access to a bunch of pre-built
filters and one time I *swear* I got free pizza.

But... these filters will *not* work for custom resources... because API platform
has *no* idea how to work with your custom data source. Nope, if you want filters
or pagination on a custom API resource, you need to some work yourself.

But no worries: it's not too hard and we're kicking butt anyways. Let's start with
pagination.

## Pagination and your Data Provider

Right now, our collection endpoint lists *every* daily stats items... no matter
*how* many there are.

Here's how pagination works: inside our data provider, instead of returning
an array of `DailyStats` objects from `getCollection()`, we're going to return a
`Paginator` object that *contains* the `DailyStats` objects that should be returned
for whatever page we're on.

In the `src/DataProvider/` directory, create a new PHP class called
`DailyStatsPaginator`. The only rule is that this needs to implement a
`PaginatorInterface` from `ApiPlatform`. I'll go to Code -> Generate - or
Command + N on a Mac - and select "Implement Methods". Select all
*five* methods we need.

Perfect! Oh, but I'll move `count()` to the bottom... it just feels better for me
down there.

To get this working, let's return some dummy data! In `getLastPage()`, pretend
that there are two pages, for `getTotalItems()`, pretend there are 25 items, for
`getCurrentPage()`, pretend we're on page 1 and for `getItemsPerPage()` return that
we want to show 10 items per page. For `count()` return `$this->getTotalItems()`.

Oh, yea know what? When I recorded this, I made a careless mistake. The `count()`
method should actually return the number of items on *this* page - not the *total*
number of items. I'll mention that in a rew minutes when we *actually* have items
to count.

Ok: there are no `DailyStats` objects inside this class yet... but let's
do as *little* as possible to see if we can get things working.

Back in the provider, instead of returning the array of objects, return a new
`DailyStatsPaginator`.

Ooh. Let's try it. Head back over and refresh the collection endpoint. And...
error!

> class `DailyStatsPaginator` must implement the interface `Traversable` as part
> of either `Iterator` or `IteratorAggregate`.

Wow. Okay. So the `PaginatorInterface` gives API Platform a bunch of info
about pagination, like how many pages there are and what page we're currently on.
But ultimately, whatever we return from `getCollection()` needs to be something
that API platform can *loop* over - can iterate over.

In other words, we need to make our paginator *iterable*. One way to do that is
to add a second interface: `\IteratorAggregate`. And then, I'm going to create a
new private property called `$dailyStatsIterator`.

Finally, thanks to the new interface, at the bottom, go to Code -> Generate - or
Command + N on a Mac - and select "Implement Methods". This requires one new function
called `getIterator()`.

Inside, first check to see if `$this->dailyStatsIterator === null`. If it *is*
null, set it: `$this->dailyStatsIterator = ` and use another core class
`new \ArrayIterator()`. For now, pass this an empty array. I'll put a little
TODO up here:

> todo - actually go "load" the stats

At the bottom of the method, return `$this->dailyStatsIterator`.

Basically, we're creating an iterator with the `DailyStats` objects inside. Well,
it's an empty array now, but it *will* hold `DailyStats` soon. The property
and if statement are there in case `getIterator()` is called multiple times. If
it is, this prevents us from unnecessarily creating the iterator again.

*Anyways*, thanks to this, our paginator class *is* now iterable! And when we
refresh the collection endpoint... it works! I mean, there's nothing *inside*
the collection - `hydra:member` is empty - but you can see `totalItems` 25 and
links below to tell us how to get to the first, next and last pages.

## Iterating over DailyStats in the Paginator

To do the heavy lifting of loading the `DailyStats` objects, we can leverage the
`StatsHelper` object, which we have access to inside of `DailyStatsProvider`.

Inside `DailyStatsPaginator`, add a constructor: public function `__construct()`
with `StatsHelper $statsHelper`.

Now, this class is *not* a service... So Symfony is *not* going to autowire this
argument. This is really a "model" class that represents a paginated collection
of `DailyStats`. But in a minute, we'll pass `StatsHelper` directly when we *create*
this object.

Anyways, hit Alt + Enter and go to "Initialize properties" to create that property
and set it. Then, before we use it, go to `DailyStatsProvider` and pass
`$this->statsHelper` when we instantiate `DailyStatsPaginator`.

For now, we're going to *completely* ignore pagination and still show *all*
of the `DailyStats` no matter *which* page we're on. To do that, down in
`getIterator()`, instead of the empty array, pass `$this->statsHelper->fetchMany()`.

Oh, and by the way, you could *now* update the `count()` method to leverage
`getIterator()`: `return iterator_count($this->getIterator())`.

Anyways, let's check it out! Head over to your browser, refresh and... it works!
Well, `hydra:member` still contains *every* `DailyStats` record... but all the
pagination info *is* there.

So next, let's finish this! To do that, we'll need to ask API Platform:

> Hey! What page are we on?

And also:

> Yo! How many items per page should I show?

Because technically, that's configurable via annotations.
