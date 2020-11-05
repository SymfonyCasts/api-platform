# Input Data Transformer

Adding the `@var User` above the `owner` property was enough for the denormalizer
to automatically convert the IRI string we're sending in our JSON into a proper
`User` object. Yay! And this *also* fixed something in our documentation. Go back
to the docs tab... actually, I'll open a new tab so I don't lose my testing data.

On the original tab, until now, when we hit "Try it out", it only listed the
`description` field in the example JSON. The docs didn't think that `title`,
`owner` and `price` were fields that were *allowed* to be sent.

But now, on the new version of the docs, when we hit "Try it out"... it *does*
now recognize that `owner` is a field we can send.

So... what's going on? It looks like there's a little bug with input DTOs where
the documentation doesn't notice that a field exists until it has some metadata
on it. So as soon as we added the type to `owner`, suddenly the documentation
noticed it!

And... that's fine because we *do* want types on all of our properties. Back in
the class, above `title`, add `@var string`, `@var int` for price and above
`isPublished`, `@var bool`:

[[[ code('8f00d56bcd') ]]]

By the way, if you're wondering why `description` was *always* in the docs, remember
that the `description` field comes from the `setTextDescription()` method, which
*does* have metadata above it and an argument with a type-hint:

[[[ code('507707d153') ]]]

Let's check the docs now: refresh, go back to the POST endpoint, hit, "Try it out"
and... yes! *Now* it sees all the fields.

## Finishing the transform Logic

Ok: let's finish our data transformer. Instead of returning, say
`$cheeseListing = new CheeseListing()` and pass the title as the first
argument: `$input->title`:

[[[ code('9e2150649d') ]]]

Then, some good, boring work: `$cheeseListing->setDescription($input->description)`,
`$cheeseListing->setPrice($input->price)`,
`$cheeseListing->setOwner($input->owner)` - which is a `User` object - and
`$cheeseListing->setIsPublished($input->isPublished)`. Return `$cheeseListing`
at the bottom:

[[[ code('27a0a54797') ]]]

Okay: moment of truth. I'll close the extra tab, go back to the original
documentation tab, hit "Execute" and... it fails:

> Argument 1 passed to `CheeseListing::setPrice()` must be of type `int`, `null` given.

The problem is that I forgot to pass a `price` field up in the JSON, which causes
the type error. We're going to talk more about this later when we chat about
validation, but for now, be sure to pass every field we need, like `price: 2000`.

Try it again. And... bah! I get the same error for the `setIsPublished() `method.
I really meant to default `isPublished` to false in `CheeseListingInput`:

[[[ code('bce7aef757') ]]]

Ok, *one* more time. And... yes! A 201 status code. It worked!

So using a DTO input is a 3-step process. First, API Platform deserializes
the JSON we send into a `CheeseListingInput` object. Second, *we* transform that
`CheeseListingInput` into a `CheeseListing` in the data transformer. And
third, the normal Doctrine data persister saves things. That's a really clean
process!

Go back to the docs and look at the put operation that *updates* cheeses. Will
this work? Well, we *do* have a data transformer... so... why wouldn't it? Well,
it won't *quite* work yet. Why not? Because our data transformer always
creates *new* `CheeseListing` objects... which would cause Doctrine to make an
INSERT query even though we're trying to *update* a record.

Next: let's make this work! It's... a bit trickier than it may seem at first.
