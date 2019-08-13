# Validation Groups

Coming soon...

You don't know, we're missing some validation right now with our new setup. Like if I
sense a empty posts requests up to /API, /users, I get a 400 air because I'm missing
the email and the username. But look, there's no violation here for password. We
forgot to put validation on our password field. So no problem. We know that the
password field in our API is actually called plain password. So here we are going to
add at a certain /not blank and good. Now it's required and I can even execute that
immediately and now we see that the password field is required. Um, but you may have
noticed a problem. This is gonna make the password field required. When I edit a user
to, if I edit a user and I only want to update, for example, the username, that's
going to be problem because that play and password field, it's going to be blank. And
it's actually getting me a validation error. Let's actually fix this. But first I
want to write a test for this just to make sure that we really keep things solid. So
in use resource tests, let's make a public function test update, user

client = cell phone cone pre-client then and then to update the user, we're going to
need to create a user. So I'll say this, air create user and logins will do both of
those@thesametimeonwhenyouusemynormalcheesepleaseatexample.com password food, Huh?

Perfect. And now we'll make the client error request. We'll make the put request to
/API /users /and then user->get id. And then for the data we'll just send, let's just
say like brisk, pretend like we're updating or use your name. So I'll say user name
to new username. Cool. That is a completely valid thing to do. This should give say
200 status code. So I'll say this era cert response is successful. That's actually a
kind of a more generic way just to check for a 200 level status code. And then one of
the other astrick assertion, which is really cool is you can say assert JSON contains
and you can give it a subset of the fields that you expect to be in there. So one of
the fields that we expect as we expect it to be user name field and it should now be
set to new username.

That's not going to be the only field that we get back, but that should be one of the
fields that we get back. Cool. So let's try that. I'm not copying test update User
Ben Page of unit change that Dash Josh filter to test update user and you look
perfect gas, it fails. You can say we get back 400 requests, bad requests because
we're getting this value should not be blank on passwords of updating. All right, so
how do we fix this? The answer is with validation groups. So one of the things that
you can do, and you don't need to do it too often, but this is a perfect case for it,
is whenever you have an assertion here, you can actually add a groups = option and
you can put that mellish and into a group. Now these group names are just kinda like
the um, similar to like the normalization groups. You just kind of make up their
name. And I'm not going to get too technical with this. I'm just going to say I'm
gonna put this into a group called create. This is something that should only be used
when I create a user object.

Then now by default, when you validate something, whenever you have a, an, an assert
like a certain not link, all of these assertions go into a group called default. And
whenever you, um, s validate something, by default, it's going to validate all the
constraints inside of the group called default. So now we want to do, because as soon
as we make this change, if we rerun our test now, it's actually went to work. Yeah.
Because our put endpoint is only running things in the default group. The problem now
is that we have means that our post endpoint is also only running things in the
default group. So this is actually, this constraints basically never been used. So we
want to say is on the collection post collection operation. We want to validate the
default group and the create group. So I'm gonna break this access control down on
the next line here just for readability. We'll add a comma and that one we can do
here is we can say validation under score groups = and then set this to an array.
Instead of here we'll put default cause we want to still run the normal validation
constraints. And then we'd say create.

All right, so let's go back here. And this time what I'm gonna do is I'm running a
PHP had been slashed of unit and then I'm going to run actually the entire tests,
functional user resource test that PHP files from to make sure we check both create
and [inaudible] and points and scroll up. Yes, they both work. So validation groups,
something that occasionally you need. That's how you do them.