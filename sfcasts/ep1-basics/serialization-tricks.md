# @SerializedName & Constructor Args

When we *read* a `CheeseListing` resource, we get a `description` field.
But when we *send* data, it's called `textDescription`. And... that's technically
fine: our input fields don't need to match our output fields. *But*... if we *could*
make these the same, that might make life easier for anyone using our API.

It's pretty easy to guess how these properties are created: the keys inside the
JSON literally match the names of the properties inside our class. And in the case
of a fake property like `textDescription`, API Platform strips off the "set" part
and makes it lower camel case. By the way, like everything in API Platform, the
way fields are transformed into keys *is* something you can control at a global
level: it's called a "name converter".

## Controlling Field Names: @SerializedName

Anyways, it would be kinda nice if the input field were just called `description`.
We'd have input `description`, output `description`. Sure, internally, *we* would
know `setTextDescription()` was called on input and `getDescription()` on output,
but the user wouldn't need to care or worry about that.

And... yes! You can *totally* control this with a *super* useful annotation. Above
`setTextDescription()`, add `@SerializedName()` with `description`.

[[[ code('556648f4d0') ]]]

Refresh the docs! If we try the GET operation... that hasn't changed: still
`description`. But for the POST operation... yes! The field is *now* called
`description`, but the serializer will call `setTextDescription()` internally.

## What about Constructor Arguments

Ok, so we know that the serializer likes to work by calling getter and setter methods... or by using public properties or a few other things like hasser or
isser methods. But what if I want to give my class a constructor? Well, right
now we *do* have a constructor, but it doesn't have any required arguments. That
means that the serializer has *no* problems instantiating this class when we POST
a new `CheeseListing`.

But... yea know what? Because *every* `CheeseListing` needs a title, I'd like to
give this a new required argument called `$title`. You definitely don't need to
do this, but for a lot of people, it makes sense: if a class has required properties:
force them to be passed in via the constructor!

And now that we have *this*, you might *also* decide that you don't want to have a
`setTitle()` method anymore! From an object-oriented perspective, this makes the
`title` property immutable: you can only set it *once* when *creating* the
`CheeseListing`. It's kind of a silly example. In the real world, we probably
*would* want the title to be changeable. But, from an object-oriented perspective,
there *are* situations when you want to do exactly this.

Oh, and don't forget to say `$this->title = $title` in the constructor.

[[[ code('261360b41e') ]]]

The question *now* is... will the serializer be able to work with this? Is it going
to be super angry that we removed `setTitle()`? And when we POST to add a new one,
will it be able to instantiate the `CheeseListing` even though it has a required
arg?

Whelp! Let's try it! How about crumbs of some blue cheese... for $5. Execute
and... it worked! The title is correct!

Um... how the heck did that work? Because the *only* way to set the title is via
the constructor, it *apparently* knew to pass the title key there? How?

The answer is... magic! I'm kidding! The answer is... by complete luck! No, I'm
still totally lying. The answer is because of the argument's *name*.

Check this out: change the argument to `$name`, and update the code below. From
an object-oriented perspective, that shouldn't change anything. But hit execute
again.

[[[ code('7b476e810a') ]]]

Huge error! A 400 status code:

> Cannot create an instance of `CheeseListing` from serialized data because
> its constructor requires parameter "name" to be present.

My compliments to the creator of that error message - it's awesome! When the
serializer sees a constructor argument named... `$name`, it looks for a `name`
key in the JSON that we're sending. If that doesn't exist, boom! Error!

So as *long* as we call the argument `$title`, it all works nicely.

## Constructor Argument can Change Validation Errors

But there *is* one edge case. Pretend that we're creating a new `CheeseListing`
and we forget to send the `title` field entirely - like, we have a bug in
our JavaScript code. Hit Execute.

We *do* get back a 400 error... which is perfect: it means that the person making
the request has something wrong with their request. But, the `hydra:title` isn't
very clear:

> An error occurred

Fascinating! The `hydra:description` is *way* more descriptive... actually a bit
*too* descriptive - it shows off some internal things about our API... that I
maybe don't want to make public. At least the `trace` won't show up on production.

Showing these details inside `hydra:description` *might* be ok with you... But
if you want to avoid this, you ned to rely on validation, which is a topic that
we'll talk about in a few minutes. *But*, what you need to know *now* is that
validation can't happen unless the serializer is able to successfully create
the `CheeseListing` object. In other words, you need to help the serializer
out by making this argument optional.

[[[ code('120e49ac3a') ]]]

If you try this again... ha! A 500 error! It *does* create the `CheeseListing`
object successfully... then explodes when it tries to add a null title in the
database. But, that's *exactly* what we want - because it will allow validation
to do its work... once we add that in a few minutes.

***TIP
Actually, the auto-validation was not enabled by default in Symfony 4.3, but may be in Symfony 4.4.
***

Oh, and if you're using Symfony 4.3, you may *already* see a validation error!
That's because of a new feature that can automatically convert your database
rules - the fact that we've told Doctrine that `title` is required in the
database - into validation rules. Fun fact, this feature was contributed to
Symfony by Kèvin Dunglas - the lead developer of API Platform. Sheesh Kèvin!
Take a break once in awhile!

Next: let's explore *filters*: a powerful system for allowing your API clients
to search and filter through our CheeseListing resources.
