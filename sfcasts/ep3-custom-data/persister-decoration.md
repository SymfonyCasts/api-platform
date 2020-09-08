# Data Persister Decoration

In the last course, we create this `UserDataPersister` class.

## Um, what is a "Persister"?

Now, let's back up real quick. Whenever you use a `POST` or `PUT` endpoint, after
ApiPlatform deserializes that data into an object and validates it, it tries
to *save* or *persist* that object. Usually, this means that we're saving an entity
object to the database with Doctrine. But we could "save" an object anywhere, like
by sending the data to *another* API, or putting it into Redis or ElasticSearch.
ApiPlatform doesn't really care. These "things that save the object" are called
"data persisters".

So far, our two API resources - `CheeseListing` and `User` - are both entities,
and ApiPlatform has special support for Doctrine, *including* a core Doctrine
data persister. So whenever we make a `POST`, `PUT` or also `PATCH` request to
an ApiResource that is am entity, that core Doctrine data persister jumps into
action and knows how to save the object to the database. This means that
*normally* we don't need to create our *own* data persister: the one in core
calls `persist()` and `flush()` for us.

## Taking Action Before/After Save

But what if you need to *do* something right before or after an item is saved
to the database? Well, you *could* use a Doctrine listener for that, but if you
want that code to only run in the context of your API, what can you do? The answer
is to create a custom data persister. We created `UserDataPersister` because
we needed to encode the plain password and set it onto the password field before
saving.

To create this data persister, we implemented `DataPersisterInterface` and added
a `supports()` method that says we support `User` objects. Now, as *soon* as we
did this, *we* became *100%* responsible for persisting `User` objects. What I
mean is, the normal, *core* Doctrine data persister will *no* longer be called
for `User` objects: our persister takes precedence. This is why we did our custom
work up here, but then had to call `persist()` and `flush()` at the bottom.

To prove that our data persister *replaces* the core persister for `User` objects,
let's comment-out the `persist()` call and our tests. Open
`tests/Functional/UserResourceTest`. This `testCreate` user method *should* now
fail because the User won't *actually* be saved to the database.

Copy that method name and run:

```terminal
symfony run bin/phpunit --filter=testCreateUser
```

And... yea! We failed! This test passed a minute ago, but now we get a 400
bad request... which happens because the unpersisted entity is missing an `id`
and so ApiPlatform has trouble serializing it.

If we put the `persist()` back and run the test again:

```terminal-silent
symfony run bin/phpunit --filter=testCreateUser
```

It passes.

## Decorating the Core Persister

So the fact that we are now *entirely* responsible for persisting the object is...
not really that big of a deal. But it would be even *better* if we could do our
custom work up here and then just call the *core* data persister so that *it* can
do its normal work.

And that's totally possible via decoration. Yep, instead of injecting the
entity manager up here, replace this with `DataPersisterInterface` and call it,
how about, `$decoratedDataPersister`. Copy that and rename the variable and the
`$entityManager` property to `$decoratedDataPersister`.

This is nice because, down here, instead `persist()` and `flush()`, all we need
is `$this->decoratedDataPersister->persist($data)`.

Down in `remove()`, we can do the same: `$this->decoratedDataPersister->remove($data)`.

Beautiful! But... before we try this... um... *don't* unless you have Xdebug
installed. Because... when I run my test:

```terminal-silent
symfony run bin/phpunit --filter=testCreateUser
```

Ah! It's recursion!

> Maximum function nesting level of 256 reached

Ok, let's figure out what's going on here. In the constructor, we just said
`DataPersisterInterface` and Symfony, apparently, figure out what to pass us.
This means that the data persister must be passed via autowiring.

Let's go get more info about this service. Run:

```terminal
php bin/console debug:autowiring Persister
```

And... there it is! This is an alias for some service called
`debug.api_platform.data_persister`. Ok, let's find more info about *that*
service:

```terminal
php bin/console debug:container debug.api_platform.data_persister
```

Ok: the class name is `TraceableChainDataPersister`. The key word in the name
is "chain". If we dug a few levels deeper, we would find out that the data
persister service *actually* holds a *collection* of data persisters inside of it!

Basically, whenever something needs to be persisted, ApiPlatform calls `persist()`
on this *one* data persister. Internally, it then loops over each data persister
inside of it, calls `supports()` on each one, and then calls `persist()` on the
first persister that supports this object.

So... this is really cool! If you need the *entire* data persister system, you
can autowire this one service and call `persist()` on it.

The *problem* is that we are *effectively* calling ourselves! ApiPlatform
originally calls persist on the `ChainDataPersister`, it calls `persist()` on us,
and then we call `persist()` back on the `ChainDataPersister`. Whoops!

So instead of calling the injecting the *entire* data persister system, let's
just inject and use the *one* data persitser we know we want: the one that is
*normally* used to save entities. We'll do that next and learn more about how
the data persister system is different than some other parts of ApiPlatform,
like the context builder system.
