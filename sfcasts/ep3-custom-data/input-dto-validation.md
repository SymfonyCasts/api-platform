# Input Dto Validation

Coming soon...

One really nice thing about having an input DTO is that after our data transformer is
called and we return our cheese return, the final cheese listing object, our entity,
or choosing empty is still validated like normal. We saw this, we submitted an empty
object to create a new cheese listing. And ultimately we got back air is that the
title should not be blank. And the description not should not be blank. And these are
coming from the assert rules that we have on our cheeses against D so yay validation.
Doesn't change. You can process everything through your input object. It finally ends
up on your cheese listing entity object, and that is valid like normal. But if you
want, you do have a choice to make the whole validation set up a bit cleaner. One
complaint about Symfony's validator system is that for it to work, you need to allow
your entities to get into an invalid state. Basically, even though title, it has an
at a certain not blank on it. You need to first allow a blank or no value to be set
onto this property so that it can then be validated. This was actually the root of
our problem a second ago.

Actually, she's listing,

We added these typecasting here basically to help us set invalid data onto the cheese
listing object. So another option is to move the validation from the entity into the
input class. Then we'll validate. Then we can validate the input class so that if we,
when we finally set the data on this Jesus thing, we know that this data is in fact
valid. So let's try this first. Let's undo the typecasting inside of jesus' input
because once we're done, we will know that this will always be validated being put on
there next, and she's listening. I'm going to move all of these ad asserts over into
our input. So this will just be, I'll grab the two off of title, move those onto the
title property.

I'll need a use statement, but I'll grab that in a second. I'll grab the one off
description, one off of price up and all the delete, the extra one on description. By
the way, you could also keep these, uh, you could, you still can keep these
validation rules on your entity if you want to. Um, it depends if you need them, if
validating your entity in other contexts. So let me write this. I'll copy the not
blank onto price. And then let's see here also is valid owner custom one. I'll put
that above owner. Okay, perfect. Now I do need a couple of use statements here. So
I'm actually going to three type the end K on blank that added the use statement. I
need up here for all the asserts and for our custom is valid owner. I'll say E R.
There we go.

Hit tab as the use statement up for that one as well. All right, cool. So we now have
all of our validation rules on jesus' input. We have no validation rules anymore on
our cheese listing. Now, unfortunately, EPA platform does not automatically validate
your input DTO objects. It only validates your final API resource object. So we'll
need to add the validation manually, but it's kind of a cool exercise to see how you
could run validation anywhere in API platform and cause the nice validation error
response that we want here. So to do it inside of our data transformer, we're
literally going to do validation right here. So first of all, need the validator. So
I'll say public function on responders, we'll construct and then say validater
interface. But watch out here, grab the one from API platform, not Symfony. I'll
explain why in a second, I'll call that validator and I'll go to Alta enter and go to
initialize properties to create that property and set it now down here, by the time
we're past input here, this is going to be the input object that contains the DC
realized JSON onto it.

So we actually want to basically validate it right here on the first line before we
use it to, uh, update our cheese listing. So to do that, we can say this arrow,
validator,->validate input, and that's it. So the validator from API platform, what
it does is it actually wraps Symfonys validator. And it does that so that it can add
some validation groups to the context, but it also does that because it actually
throws a very special exception, which will cause this output. We can actually see
this face shift shift. Look for validated at PHP, include non project items and open
the validator from API platform.

Yep.

You can see that rap Symfonys validator does some stuff with the validation groups,
but most down here, once it actually used the validation gets back these violations
and throws this validation, exception. This is actually an exception you can throw up
from anywhere to cause the nice validation response. Alright, so let me close it up
and let's go over here and try it. When I had executed see yes, 400 air and we get
the exact same responses before, but now these are coming from our input object. Our
input object must be fully valid before that data will be transformed onto our entity
object. So if you like this, do it. If you don't leave your validation constraints on
your entity class. So next let's talk about one last topic right now. All of our, uh,
resources have been using IDs auto increment IDs as our ID property. But in a lot of
cases, you can make your life easier, especially as a JavaScript developer by using U
U IDs. So let's see how to do that next.

