# Property Metadata

Thanks to the data collection provider, our endpoint returns one result... but
there are no fields! Why not?

Normally if you don't set a `normalizationContext` - like we did in `User` with
`normalizationContext` and `groups` - then your object will be serialized with
*no* serialization groups... which basically means that every property will be
included.

But... we are *not* see that at all! This is due to something we did in a previous
tutorial. In `src/Serializer/AdminGroupsContextBuilder.php`, we added code to
give you *extra* groups if you're an admin. But to do this, if the `groups` are
*not* set on the `$context`, we initialized them to an empty array.

Thanks to this, if we don't have a normalization group on the resource, instead
of serializing everything, it will serialize nothing because it thinks we want
to serialize *no* groups. It's... a quirk in our project.

## Adding Normalization Groups

But, it's no problem because I prefer being explicit with my groups anyways. In
other words, in `DailyStats`, add `normalizationContext` set to `{}` and groups
equals `{"daily-stats:read"}`.

That follows a naming convention we've been using. Copy that group name so that
we can add it above the properties we want. Above date, put `@Groups({})` and
paste. Now copy that *entire* doc block and put it above `totalVisitors`
and also `mostPopularListings`.

But we do *not* need to put this above `getDateString()`. That *is* used as our
identifier, but we don't need it as a real field in the API.

Ok, let's try it! When we refresh... Symfony *politely* reminds me that I'm
missing a comma in my annotations. There we go. Now... yes! We have fields!

## How Property Metadata Works

Head back to the documentation... find this endpoint, look at the schema, and
navigate to the `hydra:member` property. The docs *now* show the correct fields!
But... it knows nothing about the *types* of each field. Are these strings? Integers?
Aliens?

API Platform gets metadata about each property from *many* different places, like
by reading Doctrine metadata, PHPDoc, looking at the return types of getter methods,
looking at the argument type-hint on setters, PHP 7.4 property types and more.
What's *really* neat about this, is that if you code well and *document* your code,
API Platform will intelligently use that for *its* docs!

This becomes *especially* important to think about when your class is no longer
a Doctrine entity. Why? Because with an entity, API Platform gets a *ton* of
metadata from Doctrine. Without an entity, we need to do more work to fill in the
gaps.

## Adding Metadata with a Constructor

To tell API Platform the *type* of each property, we could definitely use PHP
7.4 property types or add `@var` PHPDoc above each one. But we can *also* add
a *constructor*. Now, my *true* motivation for adding a constructor is *not* really
documentation - that's a nice side effect. My true motivation is that I want
to make sure that anytime a `DailyStats` object is instantiated, all three properties
are set.

I'll cheat to do this: go to the Code -> Generate menu - or Command+N on a mac -
choose "Constructor" and select all 3 properties. Then fill in the types:
`DateTimeInterface`, `int` and `array`.

I'm also going to remove most of the documentation. This is *totally* your call,
but I usually *only* include documentation that *adds* more information: the first
two are redundant.

But, hmm, we *can* add more info about `$mostPopularListings`. The type-hint tells
us that this is an array... but not what will be *inside* the array. Help it out
by setting the type to `CheeseListing[]`.

Now, in `DailyStatsProvider`, we just need to rearrange all the data into the
constructor. Pass an empty array for the popular cheese listings.

I love this! We've written good code *and* API Platform is going to *read* our
constructor as documentation for the properties! Refresh the docs... open up the
operation, look at the schema, go to `hydra:member` and... awesome! The `date`
is a string that will be formatted as a `date-time`, `totalVisitors` is
an integer and eventually `mostPopularListings` will be an array of strings:
an array of IRI strings.

Want to add *more* documentation? We already know how:

> The 5 most popular cheese listings from this date!

Or even above the constructor.

Oh, and by the way: helping API Platform determine the *type* of each field is
*more* than just for documentation: it's also used during *deserialization*. For
example, if you send an IRI string to a field that is a `CheeseListing` type,
the denormalization system will correctly *convert* that IRI string into
a `CheeseListing` object. Similar things happen for date strings and many other
types.

And next, when we start returning `CheeseListing` objects on the
`mostPopularListings` field, we're going to learn *another* way that property
metadata affects *how* your objects are serialized.
