# Query Extension: Auto-Filter a Collection

The `CheeseListing` entity has a property on it called `$isPublished`, which defaults
to `false`. We haven't talked about this property much, but the idea is pretty
simple: when a `CheeseListing` is first created, it will *not* be published. Then,
once a user has perfected all their cheesy details, they will "publish" it and
only *then* will it be publicly available on the site.

This means that we have some work to do! We need to automatically filter
the `CheeseListing` collection to *only* show *published* items.

## Adding a Test

Let's put this into a simple test first. Inside `CheeseListingResourceTest`
add `public function testGetCheeseListingCollection()` and then go to work:
`$client = self::createClient()` and `$user = $this->createUser()` with
any email and password.

[[[ code('3b11f69594') ]]]

We're not logging *in* as this user simply because the `CheeseListing` collection
operation doesn't require authentication. Now, let's make some cheese! I'll create
`$cheeseListing1`... with a bunch of data... then use that to create
`$cheeseListing2`... and `$cheeseListing3`. These are nice, boring, delicious
`CheeseListing` objects. To save them, grab the entity manager -
`$em = $this->getEntityManager()` - persist all three... and call `flush()`.

[[[ code('0a59166899') ]]]

The stage for our test is set. Because the default value for the
`isPublished` property is false... all of these new listings will be *unpublished*.
Fetch these by using `$client->request()` to make a `GET` request to `/api/cheeses`.

[[[ code('973fd2ad46') ]]]

Because we haven't added any logic to hide *unpublished* listings yet, at this
moment, we would expect this to return *3* results. Move over to the docs... and
try the operation. Ah, cool! Hydra adds a very useful field: `hydra:totalItems`.
Let's use that! In the test, `$this->assertJsonContains()` that there will be
a `hydra:totalItems` field set to `3`.

[[[ code('4db5a7e769') ]]]

Copy the method name and flip over to your terminal: this test *should* pass
because we have *not* added any filtering yet. Try it:

```terminal
php bin/phpunit --filter=testGetCheeseListingCollection
```

And... green! I love when there are no surprises.

*Now* let's update the test for the behavior that we *want*. Allow
`$cheeseListing1` to stay *not* published, but publish the other two:
`$cheeseListing2->setIsPublished(true)`... and then paste that below and rename
it for `$cheeseListing3`.

Once we're done with the feature, we will only want the second *two* to be returned.
Change the assertion to 2. Now we have a failing test.

[[[ code('5585246d6f') ]]]

## Automatic Filtering

So... how can we make this happen? How can we hide unpublished cheese listings?
Well... in the first tutorial we talked about filters. For example, we added a
boolean filter that allows us to add a `?isPublished=1` or `?isPublished=0`
query parameter to the `/api/cheeses` operation. So... actually, an API client
can *already* filter out unpublished cheese listings!

That's great! The problem is that we *no* longer want an API client to be able
to *control* this: we need this filter to be automatic. An API client should
*never* see unpublished listings. Filters are a good choice for *optional* stuff,
but not this.

Another option is called a *Doctrine* filter. It's got a similar name, but is
a totally different feature that comes from Doctrine - we talk about it in our
[Doctrine Queries](https://symfonycasts.com/screencast/doctrine-queries/filters)
tutorial. A Doctrine filter allows you to modify a query on a *global* level...
like each time we query for a `CheeseListing`, we could *automatically* add
`WHERE is_published=1` to that query.

The downside to a Doctrine filter.... is also its strength: it's automatic - it
modifies *every* query you make... which can be surprising if you ever need to
*purposely* query for *all* cheese listings. Sure, you can work around that - but
I tend to use Doctrine filters rarely.

The solution I prefer is *specific* to API Platform... which I *love* because it
means that the filtering will only affect API operations... the rest of my app
will continue to behave normally. It's called a "Doctrine extension".... which
is basically a "hook" for you to change how a query is built for a specific
resource or even for a specific *operation* of a resource. They're... basically...
awesome.

## Creating the Query Collection Extension

Let's get our extension going: in the `src/ApiPlatform/` directory, create a new
class called `CheeseListingIsPublishedExtension`. Make this implement
`QueryCollectionExtensionInterface` and then go to the Code -> Generate menu - or
Command + N on a Mac - and select "Implement Methods" to generate the *one*
method that's required by this interface: `applyToCollection()`.

[[[ code('423e270154') ]]]

Very simply: as soon as we create a class that implements this interface, *every*
single time that API Platform makes a Doctrine query for a *collection* of results,
like a collection of users or a collection of cheese listings, it will call this
method and pass us a pre-built `QueryBuilder`. Then... we can mess with that
query however we want!

***TIP
If you're loading data from something *other* than Doctrine (or have a
custom "data provider"... a topic we haven't talked about yet), then
this class won't have any effect.
***

In fact, this is how pagination and filters work internally: both are implemented
as Doctrine *extensions*.

## Query Collection Extension Logic

Let's see... even though this method will be called whenever API Platform is
querying for a collection of *any* type of object, we only want to do our logic
for cheese listings. No problem: if `$resourceClass !== CheeseListing::class`
return and do nothing.

[[[ code('c07cbe9e29') ]]]

Now... we just need to add the `WHERE isPublished=1` part to the query. To do
that will require a *little* bit of fanciness. Start with
`$rootAlias = $queryBuilder->getRootAlias()[0]`. I'll explain that in a minute.
Then `$queryBuilder->andWhere()` with `sprintf('%s.isPublished = :isPublished')`
passing `$rootAlias` to fill in that `%s` part. For the `isPublished` parameter,
set it with `->setParameter('isPublished', true)`.

[[[ code('2f815ecadd') ]]]

This *mostly* looks like normal query logic... the weird part being that "root
alias" thing. Because someone *else* originally created this query, we don't
know what "table alias" they're using to represent `CheeseListing`. Maybe `c`...
so `c.isPublished`? Or `cheese`... so `cheese.isPublished`? We don't really know.
The `getRootAlias()` method tells us that... and then we hack it into our query.

Oh, and by the way - in addition to `$resourceClass`, this method receives the
`$operationName`. Normally only the `get` operation - like `GET /api/cheese` -
would cause a query for a *collection* to be made... but if you created some custom
operations and needed to tweak the query on an operation-by-operation basis,
the `$operationName` can let you do that.

Anyways... we should be done! Run that test again:

```terminal
php bin/phpunit --filter=testGetCheeseListingCollection
```

And... green! We are successfully hiding the unpublished cheese listings. Isn't
that nice?

Next, let's add logic so that *admin* users will *still* see unpublished
cheese listings. And what about the *item* operation? Sure, an unpublished
cheese listing won't be included in the *collection* endpoint... but how can we
prevent a user from making a GET request directly to fetch a *single*,
unpublished `CheeseListing`?
