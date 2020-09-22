# Completely Custom Field via a Data Provider

Most of the time, the fields in our API match the properties in our entity.
But we know that doesn't always need to be the case. In the previous tutorial,
there were several times when we needed to add a *custom* field. For example, in
`CheeseListing`, we added a `shortDescription` field to the API by adding a
`getShortDescription()` method and putting it in the `cheese:read` group:

[[[ code('0566e03198') ]]]

We did the same with `setTextDescription()`: this added a `textDescription`
field that could be *written* in the API:

[[[ code('e9c8f85780') ]]]

## Custom Fields that Require a Service

But you can only take this so far. If you need a service to calculate the value
of a custom field, then you can't use this trick because these classes don't have
*access* to services.

We even tackled this in the previous tutorial! We added an `isMe` boolean
field to `User`. We did this by leveraging a custom normalizer:
`src/Serializer/Normalizer/UserNormalizer`:

[[[ code('2ee2daeeb4') ]]]

We basically added an extra field *right* before the array is turned into JSON.

But this solution had a flaw. Look at the documentation on the `User`
resource: I'll open the "get" operation and, down here, you can see the schema
of what's going to be returned. It does *not* mention `isMe`! This field *would*
be there if you *tried* the endpoint, but our docs have *no* idea it exists.

And... maybe you don't care! Not every app needs to have perfect docs. But if we
*do* care? How *could* we add a custom field that's properly documented?

There are a few ways, like creating a completely custom API resource class or
using an output DTO. We'll talk about both of these later. But in most situations,
there is a simpler solution.

In `UserResourceTest`, if you find `testGetUser()`, we *do* have a test that looks
for the `isMe` field. It's false in the first test and true after we log in as this
user:

[[[ code('c519d51fa4') ]]]

Copy that method name... and let's just make sure it's passing:

```terminal
symfony php bin/phpunit --filter=testGetUser
```

Right now... it *does* pass. Time to break things! In `UserNormalizer`,
remove the `isMe` property. Actually, we can just return
`$this->normalizer->normalize()` directly:

[[[ code('8c750f44b9') ]]]

This class still adds a custom group, but it no longer adds a custom field.
Try the test now:

```terminal-silent
symfony php bin/phpunit --filter=testGetUser
```

A *lovely* failure!

## Adding a Non-Persisted Api Field

Ok: so what is the *other* way to solve this? It's beautifully simple. The idea
is to create a new property inside of `User` but *not* persist it to the database.
The new property will hold the custom data and then we will expose it like a normal
field in our API.

Yep. It's that easy! Oh, but there is one *tiny*, annoying problem: if this field
isn't stored in the database... and we need a service to calculate its value, how
do we *set* it?

Great question. And... there are about 47 different ways. Okay, not 47, but there
are a few ways and we'll look into several of them because different solutions
will work best in different situations.

## Hello Data Providers

For the first solution, let's think about how API Platform works. When we make a
request to `/api/users` or, really, pretty much any endpoint, API Platform needs
to *somehow* load the object or objects that we're requesting.

It does that with its "data provider" system. So, we have data persisters for
*saving* stuff and data providers for *loading* stuff. There are both collection
data providers for loading a collection of objects and item data providers that
load *one* object for the item operations.

Normally, we don't need to think about this system because API Platform has a
built-in Doctrine data provider that handles all of it for us. But if we want to
load some *extra* data, a custom data provider is the key.

## Creating the Data Provider

In the `src/` directory, let's see, make a new `DataProvider/` directory and,
inside, create a new PHP class called `UserDataProvider`:

[[[ code('8347006fcc') ]]]

The idea is that this class will take *full* responsibility for loading user
data... well, for now, just the "collection" user data. Add two interfaces:
`ContextAwareCollectionDataProviderInterface` - yep, that's a *huge* name - and
also `RestrictedDataProviderInterface`:

[[[ code('225159f699') ]]]

Before we talk about these, I'll go to the "Code"->"Generate" menu - or
`Command`+`N` on a Mac - click "Implement Methods" and select both methods
that are needed:

[[[ code('971f22d21b') ]]]

So, to create a collection data provider, the only interface that you should
need - in theory - is something called `CollectionDataProviderInterface`. If you
jump into `ContextAwareCollectionDataProviderInterface`, it *extends* that one.

We've seen this kind of thing before: there's the main interface, and then an optional,
stronger interface. If you implement the stronger one, it adds the `$context`
argument to the `getCollection()` method. So if your data provider needs the
context, you need to use this interface. I'll show you why *we* need the context
in a few minutes.

The `RestrictedDataProviderInterface` is actually what requires the `supports()`
method. If we didn't have that, API Platform would use our data provider for *every*
class, not just the `User` class. Let's add our logic:
`return $resourceClass === User::class`:

[[[ code('c066e534f7') ]]]

Perfect!

Up in `getCollection()` - as its name suggests - our job is to return the array of
users. So... simple, right? We just need to query the database and return every
`User`.

Well, that's not going to be *quite* right, but it's close enough for now. Add
a `public function __construct()` and autowire `UserRepository $userRepository`.
I'll do my `Alt`+`Enter` trick and go to "Initialize Properties" to create that
property and set:

[[[ code('0e9893ce56') ]]]

Now, in `getCollection()`, return `$this->userRepository->findAll()`:

[[[ code('8118640fbf') ]]]

Sweet! Let's try it. We still haven't added the `isMe` field, so instead
of trying the test, let's do this in the browser. Go to `/api/users.jsonld`.

And... oh! If you get "full authentication is required", that's our security
system in action! Go team! In another tab, I'll go back to my homepage and hit
log in. Refresh the original tab again and... sweet! It's working!

But... is it actually using our new data provider? Well... if you look closely,
you can see that we lost something: pagination! Oh, I'm *totally* looking at the
wrong spot here - these are the embedded cheeses for the first user. Scroll down Ryan!
Bah! Well, if I *had* scrolled down we would have seen that all 50 users are
being listed. But normally, pagination only shows 30 at a time.

So... this *proves* that our data provider *is* being used. And that's thanks to
autoconfiguration: if you create a service that implements
`CollectionDataProviderInterface`, API Platform will automatically
find it and start calling its `supports()` method.

So *that's* great... but the fact that we lost pagination and filtering is...
*not* so great. Our API documentation still *advertises* that pagination and
filtering exist, but it's a lie. None of that would work.

It turns out that the core Doctrine data provider is *also* responsible for reading
the page and filter query parameters and changing the query *based* on them.

We don't want to lose that just to add a custom field. So next, let's use our
favorite trick - class decoration - to get that functionality back. *Then*
we can add our custom field.
