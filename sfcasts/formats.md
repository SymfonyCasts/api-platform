# Formats

Coming soon...

Mentioned earlier that API Platform supports multiple formats. You can see this by
going to `/api/cheeses.json` did just to see raw JSON or `.jsonld` or even `.html`
which technically loads HTML documentation. But we all stocked at all
something. We also mentioned something called content-type negotiation, which is the
fact that adding this extension on here is kind of a hack. What really goes on behind
the scenes and the way that you should use your API is that if you want the JSON
version, you should do what our documentation does, which has actually since this
`accept:` header. So the cool thing is that our API out of the box supports three
different formats and we can choose the format and want with the `accept:` header.
Though by far the most useful is JSON-LD. But knowing this unlocked some cool
possibilities, we can actually very easily add more formats.

In fact, without doing anything else, API Platform supports, um, JSON Api, how JSON
Xml, YAML and CSV. So check this out. If I had a terminal 

```terminal
php bin/console debug:config api_platform
```

to see kind of what our current API Platform
configuration looks like, including um, configuration. So you can see it has this
`formats:` key under here and this is actually telling you that we support these three
minutes. I love the box and here are the mime types that should activate those. So
I'm actually going to copy that entire format section because I want to add a new
format, but in order to add a new format, I need to first list these formats so that
we don't remove them. Now hello. I can use one of the, um, one of the builtin formats
is called `jsonhal`, how it's kind of like JSON-LD, it's JSON that has some extra metadata
attached to it. And then, and, and um, it's one of the supported versions and JSON
Howell is the exact key that you need to use to support it. Now I'll say `mine_types:`
in the mime type for this is usually `application/hal+json`.

Cool. And just like that. If we refresh our documentation is being updated and I'll
actually go and try one, she's listing. And here you can change this to 
`applications/hal+json` and you can see how it looks. So it has this little `_links` section
whereas links to itself and we'll also have links to other related resources if they
were there. This looks even cooler if you try out our main and points. So I'll select
`application/hal+json` hit execute. And it's just cool to see how it does pagination
So it has that same honor `_links` thing. And here it has a `first`,
`last` and `next`, uh, links. And if we were on page 2, we would actually have a
previous, I'm just kind of fun and here all the,

So it's just a kind of a different way of a structure that's trying to solve this
problem of like adding links to our data. And there may be some cases when having
this format can be handy. Now, one of the forums I think actually is really handy
every now and then is the fact that you can do CSV but you might not want to activate
CSV across your entire application. So one of the cool things we can do is you can
activate um, formats on a resource by for resource level. So back on our 
`CheeseListing` entity. Well, once again, under this kind of special `attributes` key, there's
one call `"formats"`. Now if you want to keep all of your existing formats, you want to,
you're going to need to list those first by their names. So `jsonld`, `json`, what I'm
doing here is just kind of like mentioning these names.

So we'll also do `html` and uh, hal JSON or `jsonhal`. Then if you want to add an
additional one, like we want CSV just for this resource, we can say comma, `csv`, but
because this is not one of the main formats inside of here, we need to specify what
mind types to use second, say `={}` and then you can say `text/csv`.
So we don't need to do this for the other ones because we're basically just
activating our preexisting configuration in here about for this last one, we need to
say this should be for `text/csv`. So now when refresh the documentation, suddenly
only for this resource, which it's the only resource we have right now, but only for
this resource, we have a CSV format so we can check it out down here and execute
that. And there it is. Thank you. Even try this in our browser and say `.csv` on the
end. And then I'll flip over and I'll just cat that file so we can see what it looks
like. And he has line breaks with the library, sir. Okay. Because they're inside of
a, um, quotes, oh, we're gonna do that same thing to get all of them, `cheeses.csv`
and all cat that file as well.

cool. So, and in addition to this, you can actually create your own custom formats if
you need to return something. Um, there's a way, create a custom format and there,
and then you can activate it exactly like when you need it. So kind of a powerful
system. You have this resource and you can really express that resource and a number
of different ways, including these kind of like weird formats like CSV, which
sometimes you just need for random situations.