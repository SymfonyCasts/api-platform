# DTO Quirks: Embedded Objects

Coming soon...

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
