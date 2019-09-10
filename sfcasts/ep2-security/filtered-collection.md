# Item Extension

Coming soon...

Oh, that's nice. But there's another place in our system where cheeses, things are
returned. The extension thing takes care of the collection of cheeses and even takes
care of trying to fetch an individual `CheeseListing`. But if you fetch an individual
user that actually returns the cheese listings as well and our extension class does
not handle those extension classes, only used to modify the query for the top level
resource someone we're actually working with the `CheeseListing` resource, so one of
these operations up here that the Ma, the query is going to be modified, but when a
user is being serialized in order to get this `$cheeseListings` property, all API
platform does is go to the `User` class and call, `getCheeseListings()` and that always
returns the full collection of cheeses, things, not the filtered collection. So a few
things to think about when you have a relation like this. The first thing is having
relation like this only really works if the number of items in the relation is never
going to be too huge. If you have, if a users could have a hundred cheese listings,
they're all going to be returned here and that's going to slow down your uh, your
endpoint and it's not very user friendly anyways, it's that case. It would be better
to have a user use the `CheeseListing` and point and then filter it. Use a filter to
filter it by the owner.

But if you do, but if you know that you're not going to have too many cheeses, things
that you do want to embed it here for convenience, you have a couple of options when
it comes to not sharing the published uh, thing. First thing is instead of embedding
these two properties here, you could just, um, have the IRI shown in that case if you
had five. Now some of those IRIs, if you followed them, might four, four, if
that cheeses things in publish, but at least it wouldn't expose any of the data on
those unpublished listings. As a reminder, the um, cheese listings are returning the
`title` on `price` key here because on the `CheeseListing` entity, you go look at the
`$title` thing, they have the group's `user:read` here and down above `$price` a
`user:read`. Some of the users see realized since it's a user, the `user:read`
group.

Since we have this above `price` and `title`, it includes the title and price fields. If
we remove that, then this would only return an array of IRI strengths. But if you,
okay, but if you really want to filter this properly, if you really do want to have
this `cheeseListings` property and you really do want to have it filtered and only
each of the published ones, we absolutely can do that. So first, let's actually
modify our tests a little bit to test for this. So after we make it get requests for
our unpublished cheeses listing and get a `404` let's also make a request, I
`GET` request to `/api/users` and then `$user->getId()`. So we're creating this up here
actually because fetching a user requires you to be logged in. Let's change this to
`createUserAndLogIn()` and pass the `$client`. It's for logging in as this user. We're
going to make a request for that user down here. I'll say
`$data = $client->getResponse()->toArray()`, and we want to see here as we want to see that this
`cheeseListings` property is empty. This `User` does have one `CheeseListing`, but since it's not
published, it shouldn't show up in this collection. So we can say
`$this->assertEmpty($data['cheeseListings']);`

So if you move a right now and try the test again,

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

perfect, it fails,

> Failed asserting that an array is empty.

because it is not empty. All right, so how do we fix
this? Well, we know that API platforms calling `getCheeseListings()`, so what we can do
below here is just create a new method called `getPublishedCheeseListings()` that will
also return that same `Collection`. Instead of here, we can say return
`$this->cheeseListings->filter()`, which is because remember the cheese thing is actually a
Doctrine collection. We'll pass this a `function(){}` that will get a `CheeseListing`
argument. Inside here we'll say return `$cheeseListing->getIsPublished()`, so the
filter, if you're not familiar with it, it's basically gonna loop over every single
item and `$cheeseListings`. Call our call back for each `CheeseListing` and if we return
true from here, it'll be returned and a new collection and if not it won't be.

So the end result of this is that we get a collection of `CheeseListing`, but only
the published ones. Now again, if you have many cheat, if a users can have, could
have many cheese listings that this is not an efficient way to do this if there were
30 cheeses because it needs to query for all of the cheeses things, the database
first, only if you're going to, even if you're only going to return some of them, if
you really care about that, you can look at our doctrine extensions, tutorial docking
queries tutorial. We do talk about a more efficient way to write this function, to
avoid that performance it. But if you have, if you only have, if you don't have that
many cheese listings, it doesn't really matter. Now that we have this new function,
we can make it part of our API.

So I'm going to go up to the `$cheeseListings` property and right now this is in the
groups `user:read` and `user:write` I'm going to copy that. I'm going to
take it out of `user:read`. When we're writing to this field, I still want to
write to this property directly, which means use the normal adder and remover
methods. When we're reading from it, I want to use the other method. So I'll add the
`@Groups()` down to `getPublishedCheeseListings()` and put it in a `user:read`
group. Now if we just stopped there, this would give us a new `publishedCheeseListings`
property, but we can fix that with `@SerializedName("cheeseListings")`
and that should give us a `cheeseListings` property that only returns the published
cheese listings. So if she'd have her now and rerun that test.

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

Yes it passes. And actually to be safe, let's rerun all of our tests

```terminal-silent
php bin/phpunit
```

when we do get
one failure. So if we checked this out here, the failure is coming from
`testUpdateCheeseListing()`

> Failed asserting that Response status code is 403

cause we got 404 and then a big long stack trace there. So if you check this out, look
for a `testUpdateCheeseListing()`. As a reminder, what we're doing here, the failure is
coming from down here on line 67 and actually see that we look a little bit closer.
Yup. Line 67 we were basically we were testing that you can't update a `CheeseListing`
that's owned by somebody else, but instead of going `403`, we're getting a `404`
And the problem is that this `CheeseListing` is not published. And this is
actually really cool. Our r a extension class is actually preventing us from even
fetching this single `CheeseListing` for editing cause it's not published. So this is
actually our Accenture class doing exactly what we want. So let's set this to publish
that our test actually works and we'll go back over here and run the test again

```terminal-silent
php bin/phpunit
```

and all green.

All right. That's it.