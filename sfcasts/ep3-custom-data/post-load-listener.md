# Post Load Listener

Coming soon...

Let's create another custom field on user. It will be almost the exact same situation
as before, but slightly different. And that difference will lead us to an alternate
solution because suppose that some users are MVPs, but this isn't just a simple
Boolean field that we can store in the database. There's some super fancy calculation
that requires a service that we need to call it to figure out if a user is an MVP. So
our goal is to expose this new ease, MVP field to our API that will be populated via
custom logic and a service. So to start, we know the drill, we're going to add this
as a real field in our entity class, but we're not going to persist at the doctrine.
So we'll call it is MVP and we'll give a documentation for our, our API turns true.
If this user is an MVP and then down at the bottom, I'm going to go to code, generate
or command on a Mac and generate my getters and setters. And the only difference here
is I'm going to do get his MVP. Uh, I need to look that up, but that seems to play
better at the API.

All right, cool. So if we move over now and go to /API, we're hoping that we will
already see this field in the docs. If you look under the user's endpoint, go to
schema, boom. There it is. Is MVP Boolean. All right. So let's start with a test for
the behavior of how we want this field to be set. So in our app, a person, a user
will be an MVP if their username, which is a field where it is on our user, if the
username contains the word cheese, okay, in reality, we get accomplish this with just
a custom Gitter method like we've done before. That looks for cheese in the username,
property returns true or false. We don't actually need a service to calculate this
value, but let's pretend that we do so in user resource test, scroll down to test,
get user and rhino. When we create this user, we give them a phone number, but
otherwise we let it choose the random data that's, uh, being chosen in the user
factory class, which you can see inside this get defaults method.

You can say random username from faker. So now we'll do is let's be a little more
specific here. Let's actually use a specific username she's had. And now a few lines
later, since this is actually added the word cheese. And that's why after we make the
request to get that and assert that it's 200, we'll start that the JSON contains is
MVP set to true. And if we want to, if you go down here and check for his MVP, false
a on other users, but that's enough. Alright, so let's try this. I'm gonna copy that
method name. We'll do our usual Symfony PHP bin /PHP unit dash dash filter = test get
user and perfect. It fails because it has as MVP false, which is the default. So how
can we set? This is MVP field to the correct value.

We, you already know two solutions. We could use a custom data provider like we did
earlier, or we could actually do an event listener that sets it early on the request,
but neither of those would set this value everywhere. The data provider only sets it
when you're working with an API end point and even the event subscriber won't set it.
Won't set it when we're in the CLI and maybe that's okay. But another solution is to
use a doctrine listener, a post load listener. That's a function that's called each
time. An entity is loaded from the database and it gives you the opportunity to
change what your entity looks like. And the only downside to this solution is that
your post load listener will always be called. So whatever your, whatever value
you're setting your field to needs to be a pretty quick calculation. We don't want to
do a bunch of heavy work every time we query for a user and then not need it on most
pages.

So it's great doctrine listener. You can create a doctrine, subscriber, a doctor, and
listener or something called an entity listener. It's kind of confusing in a previous
tutorial, one of our previous stories, we created an NC listener and I kind of liked
those. So let's do that in source doctrine. You can already see the one that we have
here. This was a listener that we created that would automatically set the owner to
the currently logged in user on a cheese listing. So that before saves that we didn't
have to in our API. So let's create a new PHP class inside of here and we will call
it user set is MVP listener.

These, uh, NCP listeners don't extend anything. The only rule is that you need to
have a public function with the name of the event that you want to listen to. So post
load and in the case of an NC listener, what your extra past is the entity that is
being loaded. So we're going to have this work on users. So say user user. And then
the second I'll show you how doctor knows to call this and how it knows that this is
a listener for the user entity specifically. It's not actually magic, but before that
let's actually fill in our logic. So to determine if this user is an MVP, we're going
to need to know, we just need to do a check on the username. So we can say user->set
is MVP. And I'll say, STR POS to look for the user, the haystack, user arrow, get
username.

And if cheese we want that, is that really true? If cheese, the position of that is
not false and that's it. But as I mentioned, this is not one of the cases where a
doctor is going to magically find our class and magically no, do a post load listener
for user entities. We need a little bit of a configuration. The first thing we need
to do is over inside of our user entity. So all the way at the top here, we need to
say at, or am /entity listeners and will do is they will pass that in array and
they'll pass it. The class name of our listener. So is a user set is MVP listener,
::class. And I am going to need a use statement for that. It doesn't auto complete,
unfortunately, but it's easy enough for us to add here.

So I'll say use user set is MVP listener now, down here yet, at least let's have you.
I can hold command and I can see it's it recognizes that class. And the other thing I
need to do is actually register. This is make sure that this a user set is MVP
listener has a special tag. So that's recognized as a listener. So we do this and
config services .yaml, this is kind of an annoying step. We've done it once before so
I can copy the entity listener we had before paste it. And then we'll just change the
name here to user set is MVP listener. Phew. So now any luck that should be enough
for our function to be called app every time a user is loaded from the database.

So when we go over right now and rerun the test, woo, we got it. So that is yet
another way to add that custom field to your API resource. Your doctrine is very
source and make sure it's set. So next, adding a couple of custom fields to your API
resource is no big deal, but what if you have an API resource? What if you need an
API resource that is completely custom? Like you have need something over here and
your API, but it doesn't. But instead of pulling from one table, what pull you to get
that API resource? You need to pull from a bunch of different database tables, or
you're getting data from some completely other, uh, source. That's not your doctrine.
It's not that we're going to create a 100% custom non-entity API resource.

