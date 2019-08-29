# Custom Validator

Coming soon...

Here's the situation, all authenticated users should have access to create a cheese
listing and every authenticated user should need to be able to pass the owner field.
But the data passed to owner field can be valid or invalid depending on who you're
logged in as. I'm supposed to set this to my own user, I r I, if I try to set it to
somebody else's to a different owner, that should be denied except for maybe if I'm
an Admin user, which we'll talk about later. This is what we just showed in our test
here. We tried to post and create a cheese listing and send the owner to this other
user. The problem is that we're actually logged in, uh, as this authenticated user.
So we're logged in as chiefs, we as example.com, but we're trying to set this owner
to this other users, IRI, that should give us a 400 status code. It's actually a
validation error. But right now if we run our tests right, but when we ran our tests
a second ago, it's actually allowing this. So the way to prevent this is via a custom
validator. So find your console and run bin Console. Make validater. We're gonna
print a new validated called is valid owner.

If you're not too familiar with validators, every validator has two classes. Let's go
check them out inside the source of validated directly. The first one actually
represents the annotation we're going to use and it usually contains, and it doesn't
contain anything but just options that you want, options that you want. Um, a
configurable on your annotation. We'll talk more about this in a second. The other
one, which typically has the same name plus the word validator is actually what will
be called during validation. So here we are going to be past the, we passed the
value, we can run it whatever business logic we want on that and we'll actually set a
violation, a validation failure, um, uh, if that fails. So to see this in practice,
the way this is going to work is inside she's listing the property that we want to
validate is this owner property. We want to make sure that this owner property is set
to a valid owner based on the currently authenticated user. So this is actually where
it will add at is valid owner and right here if we wanted to we could actually
customize that message. Uh, uh, any public properties that you have on this, uh,
class, um, it can be customized as options of the annotation.

In fact, this message one, which we too typically have, let's actually change this to
cannot set owner to a different user. Now the way this works is now that we have,
when the [inaudible] listing object is being validated and thinks to this is valid
owner, the validation system is going to automatically call our validate function on
that validator. It's going to pass us the value which is going to be the value of
this property. So it's going to be the user object and it's going pass us the
constraint. That constraint is actually an instance of our annotation class. So is
valid owner no notice the first thing it does is actually checks to see if the value
is sort of empty. And if it is, it does not add a validation error. I want to talk a
little bit, we'll talk more about that in a second. But it's actually what a
different annotation to make sure that this property is actually set.

So just to see if this is working, let's not add any logic. Let's just add a bill, a
validation error in every situation, ms set parameters, a way to fill in like a wild
card value. If you wanted a something like that inside of your message, we're not
going to take advantage of that. So I'm actually just going to get rid of that set.
And so are basically saying here is I want to build a violation, a
constraint->message is actually going to be the message for that violation, which is
just going to be this property right here. Okay, so if we move over, let's run our
test again.

Okay.

And if we scroll up, awesome. It is failing and it's actually failing. If you look,
it's getting back a a 400 bad request and it's saying cannot set owner to different
users. So you can see that's actually where that a validation message shows up. This
is coming from cheese listing and resource task line 44

yeah,

so basically down here once we lock, once we get our setting into a valid owner, it's
of course setting a validation error in that case cause right now our validator is
always failing so now we need to do is actually make this smarter. So for us to do
that we're going to need to know who's logged in som to add the public
function_underscore construct and will on a wire our usual favorite security class.
I'll do the [inaudible] enter initialize fields to create that property and set it
for the logic itself. We'll start down here and say user = this->Security->and kit
use it. Now that user like they might not like in Fieri, they the user might not even
be logged in. That's not possible with this particular post endpoint, but just for
the sake of writing, making our validator tight, let's say if not user instance of

user,

then let's actually add a violation here and I could hard-code the message but let's
create another configurable message over here called anonymous call. CanNot set owner
unless you are authenticated.

Then over here we can do the same things as down there because if this->context->bill
violation, this time we'll use constraint, err on anonymous message there, add
violation and then we'll return so that this function doesn't keep running and just
returns without one validation error. Now the other thing we know that the value
object here should be a user object cause we're expecting this is valid owner
annotation to be set on a property where the value of that property is the is it user
object. But you know we could accidentally, it's possible that we might actually put
this on some other property. So let's just add a sanity check here. Let's say if not
value instance of user and here it's not a validation error. This is a programming
error. So I'm going to say throw new invalid argument. That exception with app is
valid, owner straight must be put on a property containing a user object.

Finally, we know we have a user, we have user and value are both user objects. So we
just need to compare that. So if value good id does not equal user->get id, you
probably could just compare those value directly to user. But ID is fine. Then we'll
actually have our violation that reads off our normal message property from our
constraint class. And that should be it. That covers all the cases. So let's switch
back over, run our test and it passes. Perfect. Okay. Now a second ago I kind of
pointed out that this validator starts by checking to see if the value is basically
not set. You know, if then this would happen if somebody forgot to pass an owner
property entirely. Nydia is that the validator itself as a best practice, um,
shouldn't throw up a validation error on this, on that we actually want it to be
required. We should set that with another annotation. And actually I have [inaudible]
hmm. No I need to scratch all that cause I did not order kill it all from it now a
second ago. So the nice thing about having a the owner property be something that is,
is still in our API is as I mentioned earlier, we might in the future have an admin
interface where maybe an Admin user can set the owner to anything. And we can put
that logic right into our validator if we want. So we already have a security object
auto wired in here.

So once we verify that the user is actually logged in, we can say, well if this->is
granted, this->Security->is granted role admin, then we're just going to return. This
is going to prevent us down here from actually checking to make sure that the user
has a role. So that's just the power of things you can do with the validator. Now one
thing I mentioned earlier is that a typically in validators, you'll first check to
see if the value is empty. And if it is you won't do anything. So this can happen
right now if somebody made a request and forgot to set the owner property. And the
reason we do that is we typically say that, hey, if you actually want this to, uh,
this value to be required, you should put that on your, um, you should actually add a
not blank annotation to above that property itself.

And we actually haven't done that yet. And that, which is actually a smell of a bug
in our application to make that obvious. If I go to a cheese listing resource, let's
actually move this cheesy data, oh, align here and you remember this original request
here, we log in as cheese, please, example.com and we're just sending an empty data
and making sure that we had access with a 400 status request. Well now I'm actually
going to add that cheesy data here. I should still get a 400, uh, uh, response and
I'll even add a [inaudible] test to failure and make this a more obvious, um, because
we should still be missing the owner field. We should get a 400 area here because
we're not passing the owner.

But if you go over and run the test, because we're missing the annotation, we
actually exploded. They 500 error. It's actually having an error because it's trying
to insert that into the database. But the owner property is, the owner id column is
requiring the database, so that's an easy fix, flat at not blank. This makes sure
that the, it's a valid owner if the owner is set and then we leave, uh, have and not
blank actually make sure that this property itself is set to the test. Again, we are
solid. Next, let's do something with filtering collections. Based on that. She us in
published.