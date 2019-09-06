# Automatic Serialization Groups

I want to show you guys something... kind of experimental. We've been following
a strict naming convention inside our API resource classes for the normalization
and denormalization groups. For normalization, we're using `cheese_listing:read`
and for denormalization, we're using `cheese_listing:write`. Then, when we need
even *more* control, we're able to add operation-specific groups like
`cheese_listing:item:get`.

IF you have a lot of different behaviors for your different operations, you may
end up with a *lot* of these `normalizationContext` and `denormalizationContexts`
options... which is a bit ugly... but can also be easy to mess up.

So here's the idea: in `AdminGroupsContextBuilder`, we have the ability to
dynamically and groups. Could we detect that we're *normalizing* a `CheeseListing`
*item* and automatically add the `cheese_listing:read` and `cheese_listing:item:get`
groups? The answer is... of course! But the final solution may not look like you
expect.

## Adding the Automatic Groups

Let's start in `AdminGroupsContextBuilder`. At the bottom, I'm going to paste in
a new function called `private function addDefaultGroups()`. You can copy this from
the code block on this page. This looks at which entity we're working with, whether
it's being normalized or denormalized and exactly the exact *operation* that's
currently being executed. It uses this information to always add *three* groups.
The first is easy: `{class}:{read/write}`. So `user:read`,
`cheese_listing:read` or `cheese_listing:write`. That matches the main groups we've
been using.

The next is more specific: the class name, then `item` or `collection`, which is
whether this is an "item operation" or a "collection operation" - then `read` or
`write`. If we were making a `GET` request to `/api/users`, this would add
`user:collection:read`.

The last is the *most* specific... and is kind of redundant unless you create some
custom operations. Instead of `read` or `write`, the last part is the operation name,
like `user:collection:get`.

To use this method, back up top, add `$context['groups'] = $context['groups'] ?? [];`.
That will make sure that if the `groups` key doesn't exist, it will be set to an
empty array. Now say `$context['groups'] = array_merge()` of `$context['groups']`
and `$this->addDefaultGroups()`, which needs the `$context` and whether or not
the object is being normalized. So, the `$normalization` argument. We can remove
the `$context['groups']` check in the `if` statement because it will *definitely*
be set already.

Oh, and just to clean things up, let's remove any possible duplications:
`$context['groups'] = array_unique($context['groups'])`.

That's it! We can *now* go into `CheeseListing`, for example, and remove the
normalization and denormalization context options. In fact, let's prove everything
still works by running the tests:

```terminal
php bin/phpunit
```

Even though we just *drastically* changed how the groups are added, everything should
work *just* like before. And... it does!
## Ah! My Documentation

So... that was easy, right? Well... remember a few minutes ago when we discovered
that the documentation does *not* read the groups you add via a context builder?
Yep, now that we've removed the `normalizationContext` and `denormalizationContext`
options... our docs are going to be *really* inaccurate.

Refresh the docs... and go look at the GET operation for for single `CheeseListing`
item. This, *still* shows the correct fields... but that's because we're *still*
manually... and *redundantly* setting the `normalization_context` for that one
operation.

But if you look at the *collection* GET operation... it says it will return
*everything*: `id`, `title`, `description`, `shortDescription`, `price`, `createdAt`,
`createdAtAgo`, `isPublished` *and* `owner`. Spoiler alert: it's *not* actually
going to return all of these fields.

If you try this... and hit Execute... it returns only the field we expect. So...
we've added these "automatic" groups... that was kinda nice. But we've now destryoed
our documentation. Can we have both? Yes... by leveraging something called a
resource metadata factory: a wild, low-level, advanced feature of API Platform.
Let's dig into that next.
