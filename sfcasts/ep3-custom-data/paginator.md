# Paginator

Coming soon...

When an API resource is a doctrine entity like cheese listing, we get a lot of things
for free. Like we got automatic page nation on cheese listing by doing nothing. And
we also get access to all of these filters. These filters don't work for custom
resources. These work because API platform knows how to, um, use these to query
doctrine. So if you want filters or page nation on a custom API resource, you need to
do a little bit of work yourself. Fortunately, it's not too hard. Let's start with
page nation because right now, if you look at our question, I point it just lists
every single daily stats was a item. There is no page nation. So here's the idea
inside of our data provider, instead of returning an array of daily stats objects
from get collection, we're going to return a page, a Nader object that sort of
contains those daily stats objects. So in the source data provider directory, let's
create a new PHP class called daily stats page. And later the only rule for now is
that this needs to implement a page Nader interface from API platform. Then I'll go
to code generate or Command + N on a Mac go to "Implement Methods" and let's
implement all five methods that we need. Perfect. And I want to make sure that count
is on the bottom. It's just gonna work down there a little bit better.

Alright. Does he get this working? Let's just fill out some dummy data here. So
return last page. Let's pretend that there's just two pages. Forget total items.
We'll pretend that there's 25 items. Forget current page. We'll pretend we're on page
one. Get items for page we'll return. Now that we're on 10, we're doing 10 per page.
And for count, this is actually, we can actually just return this arrow, get total
items. All right, perfect. So there's no daily stats objects in here yet, but let's
do as little as possible and see if we can get things working. So I can do this as
provider, instead of returning the array of objects, let's return a new daily stats
page and later. Ooh. All right. Let's try it. Go back over and refresh the
collection. End point and air class daily stats page in it are must implement the
interface traversable as part of either iterator or iterator aggregate. Wow. Okay. So
the interface on our page and inter-class helps give APM platform a bunch of
information about a page, a nation for our resource, but ultimately, whatever we, we
returned from gate collection needs to be something that's API platform can loop
over, can iterate over.

So to get this working, there's a couple of different ways, but I'm going to add a
second interface here called iterator and core interface called iterator aggregate.
And then I'm going to create a new private property called Bailey stats iterator. And
then thanks to that new interface down at the bottom, I'll go to code generate or
Command + N on a Mac and go to "Implement Methods". And this requires one new method
called get iterator. And what I'm gonna do here is actually, I'm going to check to
see if this->daily stats iterator is no. And if it is no, we will set it. This air
Davis has iterator = and I use another core class called new array iterator. And for
now I'm just going to pass this an empty array. I'll put a little to do up here to do
actually go load the stats and at the bottom I'll return this->daily stats iterator.

So basically words, we're creating an iterator with the daily stats, objects and
it's, and I'm creating a property here just that we don't, uh, um, create this
iterator over and over and over again, if get iterator is called multiple times. All
right. So now when we go over and refresh that end point, it works. I mean, there's
nothing inside the collection here. You can see an empty hydro member, but you can
see total items, 25. You can see that the, uh, hydro colon, uh, colon, uh, links here
are telling us how to get to the next page. And the last page, all that information
is coming from our page and enter object. All right, to actually do the heavy lifting
of loading our daily stats objects.

We can use our stats helper that we have access to instead of daily stats provider.
So that is as page editor. Let's add a constructor on top. So I'll say public
function_underscore construct, and we'll have daily stats daily stats. Now this is
not a service. So Symfony is not going to auto wire this, but we're going to pass
this in. When we create the daily stats page Nader in a second, I'll hit alt enter
and go to initialize properties to create that property and set it. Then before we
use it, I'll go over to data SAS provider. And you can see here, I'll just add this
aerostats helper to the, when we instantiate daily stats page Nader. Now for now,
we're going to completely ignore a page, a nation, and actually showing a subset of
results. We're going to continue just returning all of the daily stats on a no matter
what so down and get iterator instead of the empty array. I'll say this aerostats
that's Halbert. Oh, what did I do? What did I call that?

Oh, this is Ryan. I'm on run. Stats. Helper is more injecting here. Hmm. It's all ad
inside of the new array iterator of this aerostats helper, arrow. Uh, that's many to
return everything. Alright, let's check it out. Let me go over next time. Refresh and
yeah, it sort of works. I mean, it does work, right. We have hydro member. We have
all the items in it and down here in the bottom, we have our page name and
information and all the next pages, but we know it's not really working because a lot
of this data is hard coded, and we're still returning all 30 daily stats on every
page. So next let's finish this. But in order to do that, we're actually going to
need to ask a pamphlet from what page we're on. Like, are we on page one or are we on
page two? Because we'll need that information to figure out exactly which daily stats
objects we should return. That's next.

