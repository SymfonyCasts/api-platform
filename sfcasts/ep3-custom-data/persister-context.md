# Persister Context

Coming soon...

Data persisters are the way to do logic before or after something saves. But what if
you want to do something only when your object is being created or only when it's
being updated or maybe only when a specific field changes from one value to another,
that's the kind of stuff we're going to talk about. Let's start by logging a message
only when our user is created, which is actually pretty simple. First, I'm going to
add a third argument to my constructor for a lager interface, lager I'll hit option,
enter and go to initialize properties as a shortcut to create that property and set
it now down here, since this is an entity, we don't need to do anything special at
all. We can say, if not data arrow, get ID, then we'll log something. This era
lager->info, give it a level of sprint F and say, user present as just registered
Eureka and pass the data arrow, get email.

So we're logging something here, but you know, the point is that you can take any
actions needed for a new user, like send registration email, or any other setup that
you need. All right, just to make sure that works. Let's run our test Symfony run and
filter it for test, create user it passes and a lazy way to look for this is to tail
my bar logs, test out log, and I'll grab that for registered. Hmm. And since that was
pretty simple, I'll just assume that worked. All right. So let's a little bit more
complex. What if you only want to perform, run some custom code when there for a
specific operation, if you open our cheese NC, right now, there are a number of
operations as they get and post collection operation and get put and delete item
operations.

So for the most part, by checking, if the user has an ID or not, it's pretty easy to,
to, you can get most of the way there. We know that this is probably, this is going
to be the post operation. And if there was already an idea that it's going to be the
put operation, but if you start having also patch operations or custom operations,
then you can't really know just by checking the ID, which operation that you're in
the key to the key to really knowing what's going on inside of API platform is by
looking at the serialization context, which we do not have access to anywhere inside
this class right now, but we can get it like many parts of APAP platform. There's a,
there's an interface for the object. You're creating like a data persister or a
context builder. And then there's an optional, stronger interface, which gives you
access to the context. So this case, if you need the context degree in places with
context, all aware data per sister interface, and the difference there is that all
the methods suddenly have an array context = empty array, arguments, all copy that.
But then on sports persist and down here on remove perfect. That makes it happy.

Now persist. Let's actually see what that looks like. Some of the dump, the context
don't go over here and we will run our test again, oopsies. I might have that and it
passes and we see the dump. So I see resource class collection, operation name, and
then a couple of other flags. And actually let me read it on the test. This time I'll
run all the tests inside of user resource tests. We can see what the context looks
like under different situations. You can see here, we have the resource class
collection operation name, and then a couple other things like receive, respond
persist. So perfect. So if you need to know the operation name, we can get it from
here. If it's an item operation, it will have this key. If it's a collection
operation, it will have that key.

So this means we can say something like if context and we will grab that item,
operation named key, oops, and add my dollar sign. So we'll get the item, operation
keeper context. And I'll use the question questions that if that he is not set we'll
default to know, and if that = = = put, then we know that we are in our put
operation. So we can say something like this, arrow, lager, arrow,->info, sprint
death, user percent S is being updated and I'll pass this time. Data arrow, get ID.
Perfect. And I was running the test again. Not because it will actually worry about
checking the logs, but just to make sure I didn't break anything.

Awesome. So by using the context and the data get ID, we have a lot of control over,
um, when to run custom code, but things can get a little bit more complicated. Like
sometimes you want to run code only when a specific field on your object changes from
one value to another, or are really good example of this might be when something is
published. That's actually something we have on our, on our cheese listing entity. We
do have an is published flag yet. There's not actually a way to set this via our API.
This is not exposed to our API. And we did that on purpose because when he publish
it, we actually want some custom code to be executed. So next let's make it possible
for the user to publish their cheese listings, but also run some code when that
happens.

