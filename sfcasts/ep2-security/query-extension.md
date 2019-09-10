# Query Extension: Auto-Filter a Collection

The `CheeseListing` entity has a property on it called `$isPublished`, which defaults
to `false`. We haven't talked about this property much, but the idea is pretty
simple: when a `CheeseListing` is first created, it will *not* be published. Then,
once a user has perfected all their cheese details, they will "publish" it and
only *then* will it be available publicly on the site.

This is an interesting case where we want to filter the `CheeseListing` collection
to *only* show *published* items.

## Adding a Test

Let's put this into a simple test first. Inside `CheeseListingResourceTest`
add a `public function testGetCheeseListingCollection()`, then go to work:
`$client = self::createClient()` and then `$user = $this->createUser()` with
any email *and* password.

We're not logging *in* as this user because the `CheeseListing` collection operation
doesn't require authentication. Now, let's create some cheese! I'll create a
`$cheeseListing1`... then use that to create `$cheeseListing2`, and `$cheeseListing3`.
These are nice, boring, `CheeseListing` objects. Next grab the entity manager
with `$em = $this->getEntityManager()`, persist those three objects and... flush
them.

Perfect setup! The default value for the `isPublished` property is false... and
so, right now, all of these new listings will be *unpublished*. Of course... because
we're not hiding unpublished listings yet, these will *all* be returned. Let's
start by making sure that works: `$client->request()` to make a `GET`
request to `/api/cheeses`.

Let's see... to get things started, I want to assert that this returns ** results.
Go over to the docs... and try the endpoint. Ah, cool - Hydra adds a very useful
field: `hydra:totalItems`. Let's use that. In the test,
`$this->assertJsonContains()` that there will be a `hydra:totalItems` field set
to `3`.

This *should* pass because our API does *not* filter yet. Copy the method name,
flip over to your terminal, and try this thing out:

```terminal
php bin/phpunit --filter=testGetCheeseListingCollection
```

And... green! I love when there are no surprises.

*Now* let's update the test for the behavior that we *want*. Let's allow
`$cheeseListing1` to stay *not* published, but publish the other two:
`$cheeseListing2->setIsPublished(true)`... and then paste that below and rename
it for `$cheeseListing3`.

Once we're done with the feature, we want only the second *two* to be returned
buy the endpoint. Change the assertion to 2. Now we have a failing test.

## Automatic Filtering

So... how can we make this happen? How can we hide unpublished cheese listings?
There are a few options. In the first tutorial we talked about filters.
For example, we added a boolean filter that allowed us to add a
`?isPublished=1` or `?isPublished=0` query parameter to the `/api/cheeses` operation
to *filter* which type you want.

That's great! The problem is that we *no* longer want an API client to be able
to *control* this: we need this filter to be automatic so that users can *never*
see unpublished listings. Filters are a good choice for *optional* stuff, but
not this.

Another option is called a Doctrine filter, which we talk about in our
[Doctrine Queries](https://symfonycasts.com/screencast/doctrine-queries/filters)
tutorial. A Doctrine filter allows you to modify a query on a *global* level...
like each time we query for a `CheeseListing`, we could *automatically* add
`WHERE is_published=1` to it.

The downside to a Doctrine filter is also its strength: it's automatic - it
modifies *every* query you make... even if you were, for example, trying to query
for *all* cheese listings - including unpublished ones - for some admin section.
Sure, you can work around that - but I tend to use Doctrine filters rarely.

Another solution is specific to API Platform... which I *love* because it means
that it will only affect our API operations... not the rest of the system. It's
called a "Doctrine extension".... which is basically a "hook" for you to change
how a query is built for a specific resource or even for a specific *operation*
of a resource. They're... basically... awesome.

## Creating the Query Collection Extension

Let's get our extension going: in the `src/ApiPlatform/` Directory, create a new
class called `CheeseListingIsPublishedExtension`. Make this implement
`QueryCollectionExtensionInterface` and then go to the Code -> Generate menu - or
Command + N on a Mac - and select "Implement Methods" to generate the *one*
method that's required by this interface: `applyToCollection()`.

Very simply: as soon as we create a class that implements this interface, *every*
single time that API Platform makes a Doctrine query for a *collection* of results,
like a collection of users or a collection of cheese listings, it will call this
method and pass us a pre-built `QueryBuilder`. Then... we can mess with that
query however we want.

In fact, this is how pagination and filters work internally: both are implemented
as Doctrine *extensions*.

## Query Collection Extension Logic

Let's see... even though this method will be called whenever API Platform is
querying for a collection of *any* type of object, we only want to do our work
for cheese listings. No problem: if `$resourceClass !== CheeseListing::class`
then return and do nothing.

Now... we just need to add the `WHERE isPublished=1` part to the query. To do
that... we need a *little* bit of fanciness. Start with
`$rootAlias = $queryBuilder->getRootAlias()[0]`. I'll explain that in a minute.
Then `$queryBuilder->andWhere()` with `sprint('%s.isPublished = :isPublished')`
passing `$rootAlias` to fill in that `%s` part. For the `isPublished` parameter,
call `->setParameter('isPublished', true)`.

This *mostly* looks like normal query logic - the only weird part is that "root
alias" thing. Basically, because someone *else* started this query for us, we
don't know what "table alias" they're using for `CheeseListing` - like
`c.isPublished` or `cheese.isPublished` - we don't really know. The `getRootAlias()`
method tells us that... and we hack it into our query.

Oh, and by the way - in addition to `$resourceClass`, this method receives the
`$operationName`. Normally only the `get` operation - like `GET /api/cheese` -
would cause a query for a collection to be made... but if you created some custom
operations and needed to tweak the query between them, the `$operationName` can
let you do that.

Anyways... we should be done! Run that test again:

```terminal
php bin/phpunit --filter=testGetCheeseListingCollection
```

And... green! We are successfully hiding the unpublished cheese listings. Isn't
that nice?

Next, let's add in logic so that *admin* users will *still* see unpublished
cheese listings. And what about the *item* operation? Sure, an unpublished
cheese listings won't be included in the *collection* endpoint... but how can we
prevent a user from making a GET request to fetch a *single*,
unpublished `CheeseListing`?
