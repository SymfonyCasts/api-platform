# Decorate Persister

Coming soon...

Our user data persister is now responsible for loading the collection of users, but
we lost page nation and filtering because the normal data provider, the doctrine data
provider usually handles that let's actually open up that class. I'll hit shift
shift, and I'm gonna look for a collection data provider. I'm just kind of guessing
that name, but sure enough, you can see when Vermont mango the, or, and the ORM. So
actually make sure you hit include non project items. So you definitely say either it
is bridged doctrine ORM, and here it is. So this is the normal data provider
collection data provider from the doctrine ORM.

And you can see down here and get collection. It creates a query builder, and I'm not
going to go into the specifics, but oops, over something called the collection
extensions. And this is the doctrine extension system in API platform. And that's
actually what provides things like page nation and filtering. Those are all
extensions and they modify the query. Then finally down here, we get the result of
that query. Uh, one thing I want you to notice is that this is a context aware
collection, data provider interface. So that's actually why in our user provider, I
implemented that instead of just collection data provider interface, because I knew
that I was going to eventually want to call the core doctrine one, and I want to be
able to pass it the context argument to it's get collection method. Okay. So instead
of doing the query ourselves inside of our data provider, let's call that core
service. Or as first thing we need to do is find out what that service ID is. So
funny terminal, let's run a bin console, debug container, and let's search for how
about data_provider?

Okay, let's see here. Ah, check this out. Abe platform doctrine, or I'm a collection
data provider. Oh, but also we have something called the default ORM default
collection data provider, huh? And down here in the bottom, we see another one that's
called ape API platform collection data provider. So this last one is almost
definitely the entire data provider system. It's probably the one that's responsible
for calling all the other data providers, the supports methadone, all the other data
providers. So we don't want to inject and call this one because we would effectively
just be calling ourselves. We saw this when we did the data per sister decoration,
but what about these two ORM collection data providers up here? What's going on here?
Which one we supposed to use? Well, I'm gonna hit zero to check out this first one.

Ah, it's subtle abstract. Yes. An abstract service is not a real service. It's more
of a template that you can use to build real services. If we try to inject this
service, Symfony would give us an air and tell us that let's run the command again.
And this time check out the other one. Yes. Abstract. No, this is the real service.
The default is actually referring to our default ORM connection. So this is the one
that we want to inject. All right, let's use it. Oops. Over on our user data
provider, I'm going to remove a user repository argument. And instead we will inject
a collection data provider.

Oh, what's going on collection data provider interface and I'll call it collection
data provider. You could also type in the specific collection data provider class
that we know we're going to reject. And so the interface that's up to you now I'll
copy that. And I'll just rename my argument to that. Make sure you don't have the
extra dollar sign down here below. We'll use that in get collection return
this->collection, data provider arrow, get collection and pass it the same arguments
resource class operation name. And it's a little silly here, but I'm also going to
pass context. That's not technically a method on that get collection interface. But
if we know that this is actually going to be the doctrine one, which is context
aware, and it does have a third argument right now, if we stop now, we're probably
going to get, and don't, don't try this unless you have XD bug because this is
probably going to cause a recursive problem.

Yup. Maximum function, nesting level of 256 reached because by default Symfony is
going to auto wire, the main collection data provider, which calls us. And then we
effectively just call it again. And then it calls us again, the exact same problem we
had with our data per sister. So we need to do is tell it to inject these specific
doctrine data provider. So let's open up config services, IMO. And down here at the
bottom, we will add another service. We will override the service for apt /data
provider /user data provider. And I'll do bind. And in this case, what we're going to
bind is this collection data provider argument. And we're going to set that to at,
and then I'll go over to my terminal and copy the service ID that we figured out is
the one that we want.

All right. So now let's try it. And I'll refresh the browser. I somehow managed to
log myself out between recording. So I'll log back in refresh and it works. And this
time you can see it's page in any, it stops at 30 users and it says that there's 51
total. So page nation is back. Alright. So let's now, now that we have a user data
provider, that's doing the norm functionality. Now let's add our new, ease me field.
Now we're going to pretend we're going to ignore all this data provider stuff for a
second and just pretend that we want to add a normal field to our API. So we do that
in user, by first adding a new property, I'll say privates is me.

And then I'm going to put it into ad groups, put it into our user colon regroup. So
the only difference between this property and the other properties is that I'm not
going to put the arm /column because I don't want to store this in the database. Now
down at the bottom of this class, I'll go to command, enter code, generate, and go to
getters and setters. And I'll generate the getter and setter for is me, but I'll make
it a little better. I'll put the bull type end on the argument and a bull or return
type.

So that's actually all, we need to give this to be part of our API. I'm first going
to open a new tab and go check out the documentation down here on the good end point.
I'll look at the schema. And yeah, there it is, is me bullying. So that automatically
sees that we're exposing that field and it has I getter. So it's now part of our API
and to make it even cooler, we can go back up here to the property name. There it is.
And I'll say returns true if this is the currently antiquated user.

And that's nice because ABA platform automatically picks up the documentation on
this, just like anything else. So when I check out the schema now on our user end
point, you can see that that shows up in the documentation, which is awesome. Now, in
a moment, we're going to set this field in our user data provider, but we do sort of
have this strange field right now on our, on our entity called is me, um, that now in
theory, may or may not be set a need to think of a better way to explain when it
wouldn't be set. So I'm actually going to be cautious here. I'm gonna go down to the
public function, get is me. And I'm gonna say that if this is me, is no. That mean it
was, we know it hasn't been set yet, and I'm gonna throw a new logic exception. The
is me field has not been initialized. That's just to make sure that I don't
accidentally start relying on this is me field somewhere. And it's in a spot where we
haven't actually ever said it. Okay. So now it is time to actually set this field in
the user data provider. My guess is that the collection method is going to return an
array of users, but let's actually check for sure. So I'll say users = and then I'm
going to do a DD of users. And at the bottom I'll return the users.

When I move over and find and refresh my original tab, it's actually not an of users.
It's a doctrine that page Nader object with a page Nader inside of it. So you can't
exactly tell here, but the way that the page Nadir works in doctrine is it's not an
array, but it is something that you can iterate over. It is Iterable. So if we start
looping over this page Nader, then we're going to be able to get the results. So
about this, I'm going to add a little documentation to help my editor. I'm going to
say that this is an array of user objects, which is actually a lie. It's actually a
page Nader. If you iterate over it, it's a user objects, but this will help me down
here. Cause now I can, when I for each he'll help me down here. As I mentioned, since
it's interval, we can say for each users as user and then my doctor takes up, there's
gonna help me here.

Cause I can say user->set is me. And for now let's just say that it's always true.
All right, let's try this. Now when I move over and refresh the page. Yes. Got it. Is
me true is me true is me true. We had a field on every response. Of course it's not
set to the right value yet, but that is the easiest part of this whole thing to set
that to the right value inside of our data provider, we need the security service. So
at a second argument, security, security I'll hit alt enter and go to initialize
properties to create that property and set it.

And then down here before the, for each for simplicity, let's set current user =
this->security->get user. And then for the is me it's as simple as current user = = =
user. Beautiful. Let's try it one last time and looks good. The first one is me.
True. I did log in as that user and then is me false on all the other ones. We've got
it, but this only works on the collection. End point. I'll copy the, if I go to /API
/users /one that JSON LD, I get the is me field has not been initialized. That makes
sense. We've only added this logic to the collection data provider, not the item data
provider. If we want the field to be accessible there, then we need to do a little
bit more work. Let's do that next. And also find one other kind of surprising spot or
we need to initialize this field.

