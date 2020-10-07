# Custom Filter

Coming soon...

On CI's listing thanks to the search filter. And actually we also have a Boolean
filter and arrange filter. When we use the collection end point, there are a number
of different ways that we can filter down on this, including searching the title
description and owner fields, which is pretty cool. Well, we can only search by one
field at a time. Like we can search for the, uh, something that matches the title or
something that matches the description. But we can't, for example, say something like
question Mark search = and then take whatever that is and search across many
different fields on our cheese listing. No problem. Let's create our own custom
filter. These are super fun and a little bit weird. Let's see. Over in source API
platform, let's create a new PHP class called cheese search filter.

Now, of course, as usual, and then we need to implement an interface or extended base
class. In this case, we're going to extend abstract filter now makes you get the one
from doctrine ORM. It's actually impossible to see which one you have here. So I'll
just pick one randomly perfect doctrine ORM. If you've got the wrong one, try the
other one. It turns out creating a filter is different based on where your underlying
data sources. So if your underlying data sources, the ORM creating a filter is going
to look one way or if you're, but if your underlying data source is for example,
elastic search, which is something you can totally do, then a custom filter that
looks different. Or if you will have a completely custom API resource like our daily
stats, then creating a custom filters, going to look even another way. And we'll talk
about how to create a custom filter for a custom API resource in a few minutes.

Now, when we extend abstract filter, as you can see, we need to implement a couple of
methods. So I'll go to code generate or Command + N on a Mac and go to "Implement
Methods" and implement the two methods that we need. Okay. So filters, as I mentioned
are kind of weird, but they're there two pieces which make a lot of sense. They are
the logic that actually do the filtering, which is filter property. And then there's
the logic that describes how the filter works, which is used in the documentation.
And that's the job of get description. So let's start with good description here and
actually to help fill in this method. I'm going to cheat a little bit and look at
some core classes. So I'm going to shift shift and look for property filtered at PHP,
make sure to include non project items and we will get property filter.

As a reminder, property filter is actually kind of a strange filter that allows you
to, um, and a query parameters, uh, that allows you to actually request less data on
the end point. So it doesn't actually filter it, but it actually changed the data,
uh, changes, which fields are returned. But anyways, over on a property filter I'll
search for get description. Perfect. So what get description is kind of supposed to
return is, uh, kind of is, is basically the, what the query parameters should look
like in order to use this filter. And then below that query parameter, there's a
bunch of different keys that you can put here that are ultimately going to be used in
the documentation. And honestly, the best way, the best map for seeing what he's are
available is actually looking in these core classes.

Let's look at, I'll ask you one other example. So I'll close that hit shift shift,
and this time look for search filter dot PHP and make sure you include non project
items in case it's not there. Perfect. And in this case, good description actually
lives in this search filter traits. I'll hold command or control and click to open up
that. Alright, so this is just looking for, is going to return the exact same
structure, except this one's a little bit more complicated. You can see immediately
it says something, a property = this arrow, get properties, and then ultimately loops
over the properties and then creates. If we look down here, eventually kind of
creates that same description for each of those properties. It's a little bit
confusing, but you, but you can actually do. And we're doing this in our cheese
listing is you can take a certain search filter and actually say, I want this search
filter to work on all four of these properties. What does effectively does is
actually create four different search filters. So over here, you can see that this
one search filter ultimately exposes four different fields for us to search on.

That's a little bit confusing. Don't worry. We're going to see exactly what's going
on. So let me close up those core classes. Now, one of the pieces of, because we're
extending this abstract filter here, one of the properties that we magically have
access to is called this->properties. I want to actually DD that here. So I'm gonna
say this DD, this->properties, just to see what the heck that thing is. So right now,
if we go over, I'm actually going to, uh, copy this of open a new tab and go to /API
/cheeses that JSON LD and enter, okay, right now it works.

But now that I have this DD in here for the description, let's actually hook up this
cheese filter and use it on our cheese listing. So we're gonna do this, like all of
the other filters, every other filters you can see is just a class. So anywhere on
here, I'm going to say, how about down here? I'll say ads, API filter. And then we
will say cheese search filter, click on class. He's just turned on. Unfortunately
doesn't auto complete that yet for me. So now I'll copy that class name and up here,
I'll add the use statement for that. So use she's app /API platform, chest cheese
search filter. Now, as soon as I do this, when I refresh any endpoint over here, even
though I don't have any query parameters on my, uh, thing right now, you can see it
actually hits my get description method because it's trying to generate the
documentation for the search filter.

And you'd see this->properties is no. So here's where this area that this->properties
comes into play. A lot of times, as we've seen, you can actually pass which
properties you want a filter to operate on. So for example, here, we could say
properties equals, and I could say something like I'm price, say that we want to be
able to search on the price a property. As soon as I add this here, if I go over and
refresh there, I get the price in that array. So if you want your filter to be
configurable, if you want to do allow to pass different properties, that should be in
there. That's the purpose of the, this->properties, a property inside of your filter.
Now, in our case, I'm creating this entirely from my application. I do not. I'm not
going to worry about making it configurable. I'm only gonna use it in this one case.
And, um, and I'm gonna design it completely custom. So I'm going to remove that
properties option from annotation. And I'm going to completely ignore the,
this->properties property and just do what I want.

