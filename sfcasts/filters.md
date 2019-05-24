# Filters

Coming soon...

Okay.

Now mastered the basics of exposing our resource, controlling which operations we
want, and controlling the input and output fields, um, on, uh, for that resource,
including even, you know, some kind of fake input Napa fields like our text
description. So what else does an API need out of the box? I mean, there's a bunch of
things, page and nation, uh, validation and what both of which we're going to talk
about. And another one called filtering because it's probably not going to be the
case that you always want to get every single cheese listing in the system. You might
need a, the ability to only see published cheese listings for example, or you might
need to search for specific cheese listings. So that's what's filtering is all about
in AP bio form. And there's a bunch of built in filters to help you, um, help give
your operations much more flexibility. So let's do the first one. Let me know that
internally our cheese listings can be published or unpublished.

MMM, okay.

Probably on the front end we're going to want a way to say, I only want the published
cheese listings. In fact, later on we're actually gonna make this a requirement where
we only return published cheese listings. Right now let's let's say I want to have a
little query parameter where I can control, um, whether or not I want to see
published or unpublished. Um, Geez listings. That's the way to that is to go to the
top above your class and we're going to use a new annotation. You're called at API
filter. All filters start with API at API filter. Then you do the class name of the
filter and I guess that there's a bunch of them built in the one we one it's called
boolean filter. And you always reference the class name. So boolean filter ::class.
And then every filter has different options. This case there's a property option,
actually all of them have a properties option and here we're going to say is
published.

Okay

cause that's a boolean field. Cool. Let me try that. Refresh and it explodes. The
filter class and boolean filter does not implement filter interface. That air means,
I forgot to use statement. This is kind of a strange use of um, class names. Instead
of annotations and even PHP storm didn't auto complete that when I did that. So I
need the men that I go up here and say use boolean filter and all filters are support
both doctrine, ORM and doctrine, Mongo, Mongo, DB, ODM. So make sure you get the ORM
unless you're using Mongo. And now I move over and refresh.

Okay,

the docs work and check this out. Now when we hit try it out, it actually has a
little is published boolean filter. So if we execute by default, we get, let's see
here for results. So let's do is published true execute.

Okay.

And now we're down to just two results. So to published in two on published and you
can see what the slipped up here. It's really, really nice. It's just question mark
is published = true or question mark is published = false. So just like that we're
able to filter on our collection based on a boolean field. Oh, also down in the
response, you'll see down here there's a new hydrocodone search thing here, which
actually explains that you can now search using an is published, um, query parameter.

Yeah.

And it gives information about kind of which property that this relates to on our
resource. So you can actually use this on your clients side if you want to figure
out, um, how you can filter on your particular relationship or a particular resource.
All right, so probably the most common way to filter on things though he is to be
able to kind of do a search by text plus that. Another filter here at API filter,
once again, this one is called search filter Hong Kong class. And in this case we're
gonna say properties = and this one takes a little bit more config, we'll say title
and then we can say partial. So this suppose supports both partial exact starts with
an ends with, and once again, this time I remember, I do need that. Um, uh, you
statements. So I'll say you search filter and make sure you get the one from the ORM.
And there we go. By the way, while we're here, before we check this out, I'm going to
click to open search filter. And you can see up here, it takes me into that directory
at Doubleclick, that directory, even without looking at the documentation, you can
see a bunch of the built in filters exist, filter a date filter, um, a range filter
order filter. We're going to see some of these in a second. All right, so search
folders, awesome works exactly like you'd expect when a refresh.

Yup. Now we can search by our title to help search for cheese, which might match
everything. And you can see a question mark. Title = cheese. It looks really simple
and actually matched three of my four.

Okay.

And the hydro search now contains a second spot where it's advertising how this can
be used. And just to make this a little more complete, let's also add description as
a partial match as well. Um, we're not going to go through it in this tutorial, but
API platform also has integration with elastic search. So doing this type of fuzzy
database searches good. But if you really need a robust search engine, you can
actually have your search results exposed as an API resource, which is pretty
freaking cool. All right. And then there's kind of one more that I want to do. So if
you think about building a front end for this, I want to allow people to search for
cheese listings they need to be and I might want them to look for kind of a price
range. So at we can add at API filter, use the range filter ::class. I'll go up there
and add the use statement for that.

There we go.

And then this one is going to be properties

price.

When you flip back over and refresh the docs, now you'll see the range voters is
actually a little bit more complicated. It actually gives you a bunch of different
things. You can say price between price greater than price, less than, greater than,
equal, less than or equal to. So it's pretty powerful. So I can say I want the price
to be greater than 20 for example. Yeah, I'm going to execute that. What you're going
to is a question mark. Price g t = 20 so it's literally what you see up here. The
price between is going to be a comma separated value.

Oh in 20 is not really good because this is incense. Let's try something more like 10
bucks and this returns just one of our items and once again it advertises down here
exactly how this stuff can be used. So really cool and you can add your own custom
filter if you want to, but there are a lot of them built in which really makes your
front end very powerful with almost nothing, no work. Now there's one special filter
which is technically a filter but doesn't feel like a filter cause so far filters are
all about this filter instead of it being about returning less results, it's about
returning less fields. This can be a really cool idea. So check it out. Let's pretend
that most of the time our descriptions are super long and contain html and sometimes
on the front end we want to make a request for some cheese listings, but we don't
want to get back all that data with all the descriptions because we're not going to
show the description. In fact, maybe we just want to show like a short description
and we don't want to have to download all that data. So let's search fired, get
description method and let's add a new one called public function. Get short
description.

Okay.

This will return a nullable string case description is not set yet and I'm gonna go
ahead and immediately put this into our, um,

okay.

She's listing colon, read a group so that this shows up as a field inside. Let's put
a little bit logic here.

We'll say if

the description is already less than 40 characters, then we'll just return the
description. Otherwise we'll return a substring of the description.

Okay.

To get the first 40 characters and then do a little.dot.at the end. Fixed my type of
there. Cool. So really simple thing. And now, and we go over and

yeah,

I'll go down and look for one specific cheese with id one can, you can say we get
back description and we also get back the short description because this is a, this
is just barely longer than 40 characters. So I'm actually going to take this URL and
pop it into my browser here and then we'll add the dot JSON LD and at the end of it,
cool. So you can see what that looks like right now. So in our, so to power my front
end to just grab some of these fields, like maybe the short description but not the
description we can add.

Okay.

At the top of our class, another filter called property filter Khongthong class and
I'll go back up to the top here.

Yeah.

And get property filter this, there's only one of these and this does have some
options pass to it. That kind of control how flexible you want to allow this property
filter to be f but it works pretty well out of the box. If you go and refresh your
API documentation, this doesn't actually make any difference over here. This is not
something, it's one of the things that doesn't actually show up in any way.

Is there any options that you can actually mess with? But what you can do over here,
what's your client can do is add properties, lesker racket = and then the exact
properties you want. So let's say I don't want all of these properties. I want to get
title and properties. Oscar Breck rights record = short description and it gives you,
in addition to the JSON LD fields, it gives you those fields back. So that's called a
sparse datasets. And it's a nice way you're, we can open up everything in your Api
and then allow your client just say exactly like what pieces, um, they want back. And
fortunately you can't actually like change this to other fields that, um, orange
normally there, so you can't say a is published, it's published is not normally a
part of our API. So you can't add that. You can actually configure it to do that, but
by default, because that's usually not a good idea. You can't do that.

Okay.