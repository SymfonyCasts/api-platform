# Embedded Normalization

Coming soon...

Let's talk, we're adding a second daily stature and point just to make it a little
bit more realistic. I'll do `$stats2`. And let's actually do a thousand users on one
day, 2000 users on another day. And we'll give this one minus one days. That's kind
of cool because when you at, and then I'll make sure I include `$stats2`, over there.
That's kind of cool because now we can kind of really get the daily stats, the idea
of a daily stats with one day and then the previous day, and then we'd have more
results for later days. All right, now let's return some listings because right now
we're passing an empty array as our fourth argument here, which is supposed to be our
`CheeseListing` objects. So we're just going to kind of fake this by querying the
database for a couple of cheese, those things, and putting them in there.

Plus that's great public function `__construct()` and I'll auto wire, the 
`CheeseListingRepository` I'll Alt + Enter and go to "Initialize properties" to create that property
and set it okay. Before we start creating those stats, let's just create some, let's
get some, let's say `$listings = $this->cheeseListingRepository->findBy([], [], 5)`
cause I can do is pass out an empty array, another empty Ray for the order by, and
then five. So we'll just get five listings and we'll use those as the most popular
five listings for everything. Hey dude, we got this.

I didn't know that you were going to be Spiderman tonight. I can work for cut just a
couple more minutes and then we'll come and write down. Okay. 

So instead of this
empty array, we'll pass `$listings` and `$listings`. All right, let's give this a try. And
I spent over, I can refresh and no surprise. It works. Wait, it work. It's a little
weird because I get the ad ID and that type, but I don't actually get any of the
fields from the `CheeseListing`. And wait a second. That actually makes sense. If you
remember, let me see, realize our daily stats when you're serializing them in a group
called daily stats read. Now whenever API platform sees an embedded object, what it
does, it looks at that object `CheeseListing` and looks to see if any of its fields are
in that same group. And right now there are no fields instead of her cheese that are
inside of this `daily-stats:read` group. So that's basically our way of saying that
when we embed `CheeseListing` into `DailyStats`, we don't want any of its fields to be
included. So that explains why there's no fields over here. But usually when API
platform sees an embedded object and sees that that embedded object doesn't have any
fields that will be exposed instead of embedding an object, it uses an IRI

So the question is, why is it doing that? Why do we have an object here instead of
just being an array of I R I street? So let me show you the solution. First, we need
to tell an APAC platform

What this is an array of using a specialist index. I mean, it already knows as it's
an array, thanks to the typing on the constructor, but API platform doesn't know it's
an array of cheeses listings. No, this Jesus thing here is not actually enough to
tell API platform. Should it be maybe, but we need to use a different syntax. So
instead of by the documentation here, though, it doesn't really matter. I'm actually
going to start documenting the property directly. So here's the key we need add 
`@var` and then say `array` open less than greater than, and inside of here, say 
`CheeseListing`. So a little bit of a lesser known syntax for how to say that you are an
array of cheese listings.

As soon as we do that,

Check out that difference now is what we expect an array of. IRI strings, if we
were choosing to serialize any of these cheeses in the daily stats group read group,
then it would turn into an embedded object. But since we're not, it's an IRI string.
Now the only problem with this `array<CheeseListing>` syntax is that our editors
doesn't actually understand it. It's kind of disappointing PHPStorm, 
`$this->mostPopularListings[0]->`It doesn't actually auto-complete anything. It
doesn't know what that object is. Um, so what I do actually here is I want to be this
at bar is going to be for API platform. And then I'll say, or she's listing last
square bracket, red square bracket. And now my editor is also happy and knows that
this is going to be an array of jesus' objects. So not an ideal situation there. That
might be a bug that's fixed. Um, at some point, hopefully it is all right, so why did
we have to do this? This is kind of crazy. Like, why did, why couldn't it be apart
from just figure out once it saw this object that this was an array of cheese
listings and do the right thing, like why would this make a difference?

So let's actually

See what's going on with another example inside of user. So inside of user, there is
a `CheeseListings` property, but it is not part of our API. You can write to it, but
it doesn't have a user call on read group. There's also a get published cheese
listings method. And this is part of the API and we actually give it the cheese
listings name. So we're gonna do a temporary experiment right now. I'm actually going
to remove the assay realized name from cheese listings. So we're still going to
expose it, but what with the name published cheese listings, and then up on the
cheese listings property, I am going to start exposing this. So I'm going to give it,
um, `user:read`. Now, if we go over here to the `/api/users.jsonld`,

