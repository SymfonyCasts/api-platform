# Custom Resource Data Provider

Coming soon...

Okay. So far as daily stats, we're going to start with the, just the get to
collection end point, and maybe that's all you need for your API resource. So I'm
going to remove all these other ones instead of daily stats. We can do that by saying
`itemOperations={}`. He goes empty and then `collectionOperations={}`. And we'll just
say, `get` so simple as that. We can kind of focus on just that one operation suites.
And actually let's try this. I'm actually going to cheat and just put it up here
`/daily-stats.jsonld` and it sort of works, but it's empty in reality, this
isn't working yet. What we need is a data provider because there's no data provider
right now that supports this daily stats class, it's always going to return empty.
All right, cool. We actually have quite a bit experience at this point for grading,
uh, data providers.

So inside the `DataProvider/` directory, I'm going to create a new class called a
`DailyStatsProvider` And we'll keep this as simple as possible. We need to implement
`CollectionDataProviderInterface`, cause we're only going to be providing that
collection end point at least right now, and then the `RestrictedDataProviderInterface`
so that we can say that we just support the daily stats class. So I'll go
to Code -> Generate or Command + N  on the Mac, I'm going to "Implement Methods" to
implement the two methods that we need now for supports. It's pretty easy. Return
`$resourceClass === DailyStats::class`. Now forget collection. Let's just kind of
return a dummy object right now. So we'll say `$stats = new DailyStats()`,
`$stats->date = new \DateTime()`, `$stats->totalVisitors = 100` and we'll just leave
the, uh, `$mostPopularListings` to be empty right now.

And that the bottom we'll just return an array with `$stats` in it. Alright, let's try
this out. When we move over and refresh, it works, I'm getting AA. Doesn't work. It's
not going to work yet. We get this weird error that says no item, route associated
with type API entity stats, daily stats. So what's happening here is you'll remember
that when you use JSON LD, it always generates an `@id` with the IRI to this, uh,
resource. And the IRI is the URL to the item, uh, operation to get a single daily
stats. So the problem in this situation is that inside daily stats we've specifically
said, we don't want any item operations. So this kind of confuses API platform a
little bit, cause it says, how can I generate a URL to your good item operation if it
doesn't exist?

So an easiest solution to this would be to actually allow you to have a `get`
`itemOperation` here. And we actually are going to do that later, but let's pretend like we
don't actually want that the work around, because this is a little bit of a rough
spot in API platform, is this, and actually I'm going to make sure that my coding
standards are correct. There, there is to actually add that get method, but we're
gonna do a really kind of odd thing here. We're going to add it and I'll actually
paste in a couple of things here, and then I'm going to need a use statement for
that. Not found action right there. So I'll say use `NotFoundAction`.

All right. So this is a little strange, but what this basically says, I do want to
get operation, but if anybody goes to it, I want it to execute this controller, which
is going to be a foreign form. So it's an odd little work around to have a get
operation, but not really actually have a get operation. So now let's try it again.
Let's refresh and another air, no identifiers defined for resource of type daily
stats. So every resource needs to have some sort of a unique identifier. Usually it's
an ID or a UUID, which we'll talk about later in this tutorial. So that means that
our daily sets and we can add like a public id or public. UIB what we don't even
really need to do because this `$date` is actually going to be a unique identifier.
We're only going to have a one daily stats per date.

So what we can do as a marketer, this is our identifier. How do you Mark a property
as an identifier? Well, when you use doctrine, it happens automatically. Doctrine
sees that you have an ID that is your doctrine ID, and it assumes, unless you tell it
otherwise that this is going to be your API platform ID. When you don't have that
situation, it's still easy enough. You can say `@ApiProperty()`, and then you can say,
`identifier=true`. Now APIplatform knows this is going to be your identifier. But
when we refresh that we get another air object of class. Date time could not be
converted to string. When you have an identifier, ultimately API platform tries to
use this as a string identifier for it. This is a `Datetime` object. So that doesn't
work. It's actually totally okay. We can do a really clever thing here, create a new
public function, `getDateString()`, I'll have this return a `string`.

