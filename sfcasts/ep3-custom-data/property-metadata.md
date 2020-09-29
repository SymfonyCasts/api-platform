# Property Metadata

Coming soon...

Right. But there are no fields. We get the ad ID and that
site, but where's the rest of our data. So normally if you don't set a normalization
context like we did on user, for example, normalization in context with groups, if
you don't set that on your operation, then it's going to include every single
property.

But you can see that we're not seeing that at all. And the reason is that in a
previous tutorial and the source and the source serializer, admin groups, context
builder, this is a special group that helps add custom groups if you're an admin. But
the key thing is actually here. You can see if the groups is not set on context, we
set it to an empty array. So this means that even if, even though we don't, if we
don't have a normalization group on our resource, it actually is defaulting to an
empty array. So it's including nothing. So that's a little quirk with our project,
but it's actually no problem. Cause I prefer being explicit about my groups anyways.
So what that means is we'll go up here and add
`normalizationContexts={"groups"={"daily-stats:read"}}` and I'll copy
that group name because we need to put it on all the properties that we want. So
we'll put this above date `@Groups()` only need one at symbol curly, curly, and I'll
paste.

I'll copy that entire doc block. I'll put that above total visitors. And also most
popular listings does not need to be above get date string. We don't actually want
those a real field. We just want a platform to use it as, as identifier. And now when
we refresh, Oh man, man, not refresh. Now, the system reminds me that I was missing a
comma. There you go. Now we get real fields. Woo. And even better than this and the
new. So if we look at our documentation for this end point, I love about the end
points. And then look at the schema and open up hydrocodone member. You can see that
it now knows that we're going to get date total visitors and most populous things
fields, but it knows nothing about what those are strings into jurors has no idea. So
API platform gets metadata for the documentation in many different places, including
looking at like at VAR that we have about properties, looking at return types of
getter methods, uh, PHP 7.4, um, types like this and several other spots, which is
really cool because it allows you to just code well, and it's going to pick up
intelligently all those pieces of documentation.

But the fact that this is no longer a doctrine, NC means we lose some of that free
automatic metadata because normally API platform reads the column definition as a way
to get metadata. So when you're not in an entity, you need to do a little bit more
work to help give it some, um, to help give it to some documentation.

So one way, in addition to maybe adding `@var` or something above these of the
properties is that we can actually also add a constructor as a way to get
documentation. Now I'm going to add a constructor here, but not really for
documentation. My true motivation is that I want to kind of code, um, code a bit more
properly. I want to make sure that anytime a daily stats object is instantiated, that
all three of these properties are set and constructor is the best way to do that. So
I'm gonna say public function `__construct()`, and we will have looks at a
`DateTimeInterface` called `$date` `int` of `$totalVisitors`.

No, I don't have to do that. I'll do is I'll go to code, generate here and say, go
down to constructor and I'll select all three of those properties. That's a nice,
easy way to do it. And then the only thing we need to fill in is `DateTimeInterface`
type `int` into type. And then we know that the most popular lessons are going to be an
`array` and I'm actually going to remove most of the documentation. These first two are
redundant. And the only thing about this `$mostPopularListening` is is that we know
that this is not just going to be an array. It's going to be an array of cheese
listing. So I'll kind of help it out here by saying `CheeseListing[]` less for a bracket,
right? Square bracket. So the constructor is cool is what's cool about this is now we
can guarantee that all these fields will be in stance will be properly set.

So here we'll just move our new date time. And two daily stats are 100 and then I'll
pass it an empty array for cheese listings for right now. So one of the nice things
about this is that we coded correctly, but API platform also reads the constructor
itself as documentation. So now it knows that the date property must be a daytime
interface. So if you go over here and refresh and I'll go back and open up that, get
operation, look at my schema, open up hydro member. And awesome. Now you can see date
is a string, but it kind of says, this is a date string and those total visitors and
integer and I noticed that most popular listings is going to be an array of strings,
which will be an array of IRI strings, which is awesome.

And we want to describe this a little bit more. We already know how to do that. We
just need to go above that property or could even do it over here on the constructor
and say the five most popular she's listings from this date and the documentation
that's going to automatically pick this up. So next let's actually start loading
favorite cheese listing objects and adding them to our stats object. When we do that,
we're going to find a little surprise about how that property works and how it's
serialized. Then we'll learn more about how API platform loads, all of its property,
metadata, and fix that.
