# Hydra: Describing API Classes, Operations & More

So, at least on a high level, we understand that each resource will have an `@type`
key and that *this* page - via the `supportedClass` and `supportedProperty` keys -
*defines* what that type means - what properties it has, and a lot of info about
each property.

Right now, we only have one API resource, so we only have one entry under
`supportedClass`, right? Surprise! There's another one called `Entrypoint`!
And *another* one called `ConstraintViolation`, which defines the *resource* that's
returned when our API has a validation error.

## The Entrypoint Resource

Let's talk about this `Entrypoint` class: it's a pretty beautiful idea. We already
know that when we go to `/api`, we get, sort of, the HTML version of an API
"homepage". Whelp, there is *also* a JSON-LD version of this page! There's a link
to see it at the bottom of this page - but let's get to it a different way.

Find your terminal: we can use curl to see what the "homepage" looks like for the
JSON-LD format:

```terminal
curl -X GET 'https://localhost:8000/api' -H "accept: application/ld+json"
```

In other words: please make a GET request to `/api`, but advertise that you would
like the JSON-LD format back. I'll also pipe that to `jq` - a utility that makes
JSON look pretty - just skip that if you don't have it installed. And... boom!

Say hello to your API's homepage! Because *every* URL represents a unique resource,
even *this* page is a resource: it's... an "Entrypoint" resource. It has the same
`@context`, `@id` and `@type`, with one real "property" called `cheeseListing`.
That property is the IRI of the cheese listing collection resource.

Heck, this is *described* in our JSON-LD document! The `Entrypoint` class has
one property: `cheeseListing` with the type `hydra:Link` - that's interesting.
And, it's pretty ugly, but the `rdfs:range` part is apparently a way to describe
that the resource this property refers to is a "collection" that will have a
`hydra:member` property, which will be an array where each item is a
`CheeseListing` type. Woh!

## Hello Hydra

So JSON-LD is all about adding more context to your data by specifying that our
resources will contain special keys like `@context`, `@id` and `@type`. It's
still normal JSON, but if a client understands JSON-LD, it's going to be able to
get a *lot* more information about your API, automatically.

But in API Platform, there is one other thing that you're going to see *all* the
time, and we're already seeing it! Hydra, which is *more* than just a many-headed
water monster from Greek mythology.

Go back to `/api/docs.jsonld`. In the same way that this points to the
`xmls` external document so that we can reference things like `xmls:integer`,
we're *also* pointing to an external document called `hydra` that defines more
"types" or "vocab" we can use.

Here's the idea: JSON-LD gave us the *system* for saying that this piece of data
is this type and this piece of data is this other type. Hydra is an *extension*
of JSON-LD that adds new *vocab*. It says:

> Hold on a second. JSON-LD is great and fun and an excellent dinner party guest.
> But to *really* allow a client and a server to communicate, we need *more*
> shared language! We need a way to define "classes" within my API, the properties
> of those classes and whether or not each is readable and writeable. Oh, and we
> also need to be able to communicate the *operations* that a resource supports:
> can I make a DELETE request to this resource to remove it? Can I make a PUT
> request to update it? What data format should I expect back from each operation?
> And what is the true identity of Batman?

Hydra took the JSON-LD system and added new "terminology" - called "vocab" - that
makes it possible to *fully* define *every* aspect of your API.

## Hydra Versus OpenAPI

At this point, you're almost *definitely* thinking:

> But wait, this *seriously* sounds like the *exact* same thing that we got
> from our OpenAPI JSON doc.

And, um... yea! Change the URL to `/api/docs.json`. This is the OpenAPI specification.
And if we change that to `.jsonld`, suddenly we have the JSON-LD specification
with Hydra.

So why do we have both? First, yes, these two documents basically do the
same thing: they describe your API in a machine-readable format. The JSON-LD and
Hydra format is a bit more powerful than OpenAPI: it's able to describe a few
things that OpenAPI can't. But OpenAPI is more common and has more tools built
around it. So, in some cases, having an OpenAPI specification will be useful - like
to use Swagger - and other times, the JSON-LD Hydra document will be useful. With
API Platform, you get both.

Phew! Ok, enough theory! Let's get back to building and customizing our API.
