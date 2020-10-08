# Filter

Coming soon...

So we just learned that if you pass an argument's option to your annotation, then
these keys inside of her actually get mapped to the arguments, to the constructor of
your filter class. But there's more going on than it seems. Obviously someone
instantiates our filter object at some point. And it's a pretty good guess that API
platform does it directly somewhere and uses the arguments to try to figure out what
arguments should pass to the constructor. But actually our filter is a service in the
container. We didn't register it directly, but API platform did thanks to our
annotation. Each time it sees an annotation for our filter. It registers a unique
service for it.

And after registering it as a service and when registering a service, it takes these
arguments here and sets those as the arguments on the service. Why do we care about
this internal detail about exactly how our filter objects are instantiated? Because
when API platform registers the service, it also sets it to auto wire. True. This
means we can access services from our filter, like normal, check it out. Let's add a
logger interface, logger argument. I'm adding it as the first argument, just to kind
of prove that the order doesn't matter here, I'll grade the lager property and say,
this are a lager = logger. Can I construct her now down here, we can use that in the,
if I'll say this->lager->info, sprint F filtering from date, present S and fast that
from all right, let's go check it out. If I move over, I'll need to go back to
actually hit a real date. I'll refresh while that's loading. I'm gonna copy this URL,
open a new tab and go do slash_profiler.

Find our 200 status code here. I'll click the little Shaw and go down to logs and
perfect filtering from date in our date. Now you may be a little surprised that this
is here two times, but that's actually not a problem. The system that calls the apply
method on our filter is actually the C realization context builder service system.
That's actually a system we've hooked into before and our source serializer directory
and a previous tutorial. We created an admin groups context builder. Anyways, the
context builder system is called at two times in the request one time in the
beginning when it's reading the data. And a second time later in the request when
it's about to serialize that data. So that's the reason why you see this being called
two times anyways, all of this stuff about arguments and Ottawa, horrible services
applies equally to an entity filter like our cheese search filter. For example,
inside of this filter, we use the like query like in our query. Um, let's pretend
that we want to make this. We want to make whether or not we're going to make this
configurable. We want to make whether or not a like query should be used
configurable. So check this out, open up source entity CI's listing, and here's our
filter being used right here. Let's add arguments = and invent a new argument called
use, like set to true.

Then over in a browser, I'll close the profiler and head over to /API /cheeses that
JSON LD. So we can start using this. We immediately get an air, the error we expect
key search filter does not have argument use. Like awesome. So the only weird part in
an entity filter is that our parent class already has a constructor. So that means we
can't just say public. We can't just create a constructor. We need to override the
constructor. So go to code, generate or command, and in the Mac, go to override
methods, select constructor, and hit. Okay. Wow. That is a big constructor, which is
fine. But if you want, you can slim it down a little bit. Uh, we do need to manage a
register. We do need to request back, uh, you don't need to pass the logger to the
parent class if you don't want to, it's optional. And you only need this array, this
properties argument. If you actually specify the properties, uh, option on your
annotation, or if you use your filter above a property, since we're not in our case,
all of a sudden move that to make my constructor a little bit smaller. Now for the
log we can pass Knoll and for properties, we can actually also pass that Knoll.

Perfect. So if we go right now and refresh, we sell the same area because we still
don't have that use. Like, but now we can add it. So over here, let's add, use, like,
and I'm actually going to be tricky. I'm going to use like, as my second argument,
Google use like = false to prove that it's a passing it, I'm doing this just to prove
that, um, all of these other arguments are actually passed via auto wiring. So the
order of these arguments doesn't matter. Now I'll put my cursor back on the argument,
hit alt enter and go to initialize properties to create the use like property and set
it.

And that's it. I'm actually going to stop right there. I'm not going to actually use
this property down here to change the query. I'll leave that up to you. But as long
as if we can prove that this filter is working, then we'll have you move over and
refresh and we can prove it's working because the error is gone and it's still auto
wiring, those services and passing the use like argument. In fact, let's add a little
question, Mark search = cube here. And as you can see, it hit the first two cheeses
that don't have cube in there. So that works next. Let's talk about something else. I
have no idea what it is. Maybe DTS.

