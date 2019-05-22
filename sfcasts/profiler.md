# API Debugging with the Profiler

Debugging an API... can be tough... because you don't see the results - or errors -
as big HTML pages. So, to help us along the way, let's level-up our debugging
ability! In a traditional Symfony app, one of the *best* features is the web
debug toolbar... which we don't see down here right now because it's not installed
yet.

## Using the Profiler in an API

But... should we even bother? I mean, it's not like we can see the web debug toolbar
on a JSON API response, right? Of course we can! Well, sort of.

Find your terminal and get the profiler installed with:

```terminal
composer require profiler --dev
```

You can also run `composer require debug --dev` to install a few extra tools.
This installs the `WebProfilerBundle`, which adds a couple of configuration files
to help it do its magic.

Thanks to these, when we refresh... there it is! The web debug toolbar floating
on the bottom. This is *literally* the web debug toolbar for this *documentation*
page... which probably isn't that interesting.

But if we start making requests... check it out. When we execute an operation via
Swagger, it makes an AJAX request to complete the operation. And Symfony's web
debug toolbar has a cool little feature where it *tracks* those AJAX requests and
adds them to a list! Every time I hit execute, I get a new one!

The *real* magic is that you can click the little "sha" link to see the profiler
for that API request! So... yea! You can't see the web debug toolbar for a response
that returns JSON, but you *can* still see the *profiler*, which contains *way*
more data anyways, like the POST parameters, the request headers, request
content - which is really important when you're sending JSON - and all the
goodies that you expect - cache, performance, security, Doctrine, etc.

## Finding the Profile for an API Request

In addition to the little web debug toolbar AJAX tracker we just saw, there are
a few *other* ways to find the profiler for a specific API request. First,
every response has an `x-debug-token-link` header with a URL to its profiler page,
which you can read to figure out where to go. Or, you can just go to `/_profiler`
to see a list of the most recent request. Here's the one for `/api/cheese_listings`.
Click the token to jump into its profiler.

## The API Platform Panel

Oh, and API Platform adds its *own* profiler panel, which is a nice way to see
which *resource* this request was operating on and the metadata for it, including
this item operation and collection operation stuff - we'll talk about those really
soon. It also shows info about "data providers" and "data persisters" - two important
concepts we'll talk about later.

But before we get there, back on the documentation page, we need to talk about these
five endpoints - called *operations* - and how we can customize them.
