# Custom Filter Logic for Entities

Let's review our goal: to be able to add `?search=cheese` and have that search
across *several* columns in the database.

In `getDescription()`, let's describe that lofty goal. We don't need any fancy,
dynamic property stuff like we saw in the core filters. Just return an
`array` with one item for the one query parameter: `search`. Set that to *another*
`array` and... here is where we start using the keys that we saw inside
`PropertyFilter`:

[[[ code('89c544e8cc') ]]]

I'll cheat and list the ones we need. First, set `'property' => null`:

[[[ code('ee5e22685f') ]]]

If a filter *does* relate to a specific property, put that here. As far as I can tell,
this is only used in the Hydra filter documentation, not even in OpenAPI, which is
what drives the Swagger interactive docs. Wow, that sentence was FULL of buzzwords.
Phew!

Next set `'type' => 'string'` and `'required' => false`:

[[[ code('b1b2365389') ]]]

Both things that will help the docs.

Let's check it out! Find the API docs, refresh and open up the `/api/cheeses`
operation. And... there it is: `search`! And it says "string".

We can *even* help this a bit more. Add another key called `openapi`. Set that
to another `array`. One of the keys you can pass here is called `description`.
How about:

> search across multiple fields.

[[[ code('a817e79016') ]]]

I personally do *not* know all the possible keys that we can have... I've just been
digging around and finding what I need. So feel free to dig further.

When we refresh now and go back to that operation... nice! Our filter now has
a description!

## How & When filterProperty() is Called

Okay, enough with the documentation: let's get to the part where we actually
*apply* that filter. For a Doctrine ORM filter, this happens in
`filterProperty()`, which receives `$property` and `$value` arguments and then
some Doctrine-specific stuff like `QueryBuilder`, something called a
`QueryNameGenerator` - more on that in a few minutes - and a some other stuff:

[[[ code('d55cc1a817') ]]]

Let's figure out what that `$property` and `$value` are: `dd()` both of these:

[[[ code('92c54121aa') ]]]

Then go find the *other* tab where we're fetching the cheese collection and hit
enter to send the `?search=cheese` query param.

Cool! It passes us `search` - which is the name of the query parameter - and also
`cheese`. Now, the *really* important thing to understand is that
`filterProperty()` is going to be called for *every* single query parameter
that's on the URL. So if I go up here and say `?dog=bark`, it prints out
`dog` and `bark`. Now, obviously our filter is not meant to operate on a `dog`,
we'll leave that to trained veterinarians.

Back in `filterProperty()`, check for *our* query parameter: if `$property` does
not equal `'search'`, then return:

[[[ code('b68f8cc051') ]]]

So, it's a *little* weird because the method uses the word "property"... but
`search` isn't really a property... it's just what we decided to call our query
param. And that's *fine*. Also, there is *no* logic that connects what we
return from `getDescription()` and `filterProperty()`. So if you were expecting
that maybe `filterProperty()` would only be called for query parameters that we
return in `getDescription()`... that's *not* how it works. These two methods work
independently.

## Modifying the Query

The rest of this method is good-old query-building logic. We're passed the
`QueryBuilder` that will be used to fetch the collection, and *our* job is to
*modify* it.

To do that, we first need to know the class *alias* that's being used for the
query. We can get that by saying `$alias = $queryBuilder->getRootAliases()` and -
it looks a bit funny - but we want the 0 index:

[[[ code('40ac984803') ]]]

Now add the *real* logic: `$queryBuilder->andWhere()`, pass this
`sprintf()` - because the alias will be dynamic - and search on both
the `title` and `description` fields. So, `%s.title LIKE :search` `OR`
`%s.description LIKE :search`. For the 2 `%s`, pass `$alias` and `$alias`.
And... I'll split this onto multiple lines:

[[[ code('5412d308e5') ]]]

Finish with `->setParameter()` to assign the `search` parameter to `'%'.$value.'%'`
for a fuzzy search:

[[[ code('ff9033f96b') ]]]

Let's try this! First, remove the query parameter entirely so we can see what
the *full* list looks like. Ok, a lot of people are selling blocks of cheddar.
Add `?search=cheddar` and ah! No results!?

This smells like a typo! Bah! I added an extra `s` on the parameter:

[[[ code('39a79ee2d6') ]]]

Try it again. Much better! `/api/cheeses/1` is gone but the rest *do* have `cheddar`
in their `title`.

Let's try the word "cube" to see if the `description` is matching. And... that works
too!

To prove it, we can even *see* the query. Go to `/_profiler`... click the
token for our API request, click into the Doctrine section then "view formatted query".
Beautiful! The `is_published` and `owner_id` check comes from a Doctrine extension
we created in the last tutorial and relates to security. And *then* it searches
on the `title` or `description` fields. Pretty cool.

## The QueryNameGenerator

Before we keep going, one argument we did *not* use was `$queryNameGenerator`.

The query name generator is probably *not* very important unless you're creating
a filter that you want to share between projects.

Here's the problem it solves: the parameter we added - `search` -  could have been
called anything - it just needs to match the `:search` inside the query:

[[[ code('3f3f403785') ]]]

Now, if there are *many* independent filters being used, then, in theory, two filters
might accidentally use the same parameter name. If that happened one would
override the other. That's no fun.

The query name generator's job is to help avoid this problem by generating a
*unique* parameter name.

Check it out: say `$valueParameter = $queryNameGenerator->` - that's the argument
we're being passed - then `generateParameterName('search')`:

[[[ code('6a2b788d0c') ]]]

That will return a string with `search` then a unique index that increments:
something like `search_p1` or `search_p2`. I'll put a comment above this:

[[[ code('047bcb368c') ]]]

Down in the query, using it *does* get ugly: instead of `:search`,
it's `:%s` and then another `:%s`. For the arguments, we need `$alias`,
`$valueParameter`, `$alias`, `$valueParameter`:

[[[ code('2b2ce05f03') ]]]

*Finally,* in `setParameter()`, use `$valueParameter`:

[[[ code('6c3c691d1e') ]]]

I'll be honest, that makes my head spin a little... and I might avoid doing this
for custom filters in my *own* project.

Anyways, let's make sure it works by going to the documentation. Hit "Try it
Out", fill in "cube" for the search and... Execute! Let's see... yep! Our filter
is working!

So this is what it looks like to make a custom filter when your resource is a
Doctrine entity. But the process is different if you need to make a custom filter
for an API resource that is *not* an entity. Let's tackle that next by making
a filter for `DailyStats`.
