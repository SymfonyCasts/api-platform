# Custom Resource GET Item

Our GET collection operation for `DailyStats` is working nicely. But you know
what? I'd love to *also* be able to fetch stats for a *single* day. The docs say
that this operation *already* exists... but we know it's a lie!

If you look at the top of the `DailyStats` class, we *kind of* added the `get`
item operation... but we made it return a 404 response:

[[[ code('927ae4b7e3') ]]]

We did that as a workaround so that API Platform could generate an IRI for
`DailyStats`. Now I want to make this *truly* work.

Remove all of the custom config so that we now have a normal get item operation
and a normal get collection operation:

[[[ code('cec6f107f9') ]]]

Before we do *anything* else, let's see what happens if we try it. Go to
`/api/daily-stats.jsonld`, copy the `@id` for one of the daily stats, and navigate
there. A 404! Oh, but let me add `.jsonld`. *There's* the 404 in the JSON-LD
format.

To get the collection operation working, we created a `DailyStatsProvider` that
was a *collection* data provider:

[[[ code('edba5e37dd') ]]]

To get an *item* operation working, we need an *item* data provider. Since
we don't have one yet, 404!

## Adding ItemDataProviderInterface

No problem for us: we've done this before! Add another interface called
`ItemDataProviderInterface`:

[[[ code('611cc77506') ]]]

Then, down here, go to "Code"->"Generate" - or `Command`+`N` on a Mac - select
"Implement Methods" and implement the `getItem()` function that we need:

[[[ code('c24ac6aaf6') ]]]

Our job here is to read this `$id` and return the `DailyStats` object for that
`$id`, or null if there is none.

## "Dynamic" Data from a JSON File

Before we do that, let's make all of this a bit more realistic. Our collection
operation returns some hardcoded `DailyStats`. Let's pretend that we have
a JSON file filled with this data... or maybe in a real project, you might have
an external API you could talk to in order to fetch the data.

If you downloaded the course code, then you should have a `tutorial/` directory
with a `fake_stats.json` file inside. *This* will be our data source. Right next
to this is a `StatsHelper` class, which holds code to read from that file.

Copy both of these. Then create a new directory in `src/` called `Service/` and
paste them both there:

[[[ code('1ea01a54b4') ]]]

Perfect. Let's take a quick look at the new `StatsHelper`:

[[[ code('21fff5e288') ]]]

There's nothing fancy: it has three public methods - `fetchMany()`, where you
can pass it a limit, offset and even some filtering criteria, which we'll talk
about later - `fetchOne()`, where you pass a date string and also a `count()`
method.

And... that's basically it. The rest of this file is boring code to read that
`fake_stats.json` file, parse through it and create `DailyStats` objects.

## Using the "Dynamic" Data

In `DailyStatsProvider` let's use this! We won't need the `CheeseListingRepository`
anymore: `StatsHelper` takes care of all of that. So autowire it instead:
`StatsHelper $statsHelper`, then `$this->statsHelper = $statsHelper` and rename
the property to `$statsHelper`:

[[[ code('720c1f2093') ]]]

We can also get rid of couple of `use` statements:

[[[ code('6f914a7935') ]]]

Down in `getCollection()`, it's now as simple as return
`$this->statsHelper->fetchMany()`. For now, pass it *no* arguments:

[[[ code('a88790b300') ]]]

Cool! Let's see if that works. Go back to the collection endpoint, refresh and...
yes! We get a big list of `DailyStats` data coming from that JSON file!

## Finishing getItem()

Let's use `StatsHelper` to finish `getItem()`. Thanks to the `supports()` method,
our `getItem()` method *should* be called *every* time a request is made to an "item"
operation for `DailyStats`:

[[[ code('8e0fd2bfa8') ]]]

Let's make sure that's working with `dd($id)`:

[[[ code('8bbe08a6e2') ]]]

Back at the browser, go forward, refresh and... nice! Our date string is dumped.

Now over in `getItem()`, we can return `$this->statsHelper->fetchOne()` and
pass it the date string, which... is the `$id` variable:

[[[ code('7d60c6c1b5') ]]]

Testing time! Over at the browser, refresh! 404!? I mean, of course! The date in
the URL is *not* one of the dates I have in my JSON file. Go back one page, refresh
the collection endpoint and copy a different one - like `2020-09-01`.

So if we go to `/api/daily-stats/2020-09-01.jsonld`, then... it works! And if we go
to a date that is *not* in that JSON file, we get a 404.

So setting up our item operation was actually pretty easy. Next, let's talk about
pagination. Because if we go back to `/api/daily-stats.jsonld` and refresh...
there are a *lot* of items here. Since we're not using Doctrine, we *no* longer
get pagination for free. If we need it, we have to add it ourselves.
