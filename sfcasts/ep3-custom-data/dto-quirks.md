# DTO Quirks

The last field that we're missing on `CheeseListingOutput` is `owner`.

No worries: in `CheeseListingOutput`, add `public $owner`. Then copy the
PHPDoc from `price` and paste that here. We know that this will be a `User` object
and we'll put it in the `cheese:read` group.

Over in the data transformer, populate that with
`$output->owner = $cheeseListing->getOwner()`.

Easy enough! Try it: find your browser, refresh and it works! The `owner` field
is an embedded object because the `phoneNumber` field is being exposed.

It turns out, this detail is important. Go into the `User` class and look at the
`phoneNumber` property. This is actually in two groups: `owner:read` an
`admin:read`.

Right now, I'm logged in as an *admin*... and we created special code in the last
tutorial to always add the `admin:read` group in this situation. *This* is the
reason why we're able to see the `phoneNumber` on every user.

Let's see what happens when we are *not* an admin. Open a new tab, go to the
homepage and click log out. Perfect.

## User Serialization Has Changed?

Now that we're anonymous, refresh the same endpoint. Error? Interesting:

> The return value of UserNormalizer::normalize() - that's a class we created
> in a previous tutorial - must be type array, string returned.

Let's go check that out: `src/Serializer/Normalizer/UserNormalizer.php`.
The purpose of this is to add an extra `owner:read` group *if* the `User`
that's being serialized is the currently-authenticated user.

The error says that this method is returning a string but it's *supposed* to return
an array. And... it's right. Look at my `normalize()` method: I gave it an
array return type. But apparently, when we call `$this->normalizer->normalize()`,
this returns a string.

And, hmm... that makes sense. Now that we're anonymous, the `phoneNumber` field
will *not* be returned. And so, when the embedded `User` object is serialized,
instead of returning an array of fields, it is now *normalized* into its IRI
*string*.

Ok! So if you normalize a `User` object, sometimes it will be an object and
sometimes it will be an IRI string. The fix is to remove the array return type.
That was actually *never* needed... it's not on the `normalize()` method's
interface. I added it simply because I *thought* this would always return an array.

When we refresh now... yep! The `owner` property is an IRI string.

But... wait a second. Why didn't we have this error before? Before we started
working with the output DTO stuff... shouldn't we have had this *same* problem
with `UserNormalizer`? Why wasn't it a problem until now?

## DTO's Serialize Differently

Here's the answer, and it's *important*. When you use an output class like we're
doing for `CheeseListing`, the object that's ultimately *serialized* is
`CheeseListingOutput`. And because that class isn't *technically* an API Resource
class, it's serialized in a *slightly* different way internally. For the serialization
nerds out there, API resource classes are *usually* normalized using
`ItemNormalizer` which extends `AbstractItemNormalizer`. But with a DTO object,
it *instead* uses the simpler `ObjectNormalizer`.

## Where Did @type Go?

This causes small, but important differences. For example, when you use an output
DTO, the `@type` field is gone. We have `@id`... but not `@type`. This actually
makes one of our tests fail.

Find your terminal and run:

```terminal
symfony php bin/phpunit
```

Yep! One failure because one test is *looking* for `@type`. Let's open this test
up: `tests/Functional/CheeseListingResourceTest.php` and then scroll down to
`testGetCheeseListingCollection()`. Let's see... here it is: that `@type` is *no*
longer being returned. For now, just delete it so that the tests will pass.

But good news! Thanks to the API Platform team, this bug *has* been fixed and
should be released in API Platform 2.5.8. But since that hasn't been released yet
at the time of this recording, we'll move on.

Run the tests again:

```terminal-silent
symfony php bin/phpunit
```

And... green! Next, I want to look a bit deeper at how the serialization of
embedded objects is different with DTO classes and what to do about it.
