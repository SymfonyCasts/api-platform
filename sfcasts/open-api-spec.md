# OpenAPI Specification

Confession time: this tutorial is about a *lot* more than just API Platform. The
world of APIs has undergone *massive* changes over the past few years, introducing
new hypermedia formats, standards, specifications, performance tools and more!
API Platform lives right in the middle of these: bringing bleeding-edge best practices
right into your app. If you *truly* want to master API Platform, you need to
understand modern API development.

I told you earlier that what we're looking at is called Swagger. Swagger is basically
an API documentation interface - a sort of, interactive README. Google for
Swagger and open their site. Under tools, the one *we're* using is called Swagger UI.

Yep!

> Swagger UI allows anyone to visualize and interact with your API's resources
> without having any of the implementation in place.

Literally, you could first *describe* your API - what endpoints it will have, what
it will return, what fields to expect - and then use Swagger UI to visualize
your future API, *before* writing even *one* line of code for it.

Let me show you what I mean: they have a live demo that looks *very* similar to
our API docs. See that `swagger.json` URL on top? Copy that, open a new tab, and
paste. Woh! It's a *huge* JSON file that describes the API! *This* is how Swagger
UI works: it reads this JSON file and builds a visual, interactive interface for
it. Heck, this API might not even *exist*! As long as you have this JSON description
file, you can use Swagger UI.

The JSON file contains all your paths, a description of what each does, the
parameters of the input, what output to expect, details related to security...
it basically tries to *completely* describe your API.

So *if* you have one of these JSON configuration files, you can plug it into Swagger
UI and... boom! You get a rich, descriptive interface.

## Hello OpenAPI

The format of this file is called OpenAPI. So, Swagger UI is the interface and
it understands this sort of, official spec format for describing APIs called
OpenAPI. To make things a bit *more* confusing, the OpenAPI spec *used* to be
called Swagger. Starting with OpenAPI 3.0, it's called OpenAPI and Swagger is
just the interface.

Phew!

Anyways, this is all really cool... but creating an API is already enough work,
without needing to try to build and maintain this gigantic JSON document on the
side. Which is why API Platform does it for you.

Remember: API Platform's philosophy is this: create some resources, tweak any
configuration you need - we haven't done that, but will soon - and let API Platform
*expose* those resources as an API. It does that, but to be an *extra* good friend,
it *also* creates an OpenAPI specification. Check it out: go to `/api/docs.json`.

Hello giant OpenAPI spec document! Notice it says `swagger: "2.0"`. OpenAPI
version 3 is still pretty new, so API Platform 2 still uses the old format. Add
`?spec_version=3` to the URL to see... yep! This is that same document in the latest
format version.

Now, go back to our API doc homepage and view the HTML source. Ha! The OpenAPI JSON
data is *already* being included on this page via a little `swagger-data` script
tag! *That* is how this page is working!

To generate Swagger UI from OpenAPI version 3, you can add the same `?spec_version=3`
to the URL. Yep, you can see the `OAS3` tag. That doesn't change a *lot* on the
frontend, but there *are* a few new pieces of information that Swagger can now
use thanks to the new spec version.

## What else Can OpenAPI Do? Code Generation!

But... other than the fact that it gives us this nice Swagger UI, why should we
care that there's some giant OpenAPI JSON spec being created behind the scenes?
Back on the Swagger site, one of the *other* tools is called Swagger CodeGen:
a tool for creating an SDK for your API in almost any language! Think about it:
if your API is fully-documented in a machine-understandable language, shouldn't
we be able to generate a JavaScript or PHP library that's *customized* for talking
with your API? You totally can!

The last thing I want to point out is that, in addition to the endpoints, or "paths",
the OpenAPI specification also has information about "models". In the JSON spec,
scroll *all* the way to the bottom: it describes our `CheeseListing` model and
the fields to expect when sending and receiving this model. You can see this same
info in Swagger.

And woh! It somehow *already* knows that the `id` is an `integer` and that it's
`readonly`. It also knows price is an `integer` and `createdAt` is a string in a
`datetime` format. That's awesome! API Platform *reads* that information directly
from our code, which means that our API docs stay up-to-date without us needing to
think about it. We'll learn more about how that works along the way.

But before we get there, we need to talk about one other *super* important thing
that we're already seeing: the JSON-LD and Hydra format that's being returned by
our API responses.
