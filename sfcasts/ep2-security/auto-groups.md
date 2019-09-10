# Automatic Serialization Groups

I want to show you something... kind of experimental. We've been following
a strict naming convention inside our API resource classes for the normalization
and denormalization groups. For normalization, we're using `cheese_listing:read`
and for denormalization, `cheese_listing:write`. When we need even *more*
control, we're adding an operation-specific group like `cheese_listing:item:get`.

If you have a lot of different behaviors for each operation, you may end up with
a *lot* of these `normalization_context` and `denormalization_context` options...
which is a bit ugly... but also error prone. When it comes to controlling which
fields are and are *not* exposed to our API, this stuff is important!

So here's my idea: in `AdminGroupsContextBuilder`, we have the ability to
dynamically add groups. Could we detect that we're *normalizing* a `CheeseListing`
*item* and automatically add the `cheese_listing:read` and `cheese_listing:item:get`
groups? The answer is... of course! But the final solution may not look *quite*
like you expect.

## Adding the Automatic Groups

Let's start in `AdminGroupsContextBuilder`. At the bottom, I'm going to paste in
a new method: `private function addDefaultGroups()`. You can copy the method from
the code block on this page. This looks at which entity we're working with, whether
it's being normalized or denormalized and the exact *operation* that's
currently being executed. It uses this information to always add *three* groups.
The first is easy: `{class}:{read/write}`. So `user:read`, `cheese_listing:read`
or `cheese_listing:write`. That matches the main groups we've been using.

[[[ code('9fd55332ac') ]]]

The next is more specific: the class name, then `item` or `collection`, which is
whether this is an "item operation" or a "collection operation" - then `read` or
`write`. If we're making a `GET` request to `/api/users`, this would add
`user:collection:read`.

The last is the *most* specific... and is kind of redundant unless you create some
custom operations. Instead of `read` or `write`, the last part is the operation name,
like `user:collection:get`.

To use this method, back up top, add `$context['groups'] = $context['groups'] ?? [];`.
That will make sure that if the `groups` key does *not* exist, it will be added
and set to an empty array. Now say `$context['groups'] = array_merge()` of
`$context['groups']` and `$this->addDefaultGroups()`, which needs the `$context`
and whether or not the object is being normalized. So, the `$normalization` argument.

[[[ code('1716a9e06d') ]]]

We can remove the `$context['groups']` check in the `if` statement because it will
*definitely* be set already.

[[[ code('2611486645') ]]]

Oh, and just to clean things up, let's remove any possible duplications:
`$context['groups'] = array_unique($context['groups'])`.

[[[ code('e7f2f685a2') ]]]

That's it! We can *now* go into `CheeseListing`, for example, and remove the
normalization and denormalization context options. 

[[[ code('686cb60ad9') ]]]

In fact, let's prove everything still works by running the tests:

```terminal
php bin/phpunit
```

Even though we just *drastically* changed how the groups are added, everything *still*
works!

## Ah! My Documentation

So... that was easy, right? Well... remember a few minutes ago when we discovered
that the documentation does *not* see any groups that you add via a context builder?
Yep, now that we've removed the `normalizationContext` and `denormalizationContext`
options... our docs are going to start falling apart.

Refresh the docs... and go look at the GET operation for a single `CheeseListing`
item. This... actually... *still* shows the correct fields. That's because we're
*still* manually - and now *redundantly* - setting the `normalization_context` for
that one operation.

But if you look at the *collection* GET operation... it says it will return
*everything*: `id`, `title`, `description`, `shortDescription`, `price`, `createdAt`,
`createdAtAgo`, `isPublished` *and* `owner`. Spoiler alert: it will *not* actually
return all of those fields.

If you try the operation... and hit Execute... it *only* returns the fields we expect.
So... we've added these "automatic" groups... which is kinda nice. But we've
positively *destroyed* our documentation. Can we have both automatic groups *and*
good documentation? Yes! By leveraging something called a resource metadata
factory: a wild, low-level, advanced feature of API Platform.

Let's dig into that next.
