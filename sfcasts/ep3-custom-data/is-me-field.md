# Adding & Populating the Custom Field

Coming soon...

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
wouldn't be set. So I'm actually going to be cautious here. I'm going to go down to
the
public function, get is me. And I'm going to say that if this is me, is no. That mean
it
was, we know it hasn't been set yet, and I'm going to throw a new logic exception.
The
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
going to help me here.

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
