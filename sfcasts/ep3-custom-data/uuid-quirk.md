# UUID Quirk

Coming Soon...


by the way, there is one little quirk
with you UUIDs and that is they can't be called ID. Let me show you what I mean head
up to the year. You're already a property and let's pretend that we just want to call
this ID, uh, in the, uh, API. So for example, instead of sending you a field called,
uh, your ID here, I just want that to be called ID. So to make that happen, I can add
an `@SerializedName("id")` and then over my test, it simply means that I just
need to change that field, right? We've done this type of thing before. Normally that
works fine, but if we rerun that test,

```terminal-silent
synfony php bin/phpunit --filter=testCreateUserWithUuid
```

it fails and it says an Erik or an update is
not allowed for this operation. So this is basically a bog /complex area inside of a
API platform where in the JSON, and right now there's not a fix for it. There is a
workaround. However, whenever you send the data, the APM platform, it tries to figure
out what format you're sending it.

You know, for example, if we send XML, we would need to tell it, uh, it would need to
know that that's XML. So we can DC to realize it and back smelt by default and assume
that you're sending JSON, however, in the same way that you can get content back as,
uh, as JSON or JSON LD. So for example, by default, we get back JSON LD format. So
when we use an API and point blank /APSs Jesus, we can get things back as JSON L D,
or we can actually get things back as JSON. So there's when you request a resource,
you can tell it what outputs you want. You can actually do the same thing. When you
send an input, you can actually send it data and say, the data I'm providing you is
JSON or XML. Now it doesn't make much difference on the input format, but you can
actually tell API platform that I'm sending you JSON LD format of data versus JSON.

And if you do that, that actually makes just a small enough difference in how the,
how the content is DC realized that it avoids this air. There's a long way of saying
that the workaround ground for this is actually descend a content type header. So in
the test, I'll set `headers` and I'll set that and I'll send that to an array with
`Content-Type` set to `Application/ld+json`, we're trying to test out it passes
now, to be honest with you, I don't really know if that makes any other changes in
how our objects do seem to realize it doesn't seem to, um, but full disclosure. I
don't know that for sure it does around this bug. So the destabilization logic is
slightly different internally when you're passing this, now this work ground works
great. If you are the only person using your own API, because you just need to know
to include this header. But if other people are going to be using your API, then it's
kind of ugly for them to have to pass this content type header to avoid that error.
So another workaround is actually to disable the JSON format entirely in always
forced JSON LD.

So I'm actually gonna do is remove this header here, and then I'm going to go into
`config/packages/api_platform.yaml` and down here, I'm going to comment
out the `json` format. Now, as soon as I do that, whenever we send JSON input, it's
going to use JSON LD is the default format instead of JSON. There's one other spot I
need to change just so I don't get an error. If I go into entity, `CheeseListing`, uh,
an uh, past tutorial, we actually, uh, set specific formats for one of our
operations. No, was that specific format for this? I'm actually going to remove JSON
from the list and since no longer even available as a global format. So now I'm not
passing the header anymore and

```terminal-silent
synfony php bin/phpunit --filter=testCreateUserWithUuid
```

it still fails.

Oh, of course, huh? Because forgot. I need to actually keep this line right here.
There we go. So this tells it, if we send data applications, that's JSON use this
type course. That's the piece that I was missing.

```terminal-silent
synfony php bin/phpunit --filter=testCreateUserWithUuid
```

It's now it's going to interpret
that as using the JSON LD format when we sign it and now I pass this. So I just want
to be aware of that limitation and that workaround. I'm actually going to remove all
of this and, uh, and be content with using just you UID. So I'll put that format
back. I will re add it to my JSON and then over inside my resource, I'm actually
going to remove that serialized name so we can get back to the good spot.

Okay. That's it. Whoa. We just like API platform and really rest in general to
another level of flexibility with custom fields, custom API resources, and a lot more
in the next course, we'll dive a bit deeper into one last area we haven't talked
about yet, which is true custom end points, uh, including custom end points that are
done in a controller and also custom end points that are done with messenger
integration and Symfony. And if there is something that we still haven't talked about
yet, that you really want to know about, let us know in the comments. Okay. Go make
some awesome JSON, and we'll see you next time.
