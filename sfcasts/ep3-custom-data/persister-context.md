# Custom Logic Only for some Operations

Data persisters are *the* way to do logic before or after something saves. But what
if you want to do something only when your object is being created? Or only when
it's being updated? Or maybe only when a specific *field* changes from one value
to another? Let's talk about *all* of that.

Here's our first goal: log a message *only* when a user is *created* in our API.
And this one is pretty simple. Start by adding a third argument to the constructor for
`LoggerInterface $logger`. I'll hit `Alt`+`Enter` and go to "Initialize Properties" as
a shortcut to create that property and set it:

[[[ code('fa3fada5b6') ]]]

Down in `persist()`, since this is an entity, we don't need to do anything fancy
to determine if the object is being created versus updated. We can say: if
`!$data->getId()`:

Then log something: `$this->logger->info(sprintf())`:

[[[ code('3e0151dc47') ]]]

> User %s just registered. Eureka!

And pass `$data->getEmail()`:

[[[ code('166f386b6b') ]]]

That's it! Custom code for when a user is created, which in a real app might be
sending a registration email or throwing a party!

I'm guessing this will all work... but let's at least make sure we didn't break
anything:

```terminal
symfony php bin/phpunit --filter=testCreateUser
```

## Custom Code only for a Specific Operation

Cool! So let's do something a bit harder: what if you need to run some custom code
but only for a specific *operation*. In `User`, we have 2 collection operations
and 3 item operations:

[[[ code('4e2279e538') ]]]

For the most part, by checking the id, you can pretty much figure out which
operation is being used. But if you start also using a PATCH operation or
custom operations, then this would *not* be enough.

Like many parts of ApiPlatform, a data persister has a normal interface -
`DataPersisterInterface`:

[[[ code('17cd5662f1') ]]]

But also an optional, *stronger* interface that gives you access to extra info
about what's going on.

Change the interface to `ContextAwareDataPersisterInterface`:

[[[ code('35d46c6603') ]]]

This *extends* `DataPersisterInterface` and the difference *now* is that all
the methods suddenly have an `array $context` argument. Copy that and also add
it to `persist()` and down here on `remove()`:

[[[ code('4a219fa94e') ]]]

Beautiful! To see what's inside this array, at the top of persist, `dump($context)`:

[[[ code('4c0a0cb1ab') ]]]

And then go run the test again:

```terminal-silent
symfony php bin/phpunit --filter=testCreateUser
```

And... there it is! It has `resource_class`, `collection_operation_name` and a few
other keys. Two things about this. One, this context is *not* the same as the
"serialization context" array that we've talked about a lot. There *is* some overlap,
but mostly this word "context" is just being re-used. Two: the last 3
items relate to ApiPlatform's event system... and probably won't be useful here.

The *truly* useful item is `collection_operation_name`, which will be called
`item_operation_name` for an item operation - like a PUT request. I'll run all
the user tests:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... yep! There's a good example of both situations.

Armed with this info, we are dangerous! Back in the persister, if
`$context['item_operation_name']` - don't forget your `$` - `?? null` - to avoid
an error when this key does not exist - `=== 'put'`:

[[[ code('bc19085638') ]]]

Then this is the PUT item operation. Log `User %s is being updated` and pass
`$data->getId()`:

[[[ code('3ccdd7f81b') ]]]

I love it! When we run the tests now:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

They still pass! I *am* assuming that the logs *are* being written correctly...
I would at least test this manually in a real app.

By using the context and the `$data->getId()`, we have a lot of control to
execute custom code in the right situations. But things *can* get more complex.
What if we need to run code only when a specific *field* *changes* from
one value to another?

For example, our `CheeseListing` entity has an `isPublished` field, which - so far -
isn't writable in our API at all:

[[[ code('2789613da1') ]]]

Next: let's make it possible for a user to publish their listing... but *also*
execute some code when that happens.
