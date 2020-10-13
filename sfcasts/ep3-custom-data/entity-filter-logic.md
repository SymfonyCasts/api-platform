# Custom Entity Filter Logic

Let's review our goal: to be able to add `?search=cheese` and have that search
across *several* columns in the database.

In `getDescription()`, let's describe that lofty goal. We don't need any fancy,
dynamic property stuff here like we saw in the core filters. Just return an
`array` with one item for the one query parameter: `search`. Set that to *another*
`array` and here is where we start using the keys that we saw inside
`PropertyFilter`. I'll cheat and list the ones we need. First, set
`'property' => null`. If a filter *does* relate to a specific property, put
that here. As far as I can tell, this is only used in the Hydra filter documentation,
not even in OpenAPI, which is what drives the Swagger interactive docs. Wow,
that sentence was a LOT of buzzwords. Phew!

Next set `'type' => 'string'` and `'required' => false` - both things that will
help the docs.

Let's check it out! Find the API docs, refresh and open up the `/api/cheeses`
operation. And... there it is: `search`! And it says "string".

We can *even* help this a bit more. Add another key called `openapi`. Set that
to another `array`. One of the keys that you can pass here is called `description`.
How about:

> search across multiple fields.

I personally don't know all the possible keys that we can have... I've just been
digging around and seeing what I need. So feel free to dig further.

When we refresh now and go back to that operation... nice! Our filter now has
a description.

## How & When filterProperty() is Called

Okay, enough with the documentation: let's get to the part where we actually
*apply* that filter. For a Doctrine ORM filter, this happens in
`filterProperty()`, which receives `$property` and `$value` arguments and then
some Doctrine-specific stuff like `QueryBuilder`, something called a
`QueryNameGenerator` - more on that in a few minutes - and a few other things.

Let's figure out what that `$property` and `$value` are: `dd()` both of these.

Then go find the *other* tab where we're fetching the cheese collection and hit
enter to send the `?search=cheese` query parameter.

Cool! It passes us `search` - which is the name of the query parameter - and also
`cheese`. Now, the *really* important thing to understand is that
`filterProperty()` is going to be called for *every* single query parameter
that's on the URL. So if I go up here and say `?dog=bark`, it prints out
`dog` and `bark`. Now, obviously our filter is not meant to operate on a `dog`,
we'll leave that to trained veterinarians.

Back in `filterProperty()`, check for *our* query parameter: if `$property` does
not equal `'search'`, then return.

So, it's a *little* weird because the method use the word "property"... but
`search` isn't really a property... it's just what we decided to call our query
parameter. And that's *fine*. Also, there is *no* logic that connects what we
return from `getDescription()` and `filterProperty()`. So if you were expecting
that maybe `filterProperty()` would only be called for query parameters that we
return in `getDescription()`, that's *not* how it works. These two methods work
totally independently.

## Modifying the Query

The rest of this method good-ol' query-building logic. We're passed the
`QueryBuilder` that will be used to fetch the collection and our job is to *modify*
it.

To do that, we first need to know the class *alias* that's being used for the
query. We can get that as saying `$alias = $queryBuilder->getRootAliases()` and -
it looks a bit funny, but we want the 0 index.

Now add the *real* logic: `$queryBuilder->andWhere()` and I'm going to pass this
`sprintf()` because we need to dynamically put in the alias. Let's search on both
the `title` and `description` fields. So, `%s.title` - the `%s` will be the alias -
`LIKE :search` `OR`, again, `%s.description LIKE :search`. For the 2 `%s`, pass
`$alias` and `$alias`.

Let's split this into multiple lines. Then `->setParameter()` to set the `search`
parameter `'%'.$value.'%'` for a fuzzy search.

Let's try this! First, remove the query parameter entirely so we can see what
the *full* list looks like. Ok, a lot of people are selling blocks of cheddar.
Add `?search=cheddar` and ah! No results!

I bet I messed something up! I did! I added an extra `s` on the parameter. Try
it again. Much better! `/api/cheeses/1` is gone but the rest *do* have `cheddar`
in their `title`.

Let's try the word "cube" to see if the `description` is matching. And... that works
too!

To prove it, we can even *see* the query. Go to `/_profiler`... click the
token for our API request, click into the Doctrine and click "view formatted query".
Beautiful! The `is_published` and `owner_d`check come from a Doctrine extension
we created in the last tutorial and relates to security. And *then* it searches
on the `title` or `description` fields. Pretty cool.

## The QueryNameGenerator

Before we keep going, there was one argument that we have *not* used yet - the
`$queryNameGenerator`.

The query name generator is probably *not* super important unless you're creating
a filter you want to search between projects.

Here's the problem: the parameter we added - `search` -  could have been called
anything - it just needs to match up with the `:search` inside the query. Now,
if there are *many* independent filters being used, then, in theory, two filters
might accidentally use the same parameter name. If that happened one would
override the other.

The query name generator's job is to help avoid this problem by generating a
*unique* parameter name.

Check out it say `$valueParameter = $queryNameGenerator->` - that's the argument
we're being passed - then `generateParameterName('search')`. That will return a
string with `search` then a unique index that increments - something like
`search_p1` - the `p` is for parameter. I'll put a comment above this.

Down in the query, using it *does* get a little ugly: instead of `:search` here,
it's `:%s` and then another `:%s`. For the arguments, it's now `$alias`,
`$valueParameter`, `$alias`, `$valueParameter`.

*Finally,* in `setParameter()`, use `$valueParameter`.

I'll be honest, that makes my head spin a little... and I might avoid doing this
for custom filters in my *own* project.

Anyways, let's make sure it works... by going to the documentation. Hit "Try it
Out", fill in "cube" for the search and... Execute! Let's see... yep! Our filter
is working!

So this is what it looks like to make a custom filter when your resource is a
Doctrine entity. But the process is different if you need to make a custom filter
for an API resource that is *not* an entity. Let's tackle that next by making
a filter for `DailyStats`.
