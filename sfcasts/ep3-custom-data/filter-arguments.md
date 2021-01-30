# Filter Class Arguments

If we enter an invalid `from` date in the URL, it's simply ignored and we
return everything. We did that on purpose in `DailyStatsDateFilter`:

[[[ code('572ec8624b') ]]]

But another option is to return a 400 status code so that the user knows they
messed up. How could we do that?

## Returning a 400 any time you want

It's pretty simple actually! Symfony has a bunch of built-in exception classes that
map to various status codes. For example, before this, we could say, if not
`$fromDate`, then throw new `BadRequestHttpException`:

[[[ code('0b620c6af9') ]]]

That exception - which you can throw *whenever* you want - maps to a 400 status code.
And there are a bunch of other ones in that same directory for other status codes.
Pass this a message:

> Invalid from date format.

[[[ code('2effa3e681') ]]]

Cool! I know, I don't need this other if statement down here, but I'll leave it.

Anyways, let's see what this looks like. Refresh with the bad date and... cool!
A nice JSON error message with: "Invalid from date format" and a 400 status code.
On production, the stack trace wouldn't be here, but the user *would* see this
message.

## ApiFilter arguments Option

Let's do an extra challenge. Pretend that we want to make this behavior -
whether to throw a 400 error on an invalid date format - something that is
*configurable* when we activate the filter.

Okay, this may not be needed unless you're building a *reusable* filter, but it
will reveal some cool stuff about how filters work.

The `@ApiFilter()` annotation has several options that we can pass to it:

[[[ code('b65c2f6c2b') ]]]

Hold `Command` or `Control` and click to jump into that core annotation class. Yep!
All of these public properties are options that we can technically pass to this
annotation. But for the purposes of building a custom filter, the only options that
really matter are `arguments` and `properties`. We'll talk about `properties`
later.

Close that class. Try this: add `arguments={}` and then pass a new argument called
`throwOnInvalid` set to `true`:

[[[ code('27cc665591') ]]]

What does this do? I don't know! Let's refresh and see what happens. Ah, error!

> Class `DailyStatsDateFilter` does not have argument `$throwOnInvalid`

## arguments Option Maps to Constructor Arguments

API platform does a cool, but kind of strange thing: if you pass an `arguments`
option to a filter, it tries to pass that argument - by *name* - to the
*constructor* of your filter.

Check it out: in `DailyStatsDateFilter`, add a constructor: public function
`__construct()` with `bool` and then copy the name of the argument and paste it:
`$throwOnInvalid`. Default this to `false` in case someone uses the filter *without*
that option:

[[[ code('44cad186ac') ]]]

Next, hit `Alt`+`Enter` and go to "Initialize properties" to create that property
and set it:

[[[ code('7f162bee94') ]]]

Finally, in the `if` statement, add if not `$fromDate` *and*
`$this->throwOnInvalid`, *then* we want to throw that exception:

[[[ code('cd9843b90c') ]]]

Let's try it! Move back over, refresh and.... got it! We're back to the 400
status code.

## The properties Option

So it's kind of weird, but any `arguments` on the annotation map to constructor
arguments by name. Oh, and the one other option that we could pass to the annotation
is `properties={}` if you want to configure that this is supposed to use a certain
set of properties. And if you ever put `@ApiFilter` above a property instead
of on top of the class, this `properties` option is automatically set for you.

Either way, if the `properties` option is set, it's passed to your filter as an
argument called `$properties`.

So... cool! We *now* know how we can pass configuration to a filter. But there's
more going on than it seems. Next: we'll reveal something that will make our
filters a lot more powerful.
