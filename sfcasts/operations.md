# Operations

Coming soon...

Let's get to customizing our API for real. Now what are the best tools about Symfony
outside of an API is the web debug toolbar in which you do not see down here cause we
don't have it installed and also the web profiler. So did we not have access, but
unfortunately that doesn't work in an API, right? It actually totally does. We were
did you do your terminal? And let's install this with 

```terminal
composer require profiler --dev
```

You can also `composer require debug --dev`. If you want some
extra logging tools. This install is the web profiler bundle, which adds a couple of
configuration files which aren't that important. Most notably when we refresh the
page, now we get the web debug toolbar on the bottom. This is actually the web depot
toolbar for this documentation page, which probably isn't that interesting. But if we
start making requests, check us out.

If I had tried out and execute this behind the scenes made an Ajax request and
Symfonys, web depot toolbar has a little thing here for Ajax requests. So every time
I execute this I get a new entry. And if we want, we can actually, I'll hold command
and click this little shot here to go see the profiler for that. Api requests are
actually looking at all the information that the API request. You can see the POST
parameters, uh, information about the request, the request headers, requests,
content, which is really important when you're setting JSON, all kinds of good stuff.
Um, cache, performance security, all of the stuff that you're used to getting inside
of, um,

Symfony you get now. So this is an awesome thing to remember and there's two
different ways to get here and there's the way that I just showed you, but you can
also at any time just to go to `/_profiler` and you'll get a list in the last
requests. So here's the one for `/cheese_listings` and we can hit the token and get
there. Now API Platform also adds a it's own little icon here, which is a nice way to
be able to see your overall metadata about your resources. So this actually tells us
that this request for cheese listings, the resource in question was the cheese, those
things resource. And we can see all the metadata about it. So you can see the item
operations and the collection operations. We're going to talk about those in a
second, but it's actually talking, showing us the configuration that we have for this
resource. Also data providers and data persisters, which is a little bit more low
level.

Okay.

Okay. So we can see on our documentation page, we already talked about that. When you
add an API resource, you get five new end points. So if you opened up our cheese
listing just by adding `@ApiResource` at those five end points, those end points are
called operations. And they're divided into two different types of operations.
There's the collection operations. These are the ones where the, you were Al does not
include the ID. And then there's the item operations where you're operating on a
single uh, item. So the first thing we can customize is what operations that we
actually want, cause we might not actually want all of the operations for a specific
resource. So a lot of API Platform is going to be learning how to add configuration
inside of this API resource annotation.

Yeah.

So something that you can do is `collectionOperations`. And I'm putting `{"get", "post"}`
and then I'm going to put `itemOperations` and here I'm going to put `{"get", "put", "delete"}`
that right there is basically the default configuration that is saying
that I want all five of these end points. So not surprisingly when we refresh we get
absolutely no changes. But now if you take off `"delete"`, but let's say that we don't
actually want normally users to be users to be able to delete cheese listing. Once
you created she's listing, it's there. Maybe we will allow someone to like unpublish
it or archive it later, but we're not going to allow them to delete it. So as soon as
we take off that do that delete operation boom is gone from my documentation. So
there's two cool things happening here. Do you remember that? Behind the scenes, the
swagger is being built off of an open API specification document, which you can see
at `/api/docs.json`. So as soon as we remove that, delete it actually updated
documentation so that there's not a `DELETE` endpoint there anymore.

Yeah.

But of course behind the scenes and also actually remove the delete end point. You
can see that by going over and running beak 

```terminal
php bin/console debug:router
```

And now you can see the `GET`, `POST`, `GET` and `PUT` endpoints. But the `DELETE` 
when it's gone.

Yeah.

So the first way to customize things in API Platform, I said to customize these
operations and in addition to choosing which operations you want, you can also do
some customization on the operations themselves. Actually, I'm gonna hold on that he
knows that one of the things built into this, and you shouldn't really care, but it's
the URL is `/api/cheese_listings` API Platform by default takes your entity name and
just snake cases it and turns it into the URL. And for the most part, I don't want
you to obsess about your URLs, do you where else are not important in an API. But if
you want to, you can change this to be a little bit nicer. You can buy using somebody
called `shortName` and we're going to set that to `cheeses`. And I do that when you're
running `debug:router` again

```terminal-silent
php bin/console debug:router
```

Okay.

You can see that it changes everything. Do `/api/cheeses`. And we'd see the same thing
if we refresh over and our documentation.

Yeah.

So in addition to just saying which operations you actually want, you can do some
customization on the operations themselves. Not Too much. But I want you to think of
each operation as we know that each operation generates a route and you actually can
control the parameters of that route. So check this out in `itemOperations`. I'm going
to break this onto multiple lines here. So that's a little bit more readable. And
instead of just saying `"get"` can actually say `"get"={}` and then we can pass it some
configuration, the configuration they can pass here is basically equal to the
configuration that you're normally used to sing inside of your routes. Oh, I don't
know how to get examples. Ah, shoot. So for example, under inside of the gate
operation we can actually add `"path"=`. So that too,

some custom, you are either we want for this. So I'm going to use of course `"/i❤️️cheeses/{id}"`

As soon as we do that under refresh, we've changed that and point right there and of
course anything that you can put in a route you can put in here. So you can have
methods, you can have conditions, um, you can have hosts, all that kind of stuff.
There are a couple other pieces of the configuration that you can use to configure a
specific operation and we're going to see the most important ones along the way. But
really the operations configure the operations themselves is not that important. What
we really need to learn about is the serialization process. How is it taking our
`CheeseListing` with all these private properties and converting it into the JSON that
we see when we get it. And same thing when we actually use, when we go to create a
new cheese or edited cheese, how is it converting this JSON here, how has it taken
this input, JSON and putting it back onto our `CheeseListing`? Understanding the
serialization process is the key thing to understanding API Platform.