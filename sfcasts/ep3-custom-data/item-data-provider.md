# Custom Item Data Provider

We've successfully used our collection data provider to add a custom `isMe` field
to each `User` resource on the *collection* operation. If we go to
`/api/users.jsonld`, we can see it.

But if we go to an individual item, we get this error because the `isMe` field is
not being set at all. We can also see this in our tests - the `testGetUser()` method
looks for the `isMe` field on an *item* endpoint. Try it:

```terminal
symfony php bin/phpunit --filter=testGetUser
```

And this should fail with the same error in the browser. Hmm, it *does* fail...
but not quite like I expected. Ah, I need to make my test smarter. Before checking
the JSON, add `$this->assertResponseStatusCodeSame(200)`.

Now when we try the test:

```terminal-silent
symfony php bin/phpunit --filter=testGetUser
```

Yes! It *was* failing with the same 500 error - and now it's more obvious.

Anyways, to make this test pass, we *also* need to populate the `isMe` field when
a *single* `User` is fetched. Yep, we have a *collection* data provider, now we
need an *item* data provider.

You can do this in a separate class, but it's totally fine to combine the
collection and item data providers in the same file.

## Creating the Item Data Provider

Add a third interface called - wait for this mouthful -
`DenormalizedIdentifiersAwareItemDataProviderInterface`.

Wow. If you jump into this class, it extends a less strict
`ItemDataProviderInterface`. I'm implementing that other crazy interface because
that's what the *core* Doctrine item data provider uses, and I want to be able
to pass it the same arguments.

Ok: go to the Code -> Generate menu - or Command + N on a Mac - and implement the
one method we need: `getItem()`. Then let's *immediately* inject the core doctrine
item provider. Add the new argument: `ItemDataProviderInterface $itemDataProvider`.
Hit Alt+Enter to initialize that property and then... because I'm a bit obsessive
about order, I'll make sure this is the *second* property.

Finally, down in `getItem()`, return `$this->itemDataProvider->getItem()` - yes
I *totally* just forgot the `itemDataProvider` part - I'll catch that in a second -
and pass this `$resourceClass`, `$id`, `$operationName` and also `$context`.

To tell Symfony to *specifically* pass us the Doctrine item provider, go back to
services.yaml: we need to add one more bind. The name of the argument is
`$itemDataProvider` - so I'll copy that - and that should be passed the *same*
service id as above, but with the name "item". If you used `debug:container`,
that's what you would see.

Beautiful! Before we try this, let's jump straight it and set the `isMe` field.
To do that, instead of returning, add `$item =` and I'll - as usual - put some
phpdoc above this because *we* know this will be a `User` object or null, because
it's possible that the id wasn't found in the database. To prevent that being a
problem, if not, `$item`, then, then return null.

At this point, we *know* we have a `User` object. So we can say
`$item->setIsMe()` `$his->security->getUser() === $item`. Finish by returning
`$item` at the bottom.

Ok! We're ready to try the test:

```terminal-silent
symfony php bin/phpunit --filter=testGetUser
```

And... ah! Recursion! The problem is coming from line 43 of `UserDataProvider`.
That's the dummy mistake I made a few minutes ago: add `itemDataProvider->`
and then `getItem()`. Come on Ryan!

Now when we try it:

```terminal-silent
symfony php bin/phpunit --filter=testGetUser
```

Got it! Congrats team! We just added a custom `isMe` field that is returned on
both the item and collection operations *and* appears inside of our API properly
documented.

## Don't Forget Populating in the Data Persister

But... surprise! There is *one* last spot where the data is missing! Open
`UserResourceTest` and go to the top: the first test is about creating a user.
Copy that method name and run the tests with `--filter=testCreateUser`:

```terminal-silent
symfony php bin/phpunit --filter=testCreateUser
```

Ah! It explodes with a 500 error! Apparently the `isMe` field hasn't been
initialized! We haven't set it yet!

And... if you *think* about it... that makes sense! This is the *one* situation
where the data provider system is never called! It's called when you load a
collection of items or a single item, but when you're creating a *new* item,
it's not!

The fix for this is to *also* make sure that we set this field in the data persister.
At the top, add one more argument: `Security $security` then I'll initialize that
property.

Below in `persist()` -  we could add the logic in the if statement where we *know*
this is aa new item, but it doesn't hurt to set it every time a `User` is saved.
Add `$data->setIsMe($this->security->getUser() === $data`.

Try the test now:

```terminal-silent
symfony php bin/phpunit --filter=testCreateUser
```

We got it!

What we just did is a really natural way to use entities in API Platform... but
*also* have the ability to add custom fields that requires a service to set its
value data.

But, I admit, it's not the *easiest* thing. This required both a collection *and*
item data provider... and we also needed to add the same code in a data persister.

What I *love* about this solution is that the field isn't really *custom*: it's
a *real* field that we added our ApiResource. The *tricky* part is *populating*
that field. And it turns out, there are several *other* ways to do this, which
might be better depending on the situation. Let's talk about those next.
