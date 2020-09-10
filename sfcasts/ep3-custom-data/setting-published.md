# Publishing a Listing

One of the things that we *can't* do yet is publish a `CheeseListing`. Boo!

Right now, when we created a `CheeseListing` through our API, it always gets an
`isPublished=false` value, which is the default. There is no actual way to change
this because `isPublished` isn't exposed as a field in our API.

In the imaginary UI of our site, there will be a "Publish" button that a user can
click. When a user clicks they, we're going to obviously need to change the
`isPublished` field from `false` to `true`. But publishing in our app is *more*
than just updating this field in the database. In addition to updating the field,
let's pretend that we need to run some custom code, like maybe we need to send a
request to ElasticSearch to *index* the new listing or we want to send some
notifications to users who are desperately waiting for this type of cheese.

## Publishing: Custom Endpoint

So... how should we design this in our API? You *might* think that we need a custom
endpoint or "operation" in ApiPlatform language. Something like
`POST /api/cheeses/{id}/publish`.

You *can* do this. And we'll talk about custom operations in part 4 of this series.
But this solution would *not* be RESTful. In REST, every URL represents the address
to a unique resource. So from a REST standpoint POSTing to
`/api/cheses/{id}/publish` makes it look like there is a "cheese publish" resource
and that we're trying to create a new one.

Of course, rules are *meant* to be broken. And ultimately, you should just get your
job done however is best. But in this tutorial, let's see if we *can* solve this
in a RESTful way. How? By making `isPublished` changeable in the *same* way as
*any* other field: by making a PUT request with `isPublished: true` in the body.

That part will be pretty easy. But running code *only* when this value changes
from false to true? That will be a bit trickier.

## Testing the PUT to update isPublished

Let's start with a basic test where we update this field. Open
`tests/Functional/CheeseListingTesourceTest`, find `testUpdateCheeseListing`, copy
that method, paste, and rename it to `testPublishCheeseListing()`.

Ok! I don't need 2 users: I'll just create one user and log in is that
one user so that we have access to the PUT request. We already have security rules
in place from the last tutorial to prevent anyone from editing anyone else's listing.
Down here, for JSON body, send `isPublished` set to `true`. And... the status code
we expect is 200.

So here's the flow: we create a `User` - this uses the Foundry library and then
we create a `CheeseListing`. Oh, but we don't want that `published()` method - that's
a method I made to create a *published* listing: we definitely want to operate on
an *unpublished* listing. Anyways, we set the user as the owner of the new cheese
listing, log in as that user, and then send a PUT request to update th
`isPublished` field.

To make things more interesting, at the bottom, let's assert that the
`CheeseListing` listing *is* in fact published after the request. Do that with
`$cheeseListing->refresh()` - I'll talk about that in a second - and then
`$this->assertTrue()` that `$cheeseListing->getIsPublished()`.

The `$cheeseListing->refresh()` is another feature of Foundry library. Man, that
library just keeps giving! Whenever you create an object with Foundry, it passes
you back that object bu *wrapped* inside a `Proxy` object. Hold Command or Ctrl
and click the `refresh()`. Yep! A tiny `Proxy` object from Foundry with several
useful methods on it, like `refresh()`.

Anyways, refresh will update the entity in my test with the latest data, and then
we'll check if it's true.

Ok, testing time! I mean, time to make sure our test fails! Copy the test method
name, spin over to your terminal, and run:

```terminal
symfony php bin/phpunit --filter=testPublishCheeseListing
```

And... we're hoping for failure and... yes! Failed asserting that false is
true because... the `isPublished` as simply *not* writable in our API yet.

## Making isPublished Writable in the API

Let's fix that! At the top of our `@ApiResource` annotation, as a reminder, we
have a `denormalizationContext` option that sets the serialization groups to
`cheese:write`. So if we want a field to be *writeable* in the API, it needs
that group.

Copy that, scroll down to `isPublished` and add `@Groups({})` and paste. Now, as
long as this has a setter - and... yep there *is* a `setIsPublished()` method - it
will be writable in the API.

Let's see if it is! Go back to your terminal, run the test again:

```terminal-silent
symfony php bin/phpunit --filter=testPublishCheeseListing
```

And... got it! We can now publish a `CheeseListing`! But *we* know that this
was the easy part. The *real* question is this: how can we run custom code
*only* when a `CheeseListing` is published? So, only when the `isPublished`
field changes from false to true? Let's find out how next.
