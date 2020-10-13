# Custom Entity Filter Logic

Coming soon...

So our goal is reminder is to be able to go over here and have something that
`?search=cheese`, and that would actually search for that string across
several different, uh, uh, columns in the database. So in `getDescription()`, I'm going
to describe that. I don't need to do anything fancy here. I'm gonna return an `array`.
That array is going to have one item in it because we only support one query
parameter with this filter and that's going to be called `search`. And I'll just set
that to another `array`. And here's where we start using those keys that you saw inside
of the property filter. And I guess again, the best way is just to look these up
first, I'm going to say `'property' => null`, that's not really important. You'll see why in
a second. And then I'll say `'type' => 'string'`. This is going to help with documentation.
Know what kind of field to build for this type. And then I'm going to say `'required' => false`
And this is all helping the documentation. All right. So let's go over here.
I'm gonna go to the first tab with my documentation refresh and let's open up `/api/cheeses`
Look down here and look down here and sweet. There it is `search` and it says
string query.

And if you look down inside of our no, no, no. And if we want, we can even give that
a little bit more documentation. So one of the other fields we're going to have on
here is called `openapi`. I'll set that to an `array` and here, one of the keys that you
can pass here is called `description` search across multiple fields. I personally
don't know all the opposites or capacity here. I've just been kind of digging around
and seeing what I need. So feel free to dig further. If I refresh this time, go back
to that. Perfect. You can see now I have like a little bit of extra documentation
that shows up above that field, which is pretty cool. Okay. Enough of the
documentation. Now let's get onto the part where we actually apply that filter. So
you can see that this `filterProperty()` is past `$property` `$value`.

Then a couple of other things that are specific, the doctrine all around the 
`QueryBuilder`, in something called a `QueryNameGenerator` that we're going to talk about in
a second. And a couple of other pieces, let's actually figure out what that `$property`
in `$value` are. So I'm going to `dd()` `$property` and `$value`. And then we'll go over here back
to my other tab where I kind of have my, uh, more on the cheese, the collection that
point and hit enter. And okay, so that makes sense, right? It passes us search, which
is the thing that's up here and also cheese. Now, the really important thing to
understand here is that this `filterProperty()` is going to be called for every single
query parameter that's on the URL. So if I go up here and say `?dog=bark`
I'm going to get dog and bark up there. Now, obviously our filter is not meant
to operate on the dog query parameter. So what this means is that this property here,
we want to only do our filtering logic when that `$property === 'search'`.

So that's the first thing I'll do in here is say, if `$property` does not equal `'search'`
then return. So it's a little weird cause they use the language property. We're not
really, this isn't really a property. We've just decided to call our queer parameter
search. But that's how the logic looks. And the rest of this now is just a good query
building logic. So as you can see, we're past the query builder here, which is maybe
some other filters have already worked on. So our job is just to modify it. So do
that. First thing we need to know is what the alias is. That's being used in the
query. So we can get that as saying `$alias = $queryBuilder->getRootAlias()`. And
then it looks a little bit funny, `getRootAliases()` and then zero, it looks a little
funny, but that's how you get that.

And now we'll say `$queryBuilder->andWhere()`, and I'm going to pass this `sprintf()` cause
we need to do a little bit dynamic in here. Now what I'm gonna say is we're going to
search by the title or description fields. So I'm gonna say `%s.title`. That
percent asks is eventually I'm gonna use the alias for that. And then `LIKE :search`
to use a parameter `OR` again, `%s.description LIKE :search`. And
then for the 2 `%s`, we're going to pass `$alias` and `$alias` are perfect. And then
the next line after this, me actually break this into multiple lines. We need to say
`setParameter()` so we can fill in that search part. So we'll set search and that'll be
equal `'%'.$value.'%'`. So we can do the fuzzy search. All right, let's try
this as a reminder. I'm going to take off my query parameter entirely right now. So
we're going to see what the list here is.

It's going to see a lot of these have block of cheddar in them select add a question,
`?search=cheddar` and Oh, we get actually no results on this. Let's see, I may
have messed something up. Nope. And I did. I always do that percent, a little extra S
on the end there now in a refresh, there we go. You can see it actually is such a
process. Use a size one is gone because it did not match this all the rest of these
have cheddar in our name. And let's see, let's put the word cube and see if the
description is being searched. And it is, it works. Of course we can prove it's even
more by going to `/_profiler` and clicking on our token for our API point and
going down here and actually seeing what the doctrine query looks for. So this is a
really good spot here where we can see view format of query, whereas searching aware,
and we still have the publisher or owner that actually comes from a doctrine
extension. That's controlling security that we created in the last row. And then on
your end, the `title` or the `description` are like, uh, our percent cube. Pretty cool.
Now one last little detail I want to mention in here is that one of the things that
we're passed here is called something, a query name generator.

Now the query name generates is probably not super important if you're creating a
custom filter for your own project. So the idea is that this parameter here search,
we could've called it anything. It doesn't matter just whatever we have here. It
needs to match up with our `:search` inside of our query. Now, if there are many
independent filters being used and in theory, two filters might use the same
parameter name, which would mean that they would collide and one would override the
other one. So the query name generator is the purpose of it is to avoid this problem.
So let's just actually see what it looks like.

So up here above the query, I'll say `$valueParameter = $queryNameGenerator->`. That
argument that we have, and then say `generateParameterName()`. And I'll say `'search'`,
that's basically going to return a string with search that has an, uh, an
incrementing index on it. So that it's always unique. So this will be equal to
something like `search1`. I'll put a little comment above this. Now down here, we
just have to use it, which gets a little bit ugly. We got to add a couple of other
percent SS. So instead of a `:search` here, it's `:%s` and then `:%s`
and then we're going to have Haley is `$valueParameter`, AOE, S value
parameter. How about that for making your head spin a little bit, then finally down
here, instead of search, we're going to have `$valueParameter`. Phew.

Now, if I go over here and actually reopen my look over here, let's actually use the
documentation here. I saw it, try it out and I will search for cube and the search
field hit execute, and yes, you can see queue. Our filter is still working. So this
is what it looks like to make a custom filter when you're using the doctrine O R M.
But the process is different. If you are, for example, creating a custom filter for
an API resource that is not backed by doctrine. So next let's create a custom filter
for our daily stats or resource.
