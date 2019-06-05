# Pagination

If we have a million, or a thousand... or even a *hundred* cheese listings, we can't
return *all* of them when someone makes a GET request to `/api/cheeses`! The way
that's solved in an API is *really* the same as on the web: pagination! And
in API Platform... ah, it's so *boring*... you get a powerful, flexible pagination
system... without doing... *anything*.

Let's go to the POST operation and create a few more cheese listings. I'll put in
some simple data... and execute a bunch of times.. in fast forward. On a real
project, I'd use data fixtures to help me get useful dummy data.

We should have about 10 or so thanks to the 4 we started with. Now, head up to
the GET collection operation... and hit Execute. We *still* see *all* the results.
That's because API Platform shows *30* results per page, by default. Because I
don't feel like adding 20 more manually, this is a *great* time to learn how
to change that!

## Controlling Items Per Page

First, this can be changed globally in your `config/packages/api_platform.yaml`
file. I won't show it now, but always remember that you can run:

```terminal
php bin/console debug:config api_platform
```

to see a list of all of the valid configuration for that file and their current
values. That would reveal a `collection.pagination` section that is *full* of
config.

But we can also control the number of items per page on a resource-by-resource
basis. Inside the `@ApiResource` annotation, add `attributes={}`... which is a key
that holds a variety of random configuration for API Platform. And then,
`"pagination_items_per_page": 10`.

[[[ code('62704bc366') ]]]

I mentioned earlier that a lot of API Platform is learning exactly *what* you can
configure inside of this annotation and how. *This* is a perfect example.

Go back to the docs - no need to refresh. Just hit Execute. Let's see... the total
items are 11... but if you counted, this is only showing *10* results! Hello pagination!
We also have a new `hydra:view` property. This advertises that pagination
is happening and how we can "browse" through the other pages: we can follow
`hydra:first`, `hydra:last` and `hydra:next` to go to the first, last or next page.
The URLs look exactly like I want: `?page=1`, `?page=2` and so on.

Open a new tab and go back to `/api/cheeses.jsonld`. Yep, the first 10 results.
Now add `?page=2`... to see the one, last result.

Filtering *also* still works. Try `.jsonld?title=cheese`. That returns... only
10 results... so no pagination! That's no fun. Let's go back to the docs, open
the POST endpoint and add a few more. Oh, but let's make sure that we add one
with "cheese" in the title. Hit Execute a few times.

*Now* go refresh the GET collection operation with `?title=cheese`. Nice! We
have 13 total results and this shows the first 10. What's *really* nice is that
the pagination links *include* the filter! That is *super* useful in JavaScript:
you don't need to try to hack the URL together manually by combining the `page`
and filter information: just read the links from hydra and use them.

Next, we know that our API can return JSON-LD, JSON and HTML. And... that's probably
all we need, right? Let's see how easy it is to add *more* formats... including
making our cheese listings downloadable as a CSV.
