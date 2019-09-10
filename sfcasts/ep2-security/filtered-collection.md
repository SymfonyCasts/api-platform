# Filtering Related Collections

There are two places where our API returns a collection of cheese listings: the
first is the `GET` operation for `/api/cheeses` and our extension class takes
care of filtering out unpublished listings. The second... is down here, when
you fetch a single user. Remember - we decided to *embed* the collection of
cheese listings that are owned by this user. But... surprise! Our query extension
class does *not* filter this! Why? The extension class is only used when API Platform
needs to make a direct *query* for a `CheeseListing`. In practice, this means
it's used for the `CheeseListing` operations. But for a `User` resource, API
platform queries for the `User` and then, to get the `cheeseListings` property,
it simply calls `getCheeseListings()`. And guess what? That method returns the
*full*, unfiltered collection of related cheese listings.

## Careful with Collections

When you decide to expose a collection relation like this in your API, I want you
to keep something in mind: exposing a collection relationship is only practical
if you know that the number of related items will always be reasonably small. If
a user could have *hundreds* of cheese listings... well... then *all* of them
will be queried for, hydrated & returned whenever someone fetches that user's
data. That's overkill and will *really* slow things down... if not eventually
kill that API call entirely. In that case, it would be better to *not* embed
`cheeseListings` and instead direct an API client to make a `GET` request to
`/api/cheeses` & use the `owner` filter instead. The response will the paginate
and keep things at a reasonable size.

## IRIs Instead of Embedded Data?

But if you *do* know that a collection will never become too huge and you *do*
want to expose it like this... how can we hide the unpublished listings? There
are two options. First, instead of embedding these two properties, you could
configure API Platform to return only the IRI. This is only a... partial solution.

As a reminder, each item under `cheeseListings` contains two fields: `title` and
`price`. Why only those two fields? Because, in the `CheeseListing` entity, the
`title` property is in a group called `user:read`... and the `price` property is
*also* in this group. When API Platform serializes a `User`, we've configured
it to use the `user:read` normalization group. By putting these two properties
into that group, we're telling API Platform to *embed* these fields.

If we removed the `user:read` group from *all* the properties in `CheeseListing`,
then the `cheeseListings` field on `User` would return an array or IRI strings
*instead* of embedded data.

Ok... why does that help us? Well... it sort of doesn't. That field would *still*
contain the IRI's for *all* cheese listings owned by this user... but if an API
client made a request to the IRI of an unpublished listing, it would 404. They
wouldn't be able to see the *data* of the unpublished listing... which is great...
but the IRI *would* still show up here... which is kinda weird.

## Truly Filtering the Collection

If you *really* want to filter this properly, if you *really* want the `cheeseListings`
property to only contain *published* listings, we can absolutely do that.

Let's modify our test a little bit to look for this. After we make a get request
for our unpublished `CheesesListing` and assert the `404`, let's *also* make a
`GET` request to `/api/users/` and then `$user->getId()` - the id of the `$user`
we created above that owns this `CheeseListing`. Change that line to
`createUserAndLogIn()` and pass `$client`... because you need to be authenticated
to fetch a single user's data.

After the request, fetch the returned data with
`$data = $client->getResponse()->toArray()`. We want to assert that the
`cheeseListings` property is empty: this `User` *does* have one `CheeseListing`,
but it's not published. Assert that with `$this->assertEmpty($data['cheeseListings'])`.

Let's make sure this fails...

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

And... it does:

> Failed asserting that an array is empty.

## Adding getPublishedCheeseListings()

Great! So... how can we filter this collection? Let's think about it: we know that
API Platform calls `getCheeseListings()` to get the data for the `cheeseListings`
property. So... what if we made this method return only the *published* cheese
listings?

Yea... that's the key! Well, but instead of modifying that method - it's a getter
method for the `cheeseListings` property... so it really should return everything -
let's create a new method: `public function getPublishedCheeseListings()` that will
return that same `Collection`. Inside, return `$this->cheeseListings->filter()`,
which is a method on Doctrine's collection object. Pass this a callback
`function(){}` with a single `CheeseListing` argument. All that method needs is
`return $cheeseListing->getIsPublished()`.

If you're not familiar with the `filter()` method, that's ok - it's a bit more
common in the JavaScript world... or "functional programming" in general. The
`filter()` method will loop over *all* of the `CheeseListing` objects in the
collection and execute the callback for each one. If our callback returns true,
that `CheeseListing` is added to a *new* collection... which is ultimately returned.
If our callback returns false, it's not.

The end result is that this method returns a collection of only the *published*
`CheeseListing` objects... which is perfect! Side note: this method is inefficient
because Doctrine will query for *all* of the related cheese listings... and then
we'll only return the published ones. If number of items in the collection will
always be pretty small, no big deal. But if you're worried about this, there *is*
a more efficient way to filter the collection, which we cover in our
[Doctrine Relations tutorial](https://symfonycasts.com/screencast/doctrine-relations/collection-criteria).

But no matter *how* you filter the collection, you'll now have a *new* method
that returns only the *published* listings. Let's make it part of our API!
Find the `$cheeseListings` property.Tight now this is in the `user:read` and
`user:write` groups. Copy that and take it *out* of the `user:read` group. We
still want to *write* directly to this field... by letting the serializer call
our `addCheeseListing()` and `removeCheeseListing()` methods, but we *won't*
use it for *reading* data.

Instead, above the new method, paste the `@Groups` and put this in *just* `user:read`.
If we just stopped now, this would give us a new `publishedCheeseListings` property.
We can improve that by adding `@SerializedName("cheeseListings")`.

I love it! Our API *still* exposes a `cheeseListings` field... but it will now
*only* contain *published* listings. But don't take my word for it, run that
test!

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

Yes! It passes! To be safe, let's run *all* the tests:

```terminal
php bin/phpunit
```

And... ooh - we do get one failure from `testUpdateCheeseListing()`:

> Failed asserting that Response status code is 403

And... it looks like we got a 404. Let's check this out... find
`testUpdateCheeseListing()`. The failure is coming from down here on line 67.
We're testing that you can't update a `CheeseListing` that's owned by a different
user... but instead of getting a `403`, we're getting a `404`.

The problem is that this `CheeseListing` is not published. This is *awesome*!
Our query extension class is preventing us from fetching a this single
`CheeseListing` for editing... because it's not published. I wasn't even thinking
about this case, but API Platform acted intelligently.

Let's set this to be published... and run the test again:

```terminal-silent
php bin/phpunit
```

All green! That's it friends! We made! We added *one* type of API authentication
and then customized access in *every* possible way I could think of: preventing
access on an operation-by-operation basis, voters for more complex control, hiding
fields based on the user, adding custom fields based on the user, validating data...
again... based on who is logged in and even controlling database queries based
on security. Phew! In the an upcoming tutorial, we'll talk about custom operations,
DTO objects and any other customizations we can dream up. Are we still missing
something you wish was covered? Let us know!

Alright friends, see ya next time!
