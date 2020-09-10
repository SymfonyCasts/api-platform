# Setting Published

Coming soon...

One of the things that we can't do yet is publish a cheese listing. Whenever we
created a cheese listing through our API, it's always, it always gets an, is
published, = false value. And there's no actual way to change this yet because we
haven't exposed any is published. Field is not exposed in our API. So here's the plan
in the U. If you can imagine the UI, there will be a publish button. And obviously on
cheese listing, there is an is published field, but publishing is more than just
changing this field in our application. Let's pretend that publishing is an important
thing. And when you publish something, in addition to changing, this is published
field, the true, we need to run some custom code. Like maybe we need to send the
cheese listing to elastic search, to be indexed, or we want to send some notification
to people that have been waiting for that type of cheese.

How can we do this? What you might think that we need a custom end point or operation
something like post /API /cheeses /curly brace ID /publish. You can do this. And
we'll talk about custom operations in part four of this series, but this isn't
restful in rest. Every URL is a unique resource. So from a rest standpoint /API
/jesus' /ID /public publish, it makes it look like there is a keys publish resource,
and that you're creating a new one of course rules are meant to be broken. And
ultimately you should just get your job done. But in this tutorial, let's see if we
can solve this in a restful way. How by making is published changeable in the same
way as any other field, by making a put request, which is published colon true in the
JSON body. That part will be pretty easy running code only when this value changes
from false to true. That will be a little trickier and will involve a data persister
and some clever logic. So let's start with the basic test of being able to update
this field. So I'm going to go into tests and open she's listing resource test, and
let's find test update she's listing I'll copy that, and then duplicate it below,
update its name to test publish she's listing.

Now this cause I don't need to users. So I'll just create one user and log in is that
one user, because eventually we're only going to allow owners of a cheese listing to
a Paloma cheese listing, and then down here for the body, the JSON we're gonna send
is published set to true. And let's see here, the status code we're actually going to
want is 200. So the idea is we create a user. This uses the Foundry library, and then
we create a cheese listing using the same Foundry library that's published that's,
but we don't want this published thing that would make it already published. What got
a new cheese listing set to that user as the owner will log in, is that, and then
we'll send it port requests with is published set to true. So this is a very
traditional way to update any field now to make things a little bit more interesting
that at the bottom what's actually assert that this cheese listing actually is
published after you read the test.

So I will say she's listing->refresh. I'll talk about that in a second. And then this
assert true that she's listing->get his public of cheese listing->get is published.
Now that she's listing->refresh. This is another feature of that Foundry library. So
whenever you create objects using the Foundry library, it actually passes you back
here. Entity wrapped in a proxy object. So if I hold command on this refresh, you
see, this is a little proxy object from Foundry. It wraps the NCD object. It has a
couple of useful methods on it, like refresh. So I can call that and it's going to
automatically make sure that that refreshes with the latest data from the database
pretty convenient. All right, let me copy that method name. We'll spin over Symfony
we're on bins last PG minute dash dash filter = test published cheese listing.

And with any luck this will fail and yes, it does fail. The asserting that false is
true because this doesn't work yet. And the reason is simple is published as simply
not part of our API. It does not have any groups on it like the other fields. So what
we want, because if you go to the top of our doc of our API plat of our API resource
configuration, as a reminder, our de normalization context uses a serializer group
called cheese colon, right? So if we want a field to be writeable on the API, that's
the group that needs to get in. So I'll copy that. Scroll on is published and say ad
groups and then say curly, curly, and then paste. CI's colon right inside of there.
Now, as long as this has a setter, and if I search for a set is published, there is a
setter on this. Then the API is going to be able to use this setter and expose that
field. All right. So spend over run the test and yes, this time it passes. So as I
mentioned, that was pretty easy because we're just exposing a normal field in our
API. Like anything else? The real question now is how can we run custom code, but
only when it is published changes from false to true when it actually is published.
Let's talk about how to do that next.

