# Custom Normalizer: Object-by-Object Dynamic Fields

We now know how to add dynamic groups: we added `admin:read` above `phoneNumber`
and then, via our context builder, we're dynamically adding that group to the
serialization context *if* the authenticated user has `ROLE_ADMIN`.

So... we're pretty cool! We can easily run around and expose input our output fields
only to admin users by using these two groups.

But... the context builder - and also more advanced resource metadata factory - has
a tragic flaw! We can only change the context *globally*. What I mean is, we're
deciding which groups should be used for this *entire* request. It does *not* allow
us to change the groups on an object-by-object basis.

Let me give you a concrete example: in addition to making the `$phoneNumber` readable
by admin users, I *now* want it to *also* be readable by the user *itself*: if I
make a request to fetch my *own* data, I want the endpoint to return the `phoneNumber`
field.

You might think:

> Ok, let's add some new group like `owner:read` and add that group dynamically.

That's great thinking! But look in the context builder, look at what's passed to
the `createFromRequest()` method... or really, what's *not* passed: it does *not*
pass us the *object* that's being serialized. Nope, this method is called just
*once* per request and creates the context for *any* objects that may be serialized.

## Creating a Normalizer

Ok, no worries. Context builders are a *great* way to add or remove groups on
a global or class-by-class basis. But they are *not* the way to dynamically add
or remove groups on an object-by-object basis. Nope, for that we need a custom
normalizer. We can user MakerBundle to help us. Run:

```terminal
php bin/console make:serializer:normalizer
```

Call this `UserNormalizer`. When an object is being transformed into JSON, XML
or any format, it goes through two steps. First, a "normalizer" transforms the
object into an array. And second, an "encoder" transforms that array into whatever
format you want - like JSON or XML.

When a `User` object is serialized, it's already going through a core normalizer
that looks at our normalization groups & reads the data via the getter methods.
We're now going to hook *into* that process so that we an change the normalization
groups *before* that core normalizer does its job.

Go check out the new class: `src/Serializer/Normalizer/UserNormalizer.php`. This
works a bit differently than the context builder - it works more like the voter
system. The serializer doesn't have just *one* normalizer, it has *many* of them.
Each time it needs to normalize something, it loops over *all* the normalizers,
calls `supportsNormalization()` and passes us the data that it needs to normalize.
If we return `true` from `supportsNormalization()`, it means that *we* know how
to normalize this data. And so, the serializer will call our `normalize()` method.
Our normalizer is then the *only* normalizer that will be called for this data:
we are 100% responsible for transforming the object into an array.

## Normalizer Logic

Of course... we don't *really* want to *completely* take over the normalization
process. What we *really* want to do is change the normalization groups and then
call the *core* normalizer so it can do its normal work. That's why the class was
generated with a constructor and we're autowiring a class called `ObjectNormalizer`.
This is the main, core, normalizer: it's the one that's responsible for reading
the data via our getter methods. Right now, our normalizer is basically... just
offloading all the work to it.

Ok, let's start customizing this! For `supportsNormalization()`, return
`$data instanceof User`. So if the thing that's being normalized is a `User` object,
we handle that.

Now we know that `normalize()` will *only* be called if `$object` is an instance
of `User`. Let's add some PHPDoc above this to help my editor.

The goal here is to check to see if the `User` object that's being normalized is
the *same* as the currently-authenticated `User`. If it is, we'll add that
`owner:read` group. Add that check on top: if `$this->userIsOwner($object)` -
that's a method we'll create in a second - then, add the group. The `$context`
is passed as the third argument... and we're passing *it* to the core normalizer
below. Let's modify it first! Use `$context['groups'][] = 'owner:read`.

That's lovely! A normalizer is only used for... um... *normalizing* an object to
an array - it is *not* used for *denormalizing* an array back into an object. That's
why we're always adding `owner:read` here. If you wanted to create this *same*
feature for denormalization... and add an `owner:write` group - you'll need to
create a separate *denormalizer* class. There's no MakerBundle command to generate
it, but the logic will be almost identical to this.

Oh, also, we don't need to check for the existing of a `groups` key on the array
because, in our system, we are *always* setting at least one group. So, this will
always, already exist.

Let's add that private function: `private function userIsOwner()`. This will take
a `User` object and return a `bool`. For now, fake this: `return rand(0, 10) > 5`.

And... I think that's it! Like with voters, this is a situation where we *don't*
need to add any configuration: as soon as we create a class and make it implement
`NormalizerInterface`, API Platform will see it and start using it.

So... let's try it! Back on the docs, I'm currently *not* logged in. Let's refresh
the page... and create a new user to make sure we have one. Let's see, email
`goudadude@example.com`, password `foo`, same username, no `cheeseListings`, but
*with* a `phoneNumber`. Execute and... perfect! A 201 status code. Copy that
`email`... then go back to the homepage to log in: `goudadude@example.com` and
password `foo`.

Cool! Now that we're authenticated, head back to `/api`. Yep, on the web debug
toolbar, we *are* logged in. Let's try the GET operation to fetch a collection
of users. Because of our random logic, I'd expect *some* results to show the
`phoneNumber` and some not. Execute and... the first user has no `phoneNumber`,
second user, no `phoneNumber` and the third user... also has no `phoneNumber`.
Huh. That was hopefully just bad luck. Try it again. And... yes! This time
*two* of the three users has a `phoneNumber` field. Our group is being added dynamically
on an object-by-object basis!

So by using a normalizer, we're able to add dynamic groups on an object-by-object
basis. And we could dynamically add an `owner:write` field during denormalization
by creating a class, making it implement `DenormalizerInterface` and repeating
some very similar logic.

But.. what a second. Something is wrong... we're missing the JSON-LD fields from
these users. Well, ok, we have them on the top-level for the collection itself...
and even the embedded `CheeseListing` data has them... but each user is now missing
`@id` and `@type`. Something in our new normalizer killed the JSON-LD stuff!

Next, let's figure out what's going on and fix it!
