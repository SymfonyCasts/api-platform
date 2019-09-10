# Automatic 404 on Unpublished Items

Unpublished cheese listings will *no* longer be returned from our collection
endpoint - this extension class has taken care of that. Of course... if we want
to have some sort of an admin section where admin users can see *all* cheese
listings... that's a problem... because we've just filtered them out entirely!

No problem, let's add the same admin "exception" that we've added to a few other
places. Start with `public function __construct()` so we can autowire the `Security`
service. I'll hit Alt + Enter and click "Initialized fields" to create that property
and set it down. Down in the method, very nicely, if
`$this->security->isGranted('ROLE_ADMIN')`, then return and do nothing. Notice
I added an extra exclamation point to make this *not*. Don't do that! I'll fix
that in a few minutes.

Admin users can now fetch *every* `CheeseListing` once again.

## Testing for 404 on Unpublished Items

That takes care of the collection stuff. But we're not done yet! We also don't
want a user to be able to fetch an *individual* `CheeseListing` if it's unpublished.
The collection query extension does *not* take care of this - this is only used
when API Platform needs to query for a *collection* of items - a different query
is used for a *single* item.

Let's write a quick test for this. Copy the collection test method, paste the
entire thing, rename it to `testGetCheeseListingItem()`... and I'll remove cheese
listings two and three. This time, make the `GET` request to `/api/cheese/`
and then `$cheeseListing1->getId()`.

This is an *unpublished* `CheeseListing`... so we eventually want this to *not*
be returned. But... because we haven't added the logic yet, let's start by testing
the current functionality. Assert that the response code is 200.

Copy that method name, and let's make sure it passes:

```terminal
php bin/phpunit --filter=testGetCheeseListingItem
```

It does! But that's not the behavior we want. Just to make this *really*
obvious, let's say `$cheeseListing->setIsPublished(false)`. That `CheeseListing`
was already unpublished - that's the default - but this makes it super obvious.
For the status code, when a `CheeseListing` is unpublished, we *want* it to return
a 404. Try the test again:

```terminal
php bin/phpunit --filter=testGetCheeseListingItem
```

Failing! We're ready.

## The QueryItemExtensionInterface

So if the `applyToCollection()` method is only called when API Platform is making
a query for a *collection* of items... how can we modify the query when it's
making a query for a *single* item? Basically... the same way! Add a second interface:
`QueryItemExtensionInterface`. This requires us to have one new method. Go to the
Code -> Generate method - or Command + N on a Mac - and select "Implement Methods".
one more time. And... ha! We could have guessed that method name: `applyToItem()`.
This is called whenever API Platform is making a query for a *single* item...
and we basically want to make the *exact* same change to the query.

I'll hit Control+t, which, on a Mac, is the same as going to the Refactor menu
on top and selecting "Refactor this". Let's extra this logic to a "Method" - call
it `addWhere`.

Cool! That gives us a new `private function addWhere()`... and `applyToCollection()`
is already calling it. Do the same thing in `applyToItem()`.

Let's try this! Run the test again and...

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

It fails? Hmm. Oh... I reversed the check for `ROLE_ADMIN`. Get rid of that
exclamation point... and try that test again.

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

We are green! How cool was that? We're able to modify the collection *and*
item queries for a specific resource with one class and two methods.

There's just one more problem: the collection of cheese listings is returned
in *two* places - the `GET` operation to `/api/cheeses` and somewhere else.
And that "somewhere else" is *not* filtering out the unpublished cheese listings.

Let's find out more and fix that next!