So our goal is reminder is to be able to go over here and have something that
question Mark search = cheese, and that would actually search for that string across
several different, uh, uh, columns in the database. So in good description, I'm going
to describe that. I don't need to do anything fancy here. I'm gonna return an array.
That array is going to have one item in it because we only support one query
parameter with this filter and that's going to be called search. And I'll just set
that to another array. And here's where we start using those keys that you saw inside
of the property filter. And I guess again, the best way is just to look these up
first, I'm going to say property = no, that's not really important. You'll see why in
a second. And then I'll say type = string. This is going to help with documentation.
Know what kind of field to build for this type. And then I'm going to say required
false. And this is all helping the documentation. All right. So let's go over here.
I'm gonna go to the first tab with my documentation refresh and let's open up /API
/cheeses. Look down here and look down here and sweet. There it is search and it says
string query.

And if you look down inside of our no, no, no. And if we want, we can even give that
a little bit more documentation. So one of the other fields we're going to have on
here is called open API. I'll set that to an array and here, one of the keys that you
can pass here is called the scription search across multiple fields. I personally
don't know all the opposites or capacity here. I've just been kind of digging around
and seeing what I need. So feel free to dig further. If I refresh this time, go back
to that. Perfect. You can see now I have like a little bit of extra documentation
that shows up above that field, which is pretty cool. Okay. Enough of the
documentation. Now let's get onto the part where we actually apply that filter. So
you can see that this filter property is past property value.

Then a couple of other things that are specific, the doctrine all around the queer
builder, in something called a query name generator that we're going to talk about in
a second. And a couple of other pieces, let's actually figure out what that property
in value are. So I'm going to DD property and value. And then we'll go over here back
to my other tab where I kind of have my, uh, more on the cheese, the collection that
point and hit enter. And okay, so that makes sense, right? It passes us search, which
is the thing that's up here and also cheese. Now, the really important thing to
understand here is that this filter property is going to be called for every single
query parameter that's on the URL. So if I go up here and say question, Mark dog =
bark, I'm going to get dog and bark up there. Now, obviously our filter is not meant
to operate on the dog query parameter. So what this means is that this property here,
we want to only do our filtering logic when that property = search.

So that's the first thing I'll do in here is say, if property does not equal search
then return. So it's a little weird cause they use the language property. We're not
really, this isn't really a property. We've just decided to call our queer parameter
search. But that's how the logic looks. And the rest of this now is just a good query
building logic. So as you can see, we're past the query builder here, which is maybe
some other filters have already worked on. So our job is just to modify it. So do
that. First thing we need to know is what the alias is. That's being used in the
query. So we can get that as saying alias = QueryBuilder arrow, get root alias. And
then it looks a little bit funny, get re aliases and then zero, it looks a little
funny, but that's how you get that.

And now we'll say QueryBuilder and where, and I'm going to pass this sprint F cause
we need to do a little bit dynamic in here. Now what I'm gonna say is we're going to
search by the title or description fields. So I'm gonna say percent S dot title. That
percent asks is eventually I'm gonna use the alias for that. And then like colon
search to use a parameter or again, percent S dot description, like colon search. And
then for the 2% SS, we're going to pass alias, alias and alias are perfect. And then
the next line after this, me actually break this into multiple lines. We need to say
set parameter so we can fill in that search part. So we'll set search and that'll be
equal 2% that value that percent. So we can do the fuzzy search. All right, let's try
this as a reminder. I'm going to take off my query parameter entirely right now. So
we're going to see what the list here is.

It's going to see a lot of these have block of cheddar in them select add a question,
Mark search = shatter and Oh, we get actually no results on this. Let's see, I may
have messed something up. Nope. And I did. I always do that percent, a little extra S
on the end there now in a refresh, there we go. You can see it actually is such a
process. Use a size one is gone because it did not match this all the rest of these
have cheddar in our name. And let's see, let's put the word cube and see if the
description is being searched. And it is, it works. Of course we can prove it's even
more by going to slash_a profiler and clicking on our token for our API point and
going down here and actually seeing what the doctrine query looks for. So this is a
really good spot here where we can see view format of query, whereas searching aware,
and we still have the publisher or owner that actually comes from a doctrine
extension. That's controlling security that we created in the last row. And then on
your end, the title or the description are like, uh, our percent cube. Pretty cool.
Now one last little detail I want to mention in here is that one of the things that
we're passed here is called something, a query name generator.

Now the query name generates is probably not super important if you're creating a
custom filter for your own project. So the idea is that this parameter here search,
we could've called it anything. It doesn't matter just whatever we have here. It
needs to match up with our colon search inside of our query. Now, if there are many
independent filters being used and in theory, two filters might use the same
parameter name, which would mean that they would collide and one would override the
other one. So the query name generator is the purpose of it is to avoid this problem.
So let's just actually see what it looks like.

So up here above the query, I'll say value parameter = query name, generator. That
argument that we have, and then say generate parameter name. And I'll say search,
that's basically going to return a string with search that has an, uh, an
incrementing index on it. So that it's always unique. So this will be equal to
something like search one. I'll put a little comment above this. Now down here, we
just have to use it, which gets a little bit ugly. We got to add a couple of other
percent SS. So instead of a colon search here, it's calling percent S and then colon
percent S and then we're going to have Haley is value parameter, AOE, S value
parameter. How about that for making your head spin a little bit, then finally down
here, instead of search, we're going to have value parameter. Phew.

Now, if I go over here and actually reopen my look over here, let's actually use the
documentation here. I saw it, try it out and I will search for cube and the search
field hit execute, and yes, you can see queue. Our filter is still working. So this
is what it looks like to make a custom filter when you're using the doctrine O R M.
But the process is different. If you are, for example, creating a custom filter for
an API resource that is not backed by doctrine. So next let's create a custom filter
for our daily stats or resource.

