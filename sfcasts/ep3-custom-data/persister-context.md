# Custom Logic Only for some Operations

Data persisters are *the* way to do logic before or after something saves. But what
if you want to do something only when your object is being created? Or only when
it's being updated? Or maybe only when a specific *field* changes from one value
to another? Let's talk about *all* of that.

Here's our first goal: log a message *only* when a user is *created* in our API.
This one is pretty simple. Start by adding a third argument to the constructor for
`LoggerInterface $logger`. I'll hit Alt+Enter and go to "Initialize Properties" as
a shortcut to create that property and set it.

Down in `persist()`, since this is an entity, we don't need to do anything special
to determine if the object is being created versus updated. We can say: if
`!$data->getId()`, then log something:
`$this->logger->info(sprintf())`:

> user %s just registered. Eureka!

And pass the `$data->getEmail()`.

That's it! Custom code for when a user is created, which in a real app might be
sending a registration email or doing something else. I'm guessing this will work...
but let's at least make sure it didn't break anything:

```terminal
symfony php bin/phpunit --filter=testCreateUser
```

## Custom Code only for a Specific Operation

So let's do something a bit harder: what if you need to run some custom code but
only for a specific *operation*. In `CheeseListing`, we have 2 collection operations
and 3 item operations. For the most part, by checking the id, you can pretty
much figure out which operation is being used. But if you start also using PATCH
operations or custom operations, then this *wouldn't* be enough.


Like many parts of ApiPlatform, a data persister has a normal interface -
`DataPersisterInterface` - but also an optional, *stronger* interface that gives
you access to extra info about what's going on.

Change the interface to `ContextAwareDataPersisterInterface`.

This *extends* `DataPersisterUnterface` and the difference *now* is that all
the methods suddenly have an `array $context`  argument. Copy that and also add
it to `persist()` and down here on `remove()`

Perfect! To see what's inside this array, at the top of persist, `dump($context)`,
and then go run the test again:

```terminal-silent
symfony php bin/phpunit --filter=testCreateUser
```

And... there it is! It has `resource_class`, `collection_operation_name` and a few
other keys. Two things about this. One, this context is *not* the same
"serialization context" array that we talk a lot about. There is some overlap,
but mostly this word "context" is just being re-used. And second, the last 3
items relate to ApiPlatform's event system... and probably won't be useful here.

The *truly* useful thing here is `collection_operation_name`, which will be called
`item_operation_name` for an item operation - like a PUT request. I'll run all
the user tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... yep! There is a good example of both situations.

Armed with this info, we're dangerous. Back in the persister, if
`$context['item_operation_name']` - don't forget your `$` - `?? null` to avoid
a problem when this key does not exist - `=== 'put'`, then this is the "put"
item operation. Log `User %s is being updated` and pass `$data->getId()`.

I love it! When we run the tests now:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

They still pass! I'm not actually checking the logs, but that's ok for now.

By using the context and the `$data->getId()`, we have a lot of control over to
execute custom code in the right situations. But things *can* get more complex.
What if we need to run code only when a specific *field* *changes* from
one value to another?

For example, our `CheeseListing` entity has an `inPublished` field, which - so far -
isn't writable in our API at all. Next: let's make it possible for a user to
publish their listing... but *also* execute some code when that happens.
