# Automatic 404 on Unpublished Items

Unpublished cheese listings will *no* longer be returned from our collection
endpoint: this extension class has taken care of that. Of course... if we want
to have some sort of an admin section where admin users can see *all* cheese
listings... that's a problem... because we've just filtered them out entirely!

No worries, let's add the same admin "exception" that we've added to a few other
places. Start with `public function __construct()` so we can autowire the `Security`
service. I'll hit Alt + Enter and click "Initialized fields" to create that property
and set it. 

[[[ code('18c3f01705') ]]]

Down in the method, very nicely, if `$this->security->isGranted('ROLE_ADMIN')`, 
return and do nothing. 

[[[ code('e9bc085610') ]]]

Whoops, I added an extra exclamation point to make this *not*. Don't do that! 
I'll fix it in a few minutes.

Anyways, apart from my mistake, admin users can now fetch *every* `CheeseListing`
once again.

## Testing for 404 on Unpublished Items

That takes care of the collection stuff. But we're not done yet! We also don't
want a user to be able to fetch an *individual* `CheeseListing` if it's unpublished.
The collection query extension does *not* take care of this: this method is only
called when API Platform needs to query for a *collection* of items - a different
query is used for a *single* item.

Let's write a quick test for this. Copy the collection test method, paste the
entire thing, rename it to `testGetCheeseListingItem()`... and I'll remove cheese
listings two and three. This time, make the `GET` request to `/api/cheeses/`
and then `$cheeseListing1->getId()`.

This is an *unpublished* `CheeseListing`... so we eventually want this to *not*
be accessible. But... because we haven't added the logic yet, let's start by testing
the current functionality. Assert that the response code is 200.

[[[ code('c4f3488ae2') ]]]

Copy that method name, and let's make sure it passes:

```terminal
php bin/phpunit --filter=testGetCheeseListingItem
```

It does! But... that's not the behavior we want. To make this *really* obvious,
let's say `$cheeseListing->setIsPublished(false)`. That `CheeseListing` was
already unpublished - that's the default - but this is more clear to me. For the
status code, when a `CheeseListing` is unpublished, we *want* it to return
a 404. Try the test now:

[[[ code('309168dc97') ]]]

```terminal-silent
php bin/phpunit --filter=testGetCheeseListingItem
```

Failing! We're ready.

## The QueryItemExtensionInterface

So if the `applyToCollection()` method is only called when API Platform is making
a query for a *collection* of items... how can we modify the query when API Platform
needs a *single* item? Basically... the same way! Add a second interface:
`QueryItemExtensionInterface`. This requires us to have one new method. Go to the
Code -> Generate menu - or Command + N on a Mac - and select "Implement Methods"
one more time. And... ha! We could have guessed that method name: `applyToItem()`.
This is called whenever API Platform is making a query for a *single* item...
and we basically want to make the *exact* same change to the query.

[[[ code('a0c10b68f4') ]]]

I'll hit Control+t, which, on a Mac, is the same as going to the Refactor menu
on top and selecting "Refactor this". Let's extract this logic to a "Method" - call
it `addWhere`.

[[[ code('03aa5cf7e2') ]]]

Cool! That gives us a new `private function addWhere()`... and `applyToCollection()`
is already calling it. Do the same thing in `applyToItem()`.

[[[ code('d077a4ee0d') ]]]

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
in *two* places - the `GET` operation to `/api/cheeses` and... somewhere else.
And that "somewhere else" is *not* filtering out the unpublished cheese listings.
Oh nooooooo.

Let's find out more and fix that next!
