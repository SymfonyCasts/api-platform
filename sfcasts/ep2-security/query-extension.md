# Query Extension

Coming soon...

The chief listening entity has a property on it called is published, which defaults
to false. This is not a property that we've really talked about at all yet, but the
idea is that when she's listing as first created, it will not be published. And when
the user is finally ready, they will publish it and then it will be available
publicly. So this is an interesting case where we really want to do is automatically
filter the collection of cheeses. We don't want them, we only want this collection of
cheeses to return published cheese listings. Not all cheese listings. [inaudible]
first let's codify that in a nice simple test down here and Jesus thing resource
test. We'll do public function test, get the cheese listing collection, then we're
just going to get to work. Basically we'll create a, we'll create our client, then
we'll create a user with this arrow. Create user email and password. Don't matter
here. And I'm not logging in because the uh, collection end point doesn't actually
require you to be authenticated. And then I'm just gonna create a couple of cheese
listings.

[inaudible]

setting the owner to that owner and the rest of the details don't really matter. So
I'll create a cheese listing one.

They use that to create

hey, cheese listing too, and cheese listing three. So nice boring objects being
created at the bottom here. Let's grab the entity manager with m = this entity
manager and then we will persist those three objects and then flushed them. So that's
our perfect setup. Now, right now, because we don't have any logic to hide the right
now by default, all of these cheese listings are going to be published false, because
that's the default value. But we don't have any logic to hide them right now. We're
just returning all the cheese listings from this end point. So let's, let's at least
let's start by testing how it currently works. So I'll say a client error request
might get get request to /API /cheeses.

And then to assert, we're gonna assert that there are three results. So if you
actually go and try our end point over here, you can see that thanks to hydro, they
actually, this actually has a hydro colon total items here, which shows you how many
total items are being returned. So we can say this Arrow, certain JSON contains,
we'll say that hydro total items should be three on the response. So let's copy that
method name. We'll run over and do PHP bin /BSB unit dash filter = a test to get that
cheese listing collection and it passes no surprise. Okay, so now let's make this
work the way we want it to. I'm going to allow cheese listing one to stay and not to
published by let's say cheese listing, two->set is published, true. And then we'll
say non here we'll paste that changes the cheeses in three and also publish cheese
listing three. So it's two and three should be shown. One should be hidden
automatically. So let's change the total items on here to be two. All right, so how
can we actually make this happen? There are few options. In the first tutorial we
talked about filters. This is the idea

and where

you can't, and we actually have a filter on here, which won't really make sense
anymore, but you can, uh, if I try that filter, you'll see that right now we're
allowed to have a question market is published = true. That's fine. When you want the
user to be able to opt in to seeing published your = published possible. We don't
really want the user to be able to opt in yes. That any anymore. We want to force
them to only see publish. True. So filters are good when the user wants, can have a
choice now, so good when you need to force something on them. So another option is a
what's called a doctrine filter, which is something we talk about in our doctrine
queries. Then that's a way to modify a doctrine query on a global level on your
application. The downside of that is that doctrine filters are a little bit magical
because they literally,

mmm mmm.

Change every single query you make to a particular resource. So even if we're trying
to do queries for cheese listings elsewhere in our code, for some other reason, it
would actually add that filter to it. So what I'd like to do instead is something
called an API platform extension. It's basically a way for you to change how the
query is done for this collection resource whenever a collection is being returned by
API platforms specifically. So extensions are just a way for you to hook into the
doctrine query logic. So in the source API Platform Directory, let's create a new
class called cheese.

She's listening is published extension and we're going to make this implement query
collection extension interface. I'll go to commit the Code -> Generate menu or
command n go ahead "Implement Methods". And this requires us to have one method
called applied to collection. So very simply, as soon as we create a class that
implements this interface every single time in the system, that doctrines making a
query for a collection, it's going to call this method and it's going to pass as the
query builder. That's it. It's already built to do this. Now extensions are used in
the core of of API platform for things like page nation.

Okay.

Since we want are we only want to modify queries for CI's listings, we can say if
resource class does not equal cheese listing Colon Quan class and we're just going to
return, we don't want to actually make any modifications to it. Now to modify the
query itself, we have to do a little bit of fanciness here. We're going to say route
alias = query builder.->get root alias says left square bracket zero. I'll show you
why we do that in a second. We do that. So we can say query builder era and where
here we're going to say pass this sprint af percent s. Dot. Is published = colon is
published. And that at the end of this past that, that root alias and then for the is
published value, we'll do our normal set parameter is published set to true. So this
mostly looks just like a normal way that you uh, work with a doctrine query. The only
weird part is this root alias stuff, this percent. And that's because something,
because somebody else is creating this query builder, they're going to create this
query builder where, and they're going to be able to use any letter or word they want
here before that is published to represent the cheese listing table. So the alias
trick is a as a way for us to get that alias so we can just add onto the query.

Um, in addition to the um, resource class that you're passing, you're also passed the
operation name. So if you wanted to make some sort of a, a change to the query but
only on a specific operation, you can use that inside your logic. Well, so as we, as
soon as we do this, we're on our test now may pass. We are successfully hiding the
unpublished cheese listings just that easily. Now, like a lot of other clinic cases
in our system, I've all, I've also said, well, what if you, what if you have an admin
interface and you do want the cheese listings, the unpublished cheese listings to be
shown in the admin interface? Well, there's a couple of different ways to do that.
You could actually get the request inside of this, um, service and look what the URL
is and if you're on the, oh, sorry.

