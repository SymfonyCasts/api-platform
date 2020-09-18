# Leveraging the Doctrine Data Provider

Our `UserDataProvider` is now responsible for loading the collection of users. But
we lost pagination and filtering because the normal data provider from Doctrine
usually handles that!

## Finding the Core Doctrine Data Provider

Let's actually find that class. I'll hit Shift + Shift and look for
`CollectionDataProvider` - I'm kind of guessing that name. Oh, and make sure to
include "Non-Project" items. Here it is: a `CollectionDataProvider` in the
Doctrine ORM Bridge.

Look down at `getCollection()`: it creates a query builder and then loops
over something called the "collection extensions". This is the Doctrine extension
system in API Platform, and these extensions are actually what is responsible
for changing the query to add things like pagination and filtering. Finally, at
the bottom, we execute the query and get the result.

Oh, and notice: this class uses the `ContextAwareCollectionDataProviderInterface`.
*That* is why - in our `UserDataProvider` - *I* chose to implement that instead
of just `CollectionDataProviderInterface`. I knew that we were going to
*eventually* want to call the core Doctrine provider. And when we do, we would want
to pass it the `$context` argument.

## Finding the Core Service Id

So instead of doing the query ourselves, let's call the core Doctrine service! Our
first job is to... find out what its service ID is! At your terminal,
run `bin/console debug:container` and let's search for `data_provider`?

```terminal-silent
php bin/console debug:container data_provider
```

Let's see: ah! `api_platform.doctrine.orm.collection_data_provider`! Oh, but
there is another one with almost the same name below it. Huh. And at the bottom,
there's *another* one called `api_platform.collection_data_provider`.

This last one is almost definitely the *entire* data provider system: it's the
service that's responsible for calling the `supports()` method on all the
*true* data providers. So we don't want to inject and call *this* because we
would just end up calling ourselves again. We saw this when we decorated the
data persisters.

## Abstract Services

But what about these 2 `orm.collection_data_provider` services up here? Which one
should we use? I'll hit 0 to get more details about the first.

Ah. It's subtle: "abstract: Yes". An abstract service is not a *real* service.
It's more of a *template* that you can use to *build* real services. If we tried
to inject this, Symfony would give us an error to tell us.

Let's run the command again:

```terminal-silent
php bin/console debug:container data_provider
```

This time, check out the other one. Yes! "Abstract no". This is the *real* service.
The "default" is referring to our "default" ORM connection.

## Injecting and Using the Core Provider

Ok! Let's go use this! Over in `UserDataProvider`, remove the `UserRepository`
argument. Instead, inject a collection data provider:
`CollectionDataProviderInterface` and I'll call it `$collectionDataProvider`.

You could also type-hint the *specific* Doctrine data provider class that we
*know* we're going to inject - your call. Rename the argument and property...
and then in `getCollection()`, return
`$this->collectionDataProvider->getCollection()` and pass it `$resourceClass`,
`$operationName` and - it looks a bit silly, but also pass `$context`.

That argument doesn't exist on the `getCollection()` method of
`CollectionDataProviderInterface`, but we know that it *will* exist in the
Doctrine class.

If we stopped now and tried things - um, *don't* try things unless you have XDebug
installed - because... it's recursion! Every programmers favorite thing!

By default, Symfony will autowire the *main* collection data provider. It calls
us, we call it, it calls us, chaos ensues. We had the same problem with
our data persister

To fix this, open `config/services.yaml` and, at the bottom, override the user
data provider service: `App\DataProvider\UserDataProvider`. Add `bind` and, in
this case, we're going to bind the `$collectionDataProvider` argument. Set this
to `@`, then go copy the service id that we know we need, and paste.

*Now* it should work. Refresh the browser and... got it! Let's check the number
of results and... yes! It stops at 30 users but says that there are 51 total.
Pagination is back!

Now that we have a working `UserDataProvider`, we are *ready* to add any custom
fields we need. Let's do it next!
