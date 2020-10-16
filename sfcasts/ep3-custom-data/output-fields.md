# Output Properties & Metadata

Let's go see how having an *output* class affects our documentation. Refresh
the docs homepage. One of the things that this tells us is what to expect
*back* when you use an endpoint. For example, if we look at the get item operation
for cheeses, we know that, thanks to our "work-in-progress" output class, this
will return something different than it did a few minutes ago. And if we look at
the schema... yeah, awesome! The documentation recognizes this! It correctly tells
us that the only field we should expect back is `title`.

## DTO Documentation Models

Oh, and notice that it has this really weird name. This is referring to the
"models" at the bottom of the page. Scroll down to see them.

API Platform creates a unique "model" for each different way that a
resource might be returned based on your serialization groups. And when you
create an *output* DTO, it creates yet *another* model class to describe that...
with a unique "sha" in the name.

I don't know the full story behind *why* that sha is there, but technically, you can
configure a different `output` class for a resource on an operation-by-operation
basis. So basically, API Platform uses a hash to guarantee that each output
model has a unique name. It's a little ugly, but I don't think it really makes
much of a difference.

The *big* point is: API Platform *does* correctly notices that we're using an output
class and is uses *that* class to generate which fields will be returned in the
documentation, which, right now, is only `title`.

## Documenting the Fields

But... it doesn't have any documentation for that field. Like, it doesn't know
what *type* `title` will be.

And... that's no surprise! When we serialize a `CheeseListing` entity, API Platform
can use the Doctrine metadata above the `title` property to figure out that it's a
string. But in this case, when it looks at `title`, it doesn't get *any* info
about it.

No problem! We just need to add that info ourselves. One way is by using PHP 7.4
property types. For example, I can say `public string $title`. Now, my editor
*thinks* this is invalid because it thinks I'm using PHP 7.3... but I'm actually
using 7.4. So this *will* work. But if you're not using 7.4, you can always
use `@var` instead.

## DTO Metadata Cache Bug

Ok, refresh the docs now.... look at the same get item operation, go down to
schema and... oh! It did *not* work. The docs *still* don't know the `type` for
title!

We've just experienced our first "quirk" of the DTO system. Normally, if we
modify something on `CheeseListing`, API Platform realizes that it needs to rebuild
the property metadata cache. But there's a *bug* in that logic when using an input
or output class.

***TIP
You can track this issue here: https://github.com/api-platform/core/issues/3695.
***

It's not a big deal once you know about it: we can trigger a rebuild manually by
changing something inside of `CheeseListing`. I'll hit save, move over, and refresh.
Notice the reload takes a bit longer this time because the cache is rebuilding.
Check out the endpoint, go to the schema and... yes! It knows `title` is a string!

Back in the class, I'm going to remove the PHP 7.4 type and use `@var` instead,
*just* so that everyone can code along with me. Let's also add a description:

> The title of this listing

That will *also* be used in the docs.

## Adding More Fields

Ok, let's add the *rest* of the fields we need to this class. Check out
`CheeseListing`: it looks like `description` is usually serialized and so is `price`.

Copy the `title` property, paste, rename it to `description`... and remove the
docs. Copy *this* and make one more property called `price`, which is an `int`.

Now that we've added these properties, we need to go into our data transformer
and *set* them. So, `$output->description = $cheeseListing->getDescription()` and
`$output->price = $cheeseListing->getPrice()`.

These data transformer classes are *delightfully* boring.

Before we try this, let's grab a couple other fields from `CheeseListing`. Search
for `cheese:read`. But ignore `owner` for now: we'll come back to that in a minute.

Ok: we also output a `shortDescription` field via this `getShortDescription()`
method. Copy that whole thing and, in `CheeseListingOutput` paste it at the bottom.

That will work *exactly* like before: it's referencing the `description` property
and it has the group on it.

Back in `CheeseListing`, if you search again, there is *one* more field to move:
`createdAtAgo`. Copy this method... then paste at the bottom. PhpStorm *politely*
asks me if I want to import the Carbon `use` statement. I do!

But, hmm: this method references a `createdAt` property... which we do *not*
have inside this class. We need to add it. Add a `public $createdAt`, but I'm
*not* going to put any groups above this because this *isn't* a field that we will
expose in our API directly. We just need its data.

Oh, and, by the way, we *could* simplify this by, instead, creating a `createdAtAgo`
property, exposing *that*, then setting the string onto that property from our
data transformer. I won't do that now, but... it's a pretty great idea and shows
off the power of data transformers: you can do the work *there* and then have
*super* simple DTO classes.

Anyways, back in the data transformer, set this property:
`$output->createdAt = $cheeseListing->getCreatedAt()`.

I think we're ready! Let's *first* refresh the documentation: open the item
operation, go to schema and... yes! It *did* rebuild the cache that time and we
can see all our custom fields and their types.

And if we go over and refresh the actual endpoint... that works to! How awesome
is that? We have 5 fields and you can *quickly* look at our output class to know
what they will be.

Next: the *one* field that we *aren't* exposing yet is the `owner` field. Let's
add that. Though, when we do, there's going to be a *slight* problem.
