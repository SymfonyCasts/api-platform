# Filter Arguments

Coming soon...

If we enter in invalid date, up here on our filter, then it's simply ignored. It
starts returning everything. And we did that on purpose in our `DailyStatsDateFilter`
We actually checked right here to see if this is a valid from date and that's
totally fine. But another option is for us to return a 400 status code so that the
user knows that they messed up the format. How pretty simple actually Symfony has a
bunch of built in classes that map to various status codes. So for example, before
this, we could say, if not `$fromDate`, then throw new `BadRequestHttpException`.
That's one of those, there are a bunch of other exception classes in that same
directory, uh, that map to other, uh, uh, status codes.

And I'll pass this message that says invalid from date format. Awesome. I don't know
if statement down here, but I'll leave it just for consistency. So now if we move
over and try that same page over here, perfect. We get the big air invalid from date
format with a 400 status code and on production. They would see this message here,
but let's do an extra challenge. Let's pretend that we want to make this behavior
whether or not to throw a 400 air on an invalid date format. Something that is
configurable when you use the, the, the filter annotation. Okay. This may not be
needed unless you're building a reusable filter, but it will reveal something
important about how filters work. Now, this `ApiFilter()` annotation has several
options. We can pass through it. I'm actually going to hold command or control and
click to jump into that core class. All of these public functions, um, arguments, our
properties are options that we can pass technically pass to this annotation, but for
the purposes of building a custom filter, the only option that really matters here is
arguments.

Let me close that. Try this pass `arguments={}` and then pass a new argument called
`throwOnInvalid` set to `true`. The question is okay. Let's see what happens if we just
refresh now and error class daily stats, date filter does not have argument `throwOnInvalid`
API platform does a cool, but kind of strange thing. If you pass an
argument's option to a filter, it tries to pass this argument name to that, to your
filters. constructor, let me show you in daily says they filter. We don't currently
have a constructor. Let's create one public function `__construct()`, and let's
say `bool`, and then we need to use the same name that we used here. So I'll copy that
`throwOnInvalid`. So `$throwOnInvalid`, and let's also set it to default
to `false`. In case somebody uses the, a, the filter without that option, then I'll
have to enter and go to initialize properties to create that property and set it then
down here in our, if statement here, we can say, if not `$fromDate` and `$this->throwOnInvalid`
then we want to actually throw that exception.

So if we move over and refresh, now it works. We're back to our 400 status code
invalid from date format. So it's kind of weird, but any of your arguments on your
annotation map to construct arguments by name, by the way, one other option that we
could, that we can pass to filters is `properties={}`. If you want to configure that this
is supposed to use a certain set of properties. This properties option is also set
kind of implicitly. If you ever apply a filter above a specific property, which has
also allowed anyways, if you specify properties here or you put it above a property,
then that's passed to you as a `$properties` argument. So if you actually have an
argument, literally here called properties, and that's going to be passed, that's
going to receive whatever we set on the annotation. So cool. This is a way that you
can configure your filter, but there's more going on than it seems. Let's find out
what next we'll reveal something that will make our filters a lot more powerful.
We'll also see how to do this exact same thing with our custom entity filter.

