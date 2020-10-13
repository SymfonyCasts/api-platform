# Uuid Identifier

Coming soon...

We have a UID property on our eight on user and it's being set, but it's completely
not part of our API yet before we make it part of our API. Let's write some tests
that describe the behavior we expect. So down here in let's see tests, functional
user resource test in test, create user. Once we're done after creating a user, the
API serializes that user and sends it back. Once we changed the U U ID, we'll expect
the at ID property on that to actually be /API /user /the you UID instead of the auto
increment ID. So let's check for that first. We need to query for the user object and
the database after we have, uh, after we've created it, we can do that with user =
user factory, colon, colon repository, arrow, find one by pass it, email set to the
email that we created up here.

And then below that, I'll just do a little assert, not know as a sanity check certain
on no user. Now, the reason we're doing this is if we want to assert that the U U ID
is being, uh, that the ad ID is set to the string container of you ID. We need to
know what the UID was just set to in the database. We're doing this so we can read
the UID ID from that user and make sure it's in the response. So now I can say
this->asserted, JSON contains Passat and array, and we will say ID should be /API
/users /dot user arrow. Get you your ID, except we don't have a get you your ID yet.

So let's actually go over into user and I will create that down here in the bottom.
I'll go to code generate or Command + N on a Mac and add a getter for it right now. I
don't need a setter for it. So I'm not going to generate one. And you can see what
this actually returns is the UID interface. I'm not sure why it did the long form
there. I'm gonna get rid of that and retype the E here. Their ID that'll have the use
of it for it. So let's go with you UID property. It stores in the database has a
string in my SQL, but actually in, in PHP, it's actually a U U ID object. That's not
really that important. But one thing to know is that since this is an object, I want
to call it here is to string to actually get the string version of that.

All right, let's try it. I'm going to copy this method name here and then run Symfony
PHP, Symfony PHP, then /PHP unit dash dash filter = test grade user and perfect. It
fails because we haven't actually done the work yet. You can see what we're looking
for and you can, and you can see what we have now. So to change this initially, it's
actually pretty darn easy. And it's something we've talked about earlier all the way
up on the top of the user. Um, API platform has the idea of maybe a platform has, uh,
an identifier. And when you do it, when your ENT, when your resources, a doctor, an
entity automatically assumes that your database ID is going to be your identifier,
but you can tell it to use something different. The way to do that is to add in at
API property.

And first thing inside of here, I'm going to say identifier = false. That's gonna
tell it, Hey, even though you were thinking this would be the identifier, it's not
the identifier. And down here on the actual field, we want same thing, API property.
And that identifier = through. As soon as you do that, it's gonna start using the UA
ID everywhere to do that. It's. Now, if I go over here where you're on the test
again, got it. It is not returning that you UID, but if we rerun all the tests, it's
all, a lot of things are going to fail.

Yeah. Cause it turns out we're relying on this in a lot of places. Let's see a good
example here. Okay. So here's an example of test create she's listing. When we're
sending the owner property, we're actually passing an IRI of /API /user /one, but
that's not a valid, I R I anymore. The I R I have all of our users changed. So it's
actually kind of cool to see all the different things that we need to change inside
of here. So let's start instead of use a resource tab and you can kind of search for
a /API /users /anytime. We kind of referenced that it's not going to be the ID
anymore for updating a user. We're not going to use the U U ID in order to update
that user. And down here, we are in test to get user.

If we want to make a get request, it's not going to work to get the ID anymore. It's
got to be the U U ID. And then finally down here, we're getting another one. Same
thing. Get you your ID over in she's listing resource test, it's kind of the same
/API /users slash, and then anytime we are doing that, it's gotta be, get you your
ID, get you your ID. So this time the owner property and needs, this needs to be set
to the U the IRI TRID. The IRI is now going to be the UID. So let's see down here and
change it in a third spot and down here, and a fourth spot and down here, and it fits
spot.

Okay, let's see if I got all the spots there. So we had several parts in our code
because that was a significant change, but all those changes make sense. Our eye, our
eye is different and now our tests pass. Whew. We just switch to a UID. But next part
of the point of changing to a user ID was that I said that it would be nice to allow
my API clients, like maybe my JavaScript to set the UID when it's created, instead of
you, UIB you UID being generated automatically. How can we do that? Let's find out
next.

