# Publish State Change Validator Logic

We're working on adding some pretty complex validation rules around who can publish
or unpublish a `CheeseListing`. To get this logic right, what we really need to
know is how the `$isPublished` field is *changing*... like, is it changing from
false to true? Or true to false? Or maybe it's not changing at all because the
PUT request is just updating some *different* fields.

And hey! We already know how to get the original data from Doctrine! We did it in
`CheeseListingDataPersister`.

Oh, and by the way: if your API resource is *not* an entity - which is *totally*
allowed and something that we'll talk about later - then you can get the original
object by injecting the `RequestStack`, getting the current request and then
reading the `previous_data` attribute. If that attribute is *not* there, then you
know that your object is being created.

## Fetching the Original Entity Data

Ok: let's get the original data just like we did before. Add a
`public function __construct()` with `EntityManagerInterface $entityManager`.
I'll do my normal trick of hitting Alt + Enter and going to "Initialize properties"
to create that property and set it.

Below in the method, we can say
`$originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($value)`.
Let's `dd($originalData)` to make sure it's working.

Spin over and try the test:

```terminal-silent
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

Got it! It's the same array that we saw earlier and you can see
that `$isPublished` is `false` on the `$originalData`!

Ok: remove the `dd()` and we don't need this null check. You *do* need that when
you add a validator to a property... which might be null, but because we added
our constraint above the class, we will *always* get a `CheeseListing` object.

Get the previous is published value with
`$previousIsPublished = $originalData['isPublished'] ?? false`
in case it's not set. That's the same thing that we did in
`CheeseListingDataPersister`.

Let's start by checking if the published field did *not* change: if
`$previousIsPublished === $value->getIsPublished()`, then we don't need to do
*any* special validation. Just return and I'll add a comment.

## Not Allowing Short Descriptions

The first real case from our test is that an owner cannot publish if the description
is less than 100 characters.

No problem: add if `$value->getIsPublished()`. Inside, we know that the field
*is* changing from false to true: this listing *is* being published. So
if `strlen($value->getDescription()) < 100`, we need to fail! I'll copy the
`buildViolation()` code from below and move it up here.

The argument is the validation message... and this is coming from the
`ValidIsPublished` annotation. This allows you to customize the message when you
use it via annotations options.

But I'm not going to use that: this isn't a reusable validation constraint... so
it's simpler to keep everything in one spot:

> Cannot publish: description is too short!

And we don't need a parameter - that's if you need a dynamic wildcard in your message.
Oh, but I *will* add `->atPath('description')`.

That's nice: it will make the validation failure look like it's attached to the
`description` field, even though we added the constraint to the entire class.

If the length *is* long enough, just return.

Testing time! When we run the test now...

```terminal-silent
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

It still fails... but... yes! It's *now* failing on "admin can publish a short
description". Over in `CheeseListingResourceTest`, it our first assertion *passed*!
It *is* returning a 400 status code.

## Allowing Admin Users to Publish

It's failing now because when we log in as an admin user, it is *also* returning
a 400 status code instead of allowing this.

To fix this, we need to see if the user is an admin. Add a second argument to the
constructor `Security $security`. I'll initialize this property... then below,
update the if statement: if the description is too short *and* *not*
`$this->security->isGranted('ROLE_ADMIN')`, then fail validation. I'll add a
comment to summarize this.

Ok! This is *true* test-driven development. Try the test now:

```terminal-silent
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

It fails... but we're further!

> Normal user cannot unpublish

If we look at the test... yea! The second and third cases are now passing and we're
down to the unpublish logic. You can start to see why breaking each test case
into its own method might be a bit cleaner, even if it's more work up-front.

## Adding the Unpublish Validation

Let's add the unpublishing validation logic, which is a bit easier: *only* an
admin can unbpulish. At the bottom of the validator - thanks to the `return`
statement and the fact that we checked to make sure that the `isPublished` *is*
changing, at the bottom, we *know* that this cheese listing is being *un* published.

Ok: if *not* `$this->security->isGranted('ROLE_ADMIN')` - if we're not an admin,
then this is not allowed. Copy `$context->buildViolation()` from earlier, give
it a nice message - only admin users can unpublish - and remove the `atPath()`:
this has nothing to do with the description.

Let's try it!

```terminal-silent
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

And... yes! That *huge* test now *passes*. Thanks to our validator and the
original data, we're able to write the *exact* logic we need.

## 400 vs 403 Errors

When we set this up, we chose to use 400 validation errors, which is really nice
because the user gets a 400 status code and can see a collection of descriptive
validation errors.

If you wanted, you could instead return a 403 access denied, which might
especially make sense when a normal user tries to unpublish. How would we do
that?

One of the *really* cool things about the way Symfony is architected is that
we are free - at any point during our request - to throw an `AccessDeniedException`.
So literally, in the middle of the validation, we can say
`throw new AccessDeniedException()` - make sure you to get the one from the
`Security` component. I'll say: "only admin users can unpublish".

To see this in action, run the test again:

```terminal-silent
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

This will fail but... awesome: we can see the response. A *giant* 403 error.
I'll comment that out and keep my validation logic.

We now have a RESTful way to publish a listing and execute custom logic. In the
fourth part of this series, we'll talk about other ways that we could have
accomplished this, like using the Messenger integration or creating a truly
custom operation. But I really like this solution.

Next: I want to talk about how to add a completely custom field to your resource:
a field that doesn't live in your entity and that might even require a service
to calculate. We actually did this in a previous tutorial... but it was *so*
custom that it didn't show up in our documentation. Let's see an even better way.
