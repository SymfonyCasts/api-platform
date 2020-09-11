# Published Validator

Coming soon...

So we're going to complicate things. We can't just let anyone publish a `CheeseListing`
only the owner or an admin should be able to do that. And we've already got
that covered. Thanks to the `security` on the `CheeseListing`. `put` operation. If you
scroll up here, you can say, `put` has `"security"="is_granted('EDIT', object)`, and what's
going on. There is that, that is using the security system. Uh, it's going to be
passed to our custom `CheeseListingVoter`, which we created in the previous tutorial.
And basically this supports that `EDIT` attribute and it checks to make sure that the
current user is the owner of the `CheeseListing` or that the current user is an admin.
So it's already true that only the owner, an admin can even hit this put operation,
but our logic gets a little more complicated than that. According to our business
team and owner can only be allowed to publish their `CheeseListing`.

If the `description` is longer than a hundred characters, if it's shorter publishing
should fail what they 400 status code, but also an admin should be able to skip that
rule and publish whenever they want, no matter what. Oh, and speaking of unpublished
thing, a, an owner can actually not on publish their cheese listing, but that is
something that we want an admin to be able to do. Yikes. So let's start with a test
to describe all of this craziness first and `CheeseListingResourceTest`, to make
sure that our `testPublishCheeseListing()` keeps working. We need to make sure that
the Jesus thing you hear here has a long enough description. If you look in the
`CheeseListingFactory` `src/Factory/CheeseListingFactory`, by default, the
`description` is set to a, by default, the `description` is just set to this short
description right here, but I've already added something called a state method here
called `withLongDescription()` that will set it to three paragraphs from faker, which
is definitely going to be longer than a hundred characters. So this is a long way of
saying that inside of our test here, we can actually say `->withLongDescription()` to
make sure that the object created the `CheeseListing` object created here is going to
have a long enough `description`.

So that will take care of making that still pass. Now below this, I'm going to paste
in a new test method, which you can get from the code block on this page. And let's
just walk through it really quickly to see what the goal is. Now you can see here.
The first case is that we're going to create `CheeseListing`. This `CheeseListing` is
going to have a short `description` C here, and we're gonna try to publish it as a, the
owner of that user. So the user is the owner. We're going to log in as that user, try
to publish it and we'll get a 400 status code. But if we then log in as the admin
user and do the same thing, this will actually work. Then there's a few other tests
on here. We want to verify that if we log back in as the normal user, we are able to
make other changes to our `CheeseListing`, just to make sure he didn't break anything.

And then down here for unpublished, we're going to log in. As a normal user, try to
set `isPublished` to false. That shouldn't work. But if we log in as an admin user and
said, `isPublished` a false that will work if they 200 status code. Phew. So how can
we do this? If you think about it, it's kind of a strange mixture of validation and
security. Like are these security checks or are these validation checks? And the line
between validation security is sometimes blurry. So in theory, we could do something
up here too. We can do some pretty crazy stuff with this security expression. There
is a variable in here known as previous object, which is the object before the
changing. So in theory, we could check the previous objects `isPublished` and the
object `isPublished` and do different things. Or to keep this readable, we could
actually pass both the object and previous object into our voter.

And then in our voter, use that to figure out what's going on. But I tend to view
things like this security is best when you're trying to completely prevent access to
an operation, but validation is best when you need to prevent certain data from
changing in certain ways, even if the ways that they are allowed to change, depend on
who the current user is. So a long way of saying we are going to solve this with a
custom validator. So our terminal I'm gonna run 

```terminal
php bin/console make:validator
```

, I'll call it. This. This `ValidIsPublished`.

That's going to generate two classes. The class that kind of represents the
annotation and then the class that actually holds the validator logic. We can go over
and see these inside of `src/Validator/`. Perfect. So before we actually add any
logic to either of these classes, let's add this to our `CheeseListing`. So above the
`CheeseListing` class add `@ValidIsPublished()`. Now, the reason we're
adding this above the class and not maybe above the `$isPublished` property, is that by
adding this above the class are custom valid. Validator is going to be passed in
entire `CheeseListing` object, not just a single field. And that's important because
the rules for this validator are going to need the `$isPublished` field, but also the
`$description` field.

Now let's actually try the test right now. So over in my test, I'll copy the new test
method name, and then I'll run a 

```
symfony run bin/phpunit --filter=testPublishCheeseListingValidation
```

and paste that method name, and it fails. Let's see what's going on here and check
this out. It says the constraint at validator Valdez published cannot be put on
classes. So by default, if we look to the valid is published, annotations are
designed to go on properties, not classes, and you can change that inside of your
validator to do that. You need to do two things. First, you do `@Target()`. Then you
pass this `"CLASS"`. That's actually not that important. I think that actually more or
less just helps your editor. What you really need to do over here is actually
overriding methods. I'll go to Code Generate or Command + N but override methods.

And we're going to override `getTargets()` by default, the `parent::getTargets()` just
allows us to be out of the properties. We're going to return `self::CLASS_CONSTRAINT`
So now our validator is allowed. We put on a class instead of a property. Now again,
the fact that we added this above the class means that when our `CheeseListing` is
validated automatically, this `ValidIsPublishedValidater` is going to be executed
in the value that's passed here is going to be the `CheeseListing` object. So real
quick, if you're not too familiar with Kyle custom validators work by default, if I
add at Valdez published here, then Symfony is going to look for a service that's
valid is published validator in automatically know that this, uh, validation, uh,
annotation is connected with this validator service over here. So it's gonna
automatically start calling `validate()`.

So let's add a little sanity check here. Actually, let me put it after the, at VAR,
let's say, if not `$value instanceof CheeseListing`, then I'm going to throw a new
`\LogicException`. It doesn't really matter what type of exception and say only 
`CheeseListing` is supported. That's basically the only possible if we accidentally added
the, uh, at valid is published on top of a different class. This is only intended to
be added about the cheese list in class. So we can make our code very specific inside
of there. And then, well, this let's just `dd($value)` to absolutely make sure we
know what it looks like.

Alright, so now let's move over, run the test again. And yes, we can see that the
founder is being called and it is being passed our `CheeseListing` object, which is
going to be the `CheeseListing` object after it's the JSON has been deserialized.
Uh, so you can actually see that the `isPublished` has been changed to true already.
So this connects with the first test case inside of here, where we are, uh, we have a
short description or logging in as a, as the owner and trying to set is published to
true. So this JSON did deserealize, and it did update the `$isPublished` field. And now
our job is to determine that that was an invalid change because the description is
too short. How to do that. It's going to go back to our original data trick with
doctrine. Let's do that next.

