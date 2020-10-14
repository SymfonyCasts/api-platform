# Filter Autowiring

We just learned that if you pass an `arguments` option to your annotation, then
the keys inside get mapped as arguments to the constructor of your filter class.
But there's more going on than it seems. Obviously, someone instantiates our
filter object... and it's a pretty good guess that API Platform does it directly
and uses the `arguments` option to figure out what arguments it should pass to
the constructor.

## Out Filter is a Service!

But actually, our filter is a service in the container! *We* didn't register it
directly, *API Platform* did thanks to our annotation. Each time it sees an
`ApiFilter` annotation, it registers a unique service for that filter class.

Then, *after* registering it as a service, it takes the `arguments` option and
sets those as named `arguments` on that service. Why do we care about *how* our
filter objects are instantiated? Because when API Platform registers the service,
it *also* sets it to `autowire: true`. Yep, this means we can access *services*
in our filter class *just* like we normally would!

Check it out: add a `LoggerInterface $logger` argument. I'm adding it as the
first argument just to, kind of, *prove* that the order doesn't matter. Create
the `$logger` property and then add `$this->logger = $logger`.

Now, down in `apply()`, we can say
`$this->logger->info(sprintf())`, `Filtering from date "%s"` and pass `$from`.

Let's see if it works! Move over and go back to use a *real* from date. Refresh
and... ok! No error! Now open a new tab and go to `/_profiler`. Find the
200 status, click the token link and go down to Logs. Got it!

Oh, but it logged twice? That's actually ok. The system that calls the `apply()`
method on our filter is the "serialization context builder" system, a system
we hooked into before. In the `src/Serializer/` directory, in the last tutorial,
we created an `AdminGroupsContextBuilder`.

*Anyways*, the context builder system is called two times: once at the beginning
when it's *reading* the data and again later when the data is serialized. Hence,
we see two logs.

## Adding arguments in an Entity Filter

Anyways, *all* of this stuff about the `arguments` option and autowiring applies
equally to an *entity* filter like our `CheeseSearchFilter`.

For example, inside of this class, we use `LIKE` in our query instead of equals.
Let's pretend that we want to make this configurable: you can decide if you want
a fuzzy or exact search.

Open up `src/Entity/CheeseListing.php` and find that filter. Let's add
`arguments={}` and invent a new one called `useLike` set to `true`.

Then, over in a browser, close the profiler and head to `/api/cheeses.jsonld`.
Yep, we immediately get the error we expect:

> CheeseSearchFilter does not have argument `$useLike`

Let's go add it! The only weird part in an *entity* filter is that our parent class
*already* has a constructor. This means we can't just add a new constructor,
we need to override the existing one.

To do that, go to Code -> Generate - or Command + N on a Mac - select "Override
Methods" and choose `__construct()`.

Oh, wow: that is a *big* constructor... which is fine. But we *can* slim it down
a bit. We *do* need `ManagerRegistry`, we *do* need `RequestStack`, but we don't
need to pass the `$logger` to the parent class: it's not even used there. And
you only need the `$properties` argument if you actually *need* the `properties`
option on the annotation, or if you allow the filter to be used *on* a property.
Since we don't have this use-case, we don't need it.

Ok! Our constructor now looks a bit nicer. For the parent construct call, pass
`null` for the logger, and `null` for `$properties`.

If we go right now and refresh... we get the same error... but *now* we can add
that argument. Back in the filter, I'm going to be tricky and add this as the second
argument - `bool $useLike = false` - to prove that order don't matter. Each
argument is passed via autowiring or by the named arguments matching.

Put your cursor back on the argument, hit Alt + Enter and go to "Initialize
properties" to create the `useLike` property and set it.

And... that's it! I'm going to stop right there and not *actually* use the
`useLike` property to change the query... because that would be pretty boring.

As long as it doesn't *explode*, we know it's working. Refresh and... yes! If we
add `?search=cube`... that still works too!

Next, let's talk about something *totally* different: API Platform's super-cool
output DTO system: a middle-ground between adding custom properties to your entity
and creating totally custom API resource classes.
