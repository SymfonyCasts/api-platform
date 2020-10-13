# Apply

Coming soon...

[inaudible]

Let's talk about how these filter classes work. Internally. As I mentioned, each data
provider is a 100 responsible for taking filters into account in changing the data.
It returns. There's not some system after we return our page native, that's going to
then modify it. We need to modify our paginator here to take into account page
nation and filtering. Now doctrines data provider in core, for example, has an
extension system that allows anyone to modify the query that that's been made. I, I
should see that by saying `doctrinedataprovider` looking for all non project items.

And there it is on here `CollectionDataProvider` from `Orm\`. And you can see we've
talked about this before. Here's it's get collection method, and then it actually has
these things called collection extensions that it calls that allow you to modify the
query. We actually have. One of these extensions is our `CheeseListingIsPublishedExtension`
which we use right here to actually make sure that we don't return
published listings, unless you're the owner or an admin. Now, why am I telling you
this? Because one of the core, one of the doctrine extensions is called the filter
extension.

You can actually open this up. I'll look for `FilterExtension.php`. Make sure you
include all non-project items and get the one from `Orm\`. Here it is right here. And
what the filter extension does is it actually loops over all the filters that are
applied in our object in it calls `apply()` on each one and passes it. The query builder.
This is why with our `CheeseSearchFilter`, all we needed to do was basically extend
`AbstractFilter` and add this `filterProperty()` method. If you looked at an 
`AbstractFilter`, it actually has that `apply()` method, which loops around a couple of things and
calls our `filterProperty()`. So this is how a normal doctrine filter works. It's via
the extension system. And then this filter extension system, which knows to call all
of the filters. No, obviously that's not going to happen for us because we're not
using the doctrine data provider, but because we made our filter implement the 
`FilterInterface`, the one from core serializer API platform will help us out a bit. How by
automatically calling our `apply()` method

On every request for a resource for an API platform resource class that has this
filter on it. What I mean is on our `DailyStats`, we've added at API filter daily
staff state filter. Thanks to that. Whenever we make request for a daily stats, it's
going to call our `DailyStatsDateFilter::apply()` method automatically. This is huge.
It means API platform is smart enough to activate our filter only when needed. So now
we can just get to work in here. So we know that we want to read off the, from query
parameter off of a URL. Unfortunately, if it's passing us the request object, so
let's `dd($request->query->all())` so we can make sure that we can see that query
parameter on there. Now I'm gonna go over and refresh. There it is, are `from` query
parameter. Is there.

All right. So let's grab that. We'll say `$from = $request->query->get('from')`
And of course, if no `$from` that's totally valid, it means there isn't a
filter and we're just going to return without doing anything done here. Let's 
`dd($from)`

And refresh. Perfect. We have our nice little date string. Okay. So what do we do
with that string? I mean, we're not exactly inside our `DailyStatsProvider` where we
actually need this. We're over here in this filter class. So the answer is that we're
going to add this information to the `$context`, check it out. The apply method is past
the `$context` and it's passed it by reference, which means we can actually add a keys
to the context to do this. It's actually at a public constant up here. We'll call it
`FROM_FILTER_CONTEXT` We'll set that to, how about `daily_stats_from` this? May I a
constant here? Cause this is the key that we're going to set our from date into on
the context. But before I do that, let's actually convert this string into a `DateTime`
object,

So we do that with, `$fromDate = \DateTimeImmutable::createFromFormat()`
And then I'm going to pass it, `Y-m-d` and then pass. It are `$from` using, 
`createFromFormat()` because if this
is passed an invalid format, then a create from format will actually return false,
which is nice. Cause we can code defensively and say if `$fromDate`, that means we
actually have a validate format. Then we can set it on the context, uh, before we do,
I'm actually going to just do a little normalization here. I'll say 
`$fromDate = $fromDate->setTime()` and pass this zero, zero, zero. So I wanna make sure it's set at
midnight so that when we compare the dates where I was comparing midnight to
midnight, the real key here, areas that we say `$context[self::FROM_FILTER_CONTEXT] = $fromDate`

So the job of the supply method is not really to actually apply that filter, but just
to advertise that advertise that it exists, that it should be applied in the context.
So now we're dangerous. Well, we're almost dangerous. If we can get access to the
`$context` from inside of our `DailyStatsProvider`, then we can read that key off of it
and set the from date. Unfortunately, we do not have the context right now, but
fortunately we know that we can get it. How by implementing instead of 
`CollectionDataProvider`, implementing `ContextAwareCollectionDataProvider`, the only
difference being that down here, we're not going to have an `array $context = []`
argument. You can see now Peachtree storm is happy. All right. So let's just
`dd($context)` here and see if we can see the filter passing this, uh, to the provider in
the context. So if over now and refresh and yes, we got it. Whew. `daily_stats_from` it
is on there perfectly. And if we take off the, from then everything still works. We
just don't have that inside of the context.

So now we can finally use this, remove the `dd()`, and also remove the down here. We'll
say `$fromDate = $context`. And here's where we can use that con that constant 
`DailyStatsDateFilter::FROM_FILTER_CONTEXT`. And then we'll do
`?? null` So if it is set, use that value, otherwise set it to null. And
then if we have a `$fromDate`, then we can call the `$paginator->setFromDate()` and
pass it are `$fromDate`. Alright, let's get that a tribe. Our queer parameters
filtering from Oh nine Oh one. When we refresh. Yes, it works. We only get those
three results. The results starting on Oh nine Oh one. And if we take off the query
parameter, awesome. We get everything. That's perfect, but I want to go just a teeny
tiny bit further next inside of our daily stats filter. We decided that if the, if
the date is an invalid format, that we're just going to ignore it, but we could also
throw a 400 air that set tells the user that there's an invalid format. Heck we could
even make that behavior configurable whether or not we should throw the error on our
filter. Annotation doing that is gonna show us a mysterious truth about our filter
class. It's a service.

