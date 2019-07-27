# JSON-LD: Context for your Data

A typical API returns JSON. Go to `/api/cheese_listings/2.json`. When I think
of an API, this is what I *traditionally* picture in my head.

## Your Data Lacks Meaning

But, what is the *significance* of these fields? What *exactly* do the `title`
or `description` fields *mean*? Are they plain text? Can they contain HTML? Does
the description describe this *type* of cheese in general, or is this specific
to the condition of the *exact* cheese I'm selling? What about price? Is that a
string, float, integer? Is it in US Dollars? Euro? Is it measured in cents?

If you're a human... you *are* a human, right? A human can *usually* "infer"
some meaning from the field names *or* find some human-readable documentation
to help learn *exactly* what each field represents. But, there's no way for a *machine*
to understand *anything* about what these fields mean or their types. Even a *smart*
algorithm could get confused! A field called `title` could be the "title" of
something - like the title of a book - *or* it could be the title of a person -
Mr, Mrs, etc.

## RDF & HTML

This is what JSON-LD aims to solve. Ok, honestly, there is *a lot* going on these
days with this problem of:

> How do we give data on the web *context* or *meaning* that computers can understand?

So let's hit some basic points. There's this thing called RDF - Resource Description
Framework - which is a, sort of, set of *rules* about how we can "describe"
the *meaning* of data. It's a bit abstract, but it's a guide on how you can say
that one piece of data has this "type" or one resource is a "subclass" of some
other "type". In HTML, you can add attributes to your elements to add RDF
metadata - saying that some `div` describes a *Person* and that this
Person's `name` and `telephone` are these other pieces of data:

```html
<p typeof="http://schema.org/Person">
   My name is
   <span property="http://schema.org/Person#name">Manu Sporny</span>
   and you can give me a ring via
   <span property="http://schema.org/Person#telephone">1-800-555-0199</span>.
</p>

<!-- or equivalent using vocab -->
<p vocab="http://schema.org/" typeof="Person">
   My name is
   <span property="name">Manu Sporny</span>
   and you can give me a ring via
   <span property="telephone">1-800-555-0199</span>.
</p>
```

This makes your unstructured HTML understandable by machines. It's even
*more* understandable if 2 different sites use the *exact* same definition
of "Person", which is why the "types" are URLs and sites try to re-use existing
types rather than invent new ones.

Kinda cool!

## Hello JSON-LD

JSON-LD allows us to do this *same* thing for JSON. Change the URL from `.json`
to `.jsonld`. This has the *same* data, but with a few extra fields:
`@context`, `@id` and `@type`. JSON-LD is nothing more than a "standard" that
describes a few extra fields that your JSON can have - all starting with `@` - that
help machines learn more about your API.

## JSON-LD: @id

So, first: `@id`. In a RESTful API, *every* URL represents a resource and should
have its own unique identifier. JSON-LD makes this official by saying that every
resource should have an `@id` field... which *might* seem redundant right now...
because... we're *also* outputting our *own* `id` field. But there are two special
things about `@id`. First, anyone, or any HTTP client, that understands JSON-LD
will know to look for `@id`. It's the official "key" for the unique identifier.
Our `id` column is something specific to our API. Second, in JSON-LD, *everything*
is done with URLs. Saying the `id` is 2 is cool... but saying the `id` is
`/api/cheese_listing/2` is *infinitely* more useful! That's a URL that someone
could use to get details about this resource! It's also unique within our entire
API... or really... if you include our domain name - it's a unique identifier for
that resource across the entire web!

This URL is actually called an IRI: Internationalized Resource Identifier. We're
going to use IRI's everywhere instead of integer ids.

## JSON-LD @context and @type

The other two JSON-LD keys - `@context` and `@type` work together. The idea is
*really* cool: if we add an `@type` key to *every* resource and then define the
exact *fields* of that type somewhere, that gives us two superpowers. First, we
instantly know if two different JSON structures are in fact *both* describing
a cheese listing... or if they just *look* similar and are actually describing
different things. And second, we can look at the definition of this type to learn
more about it: what properties it has and even the type of each property.

