# A "Normalizer Aware" Normalizer

When a `User` object is being normalized, our `UserNormalizer` is called. After
adding a dynamic group to `$context`, we want to send the `User` object *back*
through full normalizer system: we want the *original*, "core" normalizer - whatever
class that might be - to do its magic. Right now, we're not *quite* accomplishing
that because we're directly calling a *specific* normalizer - `ObjectNormalizer`.

So... how can we call the "normalizer chain" instead of this specific normalizer?
By implementing a new interface: `NormalizerAwareInterface`.

This requires us to have a single new method `setNormalize()`. When the serializer
system sees this, *before* it calls us, it will call `setNormalize()` and pass us
a normalizer that *really* holds the "chain" of normalizers. It, sort of, passes
us the top-level normalizer - the one that is responsible for looping over all
of the normalizers to find the correct one.

So, we *won't* autowire `ObjectNormalizer` anymore: remove the constructor and
that property. Instead, `use NormalizerAwareTrait`. That trait is just a shortcut:
it has a `setNormalizer()` method and it stores the normalizer on a protected
property.

The end result is that `$this->normalizer` is *now* the main, normalizer chain.
We add the group, then pass `User` back through the original process.

Some of you *may* already see a problem with this. When we hit Execute to try this...
it runs for a few seconds, then... error!

> Maximum function nesting level of 256 reached, aborting!

Yay! Recursion! We're changing the `$context` and then calling `normalize()` on
the original normalizer chain. Well... guess what that does? It once again calls
`supportsNormalization()` on this object, we return true, and it calls `normalize()`
on us again. We're basically calling *ourselves* over and over and over again.

Wah, wah. Hmm. We only want to be called *once* per object being normalized. What
we need is some sort of flag... something that says that we've *already* been called.

## Avoiding Recursion with a $context Flag

The way to do this is by adding that flag to the `$context` itself. Then, down in
`supportsNormalization()`, if we see that flag, it means that we've already been
called and we can return false.

But wait... the `$context` isn't passed to `supportsNormalization()`... so that's
a problem. Well... not a big problem - we can get that by tweaking our interface.
Remove `NormalizerInterface` and replace it with `ContextAwareNormalizerInterface`.
We can do that because it extends `NormalizerInterface`. The only difference is
that this interface requires our method to have one extra argument: `array $context`.

For the flag, let's add a constant: `private const ALREADY_CALLED` set to, how
about, `USER_NORMALIZER_ALREADY_CALLED`. Now, in `normalize()`, right *before*
calling the original chain, set this: `$context[self::ALREADY_CALLED] = true`.
Finally, in `supportsNormalization()`, if `isset($context[self::ALREADY_CALLED]`,
return false. That will allow the "normal" normalizer to be used on the second
call.

We've done it! We've fixed the recursion! Let's celebrate by hitting Execute and...
of course... we *somehow* get the *exact* same thing. We still have recursion!

## Removing hasCacheableSupportsMethod()

We're missing *one* subtle detail. Find the `hasCacheableSupportsMethod()` - this
was generated for us - and return `false`. Go backup, hit Execute and... it
works! The `phoneNumber` field is still randomly included... because we have some
random logic in our normalizer, but the `@id` and `@type` JSON-LD stuff is back.

The `hasCacheableSupportsMethod()` is an optional method that each normalizer can
have... which relates to this optional interface - `CacheableSupportsMethodInterface`.
The purpose of this interface and the method is performance.

Because *every* piece of the object graph is normalized, the serializer calls
`supportsNormalization()`... a *lot* of times. If your `supportsNormalization()`
method *only* relies on the `$format` and the *class* of `$data` - basically
`$data instanceof User`, then you can return `true` from `hasCacheableSupportsMethod()`.
When you do this, the serializer will only call `supportsNormalization()` once per
class. That can speed things up.

But as *soon* as you rely on the `$data` itself or the `$context`, you need to
return false - or remove this interface entirely - both have the same result. This
forces the serializer to call our `supportNormalization()` method each time the
chain is called. That *does* have a performance impact, but as long as your logic
is fast, it should be minor. And *most* importantly, it fixes our issue!

Next, let's add the proper security logic to our class and then investigate on
*other* superpower of normalizers, the ability to add *completely* custom fields.
We'll add a strange... but potentially useful boolean field to `User` called `isMe`.