[inaudible],

I am somehow logged out again. You can see that we now have a cheese, those things,
and published cheese listing property, and they're both actually embedded objects.
And the reason they're embedded objects is that the `$title` and `$price`, field, and
`CheeseListing` have the `user:read` field. So I'm actually going to remove those
temporarily. So I'm going to go into `CheeseListing` and take off `user:read `off of
`$title` and `user:read` off of `$price`. So because of this, ideally when API platform goes
to serialize these two array fields, it's going to realize that there are no embedded
properties. And so this is going to turn into an array of, IRI strings in both
cases,

But when we refresh, `cheeseListings` is an array of IRI strings, but check us out,
published she's listings is an embedded object. So they actually returned basically
the same thing, but one of them, but they're serialized in a different way. So here's
the deal. Internally API platform reads a lot of metadata about each property. It
reads that properties type, whether it's required. And it reads that for a lot of
different sources like dr. Meta-data also our own documentation and it takes all of
that data and it caches it. And API platform is really going good at knowing when it
needs to rebuild the cache while developing. So we don't really notice that it caches
this kind of stuff.

So it builds this, it builds this cache purely by looking at our code. And it does it
before it even starts doing any of this other work. Now, right now, when it's looking
at our code and building this property, CA metadata cache API platform knows that
she's the cheese listening's property is a collection of `CheeseListing` objects and
knows that thanks to the doctrine, annotation, it reads this and says, this is a
collection of `CheeseListing` objects, but it does not realize that 
`getPublishedCheeseListings()` returns, and a collection of `CheeseListing` objects. It knows as a
`Collection`, and this is an `IterableCollection`. So basically he knows it's an `array`,
but it doesn't know what is an array of now, why is this a problem? Well, whenever it
means that that property metadata is different between these two properties. Now,
once API platform actually tries to serialize these properties, whenever API platform
actually eventually serializes a collection before it even starts to realize in that
collection, it asks, what is this a collection of? If the thing that's being
serialized is a collection of API of objects that are an API resource, like up here
on `CheeseListing`, then it calls one set of code. It knows it's about the serialize
other API resources. But if it's an array of anything else, which is what happens
down here in `getPublishedCheeseListings`, since it doesn't know that it doesn't
know what it's an array of, then it runs a different set of code and that different
set of code has slightly different behavior.

So yes, this is a bit unexpected and it's a bit of a gotcha you need to really make
sure. And it doesn't really come up in very many situations, but you need to be
always thinking with collections. What does API from? Know what this is a collection
up? So we actually already know the solution here. We can say `@return` we can say
`Collection<CheeseListing>` And now I'm going to go over and refresh. We get an array
of IRI strings in both cases. Now, internally you can actually control this behavior
directly. If you want to, normally maybe a platform should figure out if a property
sh if, if somebody should be an IRI string or an embedded object automatically based
on your serialization groups, but you can control this. For example, we can say at
`@ApiProperty({readableLink=true})`. I want to refresh now

This forces it to be an embedded object. So basically this `readableLink` property is
normally something that is automatically set by API platform. And it sets it by
looking to if there are intersecting groups between the `User` and the `CheeseListing`.
So basically it says, Hey, this is going to be, this property holds an array of
cheeses and objects. Let's see if any of the cheese listing properties are going to
be included if they are, and then it sets the readable link for you by using API
property, redoubling pickles, true or false. You basically say I'm going to override
that setting and control it myself. Now this `readableLink` setting is super weird.
It's I can't wrap my mind around. It almost seems like it's backwards to me. So when
you say `readableLink=true`, that actually says, I want you to embed this use
embedded objects. When you set `readableLink=false`, I can even take a temporary
tick off the, at return to prove it and refresh

[inaudible]

Let's undo everything I'm actually going to take. I shouldn't take the readable link
off and I'll leave that return collection. Cause that's actually helpful. I'm going
to put back my ass serialized name and then up here on the she's listening property,
we'll take off the user call and read, And then over in jesus' thing, I'll actually
undo to add Riyadh user read on price and on up here that an actually want to make
that change. But that kind of helped us explain what's on. So go over now and refresh
things are back to normal. So next we've implemented the collection operation for
daily stats. Let's implement the good item operation.

