# Automatic 404 on Unpublished Items

Coming soon...

just that easily. Now, like a lot of other clinic cases
in our system, I've all, I've also said, well, what if you, what if you have an admin
interface and you do want the Cheese Listings, the unpublished cheese listings to be
shown in the admin interface? Well, there's a couple of different ways to do that.
You could actually get the request inside of this, um, service and look what the URL
is and if you're on the, oh, sorry.

Oh, I would mark. No, so we can do is we can once again add
`public function __construct()` and we'll just inject that same `Security` service, do our
Alt + enter "Initialized fields" to create that property and set it down here very simply. If
`$this->Security->isGranted('ROLE_ADMIN')`, we're going to return nothing. So admin users
are still going to get a return to every single collection in the resource. So this
takes care of the collection stuff works awesome. But we also had the problem that,
and we don't, we no longer want someone to be able to fashion individual cheese
listing if it's unpublished. So we can actually write a really quick test for this.
I'm going to copy the test, get the test method. We just graded paste that entire
thing and let's call this one test `getCheeseListingItem()` and I'll delete cheese
listings. Two and three

and we're going to do here is we're going to make you `GET` request to `/api/cheese/`
and then we'll say `$cheeseListing1->getId()`, and for the
assertion we'll say `->assertResponseStatusCodeSame(200)` so if
you copy this methadone right now, it's going to pass copy the method name. Right
now, this Jesus number here is unpublished. So I'm actually asserting the behavior we
don't want, we actually don't want `200` we want this to be a `404` but this is
how it, this is the current behavior. So let's at least make sure that the test
passes right now it's a

```terminal
php bin/phpunit --filter=testGetCheeseListingItem
```

and it passes. So that's not what we want. Just to make this really
obvious, I'm going to say `$cheeseListing->setIsPublished(false)`. That was already, it
was already unpublished, but that will make this test more obvious. And then now here
it's not published. We actually want a `404`. Now if you're on the test, it
fails.

So the cool thing is these `CheeseListing`, uh, extension things, you can either, um,
change the method when you're querying for a collection. Or you can also add another
interface called `QueryItemExtensionInterface`. When you do that, you're required to
have another method. I've got to Code generate or command + N again go to "Implement
Methods". And this time it's `applyToItem()` or basically on one to apply the exact
same method. It's almost like all of this logic from apply to collection. I'll type
control t, which is three factor this menu. And I will say extract to a method we'll
call this method and where, so that just actually takes that and extracts all that
logic down here into it's own and where and where function and a calls up from up
here. So the collection is still doing the same as before and now we'll do the exact
same thing in the applied item. And this time when we try to test fails still it
fails. Huh? I moved back. Oh. And this is cause I reversed my logic earlier with
rural admin. Good thing for test for catching that. So I don't run the test again.

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

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