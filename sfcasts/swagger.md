# Swagger: Instant, Interactive API Docs

We're currently looking at something called Swagger: an open source API documentation
interface. We're going talk more about it soon, but the idea is basically this:
*if* you have an API - built in any language - and you create some configuration
that *describes* that API in a format that Swagger understands, boom! Swagger can
render this beautiful *interactive* documentation for you. Behind the scenes,
API Platform is already preparing that configuration for Swagger.

Let's play with it! Open the POST endpoint. It says what this does and shows how
the JSON should look to use it. Nice! Click "Try it out"! Let's see what's in
my kitchen - some "Half-eaten blue cheese", which is still... *probably* ok to eat.
We'll sell it for $1. What a bargain! And... Execute!

Um... what happened? Scroll down. Woh! It just made a `POST` request to
`/api/cheese_listings` and sent our JSON! Our app *responded* with a 201 status code
and... some weird-looking JSON keys: `@context`, `@id` and `@type`. *Then* it has
the normal data for the new cheese listing: the auto-increment `id`, `title`, etc.
Hey! We *already* have a working API... and this just proved it!

Close up the POST and open the `GET` that returns a collection of cheese listings.
Try this one out too: Execute! Yep... there's our *one* listing... but it's *not*
raw JSON. This extra stuff is called JSON-LD. It's just normal *JSON*, but with
special *keys* - like `@context` - that have a specific meaning. Understanding
JSON-LD is an important part of leveraging API Platform - and we'll talk more about
it soon.

Anyways, to make things more interesting - go back to the POST endpoint and
create a second cheese listing - a giant block of cheddar cheese... for $10.
Execute! Same result: 201 status code and id 2.

Try the collection `GET` endpoint again. And... alright! Two results, with ids 1
and 2. And if we want to fetch just *one* cheese listing, we can do that with the
other `GET` endpoint. As you can see, the `id` of the cheese listing that we want
to fetch is part of the URL. This time, when we click to try it, cool! It gives
us a box for the id. Use "2" and... Execute!

This makes a very simple GET request to `/api/cheese_listings/2`, which returns a
`200` status code and the familiar JSON format.

## Content-Type Negotiation

How cool is this! A full API "CRUD" with *no* work. Of course, the trick will be
to *customize* this to our exact needs. But sheesh! This is an *awesome* start.

Let's try to hit our API directly - *outside* of Swagger - just to make sure this
isn't all an elaborate trick. Copy the URL, open a new tab, paste and... hello
JSON! Woh! Hello... API doc page again?

It scrolled us down to the documentation for this endpoint and executed it with
id 2... which is cool... but what's going on? Do we *actually* have a working API
or not?

Built into API Platform is something called Content-Type negotiation. Conveniently,
when you execute an operation, Swagger shows you how you could make that *same*
request using curl at the command line. And it includes one *critical* piece:

> `-H "accept: application/ld+json"`

That says: make a request with an `Accept` header set to `application/ld+json`.
The request is *hinting* to API Platform that it should return the data in this
JSON-LD format. Whether you realize it or not, your browser *also* sends this
header: as `text/html`... cause... it's a browser. That basically tells API Platform:

> Hey! I want the CheeseListing with id 2 in HTML format.

API Platform responds by doing its best to do exactly that: it returns the HTML
Swagger page with CheeseListing id 2 already showing.

## Faking the Content-Type

This isn't a problem for an API client because setting an `Accept` header is easy.
But... is there some way to kinda... "test" the endpoint in a browser? Totally!
You can cheat: add `.jsonld` to the end of the URL.

Boom! *This* is our API endpoint in the JSON-LD format. I called this "cheating"
because this little "trick" of adding the extension is *really* only meant for
development. In the real world, you should set the `Accept` header instead, like
if you were making an AJAX request from JavaScript.

And, check this out: change the extension to `.json`. That looks a bit more familiar!

This is a *great* example of the API Platform philosophy: instead of thinking about
routes, controllers and responses, API Platform wants you to think about creating
API resources - like `CheeseListing` - and then *exposing* that resource in a variety
of different formats, like JSON-LD, normal JSON, XML or exposing it through a GraphQL
interface.

## Where do These Routes Come From?

Of course, as *awesome* as that is, if you're like me, you're probably thinking:

> This is cool... but how did all these endpoints get magically added to my app?

After all, we don't *normally* add an annotation to an entity... and suddenly
get a bunch of functional pages!

Find your terminal and run:

```terminal
php bin/console debug:router
```

Cool! API platform is bringing in several new routes: `api_entrypoint` is sort
of the "homepage" of our api, which, by the way, can be returned as HTML - like
we've been seeing - *or* as JSON-LD, for a machine-readable "index" of what's
included in our API. More on that later. There's also a `/api/docs` URL - which,
for HTML is the same as going to `/api`, another called `/api/context` - more on
that in a minute - and below, 5 routes for the 5 new endpoints. When we add more
resources later, we'll see more routes.

When we installed the API Platform pack, its recipe added a
`config/routes/api_platform.yaml` file. 

[[[ code('ec8e8b39c5') ]]]

*This* is how API Platform magically adds the routes. 
It's not very interesting, but see that `type: api_platform`? That basically says:

> Hey! I want API Platform to be able to dynamically add whatever routes it wants.

It does that by finding all the classes marked with `@ApiResource` - just one right
now - creating 5 new routes for the 5 operations, and prefixing all the URLs with
`/api`. If you want your API URLs to live at the root of the domain, just change
this to `prefix: /`.

I hope you're already excited, but there is *so* much more going on than
meets the eye! Next, let's talk about the OpenAPI spec: an industry-standard API
*description* format that gives your API Swagger superpowers... for free. Yes,
we need to talk a *little* bit of theory - but you will *not* regret it.
