# Serialization Groups

If the only way to control the input and output of our API was by controlling
the getters and setters on our entity, it wouldn't be that flexible... and
*could* be a bit dangerous. You might add a new getter or setter method for
something internal and not realize that you were exposing new data in your API!

The solution for this - and the way that I recommend doing things in all cases -
is to use serialization groups.

## Adding a Group for Normalization

In the annotation, add `normalizationContext`. Remember, normalization is
when you're going from your object to an array. So this option is related to when
you are *reading* data from your API. Context is basically "options" that you pass
to that process. The most common option by far is called `"groups"`, which you
set to another array. Add one string here: `cheese_listing:read`.

[[[ code('8750fdc710') ]]]

Thanks to this, when an object is being serialized, the serializer will *only*
include fields that are in this `cheese_listing:read` group, because, in a second,
we're going to start adding groups to each property.

But right now, we haven't added *any* groups to *anything*. And so, if you go over
and try your get collection operation... oh! Ah! A huge error!

## Debugging Errors

Let's... pretend like I did that on purpose and see how to debug it! The problem is
that the giant HTML error is... a bit hard to read. One way to see the error is
to use our trick from earlier: go to `https://localhost:8000/_profiler/`.

Woh! Ok, there are two types of errors: runtime errors, where something went wrong
*specific* to that request, and build errors, where some invalid configuration
is killing *every* page. Most of the time, if you see an exception, there is *still*
a profiler you can find for that request by using the trick of going to this URL,
finding that request in the list - usually right on top - and clicking the sha
into its profiler. Once you're there, you can click an "Exception" tab on the left
to see the big, beautiful normal exception.

If you get a build error that kills *every* page, it's even easier: you'll see it
when trying to access anything.

Anyways, the problem here is with my annotation syntax. I do this a lot - which
is no big deal as long as you know how to debug the error. And, yep! I forgot a
comma at the end.

## Adding Groups to Fields

Refresh again! The profiler works, so now we can go back over and hit execute again.
Check it out - we have `@id` and `@type` from JSON-LD... but it doesn't contain
*any* real fields because *none* of them are in the new `cheese_listing:read` group!

Copy the `cheese_listing:read` group name. To *add* fields to this, above title,
use `@Groups()`, `{""}` and paste. Let's also put that above `description`...
and `price`.

[[[ code('bb90cac6bf') ]]]

Flip back over and try it again. Beautiful! We get those *three* exact fields.
I *love* this control.

By the way - the name `cheese_listing:read`... I just made that up - you could
use anything. *But*, I *will* be following a group naming convention that I
recommend. It'll give you flexibility, but keep things organized.

## Adding Denormalization Groups

Now we can do the same thing with the input data. Copy `normalizationContext`,
paste, and add `de` in front to make `denormalizationContext`. This time, use
the group: `cheese_listing:write`

[[[ code('2ab6c65f11') ]]]

Copy that and... let's see... just add this to `title` and `price` for now. We
actually *don't* want to add it to `description`. Instead, we'll talk about how
to add this group to the fake `textDescription` in a minute.

[[[ code('00f6954f3d') ]]]

Move over and refresh again. Open up the POST endpoint.... yea - the *only* fields
we can pass now are `title` and `price`!

So `normalizationContext` and `denormalizationContext` are two totally separate
configs for the two directions: *reading* our data - normalization - and *writing*
our data - denormalization.

## The Open API Read & Write Models

At the bottom of the docs, you'll *also* notice that we now have two models: the
*read* model - that's the normalization context with `title`, `description` and
`price`, and the write model with `title` and `price`.

And, it's not really important, but you can control these names if you want. Add
another option: `swagger_definition_name` set to "Read". And then the same thing
below... set to Write.

[[[ code('6a589d6804') ]]]

I don't normally care about this, but if you want to control it, you can.

## Adding Groups to Fake Fields

But, we're missing some fields! When we *read* the data, we get back `title`,
`description` and `price`. But what about our `createdAt` field or our
custom `createdAtAgo` field?

Let's pretend that we *only* want to expose `createdAtAgo`. No problem! Just add
the `@Groups` annotation to that property... oh wait... there *is* no `createdAtAgo`
property. Ah, it's just as easy: find the *getter* and put the annotation there:
`@Groups({"cheese_listing:read"})`. And while we're here, I'll add some documentation
to that method:

> How long ago in text that this cheese listing was added.

[[[ code('69efb9d8b8') ]]]

Let's try it! Refresh the docs. Down in the models section... nice! There's our
new `createdAtAgo` readonly field. *And* that documentation we added shows up
here. Nice! No surprise that when we try it... the field shows up.

For denormalization - for *sending* data - we need to re-add our fake `textDescription`
field. Search for the `setTextDescription()` method. To prevent API clients from
sending us the `description` field directly, we removed the `setDescription()` method.
Above `setTextDescription()`, add `@Groups({"cheese_listing:write"})`. And again,
let's give this some extra docs.

[[[ code('794f4ff08a') ]]]

*This* time, when we refresh the docs, you can see it on the write model and, of
course, on the data that we can send to the POST operation.

## Have Whatever Getters and Setters You Want

And... this leads us to some great news! *If* we decide that something internally
in our app *does* need to set the description property directly, it's now perfectly
ok to re-add the original `setDescription()` method. That won't become part of our API.

[[[ code('ba9aa2825a') ]]]

## Default isPublished Value

Let's try *all* of this out. Refresh the docs page. Let's create
a new listing: Delicious chèvre - excuse my French - for $25 and a description
with some line breaks. Execute!

Woh! A 500 error! I could go look at this exception in the profiler, but this one
is pretty easy to read: an exception in our query: `is_published` cannot be null.
Oh, that makes sense: the user isn't sending `is_published`... so *nobody* is setting
it. And it's set to not null in the database. No worries: default the property
to `false`.

[[[ code('a5d6654c9b') ]]]

***TIP
Actually, the auto-validation was not enabled by default in Symfony 4.3, but may be in Symfony 4.4.
***

By the way, if you're using Symfony 4.3, instead of a Doctrine error, you may
have gotten a validation error. That's due to a new feature where Doctrine database
constraints can automatically be used to add validation. So, if you see a validation
error, awesome!

Anyways, try to execute it again. It works! We have *exactly* the input fields
and output fields that we want. The `isPublished` field isn't exposed at *all* in
our API, but it *is* being set in the background.

Next, let's learn a few more serialization tricks - like how to control the field
name and how to handle constructor arguments.