Heck, this is nothing new! We do this *all* the time in PHP! When we create a *class*
instead of just an array, we are giving our data a "type". It allows us to know
*exactly* what type of data we're dealing with *and* we can look at the class to
learn more about its properties. So... yea - the `@type` field sorta transforms
this data from a structureless array into a concrete class that we can understand!

But... *where* is this `CheeseListing` type defined? That's where `@context`
comes in: it basically says:

> Hey! To get more details, or "context" about the fields used in this data, go
> to this other URL.

For this to make sense, we need to think like a machine: a machine that *desperately*
wants to learn as much as possible about our API, its fields and what they mean.
When a machine sees that `@context`, it follows it. Yea, let's *literally* put that
URL in the browser: `/api/contexts/CheeseListing`. And... interesting. It's another
`@context`. Without going into *too* much crazy detail, `@context` allows us to
use "shortcut" property names - called "terms". Our actual JSON response includes
fields like `title` and `description`. But as far as JSON-LD is concerned, when
you take the `@context` into account, it's as *if* the response looks something
like this:

```json
{
    "@context": {
        "@vocab": "https://localhost:8000/api/docs.jsonld#"
    },
    "@id": "/api/cheese_listing/2",
    "@type": "CheeseListing",
    "CheeseListing/title": "Giant block of cheddar cheese",
    "CheeseListing/description": "mmmmmm",
    "CheeseListing/price": 1000,
}
```

The idea is that we know that, in general, *this* resource is a `CheeseListing`
type, and when we find its docs, we should find information also about the meaning
and types of the `CheeseListing/title` or `CheeseListing/price` properties. Where
does that documentation live? Follow the `@vocab` link to `/api/docs.jsonld`.

This is a *full* description of our API in JSON-LD. And, check it out. It has
a section called `supportedClasses`, with a `CheeseListing` class and all the
different properties below it! *This* is how a machine can understand what the
`CheeseListing/title` property means: it has a label, details on whether or not
it's required, whether or not it's readable and whether or not it's writeable.
For `CheeseListing/price`, it already knows that this is an integer.

This is *powerful* information for a machine! And if you're thinking:

> Wait a second! Isn't this exactly the same info that the OpenAPI spec gave us?

Well, you're not wrong. But more on that in a little while.

Anyways, the *really* cool thing is that API Platform is getting all of the data
about our class and its properties from *our* code! For example, look at the
`CheeseListing/price` property: it has a title, type of `xmls:integer`
and some data.

By the way, even that `xmls:integer` type comes from *another* document. I didn't
show it, but at the top of this page, we're referencing *another* document
that defines *more* types, including what the `xmls:integer` "type" means in a
machine-readable format.

Anyways, back in our code, above price, add some phpdoc:

> The price of this delicious cheese in cents.

Refresh our JSON-LD document now. Boom! Suddenly we have a `hydra:description`
field! We're going to talk about what "Hydra" is next.

## How this Looks to a Machine

I know, I know, this is all a bit confusing, well, it is for *me* at least. But,
try to picture what this looks like to a *machine*. Go back to the original JSON:
it said `@type: "CheeseListing"`. By "following" the `@context` URL, then following
`@vocab` - *almost* the same way that we follow links inside a browser - we can
eventually find details about what that "type" actually means! And by
referencing external documents under `@context`, we can, sort of, "import" more
types. When a machine sees `xmls:integer`, it knows it can follow this `xmls`
link to find out more about that type. And if *all* APIs used this same identifier
for integer types, well, suddenly, APIs would become *super* understandable
by machines.

Anyways, you don't need to be able to read these documents and make perfect sense
of them. As long as you understand what all of this "linked data" and shared
"types" are trying to accomplish, you're good.

Ok, we're *almost* done with all this theoretical stuff - I *promise*. But first,
we need talk about what "Hydra" is, and see a few other cool entries
that are already under `hydra:supportedClass`.
