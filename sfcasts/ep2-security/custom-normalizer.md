# Custom Normalizer: Object-by-Object Dynamic Fields

We now know how to add dynamic groups: we added `admin:read` above `phoneNumber`
and then, via our context builder, we're dynamically adding that group to the
serialization context *if* the authenticated user has `ROLE_ADMIN`.

So... we're pretty cool! We can easily run around and expose input our output fields
only to admin users by using these two groups.

But... the context builder - and also the more advanced resource metadata factory -
has a tragic flaw! We can only change the context *globally*. What I mean is, we're
deciding which groups should be used for normalizing or denormalizing a specific
*class*... no matter how many different objects we might be working with. It
does *not* allow us to change the groups on an object-by-object basis.

Let me give you a concrete example: in addition to making the `$phoneNumber` readable
by admin users, I *now* want a user to *also* be able to read their *own*
`phoneNumber`: if I make a request and the response will contain data for my
*own* `User` object, it *should* include the `phoneNumber` field.

You might think:

> Ok, let's put `phoneNumber` in some new group, like `owner:read`... and add
> that group dynamically in the context builder.

That's great thinking! But... look in the context builder, look at what's passed to
the `createFromRequest()` method... or really, what's *not* passed: it does *not*
pass us the specific *object* that's being serialized. Nope, this method is called
just *once* per request.

## Creating a Normalizer

Ok, no worries. Context builders are a *great* way to add or remove groups on
a global or class-by-class basis. But they are *not* the way to dynamically add
or remove groups on an object-by-object basis. Nope, for that we need a custom
normalizer. Let's convince MakerBundle to create one for us. Run:

```terminal
php bin/console make:serializer:normalizer
```

Call this `UserNormalizer`. When an object is being transformed into JSON, XML
or any format, it goes through two steps. First, a "normalizer" transforms the
object into an array. And second, an "encoder" transforms that array into whatever
format you want - like JSON or XML.

When a `User` object is serialized, it's already going through a core normalizer
that looks at our normalization groups & reads the data via the getter methods.
We're now going to hook *into* that process so that we can change the normalization
groups *before* that core normalizer does its job.

Go check out the new class: `src/Serializer/Normalizer/UserNormalizer.php`. 

[[[ code('e552154f0f') ]]]

This works a bit differently than the context builder - it works more like the voter
system. The serializer doesn't have just *one* normalizer, it has *many* normalizers.
Each time it needs to normalize something, it loops over *all* the normalizers,
calls `supportsNormalization()` and passes us the data that it needs to normalize.
If we return `true` from `supportsNormalization()`, it means that *we* know how
to normalize this data. And so, the serializer will call *our* `normalize()` method.
Our normalizer is then the *only* normalizer that will be called for this data:
we are 100% responsible for transforming the object into an array.

## Normalizer Logic

Of course... we don't *really* want to *completely* take over the normalization
process. What we *really* want to do is change the normalization groups... and then
call the *core* normalizer so it can do its normal work. That's why the class was
generated with a constructor where we're autowiring a class called `ObjectNormalizer`.
This is the main, core, normalizer for objects: it's the one that's responsible
for reading the data via our getter methods. So... cool! Our custom normalizer is basically... just offloading all the work to the *core* normalizer!

Let's start customizing this! For `supportsNormalization()`, return
`$data instanceof User`. So if the thing that's being normalized is a `User` object,
we handle that.

[[[ code('212ef6e7a0') ]]]

Now we know that `normalize()` will *only* be called if `$object` is a `User`.
Let's add some PHPDoc above this to help my editor.

[[[ code('9732dd0102') ]]]

The goal here is to check to see if the `User` object that's being normalized is
the *same* as the currently-authenticated `User`. If it is, we'll add that
`owner:read` group. Add that check on top: if `$this->userIsOwner($object)` -
that's a method we'll create in a minute - then, add the group. The `$context`
is passed as the third argument... and we're passing *it* to the core normalizer
below. Let's modify it first! Use `$context['groups'][] = 'owner:read`.

That's lovely! A normalizer is only used for... um... *normalizing* an object to
an array - it is *not* used for *denormalizing* an array back into an object. That's
why we're always adding `owner:read` here. If you wanted to create this *same*
feature for denormalization... and add an `owner:write` group.. you'll need to
create a separate *denormalizer* class. There's no MakerBundle command to generate
it, but the logic will be almost identical to this... and you can even make your
*one* normalizer class implement both `NormalizerInterface` *and*
`DenormalizerInterface`.

Oh, also, we don't need to check for the existence of a `groups` key on the array
because, in our system, we are *always* setting at least one group.

Let's add that missing method: `private function userIsOwner()`. This will take
a `User` object and return a `bool`. For now, fake it: `return rand(0, 10) > 5`.

[[[ code('1de2216072') ]]]

And... I think that's it! Like with voters, this is a situation where we *don't*
need to add any configuration: as soon as we create a class and make it implement
`NormalizerInterface`, the serializer will see it and start using it.

So... let's take this for a test drive! Back on the docs, I'm currently *not*
logged in. Let's refresh the page... and create a new user. How about email
`goudadude@example.com`, password `foo`, same username, no `cheeseListings`, but
*with* a `phoneNumber`. Execute and... perfect! A 201 status code. Copy that
`email`... go back to the homepage.. and log in: `goudadude@example.com`,
password `foo` and... go!

Cool! Now that we're authenticated, head back to `/api`. Yep, the web debug
toolbar *confirms* that I'm a "gouda dude". Let's try the GET operation to fetch
a collection of users. Because of our random logic, I'd expect *some* results to
show the `phoneNumber` and some not. Execute and... hey! The first user has a
`phoneNumber` field! It's null... because apparently we didn't set a `phoneNumber`
for that user, but the field *is* there. And, thanks to the randomness, there
is *no* `phoneNumber` for the second and third users. If you try the operation
again... yes! This time the first *and* second users have that field, but not
the third. Hey! We're now dynamically adding the `owner:read` group on an
object-by-object basis! Normalizers rock!

But... wait a second. Something is wrong! We're missing the JSON-LD fields for
these users. Well, ok, we have them on the top-level for the collection itself...
and even the embedded `CheeseListing` data has them... but each user is missing
`@id` and `@type`. Something in our new normalizer killed the JSON-LD stuff!

Next, let's figure out what's going on, find this bug, then *crush* it!
