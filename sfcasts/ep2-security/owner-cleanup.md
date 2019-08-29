# Owner Cleanup

Coming soon...

Right now when we create a cheese listing, the owner property is uh, is of course
rideable has the cheese calling right crew, which means it's actually something that
we can

[inaudible].

It's actually something that we can send when we create the cheese listing, but right
now technically it's also something that we could change would be the put and point
because this is simply a allowed on both operations. We even add some fanciness where
when you create or edit achieves listing, you can actually modify the username
property on user. Check out our last chapter for this, our last tutorial for this,
but because this has the cheese listing calling, right? Actually that should be
cheese calling, right? I missed that one from earlier when we are renaming our items
because we have this cheese calling, right?

That actually cascades down and allows us to when we uh, are passing you on or we can
pass an object where the username is changed and actually change that using that
user's username at the same time that we are creating or updating a cheese listing.
So kind of crazy. We're going to simplify this now a little bit. I want to say that I
only want the owner property to be a added. When we create the cheeses thing, I don't
want it to be updated and I'm also going to remove this kind of extra fanciness of
being able to update the username. So I'm just going to move the cheese colon right
from username and user. Then I can remove the assert /valid which we had to have to
make sure that the username that we were changing to is valid. And here for cheese
colon, right.

If you remember from our, from our auto group resource metadata factory, this monster
we created, we actually already have something set up where you can um, uh, where you
can use a special group name if you want a operation, if you want a a fuel to be
accessible only for a specific operation. So to make this something that we can only
send on the post request, we can use cheese, colon collection, colon and post that's
using the a the last one down here at cheese and then item or collection and the
operation. And Post. Cool. So now we can, now the owner field is not editable. Now
for setting this field, when you create a cheeses listing, we really have two
options. The first option is we could add something in our code so that when you
said, so that you don't need a pass the owner at all, that when you discredit a
title, press and description, we automatically see who is authenticated and assign
the cheese listing to the currently authenticated user. That's actually a pretty nice
feature. Would make your API a little bit like fairly user friendly for most cases.
Um, it's not perfectly restful, it's maybe not as restful,

but that is a valid way to do that. And you could do that with a custom data
persister like we already have for our user or you can implement an event listener to
do that, which is something that we'll talk about in the next tutorial. So that is
something that you can do, but I'm going to kind of keep it the a bit more than
manual way. I want to continue to, um, force my user to pass the owners string when
they post a cheese listing. It's just a matter of flexibility by forcing the user to
pass this, we could it.

And part of the reason is because I later maybe I want to create an admin interface
where I allow admin users to assign the owner to different users. Maybe we're
creating cheese listings over the phone or something like that. So I want to keep
owner as part of my API. Of course, if we allow the owner to be sent, well the
problem is that we can't just allow anyone to create a cheesesteak and say that
someone else owns it. This is actually the first time where the data itself can be
valid or invalid based on the user. The way to fix this is actually via validation.
The topic of validation and security end up being very, very tight. Some people,

we could prevent this operation entirely or prevent this field from being set
entirely at via security. But if the field itself has some valid or invalid data,
even if that validated, it depends on who's logged in, that needs to be done via
validation. So first let's actually right, I'm going to update one of our tests, uh,
to kind of show the functionality we looking for. So I'm going to go to open our CI's
listing resource test and inside test create listing. All we're doing right now it's,
we're basically uh, verifying that you do need to be logged in to use the end. So if
you posting a four oh one down here, you get a 400 status code because of the
validation error. But after you log in, you do have access. So let's actually extend
this a little bit first. I'm actually going to set this a set, a new authenticated
user variable equal to the user that we're logging in as I'm going to create another
user a year other user.

Cool, cool.

He pulls this Arrow, create user for other user@example.com password Fu and, and then
down here, let's create a new variable. I'll call it cheesy data. And I want to set
up some valid data for creating a cheese listing. So I'll set a title two mystery
cheese kind of green description.

Okay,

and a price. These are the three required fields other than owner? No, I'm here. I
want a copy of my client error request and my assert from earlier and we're going to
do on here is instead of passing an empty JSON, I'm going to pass that cheesy data
but I am going to pass an owner but I'm going to pass other and I set the owner to
/API /users /and then the other user->get id

and then down here I'm going to start that. This is a foreignness task code and just
as a little message air message to make it obvious what's failing here. I'll say not
passing be correct owner. So if you look what's happening here, we are logging in as
CI's pleased as an example@example.com. But then down here we're trying to create a
cheese listing and say it's owned by a totally different user. This is actually the
behavior. This is the thing that we want to prevent. Now, while we're here, I'll copy
of these two lines again and change other user to authenticated user. And we want to
say, Hey, if you do this, if you actually passed the owner to yourself, this should
be a two oh one that status-quo. That's the happy case. So let's copy the method name
here and test great Jesus' name. I'll spin over PHP bin /PHP unit dash dash filter =
test create cheese listing. And yes, it fails. Check this out. Failed asserting
response subset as code is 402 oh one created. So we actually were able to, um,
create this cheese listing. You can see the failures online 39, uh, instead it to the
wrong owner. So next, let's create a custom validator to prevent this.