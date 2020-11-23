# UUID Quirk with "id" Name

There is one *tiny* little quirk with you UUID's. What is it? If you want to be
able to send it in JSON, the field can't be called... `id`!

## Naming the Identifier "id"

I know, that's sounds kinda strange, so let me show you. Find the `uuid` property
and pretend that we want to call this `id` in the API:

[[[ code('d7cfcd6370') ]]]

Literally, instead of sending a field called `uuid`, I want to send one called `id`.

The easiest way to make that happen is to add `@SerializedName("id")`:

[[[ code('7bb1e0a08d') ]]]

Then over in the test, it's simple: change `uuid` to `id`:

[[[ code('a744b08f7c') ]]]

We've done this type of thing before. And until now, it's been working fine. If
you're getting the feeling that it *won't* work this time... yea... you're right.
Run that test again:

```terminal-silent
symfony php bin/phpunit --filter=testCreateUserWithUuid
```

It fails! It says:

> Update is not allowed for this operation.

This... is sort of a bug in API Platform. Well... it's not that simple - it's related
to the idea that the `id` field is, sort of special, in the `jsonapi` format.

Obviously, the easiest fix is to *not* call your field `id` if you want to allow
it to be sent on create. But, if you really *do* want to call it `id`, then we
have two work-arounds.

## Sending the Data as json-ld

Whenever you send data, API Platform tries to figure out what *format* that data
is. For example, if we sent XML instead of JSON, API Platform would somehow need
to *know* the data is XML so that it could use the correct deserializer.

And the same thing happens with the data we get *back* from our API. Normally,
our API return JSON. Well, more specifically, it returns in the JSON-LD format -
we can see that if we go to `/api/cheeses.jsonld`.

But we can *also* get things back as normal json! We're controlling this here
by adding `.json` to the URL, but the *normal* way you should control this is
by sending an `Accept` header to tell the API which format you want.

We haven't talked about it yet, but you can do the *same* thing for the format
of your *input* data. Yep, you can say:

> Hey API! The data I'm providing you is JSON... or XML... or gibberish.

You can *even* tell API Platform that you are sending JSON-LD instead of plain JSON.
Now.... in reality, that makes very little difference - the JSON would still look
the same. But internally, this activates a different denormalizer that *avoids*
the error.

How do we tell API Platform what format the data we're sending is in? With the
`Content-Type` header. In the test, add `headers` and set that to an array with
`Content-Type` equals `application/ld+json`, which is the official "media type"
or "mime type" for JSON-LD:

[[[ code('61902fe093') ]]]

Let's try it!

```terminal-silent
symfony php bin/phpunit --filter=testCreateUserWithUuid
```

*Now* having an `id` field is ok.

## Removing the JSON format Entirely

If you're the only person using your API, then this is probably ok: you just
need to know to include *this* specific header. But if third-parties will use
your API, then it's kind of ugly to force them to pass this *exact* header or
get a weird error.

Oh, and by the way, your API clients *do* need to include a `Content-Type` header
when sending data, otherwise API Platform won't understand it or will think that
you're sending form data instead of JSON. Basically, you'll get a 400 error. But
the `Content-Type` header is *usually* sent automatically by most clients and set
to `application/json`... because the client realizes you're sending JSON.

So... if you want to prevent your API users from needing to override that and
send `application/ld+json`, there is another - kind of more extreme - option:
disable the JSON format and always force JSON-LD.

Let's try this. Remove the header:

[[[ code('67fa246a6e') ]]]

And then go into `config/packages/api_platform.yaml`. Down here, comment-out the
`json` format completely:

[[[ code('fc57936138') ]]]

This means that this is *no* longer a format that our API can read or output.

There's one other spot I need to change to avoid an error. Open `CheeseListing`.
In a previous tutorial, we set specific formats for this resource to allow a `csv`
format. Remove `json` from this list:

[[[ code('8dbbc770c5') ]]]

Ok, let's see what happens! When we run the test...

```terminal-silent
symfony php bin/phpunit --filter=testCreateUserWithUuid
```

It *still* fails. And the error is cool!

> The content-type "application/json" is not supported

The test client - realizing that we're sending JSON data - sent this header
automatically *for* us. But now that our API doesn't support JSON, this fails!

Back in `api_platform.yaml`, move that `application/json` line up under the
`jsonld` mime type:

[[[ code('46027d08d0') ]]]

This means that if the `Content-Type` header is `application/json`, use `jsonld`.

Try the test one more time.

```terminal-silent
symfony php bin/phpunit --filter=testCreateUserWithUuid
```

And... got it! This solution is a bit odd... but if you're always using JSON-LD,
it's one option. But I'm going to remove all of this and be happy to call the field
`uuid`. So I'll put that formats back:

[[[ code('2d3c82ed8c') ]]]

Re-add the `json` format:

[[[ code('4790dd1060') ]]]

And, over in the resource class, remove the `SerializedName`:

[[[ code('24270d3d78') ]]]

Whoa! Friends! That's it! Congratulations on taking your API Platform skills up
another *huge* level! You can now properly add custom fields in... about 10 different
ways, including by creating custom API resources or DTO's. We also learned how
to run custom code on a "state change", like when a cheese listing becomes
published. That's a great way to keep your endpoints RESTful, but still run
the business code you need in situations like this.

In the next course, we'll dive a bit deeper into one area we haven't talked about
much yet: creating true, custom endpoints, both via a controller and also via
API Platform's Messenger integration.

If there's something that we still haven't talked about, let us know down in the
comments!

Now, go make some awesome JSON, let us know what cool stuff you're building and
we'll seeya next time.
