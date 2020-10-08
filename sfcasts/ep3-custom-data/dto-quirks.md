# Dto Quirks

Coming soon...

The last deal. I feel that we're missing on our new cheese listing output is actually
the owner property. So no problem. Let's add it over in Jesus and output. I will add
public owner and then I'll copy the PHP doc from price paste that here. And we know
that this will be a user object Pope, and we'll put it in the cheese, gone regroup
over in our data transformer. Well, make sure to populate that output->owner = jesus'
thing,->get owner. Alright, easy enough. So if we move over and refresh that works,
you know, have an owner which is embedded because which is, which is embedded. And
the reason it's embedded is it has this phone number property. Now that is actually
important. As a reminder, if I go into the user class and look at the phone number,
this is actually inside of a group called owner, read an admin read the reason where
I should have seen that as we're logged in as an admin right now. So we're actually
able to see the phone number on every single user. So actually I'm going to open a
new tab here and go to the homepage and now hit log out. Perfect. I'm now logged out
and then refresh this same endpoint error, interesting return value of user
normalizer. This is a class that we created in a previous tutorial return value, uh,
must be type array, string returned.

So let's go check that out. This is in source serializer, normalizer user normalizer.
As I mentioned, we created this in the last tutorial and the purpose of this is
actually to add that extra owner, colon read group. If the, if the current user
object is the same as the authenticated user. Now the problem that the error is
talking about how this is returning a string, but that, but it's supposed to return
an array. And actually it's true. If you look at my normalized method, I have an
array returned type on there, but apparently when I call this arrow, normalizer
normalized object. This is returning a string. And that actually makes sense. As I
mentioned a second ago, the phone that number field is only returned when, uh, now
that we're anonymous, the phone number field is not going to be returned as an
embedded object anymore.

Which means that the user is as she should be in, I R the owner property should
actually now be an IRI since there are no embedded fields. So this actually makes
sense. If you normalize a user object, sometimes that's going to be a object, but in
this case, it's actually going to return a string IRI. So the fix here is actually
just to remove the array returned type that was actually never needed. I just put
that because I was returning an array, but really we know that this will not return
an array or sometimes a string. So when I refresh, you'll see what I mean, owner is
now, there we go in, I R I string, but wait a second. Why didn't we have this error
before? Because before we started doing all of this output stuff, this is what our
cheese end point look like.

And owner was set to a string in this case. So why didn't that cause an error in our
normalizer until this second? Well, the answer is that when you use an output class,
like we're doing she's listing the client, your class, that's serialized, isn't
serialized in the exact same way as before. There are some subtle differences that
you need to be aware of. For example, one of the differences is that when you use an
ALPA field, you're at type is gone. So we have an ID and all of these, but we don't
have ad type anymore. This is probably the biggest downside of using DTRS. There are
these subtly different things, and sometimes it's just not clear if these are on
accident, or if they're on purpose, serialization is complex. So let me show you a
couple of these things. First, find a terminal and let's run it and let's run our
tests, Symfony BHB bin /PHP unit.

And we get one failure because actually in one of our tests, we were looking for the
ad type to come back on cheese. So this is coming from cheese listing resource test.
So open tests, functional cheese listing resource test. And then this method down
here is test to get Jesus and collection. There we go. And if we scroll down a little
bit here, you can see I'm actually detesting that I should get back at type cheese.
We're not getting that back anymore. I'm actually going to delete that so I can get
my tests pass. Now, why is that at type missing? And that is actually an example of a
bug with the APA format. And that bug has actually been fixed and will be included in
API platform, 2.5 0.8. So 2.5 0.8. You should see the at type there again, but since
I'm not on that hasn't been released yet. I'm going to remove it from my task. And
now my test pass. Perfect to see what other kind of quirk go to /API /users that JSON
LD, this is actually going to tell us that we need to log in. So once again, I'll go
to my homepage and then just hit log in here. Perfect. I'm logged in. I can close
this tab, refresh this again. All right. Now it's a little bit subtle, but check out
the embedded cheese listings. That's not right. It's actually, it's embedding the
cheese listings, but only with ad ID. Okay.

The reason that's not right is if, as we know, if there aren't any fields that are
actually going to be embedded, then this should actually be an array of IRI strings
instead of an array of embedded objects. This is a bug in how the readable link for
properties is calculated when you have a DTO. So specifically in the user class, if
we search for gift published, jeez listings, this is actually the method that has
given us the cheeses and his property. And because she's listening is using a DTO, it
calculates the, uh, calculates the, it doesn't calculate the readable link correctly.
So of course, one way to fix this is to just force it. So for example, I could say at
API property, and I can say readable link = false. So now when I go over here and
refresh, that will kind of force it to do the I R I strings. So let's just another
spot and you need to be aware of now, actually in this case, uh, I am going to
actually remove this originally, before we started all this output stuff, we were
actually embedding the jesus' things into user and including a couple of the fields.
Let me show you and she's listing. Okay.

Uh, if you go down to the title property, the title property, I actually had user
colon read on it as did the price user column read. We did that because we actually
wanted the GS listings, those two fields to be embedded, um, when we're serializing a
user.

Okay.

So the reason it wasn't being serialized is we have, we forgot to actually add these
to this group, to our and price fields inside of our new cheese listing output. So
I'll go in here, I'll go to title and we will add user call and read the title, and
then also add user call and read to price. So regarding now, refresh, that is
actually the way it looked before. We do want this to be embedded and we're embedding
these two properties. So, Hey, we switched to an output DTO. This is now the exact
same app that we had before. There's a couple of little quirks along the way, but
overall it's a really, really clean process. This class looks exactly only holds the
fields that we, that we actually want to, uh, output our API and move this very
straightforward data transformer to convert from achieved list and gap. But so let's
celebrate inside of our cheese listing entity. We no longer need to have anything
related to serializing, uh, these, uh, these fields, because this object is no longer
being serialized to show to the user.

So I'm actually going to search in here for colon read, cause that's the name of the
group that we've been using. And we can basically take off, jeez, calling read off of
the title, property and user colon read, um, keep the right, uh, groups right now
because we are still using this as our input format, then down on description and we
can remove ad groups entirely for price. I'll remove those two groups or three
groups, and then I can move cheese and read from owner. And then finally down here
and get short description. We don't need this method at all. Of course, if you're
calling this from somewhere else in your code, you'll still need it, but we're not.
So I'm going to remove it. And then same thing down here and get created at ago, we
can remove that entirely. So that's the nice benefit there is. We can actually slim
down this class to be a little more focused on just, uh, just being an entity because
all of our serialization logic is in a different class. So just to make sure I'll
move on, I'll move over and refresh my user's endpoint looks good.

Let me go over here and refresh user's end point, ah, dress. This actually changed
again. So it's kind of that same problem. I'm talking about what the, the, a readable
link, not being calculated correctly. Now that I've removed those groups from my
Anstey and now thinks that no items are going to be embedded on cheese listings. Even
though we do have a couple of properties over there that are, uh, that are correct.
So I'm actually going to go back to user and we're going, gonna re add that at API
property this time, I'm gonna say readable link = true. Cause actually we do want to
force that as an embedded object. So when I refresh again, that's back to an embedded
object and we'll just go over /cheeses here. This looks good. And finally, let's just
run our tests one last time and they do pass. So you just gotta be a little bit
careful. Uh, it's just not going to work exactly the same as before, and as long as
you have an eye out for it and good test coverage, you're going to be okay. Next
let's talk about input DTS.