And then I'll return `$this->date->format('Y-m-d')` And then we're
going to take this API property thing and actually put it above there. So we're sort
of creating is kind of like an, a, another custom property via a getter. And it's
actually going to be our identifier. So finally, why don't we try this? Oh, it's
still errors out because I have a typo silly Ryan and how would I refresh? There we
go. It works. We get our response. And then we have a whole and hydro member here
with our one item and you can see the ID string here being used, um, inside of the I
R and the at ID. Awesome. Right. But there are no fields. We get the ad ID and that
site, but where's the rest of our data. So normally if you don't set a normalization
context like we did on user, for example, normalization in context with groups, if
you don't set that on your operation, then it's going to include every single
property.

But you can see that we're not seeing that at all. And the reason is that in a
previous tutorial and the source and the source serializer, admin groups, context
builder, this is a special group that helps add custom groups if you're an admin. But
the key thing is actually here. You can see if the groups is not set on context, we
set it to an empty array. So this means that even if, even though we don't, if we
don't have a normalization group on our resource, it actually is defaulting to an
empty array. So it's including nothing. So that's a little quirk with our project,
but it's actually no problem. Cause I prefer being explicit about my groups anyways.
So what that means is we'll go up here and add
`normalizationContexts={"groups"={"daily-stats:read"}}` and I'll copy
that group name because we need to put it on all the properties that we want. So
we'll put this above date `@Groups()` only need one at symbol curly, curly, and I'll
paste.

I'll copy that entire doc block. I'll put that above total visitors. And also most
popular listings does not need to be above get date string. We don't actually want
those a real field. We just want a platform to use it as, as identifier. And now when
we refresh, Oh man, man, not refresh. Now, the system reminds me that I was missing a
comma. There you go. Now we get real fields. Woo. And even better than this and the
new. So if we look at our documentation for this end point, I love about the end
points. And then look at the schema and open up hydrocodone member. You can see that
it now knows that we're going to get date total visitors and most populous things
fields, but it knows nothing about what those are strings into jurors has no idea. So
API platform gets metadata for the documentation in many different places, including
looking at like at VAR that we have about properties, looking at return types of
getter methods, uh, PHP 7.4, um, types like this and several other spots, which is
really cool because it allows you to just code well, and it's going to pick up
intelligently all those pieces of documentation.

But the fact that this is no longer a doctrine, NC means we lose some of that free
automatic metadata because normally API platform reads the column definition as a way
to get metadata. So when you're not in an entity, you need to do a little bit more
work to help give it some, um, to help give it to some documentation.

So one way, in addition to maybe adding `@var` or something above these of the
properties is that we can actually also add a constructor as a way to get
documentation. Now I'm going to add a constructor here, but not really for
documentation. My true motivation is that I want to kind of code, um, code a bit more
properly. I want to make sure that anytime a daily stats object is instantiated, that
all three of these properties are set and constructor is the best way to do that. So
I'm gonna say public function `__construct()`, and we will have looks at a
`DateTimeInterface` called `$date` `int` of `$totalVisitors`.

No, I don't have to do that. I'll do is I'll go to code, generate here and say, go
down to constructor and I'll select all three of those properties. That's a nice,
easy way to do it. And then the only thing we need to fill in is `DateTimeInterface`
type `int` into type. And then we know that the most popular lessons are going to be an
`array` and I'm actually going to remove most of the documentation. These first two are
redundant. And the only thing about this `$mostPopularListening` is is that we know
that this is not just going to be an array. It's going to be an array of cheese
listing. So I'll kind of help it out here by saying `CheeseListing[]` less for a bracket,
right? Square bracket. So the constructor is cool is what's cool about this is now we
can guarantee that all these fields will be in stance will be properly set.

So here we'll just move our new date time. And two daily stats are 100 and then I'll
pass it an empty array for cheese listings for right now. So one of the nice things
about this is that we coded correctly, but API platform also reads the constructor
itself as documentation. So now it knows that the date property must be a daytime
interface. So if you go over here and refresh and I'll go back and open up that, get
operation, look at my schema, open up hydro member. And awesome. Now you can see date
is a string, but it kind of says, this is a date string and those total visitors and
integer and I noticed that most popular listings is going to be an array of strings,
which will be an array of IRI strings, which is awesome.

And we want to describe this a little bit more. We already know how to do that. We
just need to go above that property or could even do it over here on the constructor
and say the five most popular she's listings from this date and the documentation
that's going to automatically pick this up. So next let's actually start loading
favorite cheese listing objects and adding them to our stats object. When we do that,
we're going to find a little surprise about how that property works and how it's
serialized. Then we'll learn more about how API platform loads, all of its property,
metadata, and fix that.
