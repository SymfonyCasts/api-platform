# Validator Logic

Coming soon...

We're working on adding some pretty complex validation rules around who can publish
or unpublish achieves listing and to get this logic, right. What we really need to
know is how the is published field is changing. Like, is it changing from false to
true or true to false, or maybe it's not changing at all. And there are some other
fields on cheese listing that are actually being changed. Okay. And Hey, we know how
to get the original data via the entity manager, just like we did in the cheese
listing a data. Persister by the way, if your API resource in our case, cheese
listing is not an entity, which is totally allowed and something that we'll talk
about later, then you can get the original object by injecting the request, stack,
getting the current request and reading the previous underscored data attribute. If
that attribute is not there, then you know that your object is being created.

All right. So let's get the original data just like we did before. So I'll add a
public function._underscore construct. We will add one argument and city manager
interface and city manager, and I'll do my normal trick of hitting all to enter and
going to initialize properties to create that property and set it now down here, we
can say original data = this->and city manager->get unit of work, arrow, get original
entity entity data, and we'll pass it value. And below let's just DD that original
data. So we can make sure that that's working. Then I'll spend over here and rerun
our test and awesome. Got it. So that same array that we saw earlier, and you can see
that is published, is false on the original data. Awesome. Okay. Let's remove the D D
and we don't need this Knoll check. You do need that when you add validators to
fields, that might be no, but because we added ours above the class where either
we're always going to have a cheese listing object let's we don't need to add that.

So, first thing I want to do is let's actually get the previous is published values
like previous is published = original data, left square bracket is published.
Question, question false in case it's not set. So that's the same thing that we did
in our cheese listing data per sister. Now, the first thing I wanna do actually
inside of here is, uh, make sure that we do no, we don't do any validation, any
special validation if the is published, didn't change. So if previous is published, =
value,->get is published, then we don't need to do any of our special is published
logic. It's not changing. We can just return.

I'll put a little comment above that. Alright, so let's handle the first real case
inside of our test. So the first case here is that you cannot publish an owner, can
not publish with a short description of the description of less than a hundred
characters. So down here after the first, if statement will say, if value,->get is
published and immediately inside this, if statement, we know that we are publishing,
we're published, and we know that the value of the publish changed. So we are
publishing right now. So if STR Len of value->get description is less than a hundred,
then I'll actually copied this bill. Val violation logic up here from a copy from
down there and paste it up here. We'll say this era context or bill Val violation.
You notice this is reading constraint, error message. That's coming off. The valid is
published. This allows you to customize the message when you use your annotation
right here and the options. We're not even going to use that. I'm just going to
hardcode all this stuff in the validator. Cause I am not designing this constraint to
be used in multiple places. So we'll keep it simple and just put the message right
here. I'll say cannot publish description is too short.

We don't need a parameter. This is a little wild card in case you have like a good
little curly curly value inside your message. But I am going to add a little at path
description. That's kind of a nice thing. This is going to make it look like this is
failing on the description field specifically, and then down here in either case
we'll return, we don't need to add any more publishing, any more logic, any more
validation, logic. All right. So if we run this test, now you can see it fails, but
check us out. It's failing now on admin can publish a short description. So if you
look over at our cheese listing resource tests, it actually has passed our first
condition. Yay. That is returning a 400 status code. And down, down here, it's
failing because when we log in as the admin user, it is also returning a 400 status
code. Alright? So our next condition is if the user is an admin, then we do want to
allow you to publish, even if there's a short description. So to do that in our
validator, we're gonna need to know we're gonna be able to, we're going to need to
use the, is granted function, which we know comes from the security service. So a lot
of second argument is the constructor security security. I'll go initialize those,
that property.

And then down here, right before we do our check inside the if statement, we'll say,
if the length is less than a hundred and Oh, I totally messed that up and got lucky
that it passed Silly PHP. So say if the length is less than a hundred And not
this->is security,->is granted role admin. Then we actually want to, uh, add the
violate violations, all thing up here. So there's, don't allow short descriptions
unless you are an admin. All right. So test driven development here. Let's see if we
can get past this failure. Why don't we run our tests? Now

It fails,

But now you can see it says normal user cannot unpublish. So if we look back on our
test here, you can see, we actually are passing this case. We're actually passing the
third case. And now we're down here to a normal user. Can not unpublish by the way
you notice, I created this kind of giant test method where I'm kind of going through
this entire process. It cleaner way to do this would be to break all of these
different tests down into their own test method. It would make it a lot more obvious,
which one of these is failing. I'm being a little lazy by combining them, but in your
project, you can choose to kind of do a better job than I am.

Alright,

So let's add the unpublished logic right now when we unpublish, it is actually
allowing the user to do that and returning 200, okay. Status. But really we only want
to allow admins to be able to do that. So down here at the bottom, because we have
this return statement here and we checked to make sure that published is changing. If
we get down to the bottom of this, we know we are on publishing. So we'll say, if not
this->security->is granted rural admin. If we're not a real admin, this is not
allowed. So a copy of my context, bill violation from earlier in this time, we will
say only admin users can on Polish, or maybe you don't even want to give that many
details to your user. It's up to you. And I'm gonna remove the app path this time.
This has nothing to do with the description. So this will make it look kind of like a
global validation air. That's not attached to any specific field.

Alright, let's try the test and yes, got it. So thanks for our validator and the
original data. We're just able to write the exact logic that we need. Now, when we
set this up, we chose to use 400 validation errors, which is really nice because the
user gets this 400 status code, and then they get the actual description of what went
wrong. Like you can not publish because your description is too short. Now, if we
wanted to, we could have returned a four Oh three access denied instead, which might
especially make sense if a normal user tries to unpublish maybe we want to give them
a four Oh three. How would we do that? Well, one of the really cool things about the
way Symfony is architected is that even though we're in a validator, we are free at
any point during our request to throw an access to not exception.

So literally right here, I can say, throw new access, denied exception, make sure you
get the one from the security components. And I'll say only admin users can unpublish
and that's it to actually see that working, this will make our test fail, but we
should be able to see what the response looks like. Yep. There it is. Giant thing
here. You can see four Oh three for bin that is totally up to you. I'm going to
comment that out and kind of keep my validation logic, but you can do whatever you
want. All right. So we now have a restful way for the user to publish and we can run
a custom logic. Like I said, in the part for this tutorial, we'll talk about other
ways to do this, like using the messenger integration or creating a truly custom end
point, but I really, really liked this solution. So next I want to talk about
something slightly different. I want to talk about custom fields inside of your
entity in previous tutorials. How can we add a completely custom field? That's not in
our entity and maybe even a custom field that requires a service to Calgary. That
value. We actually did this in a previous tutorial, but I want to take it to the next
level by adding the field in a way where it actually will show up properly inside of
our API documentation. That's next.

