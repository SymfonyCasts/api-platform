# Operations

Let's get to work customizing our API. A RESTful API is all about *resources*.
We have *one* resource - our `CheeseListing` - and, by default, API Platform generated
5 endpoints for it. These are called "operations".

## Collection and Item Operations

Operations are divided into two categories. First, "collection" operations. These
are the URLs that don't include `{id}` and where the "resource" you're operating
on is *technically* the "collection of cheese listings". For example, you're "getting"
the collection or you're "adding" to the collection with POST.

And second - "item" operations. These are the URLs that *do* have the `{id}` part,
when you're "operating" on a *single* cheese listing resource.

The *first* thing we can customize is which operations we actually want! Above
`CheeseListing`, inside the annotation, add `collectionOperations={}` with
`"get"` and `"post"` inside. Then `itemOperations` with `{"get", "put", "delete"}`.

[[[ code('4eafb7ea76') ]]]

*A lot* of mastering API Platform comes down to learning about what options
you can pass inside this annotation. *This* is basically the default configuration:
we want *all* five operations. So not surprisingly, when we refresh, we
see absolutely no changes. But what if we don't want to allow users to delete a
cheese listing? Maybe instead, in the future, we'll add a way to "archive" them.
Remove `"delete"`.

[[[ code('70cbcf174b') ]]]

As *soon* as we do that... boom! It's gone from our documentation. Simple, right?
Yep! But a bunch of cool things just happened. Remember that, behind the scenes,
the Swagger UI is built off of an Open API spec document, which you can see
at `/api/docs.json`. The reason the "delete" endpoint disappeared from Swagger
is that it disappeared from here. API Platform is keeping our "spec" document
up to date. If you looked at the JSON-LD spec doc, you'd see the same thing.

And of course, it also completely removed the endpoint - you can see that by
running:

```terminal
php bin/console debug:router
```

Yep, just `GET`, `POST`, `GET` and `PUT`.

## Customizing the Resource URL (shortName)

Hmm, now that I'm looking at this, I don't love the `cheese_listings` part of the
URLs... API Platform generates this from the class name. And really, in an API,
you shouldn't obsess about how your URLs look - it's just not important,
especially - as you'll see - when your API responses include links to other resources.
But... we *can* control this.

Flip back over and add another option: `shortName` set to `cheeses`.

[[[ code('f041112cf9') ]]]

*Now* run `debug:router` again:

```terminal-silent
php bin/console debug:router
```

Hey! `/api/cheeses`! Much better! And we see the same thing now on our API docs.

## Customizing Operation Route Details

Ok: so we can control *which* operations we want on a resource. And later, we'll
learn how to add *custom* operations. But we can *also* control quite a lot about
the individual operations.

We know that each operation generates a route, and API Platform gives you full
control over how that route looks. Check it out: break `itemOperations` onto multiple
lines. Then, instead of just saying `"get"`, we can say `"get"={}` and pass this
extra configuration.

Try `"path"=` set to, I don't know, `"/i❤️️cheeses/{id}"`.

[[[ code('e0bf4ca943') ]]]

Go check out the docs! Ha! That works! What else can you put here? Quite a lot!
To start, *anything* that can be defined on a route, can be added here - like
`method`, `hosts`, etc.

What else? Well, along the way, we'll learn about other, API-Platform-specific stuff
that you can put here, like `access_control` for security and ways to control the
serialization process.

In fact, let's learn about that process right now! How does API Platform transform
our `CheeseListing` object - with all these private properties - into the JSON that
we've been seeing? And when we create a *new* `CheeseListing`, how is it converting
our *input* JSON into a `CheeseListing` object?

Understanding the serialization process may be *the* most important piece to
unlocking API Platform.
