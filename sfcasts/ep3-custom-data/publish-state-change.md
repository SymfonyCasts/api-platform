# Publish State Change

Coming soon...

Here's the plan. Whenever a cheese listing becomes published, we need to create a
notification that is sent to certain users. Maybe some users have subscribed to hear
about all the latest new cheeses to help sort of fake this system. I've created a
cheese listing notification entity. The goal is to insert a new record in this table.
Whenever achieves listing is published, it's not a real notification system, but
it'll help us figure out how to run custom code in just the right situations. Let's
start in the test. So we want to assert is after we send the publishing, there is
exactly one jeez listing notification in the database. Now, first, thanks to Foundry
the fixtures library that helps us insert the data very easily in data fixtures and
in tests before each test case, our database starts empty. So that will make it very
easily to count the number of rows inside the cheese notification table.

And thanks to another cool Foundry feature to do this task. If we can simply say
she's listing notification she's notification factory, colon, colon repository, to
give us this special repository object, maybe by fact Foundry->assert count one. How
nice is that? That will help us check that there is exactly one row on the table.
I'll even give this a little message in case of failure, there should be one
notification about being published. Perfect. Now, before we try this and make sure
that it fails, what's that a bit more, I'm going to copy the put call from up here
and paste it down here because we want to make sure that if we publish the same Jesus
thing, again, it shouldn't create a second notification because we're not really
publishing it again. We're not doing anything with this second call, so we can
guarantee that by doing the same thing, she's notification factory, colon, colon
repository, arrow, assert count one.

There should still be just a one in there. All right, let's try this spin over and
run just that test, published cheese listening test and got it. Failed. Asserting
that zero is identical to one. There should be one notification about being
published. No surprise there. All right. So how can we do this? Well first, because
we need to run some code right before in API resource CI's listing is saved. We need
a custom data, persistent, persistent, no problem. We have done this before with the
user data persister so we'll just repeat that same process. So I'll create a new PHP
class, call it cheese listing data per sister.

We'll keep this as simple as possible. I'll implement data per sister interface, and
then I'll go to code generate or Command + N on a Mac, go to "Implement Methods" and
implement the three methods that we need. Now, before I actually fill in the code,
let's immediately decorate the doctrine data per sister, so that we can call its
methods to actually do the saving. So I'll make a public function constructed with
data per sister interface, decorated data per sister. Then I'll hit enter and go to
initialize properties to add that property and set it in a constructor. Now, down
here in supports, not on here in sports, actually, before I do supports to make sure
that the correct data per sister has passed in here, I bought a config services
dynamo, and we can actually copy the user data persister service and change it to
jeez listing date of her sister, because we need to do the same thing. We're going to
bind the decorated data per sister argument to this exact doctrine data per sister.
We want that one data per sister, not the entire data per sister system. All right,
back in Jesus and data per sister in supports, we can say return data instance of
jeez listing. So it T's listing as being saved. We are now in charge of it and a
persist. We can use the decorated, this arrow, this->decorated data, persister arrow,
persistent data, and then down in remove this->decorated data, persister->remove the
data.

So congratulations, we've just graded a data persister that does the exact same thing
as the, as before we can prove it by running the test to see that the end point does
a function, but we still are failing because we don't have any notifications. Okay.
So now in persist, the key question is how can we detect if the item was just
published? Well, first I'm going to add a little bit of peach doc up here to help my
editor, help my editor know that the data is going to be a cheese listing object. Now
here, the easiest thing to do is to say, if data->is published, then we're published,
right?

Well, I'm sure you saw the flaw in that that would tell us that it is published, but
not that it was necessarily just published and may have been published a week ago. We
have no idea what we really need is access to the original data. The way it looked
before it was changed by APM platform. If we had that original data, we could then
compare that with the current data and determined that the is published field did
change from false to true, but how could we do that? Actually with doctrine that's
totally possible to do doctrine keeps a track of the original data that it got when
it queries the database. So to get this, we need the entity manager plus add a second
argument that constructor and state manager interface and city manager, I'll hit all
to enter and go to initialize properties to create that property and set it. And then
down here, we can say original data = this->and city manager->get unit of work. And
it's a core object and doctrine that does the deepest darkest stuff, and then say,
get original entity data and we can pass it data. How cool is that? All right. Let's
dump that original data so we can see what it looks like.

Move over, rerun the test and all right. Cool. Cool. Cool. Let's see here. Okay.
Awesome. It's an array actually, where each field is the original piece of data. So
you can see here is published set to false. So that's the key thing we can use. All
right, now we are dangerous. I'm gonna remove that dump. And I'm going to add a new
variable called was already published, set to original data. Lesker bracket is
published. And then question, question, false, because if this was actually a new
entity than original data could actually be an empty array. And if this is new and
there's no is published key, then of course it's published defaults to false.

Now down here, we can say, if data->get is published and it wasn't not was already
published. Now we know that we were just published. So let's create a new cheese
listing and your notifications say, notification, new cheese notification. And this
class has a little constructor to make life easy. We can pass the cheese listing. So
that's data. And then the notification text, I'll say cheese listing was created. And
boy, we're here. We need to say this to the database. So I'll say this->entity
manager,->persist notification, and then this->and the manager arrow, lush, what the
hell?

And yes, you could skip the flush here. That section, nobody is going to save the
cheese listing or right now, because we know the flush is going to happen and the
parent data persister anyways, let's try this move over on the task and yes, got it.
So this original data trick is really the key to doing custom things on a simple
restful state change, like is published. False two is published true, but next let's
add some complex rules around who and when a listing can be published, like only the
owner can publish the CI's listing, but maybe they can't publish it unless the
description is longer than a certain length, but maybe an admin can publish always
that's typical. Awesome, confusing business logic. How can we enforce those rules in
API platform? Let's find out.