Oh, I would mark. No, so we can do is we can once again add papa function
underscore,_construct and we'll just inject that same security service, do our all to
enter initialized fields to create that property and set it down here very simply. If
this->Security->is granted roll_admin, we're going to return nothing. So admin users
are still going to get a return to every single collection in the resource. So this
takes care of the collection stuff works awesome. But we also had the problem that,
and we don't, we no longer want someone to be able to fashion individual cheese
listing if it's unpublished. So we can actually write a really quick test for this.
I'm going to copy the test, get the test method. We just graded paste that entire
thing and let's call this one test get cheese listing item and I'll delete cheese
listings. Two and three

[inaudible]

[inaudible] and we're going to do here is we're going to make you get request to /a
pass last cheeses /and then we'll say cheese listing one->get id, and for the
assertion we'll say a cert status code for Cert Response Status Code same 200 so if
you copy this methadone right now, it's going to pass copy the method name. Right
now, this Jesus number here is unpublished. So I'm actually asserting the behavior we
don't want, we actually don't want 200 we want this to be a four oh four but this is
how it, this is the current behavior. So let's at least make sure that the test
passes right now it's a PHP bin /PHP unit that's just filter = test to get Jesus
thing item and it passes. So that's not what we want. Just to make this really
obvious, I'm going to say Jesus thing->set is published. False. That was already, it
was already unpublished, but that will make this test more obvious. And then now here
it's not published. We actually want a four oh four. Now if you're on the test, it
fails.

So the cool thing is these cheese listing, uh, extension things, you can either, um,
change the method when you're querying for a collection. Or you can also add another
interface called query item extension interface. When you do that, you're required to
have another method. I've got to code generate or command and again go to "Implement
Methods". And this time it's applied to item or basically on one to apply the exact
same method. It's almost like all of this logic from apply to collection. I'll type
control t, which is three factor this menu. And I will say extract to a method we'll
call this method and where, so that just actually takes that and extracts all that
logic down here into it's own and where and where function and a calls up from up
here. So the collection is still doing the same as before and now we'll do the exact
same thing in the applied item. And this time when we try to test fails still it
fails. Huh? I moved back. Oh. And this is cause I reversed my logic earlier with
rural admin. Good thing for test for catching that. So I don't run the test again.
Yes, this time it passes.

This is great though. There's one caveat that you need to remember and that is that
the collection of cheese listing is, is returned in two places. One, it's returned
when you actually fetch a specific uh, fat use the cheese listing, um, resource. But
if you fetch a specific user, that specific user actually returns, uh, the cheeses,
things related to that user. And these, this extension does not filter those. This
extension is for the initial query. But when API platform goes to get the cheese
listings off of a use off of, uh, the user, all it does is call get cheese listings.
And the way doctrine works is this will always be a collection of all of the cheese
listings. So it's something you need to be aware of, like in general, this is
something you need to be aware of if you do embed, uh, relations like that.

Another thing is that if a user is a lot to have many cheese listings, eventually,
uh, it's not going to work too well because this cheese listing results, it's going
to have way too many things in it. It's gonna slow down your API. But if you do want
to have this Jesus and his property and user, and you do want to hide the unpublished
ones, another option is that you could just have the cheese listing. Instead of
embedding the object, you just gotta have the I our eyes return. That's the default
mode. And then some of those IRAs, if you follow them one for four, cause they're not
published, but at least it wouldn't expose the underlying details. But another option
is you could filter this list if you want it to.

We did it by creating something like get published. She's listings. I will say this
is going to return a doctrine collection. Then you can say, return this air cheese
listings, the->filter. I'm pass out a call back with a cheese argument and you'll
say, return. She's listing Arrow, uh, get is published. So basically that's gonna
return a smaller collection. The only returns, the published cheese listings. Now
doing this is actually inefficient. If that cheese listing property holds many cheese
listings, this is actually going to need to go and query for all of the cheese
listings. Like maybe I'll 30 cheese listings and only to reduce the list down to 15.
So there is a more efficient way to do this. You can see it in our doctrine breweries
chapter. For now, I'm just gonna do it the simple way.

And then what we can do is now, if I look at the original CI's listings property, we
don't want to expose that property anymore. So I'll take the ad groups off of that
and go down to the new get published cheese listings. And we will put at groups onto
that. And we also say at serialized name, she is listings so that it becomes the uh,
the cheese listings property. To test this, instead of our, uh, test get cheese
listing item, we could actually make another request on below this. So we'll say
client ever request to /API /users. And then our user->get id. So up here, and also
going to change this to create user and login cause we actually need to be logged in
in order to make that request. So we're gonna log on as set user, we're going to
fetch that user's data. And here I'll say a cert response, uh, JSON contains, and we
should have a cheese listings property that set to an empty array. So go back over
here and run the test again and it passes. And actually I should have run that and
got it to fail first. So let's actually reverse our logic slightly for a second.
Instead of user, let's just return this arrow. Cheese listings, get Jesus' things,
and then we run the test. I want to make sure it, it's actually failing, which it's
not

[inaudible]

so let's just dump, but that's returning. Oh, you can see she's listening. Yeah.

Okay.

Do you want to stop all that?