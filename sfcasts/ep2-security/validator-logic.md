# Security Logic in the Validator

so basically down here once we lock, once we get our setting into a valid owner, it's
of course setting a validation error in that case cause right now our validator is
always failing so now we need to do is actually make this smarter. So for us to do
that we're going to need to know who's logged in som to add the
`public function __construct()` and will on a wire our usual favorite `Security` class.
I'll do the Alt + Enter Initialize fields to create that property and set it
for the logic itself. We'll start down here and say `$user = $this->security->getUser()`
Now that `$user` like they might not like in Fieri, they the user might not even
be logged in. That's not possible with this particular post endpoint, but just for
the sake of writing, making our validator tight, let's say if `!$user instanceof User`

then let's actually add a violation here and I could hard-code the message but let's
create another configurable message over here called `$anonymousMessage` call.

> Cannot set owner unless you are authenticated

Then over here we can do the same things as down there because if
`$this->context->buildViolation()`, this time we'll use `$constraint->anonymousMessage` there,
`->addViolation()` and then we'll return so that this function doesn't keep running and just
returns without one validation error. Now the other thing we know that the `$value`
object here should be a `User` object cause we're expecting this `IsValidOwner`
annotation to be set on a property where the value of that property is the is it `User`
object. But you know we could accidentally, it's possible that we might actually put
this on some other property. So let's just add a sanity check here. Let's say if
`!$value instanceof User` and here it's not a validation error. This is a programming
error. So I'm going to say `throw new \InvalidArgumentException()` with

> @IsValidOwner constraint must be put on a property containing a User object

Finally, we know we have a user, we have `$user` and `$value` are both `User` objects.
So we just need to compare that. So if `$value->getId() !== $user->getId()`, you
probably could just compare those `$value` directly to `$user`. But ID is fine. Then we'll
actually have our violation that reads off our normal message property from our
constraint class. And that should be it. That covers all the cases. So let's switch
back over, run our test

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

and it passes. Perfect. Okay. Now a second ago I kind of
pointed out that this validator starts by checking to see if the value is basically
not set. You know, if then this would happen if somebody forgot to pass an owner
property entirely. Nydia is that the validator itself as a best practice, um,
shouldn't throw up a validation error on this, on that we actually want it to be
required. We should set that with another annotation. And actually I have [inaudible]
hmm. No I need to scratch all that cause I did not order kill it all from it now a
second ago. So the nice thing about having a the owner property be something that is,
is still in our API is as I mentioned earlier, we might in the future have an admin
interface where maybe an Admin user can set the owner to anything. And we can put
that logic right into our validator if we want. So we already have a security object
auto wired in here.

So once we verify that the user is actually logged in, we can say, well
`$this->security->isGranted('ROLE_ADMIN')`, then we're just going to return. This
is going to prevent us down here from actually checking to make sure that the user
has a role. So that's just the power of things you can do with the validator. Now one
thing I mentioned earlier is that a typically in validators, you'll first check to
see if the value is empty. And if it is you won't do anything. So this can happen
right now if somebody made a request and forgot to set the owner property. And the
reason we do that is we typically say that, hey, if you actually want this to, uh,
this value to be required, you should put that on your, um, you should actually add a
`@Assert/NotBlank()` annotation to above that property itself.

And we actually haven't done that yet. And that, which is actually a smell of a bug
in our application to make that obvious. If I go to a `CheeseListing` resource, let's
actually move this `$cheesyData`, oh, align here and you remember this original request
here, we log in as `cheeseplease@example.com` and we're just sending an empty data
and making sure that we had access with a `400` status request. Well now I'm actually
going to add that `$cheesyData` here. I should still get a `400`, uh, uh, response and
I'll even add a [inaudible] test to failure and make this a more obvious, um, because
we should still be missing the owner field. We should get a `400` area here because
we're not passing the owner.

But if you go over and run the test, because we're missing the annotation, we
actually exploded. They `500` error. It's actually having an error because it's trying
to insert that into the database. But the `$owner` property is, the `owner_id` column is
requiring the database, so that's an easy fix, flat `@Assert/NotBlank()`. This makes sure
that the, it's a valid owner if the owner is set and then we leave, uh, have and not
blank actually make sure that this property itself is set to the test. Again, we are
solid. Next, let's do something with filtering collections. Based on that `CheeseListing`
`isPublished`.
