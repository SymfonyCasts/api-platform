# Pagination Context

We... sort of have pagination working? Except that the number of pages and items
per page are hard coded. Oh, and we're always returning *every* `DailyStats`, no
matter which page we're on. Ok, so it's not *really* working, but we're close.

There are two things that we need to know inside of `DailyStatsPaginator`: we need
to know what the *current* page is - which is normally in the URL - and the max
results per page that we should be showing. If we had those two pieces of info,
we could fill in *all* of the methods.

Since these are, sort of, "options" that control the behavior of our paginator,
let's force them to be passed in via the constructor. Add `int $currentPage` and
`int $maxResults` arguments to the constructor. I'll even add a comma to separate
them!

Next, hit Alt+Enter and go to "Initialize Properties" to create those two properties
and set them.

Sweet! Now in `getCurrentPage()`, return `$this->currentPage` and in
`getItemsPerPage()`, return `$this->maxResults`.

## Completing all the Paginator Methods

And... we even have enough info to complete the *other* methods. For `getLastPage()`,
I'll paste in a calculation. This returns the ceiling of the total items divided
by the the max items per page. And if that equals zero because there are *no*
results, return 1.

Next, in `getTotalItems()`, `return $this->statsHelper->count()`.

That method returns the *total* number of results, not just the results on *this*
page.

Finally in `getIterator()`, we need to figure out which results we should show
based on the current page. We'll do that by calculating a limit and offset. Say
`$offset =` and I'll paste in another calculation. The offset is the current page
minus one, times the items per page.

Now, for `fetchMany()`, this accepts limit and offset arguments. Pass it
the limit - `$this->getItemsPerPage()` - and then `$offset`.

Phew! Our paginator is now 100% ready. To test it, open `DailyStatsProvider`.
We now need to pass it the current page and the max items per page. To start,
let's hardcode these: pretend we're on page 1 and we want to show 3 items per page
so that pagination is really obvious.

Let's see what it look like! Refresh the page and... awesome! 3 results,
30 `totalItems` - which is correct - and we're currently on page 1, next is page
2 and it will take us 10 pages to get through all 30 results. Our paginator is
alive!

## The Pagination Object

All we need to do now is remove these hard-coded values. So how *do* we figure
out what the current page is... or the max items per page that we should be showing?
We *could* just choose any number we want and put it here for the max items. And
in a project, that would be *fine*.

But technically, the max items per page is something that is *configurable* via
the `ApiResource` annotation. And even the query parameter that's used for pagination
can be changed - it doesn't *need* to be `?page=`.

My point is: we don't need to hardcode the max per page or read the page query
parameter directly because API Platform *already* has this info! Where? It's
hiding in a service called `Pagination`: a service that we can autowire.

Add a second argument to `DailyStatsProvider`: `Pagination` - the one from
`DataProvider` and call it `$pagination`. I'll hit Alt+Enter and go to
Initialize Properties to create that property and set it.

Ok: `Pagination` is an object that knows everything about the *current*
pagination situation. So it has methods like `getPage()` and `getOffset()`,
which is calculated from the current page and max items per page. We're going to
use a - kind of strange - method called `getPagination()` which returns 3 pieces
of info as an array.

Check it out: use the odd function `list()` to create three variables - `$page`,
`$offset` and `$limit` - and set this to `$this->pagination->getPagination()`
passing `$resourceClass` and `$operationName`.

Notice that there *is* a third argument - `$context`. I'm *not* going to
pass that simply because I don't *have* `$context` in this method. But if you *did*
want to support the *full* features of the pagination system - there are a few
edge cases where pagination changes based on the context - then make your class
implement `ContextAwareCollectionDataProviderInterface`, which allows you to have
a `$context` argument on `getCollection()`.

Anyways, hold Command or Ctrl to jump into the `getPagination()` method. This
returns an array containing the current page, the offset and the limit. We're
using the strange `list()` function as a quick way to create three new variables:
`$page` set to the array's zero index, `$offset` set to the 1 index and `$limit`
to the 2 index.

Thanks to this, below, we can use `$page` and `$limit`. We don't actually need
`$offset` because we're calculating that ourselves.

Let's see if it works! Move over, refresh and this time... hmm. It looks like it's
not paginating at all. The problem is that API Platform allows 30 items per page
by default. And our JSON file has... yep - 30 items in it.

Let's limit this to showing 7 results per page. To do that, go over to
`DailyStats` and pass a new option: `paginationItemsPerPage` set to 7.

*This* is why we read the max per page - which is the `$limit` - from API Platform's
`Pagination` object instead of hardcoding it.

Now when we move over and try it... beautiful! Seven items and five pages total!
Say hello to our home-rolled pagination!

Next: we have our get item and get collection operations working - even with
pagination! Let's see if we can get the `put` operation to work so that we
can *update* `DailyStats` data.
