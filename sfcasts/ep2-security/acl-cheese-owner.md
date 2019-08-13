# Acl Cheese Owner

Coming soon...

All right back to security stuff. We want to make sure that if you use the put end
points to update a cheese listing that only you can only do that if you are the owner
of that cheese listing. So as a reminder here, we actually have a relationship
between cheese listing, an end user which is cheese listing as an owner property and
it's set to the user that owns this cheese listing. So makes perfect sense that we
only want a that user trialed ended it right now all we have is that you just need to
be logged in. So fixing this is actually fairly easy but because things are getting a
bit more complicated and security's important, I want to read a test for this first.
So radio task for creating a cheese listing. Let's create one down here and public
function test update, cheese dusting and I'll start off with client = self, colon
concrete clients. And then I'm going to create a user with user = this->create user
pass and our, no, she's pleased@example.com password food. Now one really important
thing with the way that the test stuff works is that you want to do client = self
call and concrete client on the first line always. Because what that really does
behind the scenes is it creates this client object that's ready to make tests to test
your API. Um, but also boot Symfonys container, which gives you access to light the
entity manager and saving things to the database. So nope, and not Craig's your log
an extra month and say, just create user.

Um, so if we actually swapped these two lines, this, uh, create user alignment, have
an air because the container isn't ready yet. So always start with client, uh, e the
create client call. All right, now let's think about this. In order to test up adding
a cheese listening, the first thing we need is a cheese listing in the database. So
similar to user, we're just going to create one by hand. She is listing = new. She's
listing, uh, we can actually pass the title right here. So let's say block of
Cheddar. Then she's listing set owner and we're going to set this to be owned by that
user. Then the other fields are needed. We need to set a price, so I'll say $10 and
we also need to set a description and find down here we can see a better database by
getting the entity manager. Now if you open up your a custom area test case, we've
already seen how to get the entity manager. I'm actually gonna copy this line here
and let's actually get a shortcut for getting the antiquey manager cause it's another
really common thing that you'll need. So protected function and get entity manager.
This will return in entity manager interface.

And then down here I'll just return self calling and calling container. Every good
doctrine error would get manager perfect. So now we can use this in here. C e m =
this [inaudible] entity manager and then we'll persist that she is listing and flush,
right? So that is a very nice set up for our test. Now it actually tests the end
point. The first thing we're to do is we're just going to test that. Hey, if you log
in, you get a 200 status code, we're going to log. If you log in as this user, you
get a 200 status code. So this is kind of the expected situation. So I'll see this on
login, pass out the client, our email, our password.

Okay.

Then we'll make a request. So client error request. In this case it's going to be a
put request and the URL is going to be /API /cheeses and then the id of that Jesus
thing. So cheeses in->get id. Now for the options, most of the time the only thing
you're going to need going to need to pass and the only thing we'll need to pass here
is the actual dating on a sense. So we want to send JSON and let's just update the
title field policy title updated. That should be enough to be a valid request. Now on
edit, what we get back is we get back a 200 status code and it actually, I believe it
will say that down here. Yep. When it's updated, what we get is a 200 status code. So
we'll say a circuit response status code, same 200 all right, perfect. I'm actually
gonna copy the name of this method. Test update she's listing. So we can run over
here and say PHP bin /PHP unit dash dash filter = and we'll just run that one test.
And if you scroll up from all those deprecation warnings, yes it works.

So now let's enhance those tests for the more interesting case, which is what if I
try to edit this and I'm not the owner. So first thing I'm going to rename this user
variable to user one and uh, change the email to user1@example.com. And then down
here, user1@example.com because now I'm going to create a user too. So say user two =
this Arrow, create user and we'll just make that email address user2@example.com

[inaudible]

and then copy the entire login request, assert response status code thing and paste
that right above here. So before we test the actual case down here of you know what
if I actually log in as the user that owns that cheese listing before we test this,
let's actually test what happens if we log in as somebody that doesn't own this. So
we're going to log in as user too. At example.com we're going to make that exact same
request. In this case, the status code we're expecting is four oh three which means
we are logged in but we should not have access to do this. So we'll check for four oh
three this time when we test we should see a failure and we do ask perfect and failed
a certain four three we actually back a 200 it did allow us to change to modify the
object.

All right, so let's back back to API platform security stuff. Whenever you use the
access control here, I mentioned you set this to sort of a mini expression. This uses
Symfony's expression language and you're using an is granted function. So this is a
true expression and you can actually make this expression a bit more interesting by
saying and, and then inside a period API platform and gives you access to a couple of
variables, whatever object you're currently, um, working with. So in our case, a
cheese listing is available via an object variable and we can even call methods on it
as soon as a object dot get owner that's calling to get her on here = = and another
variable it gives you access to as the currently authenticated user, which is either
going to be our user object or null. So we can say objects that get owner = = user.
So basically give us access if we're logged in and if the logged in user is the owner
of this object. Now we are in the test again and yes, it passes. So the one other
option that you're going to see sometimes it's really less important. Um, but in
addition to access control, I just want to show this. There is also another thing you
can put here called access control message. We can set this to something like only
the creator can edit and listing.

If you read on test immediately, that's not gonna make any difference. And make sure
you have a comma here at the previous line. Yeah, that looks good. Now if you're in
the test immediately, it's not gonna make any difference because this is just
changing the message that's shown to the user. Yeah, it passes. But I want to show
you what that looks like. So, uh, right after me assert the four three status go and
I'm actually going to bar dump this arrow. Um, nope. Here, part of client would get
response or get contents. We can see what that looks like and then we'll rerun the
tests over here and there you go.

Inside of get content, we're actually gonna pass false. So by the thought, when you
can't get content on the response object, it's going to throw it if it, if it was a,
if the response was an error, we'll actually throw an exception and say, hey, this
was an air. If you actually want to get what the actual current, the error content
looks like, pass the false here so that it won't throw an exception. And then s you
can totally see here as you can see, the hydro title as an aircrew, but the hydra
description is only the creator can end a cheese listing. So do you want to get more
context? That's the way to do it. Or I would take out my Barnum.