# More Formats: HAL & CSV

API Platform supports multiple input and output formats. You can see this by going
to `/api/cheeses.json` to get "raw" JSON or `.jsonld` or even `.html`, which
loads the HTML documentation. But adding the extension like this is kind of a "hack"
that API Platform added just to make things easier to play with.

Instead, you're supposed to choose what "format", or "representation" you want for
a resource via content negotiation. The documentation already does this and shows
it in the examples: it sends an `Accept` header, which API Platform uses to figure
out which format the serializer should use.

## Adding a new Format: HAL

Out-of-the-box, API Platform uses 3 formats... but it *actually* supports a bunch
more: JSON-API, HAL JSON, XML, YAML and CSV. Find your terminal and run:

```terminal
php bin/console debug:config api_platform
```

This is our current API Platform configuration, *including* default values. Check
out `formats`. Hey! It shows the 3 formats that we've seen so far and the mime
types for each - that's the value that should be sent in the `Accept` header
to activate them.

Let's add *another* format. To do that, copy this entire formats section. Then
open `config/packages/api_platform.yaml` and paste here. 

[[[ code('9c59cc589d') ]]]

This will make sure that we *keep* these three formats. Now, let's add a new one: `jsonhal`. 
This is one of the *other* formats that API Platform supports out-of-the box. Below, add
`mime_types:` then the standard content type for this format: `application/hal+json`.

[[[ code('0e59e2d1cb') ]]]

Cool! And *just* like that... our *entire* API supports a new format! Refresh the
docs and open the GET operation to see cheese listing 1. Before you hit execute,
open the format drop down and... hey hey! Select `application/hal+json`. Execute!

Say hello to the JSON HAL format: a, sort of "competing" format with JSON-LD or
JSON-API, all of which aim to standardize how you should *structure* your JSON:
where you data should live, where links should live, etc.

In HAL, you have an `_links` property. It only has a link to `self` now, but this
often contains links to other resources.

This is more fun if we try the GET collection operation: select
`application/hal+json` and hit Execute. It's kinda cool to see how the different
formats "advertise" pagination. HAL uses `_links` with `first`, `last` and `next`
keys. If we were on page 2, there would also be a `prev` field.

Having this format available may or may not be handy for you - the awesome part
is you can *choose* whatever you want. *And*, understanding formats unlocks other
interesting possibilities.

## CSV Format

For example, what if, for some reason, you or someone who uses your API wants to
be able to fetch the cheese listing resources as CSV? Yea, that's totally possible!
But instead of making that format available globally for *every* resource, let's
activate it for *only* our `CheeseListing`.

Back inside that class, once again under this special `attributes` key, add
`"formats"`. If you want to keep all the existing formats, you'll need to list
them here: `jsonld`, `json`, then... let's see, ah yep, `html` and `jsonhal`.
To add a *new* format, say `csv`, but *set* this to a new array with `text/csv`
inside.

[[[ code('451350bf22') ]]]

This is the mime type for the format. We didn't need to add mime types for the
*other* formats because they're already set up in our config file.

Let's try it! Go refresh the docs. Suddenly, *only* for *this* resource... which,
ok, we only have one resource right now... but CheeseListing *now* has a CSV
format. Select it and Execute.

There it is! And we can try this directly in the browser by adding `.csv` on the
end. My browser downloaded it... so let's flip over and `cat` that file to see what
it looks like. The line breaks look a bit weird, but that *is* valid CSV.

A better example is getting the full list: `/api/cheeses.csv`. Let's go see what
that looks like in the terminal as well. This is awesome! Fastest CSV download
feature I've *ever* built.

And... yea! You can *also* create your own format and activate it in this same
way. It's a powerful idea: our *one* API Resource can be *represented* in any
number of different ways, including formats - like CSV - which you don't need...
until that one random situation when you suddenly *really* need them.

Next, it's time to stop letting users create cheese listings with *any* crazy
data they want. It's time to add validation!
