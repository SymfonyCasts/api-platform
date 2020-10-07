# Custom Resource Filter

Coming soon...

That's great. One more custom filtered this time for our daily stats resource. The
goal is to be able to add a question Mark from = a date to only show them listings on
that date. Or later, for example, if we go to /API /daily stats dot JSON LD, we see a
bunch of States. Uh, and the goal is if I had question Mark from = 20, 20 dash Oh
nine dash Oh one, that would only show me things starting with that date and not
before. All right, cool. So let's start basically the same way as we did before with
the other filter in the source API platform directory, I'm going to create a new PHP
class called daily stats date filter.

Now for the key search filter, we extended abstract filter, but that was from
doctrine ORM. So that was a base class specifically when you're doing a filter for
the ORM, which we're not. So instead we're going to implement filter interface. Now
be careful. There are actually multiple ones from API platform. A few of them are for
one of them is for the ORM. And even inside the main part of API platform, there are
two, one serializer, and one of the API get the one in serializer. The one in
serializer actually extends I'll jump into it, extends the other one from the API.
And if you extend the kind of parent one from API, you're going to have to do more
work to make sure you extend the one from the API offering court serializer, then
I'll go to code generate or command and on the Mac and hit "Implement Methods" to
implement the two methods we need, which are very similar to the two methods we had
before get description and apply. The only differences that you'll notice apply has
different arguments then in R or M filter, which makes sense because, Oh, pretty
similar to the two methods that we had inside of our other thing all before we start
filling this end, let's go ahead and tour source entity daily stats. And let's add
this as a filter. So at eight API filter it tabbed to auto, complete that and a daily
stats, date filter, colon phone class. And I need to go add that you statement manual
manually up here. I'll say use daily stats, date filter.

All right, perfect.

Now that we've plugged that together, let's jump straight into get description and
we're going to make this very, very similar to what we did with chief search filter.
In fact, let's just go and steal our code from there. So I'll copy the return
statement from the other filter paste it here. And this time we're going to have the
query parameter to be called from I'll keep property = no, that deals with the open,
uh, the swagger. Now the Hydra documentation type string required false open API
description. We'll change that to be from date or example 2020 dash Oh nine dash Oh
one. All right. So we haven't done anything yet in this apply method, but there
should be enough to get it in our docs. So let's refresh open up. Our daily says
collection point and there it is. We have a nice lead documented new format.

Okay. So now what, because it's not going to automatically work. We've just
documented it. Well, no matter what forgetting about the filter system in general,
it's 100% up to our data provider to return the correct collection data. What I mean
is we're not going to return this daily stats page Nadir object, and then some other
system is going to filter its results. If we want to take into account filters inside
of our, for our daily stats, we're going to need to add that logic into get
collection. Now first, before we are really thinking about filtering the filtering
system, let's just focus on making our daily provider system able to, able to, um,
filter need they'll work on my wording. They're able to filter by a date and actually
most of the work of figuring out which results to return is actually done by daily
stats at page later.

So let's jump into daily status page Nader and down here and get iterator. This is
actually where we use these stats helper to actually fetch all of the objects. And
right now it's already kind of filtering. So we have page nation data. That's helping
fetch many filter. Now, fortunately stats helper itself. If we jump into source
service stats helper, the fetch many already has a third argument called criteria,
which supports a, from an a to key, which are a date time object. So basically I've
already written code into this class that if we pass it a from or a two on that third
argument, it's going to filter the results. So we already kind of have that boring
work done. So on a high level, in order for our daily stats page Nader, to be able to
return the correct results, it's going to need to be aware. It needs to note what the
from end date is. So to do that, I'm actually going to add it as a property. So I'm
gonna add a new private from date and above this. I'll add some documentation that
this is a date to time interface, or no, if it's not set.

And then down at the bottom, I'm going to go to code generate or Command + N on a Mac
and go to setters and generate the set from date method. Uh, but I'll remove the
question Mark. Cause if you call this, you will set a date time object and your S and
you'll see how we're going to set this in a minute. But assuming that this is set, we
can start using it inside of our get iterator method. So we can add criteria = an
empty Ray. And then if we have this->from date, then we'll say criteria left square
bracket from = this->from date.

And then we can pass this criteria as that third argument to fetch medic. Perfect. So
our daily stats page and our via this center is now able to filter by from date. So
let's test to see if this is working by going to our daily stats provider and just
hard coding a date inside of there. So let's see, I'm gonna re instead of the return
statement, let's say page and eight are = at the bottom let's return page Nader. And
then let's actually set that here. Page later,->set from date, and we'll say new
/date time oldest pass it. 2020 dash Oh eight dash 30.

All right, let's see if this works. I'm gonna go over here. I'll go over to my other
tab here, where I have my from day now, this query parameter from as not being read
yet at all, but it should filter everything based on our hard coded date here. So
when we refresh, let's see here. Yes, we got it only returns five results with the
eight hard-coded eight 30 being the oldest one. So you will notice there's one little
bug in my application. I'll let you fix this as hydro total items 30. This should
actually now only say five. This total items comes from our page Nader object, the M
get total items down here, which calls this aerostats error count. So really what we
should do now is actually also allow that method, that stats, that count method to
take in the array of criteria so that it can then do that same filtering that it does
when we call the, uh, fetch Manny method. But I'll leave that for you to do alright,
so next. So this is good. We have the, kind of the filtering logic working, but how
can we fetch this date from the query parameter instead of just hard coding right
here? Well, I sort of just answered the easiest way. We could just grab the request
object and read this query parameter often directly, but there's actually a cooler,
more built in way, uh, that that we're API platform can help us. Let's do that next
and discover also that our daily stats date filter is a service.

