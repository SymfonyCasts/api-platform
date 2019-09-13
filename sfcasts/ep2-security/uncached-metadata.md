# Dynamic Groups without Caching

Our resource metadata factory is now automatically adding a specific set of
normalization and denormalization groups to each operation of each resource.
That means that we can customize which fields are readable and writable for
each operation just by adding specific groups to every property. And the *true*
bonus is that... our documentation is aware of these dynamic groups! It
*correctly* tells us which fields are readable and writable.

But... if you're coding along... it's possible that your docs did *not* update.
If that happened, the fix is to run:

```terminal
php bin/console cache:clear
```

Here's the deal: the results of `AutoGroupResourceMetadataFactory` are cached...
which makes sense: the `ApiResource` options should only need to be loaded one
time... then cached. Unfortunately, for right now, this means that each time you
make *any* change to this class, you need to manually rebuild your cache.

## Changing CheeseListing shortName & Groups

But before we worry about that... all of our `CheeseListing` operations are...
wait for it... broken! Yay! Check out the `GET` operation for the collection of
cheese listings. It says that it will return an array of... nothing! And in fact...
if you tried it, it *would* indeed return an array where each `CheeseListing`
contains *no* fields!

This is a small detail related to how our resource metadata factory names the
groups: it uses the API resource "short name" for each group - like `user:read`.
What *is* this `shortName` thing? For `CheeseListing`, it comes from the `shortName`
option inside the annotation. Or, if you don't have this option - API Platform
guesses a `shortName` based on the class name.

The `shortName` most importantly becomes part of the URL. Check this out: execute
a `GET` request to `/api/cheeses`. Then, use the web debug toolbar to open the
profiler for that request... and go to the API Platform section. This shows you
the "Resource Metadata" for `CheeseListing`. Hey, "Resource Metadata" - that's
the name of the class that our resource metadata factory is creating!

Look at the `normalization_context` of the item GET operation: it still has
`cheese_listing:read` and `cheese_listing:item:get`... because we still have
those groups manually on the annotation... which we really should remove now.
Then our resource metadata factory added 3 new groups: `cheeses:read`,
`cheeses:item:read` and `cheeses:item:get`.

Basically, the group names in the new system - like `cheeses:read` - don't *quite*
match the group names that we've been using so far - like `cheese_listing:read`.
No worries, we just need to update our code to use the new group names.

But first, we added the `shortName` option in part 1 of this tutorial to change the
URLs from `/api/cheese_listings` to `/api/cheeses`. Now, change the `shortName`
to just `cheese`.

[[[ code('46a495d08a') ]]]

If you refresh the docs... surprise! All of the URLs are *still* `/api/cheeses`:
API Platform automatically takes the `shortName` and makes it *plural* when creating
the URLs. So, this change... didn't really.. change anything! I did it just so we
could keep all of our group names singular. I'll do a find and replace to change
`cheese_listing:` to `cheese:`. 

[[[ code('b1c6f4827a') ]]]

Then, in `User`, there's *one* other spot we need to change. 
Above `username`, change this to `cheese:item:get`. Don't forget to
*also* change `cheese_listing:write` to `cheese:write`. I'll catch that mistake a
bit later.

[[[ code('18bd9565ec') ]]]

Phew! Ok, go refresh the docs... and open the GET operation for `/api/cheeses`.
Yay! It properly advertises that it will return an array of the correct fields.
And when we try it... hey! It even works!

## Making our Resource Metadata Factory Not Cached

This whole resource metadata factory thing is *super* low-level in API Platform...
but it *is* a nice way to add dynamic groups and have your docs reflect those
changes. The only problem is the one I mentioned a few minutes ago: the results
are cached... even in the dev environment. So if you tweak any logic inside
this class, you'll need to manually clear your cache after *every* change. It's not
the end of the world... but it *is* annoying.

And... ya know what? It might be *more* than simply "annoying". What if you wanted
to add a dynamic normalization group based on who is logged in *and* you wanted
the documentation to automatically update when the user is logged in to reflect
that dynamic group? We already know that a context builder can add a dynamic group...
but the docs won't update. But... because our resource metadata factory is cached...
we can't put the logic there either: it would load the metadata just once, then
use the same, cached metadata for everyone. It would *not* change after the user
logged in.

But what if our resource metadata factory... *wasn't* cached? Duh, duh, duh!

## Service Decoration Priority

Check this out: in `config/services.yaml`, add a new option to the service:
`decoration_priority` set to -20.

[[[ code('e8398dad80') ]]]

Wow... yea... we just took an already-advanced concept and... went even deeper.
When we decorate a core service, we might not be the *only* service decorating it:
there may be *multiple* levels of decoration. And... that's fine! Symfony handles
all of that behind the scenes.

In the case of the resource metadata factory, API Platform *itself* decorates
that service multiple times... each "layer" adding a bit more functionality.
Normally, when *we* decorate a service from our application, our object becomes
the *outermost* object in the chain. One of the *other* services that decorates
the core service and is part of that chain is called `CachedResourceMetadataFactory`.
You can probably guess what it does: it calls the core resource metadata factory,
gets the result, then caches it.

So... why is this a problem? If we are the *outermost* resource metadata factory,
then... even if the `CachedResourceMetadataFactory` caches the core metadata,
our function would still *always* be called... and *our* changes should *never*
be cached. But... that is *not* what's happening!

Why? Because that `CachedResourceMetadataFactory` has a `decoration_priority`
of -10... and the default is 0. Before we added the `decoration_priority` option,
this meant that Symfony made `CachedResourceMetadataFactory` the *first* object
in the decoration chain and *our* class the second. And *that* caused our class's
results to be cached. By setting our `decoration_priority` to -20, our object
is moved *before* `CachedResourceMetadataFactory`... and suddenly, our results
are no longer cached.

Crazy, right? We can *now* put whatever dynamic logic we want into our custom
resource metadata factory. Refresh the docs... and look down on the models. Yep,
no surprises. *Now* go into our class and add `FOOO` to the end of one of the
groups. If we had made this tweak a minute ago and refreshed without clearing
the cache, we would have seen *no* changes. But now... it's there instantly!
All the *core* logic that reads the annotations *is* still cached, but our class
is *not*.

Just... be careful with this: the reason the logic is normally cached is that
API Platform calls this function *many* times during a request. So, any logic
you add here needs to be lightning quick. You may even decide to add a
`private $resourceMetadata` array property where you store the `ResourceMetadata`
object for each class as you calculate it. Then, if `create()` is called on the
same request for the same `$resourceClass`, you can return it from this array
instead of running our logic over and over again.

Ok team, I hope you enjoyed this crazy dive into custom resource metadata factories.
Next, we know how to hide or show a field based on who is logged in - like returning
the `phoneNumber` field *only* if the user has `ROLE_ADMIN`. But what if we *also*
need to hide or show a field based on which *object* is being serialized? What if
we *also* want to return the `phoneNumber` field when an authenticated user is
fetching their *own* `User` data?
