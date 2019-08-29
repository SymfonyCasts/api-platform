# Custom Field

Coming soon...

Our user normalizer is now totally set up. These classes are beautiful, they're
flexible, they allow you to add groups dynamically. They're just a little weird to
set up because you need to know about this normalizer aware interface and you need to
understand, um, why you needed to send it. This idea of setting a flag so that, uh,
we're not calling yourselves recursively. So normalizes are great. They do have a
little bit of weirdness with them, but we've got that now. So now we're really
dangerous because every time are you object, um, cause the job of a normal as just to
take this object and turn it into an array, that's what this data is here and return
it. So we can use this to add custom groups to the context like we have, but we can
also just add custom fields at this point. Now whenever you can, you should add
custom fields, sort of the correct way.

You should, uh, you know, add a, like we did in cheese listing where we wanted to add
a custom field called short description. So we added a get short description method
and put it in a group and boom, we have a custom field. Um, you should do that
because then it actually shows up like a real field in here. It's in the
documentation. Life is good. Sometimes you can't do that. So example right now is I'm
going to add a very not restful field, uh, to our user that whenever we finish a
user, I would add a little ease me field here that over your turn. True or false. So
if I'm fetching, uh, cause I'm logged in as good a dude, uh, or returned his me,
we'll return true. Just for this one record. We can't put this on the user entity
itself because we can't, we don't know who was logged in. We don't have access to
that. So one way to do this is with a custom normalizer. Oh, but before we get there,
I almost forgot we need to actually make this user, is it owner at work correctly? So
at the top of this, let's create a public function underscore,_score construct. We
will auto wire these security class and then I'll have all tenter go to initialize
fields to create that property, et cetera. Then down here we'll say user =
[inaudible].

Oh,

I'll say authenticated user = this->Security Arrow, get user [inaudible] and above
this off for a little help from my editor. I'll say this is either going to be a user
object or null. If the user's not logged in. I'll say if not user, if not
authenticated, user return, false. Otherwise we'll return authenticated user Arrow,
get email = = = user arrow, get email. We could actually just compare those objects
themselves comparing the emails. Fine as well. All right, so now if we run over here
and try this request again, shouldn't see phone number only on that third field, so
let's see here. Yep, no email, no email or phone number and that there's a phone
number for the one user that we are logged in as awesome. This does actually break
one of our tests. However, Ben PHP, PHP bin /PHP units.

Okay.

Most of our tests will pass, but we do get one failure failed. Asserting that an
array does not have a key from user resource test lines 66 so if you check out tests,
functional user resource test lines 66 this is actually the test where we are testing
to make sure that if you set a phone number on a user and then you make request to
it, you do not get that key back. But then down here when you become an Admin user,
we do get that key back. While I would not change that behavior a little bit, um,
because now we've changed it so that in addition to an Admin user, the owner is also
gets back the phone number and here since we are logging in as the same user or
logging in as a cheese please an example of that common. Then we're also fetching
that same user. It is actually returning and phone number field. So what we can do
here is let's just change this to create user. So we are going to create that same
user and we are going to fetch that same user, but we're not gonna log in as that
user. Instead we will

great user and login a different user.

Great.

Authenticated at example

that kind of fit

so that you were fetching cheese please. An example of that. Com we're logged in as a
different user so we should not see that phone number field. So that should make this
pass on the test again and above definitions. Yes, we got it. All right, so let's go
back to adding that is me field in the user normalizer it's actually really simple.
You can see this data here is an array. So we're free to add whatever fields we want.
So up here I'll, I'll set a variable called his owner. I'll set that to the
this->user is owner and I'll use the is owner variable if statement. Then down here
we'll say data Lasker girl racket is me,

people's is owner. That's it. Swing back over here. Try this end point again and
that's it. There we go. Is me False? Is me false and is made true. So that's a super
powerful way to add fields. The one thing you need to realize is that API platforms
documentation has no idea that that exists. So it's not going to document that at
all. It's still going to say that you know, the only things, well, that's actually a
refresh this page. If you refresh this page and go over the documentation for that,
it's not going to have any idea that that is me. Field is being returned. So that's
kind of the price you get for the happiness.