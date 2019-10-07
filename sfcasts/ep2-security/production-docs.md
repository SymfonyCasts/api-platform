# Hello API Security + API Docs on Production?

Friends! Welcome to part two of our API Platform series - the one where we're
talking all about ice cream, um, security. We're talking about security, not uh,
ice cream. Hmm. Um, it's going to be *almost* as awesome as ice cream though,
because the topic of security - especially API security - is fascinating! We've
got API tokens, session-based authentication, CSRF attacks, dragon attacks and
the challenge of securing our API Platform application down to the smallest details,
like letting different users see different records *or* even returning or accepting different fields based on the user. Yep, we're going to take this *wonderful*
base that API platform has given us and shape it to act *exactly* like we need
from a security perspective.

The great thing about API platform is that it's basically just... Symfony security.
They didn't reinvent the wheel at all. If you haven't gone through our Symfony
Security tutorial yet, *now* would be a good time: you'll feel *much* more dangerous
after.

Anyways, let's dive into this! To put the lock down on your API Platform
security skills, download the course code from this page and code along with me.
After you unzip the file, you'll find a `start/` directory that has the same
code you see here. Open up the `README.md` file for all the details on getting
the app set up. If you coded through part one of this tutorial, um, you *rock*,
but also, I recommend downloading the new course code because I upgraded a few
dependencies and added a frontend to the site.

Anyways, one of the last steps in the README will be to open a terminal, move
into the project and run:

```terminal
yarn encore dev --watch
```

to run Encore and build some JavaScript that'll power a small frontend. To make
things more realistic, we'll do a *little* bit of work in JavaScript to see how
it interacts with our API from an authentication standpoint. Next, open *another*
terminal and start the Symfony local web server:

```terminal
symfony serve
```

Once that starts, you should be able to fly back over to your browser and open
up `https://localhost:8000` to see... Cheese Whiz! The peer-to-peer cheese-selling
app we build in part 1. Actually, this is our brand new Vue.js-powered frontend.
OOOOooOOO. Go to `/api`. Ah yes, *this* is the API we built: we have a `CheeseListing`
resource, a `User` resource, they're related to each other and we've customized
a ton of stuff. In a bit, we'll start making this frontend talk to the API.

## Hiding the Docs on Production?

But before we get there, head back to `/api` to see our beautiful, interactive,
Swagger documentation. I know some of you are probably thinking: I know, this is
great for development, but how can I disable this for production?

The answer is... you shouldn't!

Well first, let me show you how you *can* disable this on production and *then*
I'll try to convince you to keep it.

Open up `config/packages/api_platform.yaml`. API Platform is *highly* configurable.
So, reminder time: if you go to an open terminal, you can see all your current
configuration - including any default values - by running:

```terminal
php bin/console debug:config api_platform
```

You can *also* run:

```terminal
php bin/console config:dump api_platform
```

to see an example tree of *all* the possible keys with some explanations. One
of the options is called `enable_docs`. Copy this. The goal is to disable the
docs *only* in production, but let's start by disabling them everywhere to see
how it works.

Set `enable_docs` to false.

[[[ code('8e0111aa8c') ]]]

Ok, go back and refresh `/api`. Um... there is absolutely *no* change! That's due
to a small dev-only bug with a few of these options: when we change those options,
they're not causing the routing cache to rebuild automatically. No big problem,
clear that cache manually with:

```terminal
php bin/console cache:clear
```

Fly back over, refresh and... yep! The documentation is gone. Oh... but instead
of a 404, it's a 500 error!

It turns out that going to `/api` to see the docs was just a convenience. The
documentation was *really* stored at `/api/docs` and this *is* now a 404. You
*could* also go to `/api/docs.json` or `/api/docs.jsonld` before, but now it's
all gone.

One of the unfortunate things about removing the documentation is that it wasn't
*just* rendered as HTML. In part 1 of this series, we talked about JSON-LD
and Hydra, and how API Platform generates machine-readable documentation to explain
your API. If I go to `/api/cheeses.jsonld`, this advertises that we can go to
`/api/contexts/cheeses` to get more "context", more machine-readable "meaning".
Whelp, that doesn't work anymore. The point is: if you disable documentation,
realize that you're *also* disabling the machine-friendly documentation.

And if you want to fix the 500 on `/api`, you also need to disable the "entrypoint".
Right now, if we go to `/api/index.jsonld`, we get a, sort of, "homepage" for our
API, which tells us what URLs we could go to next to discover more. When we go
to `/api`, that's the HTML entrypoint and that's... totally broken. To disable
that page set `enable_entrypoint` to false. Rebuild the cache:

[[[ code('1c66fc7b23') ]]]

```terminal-silent
php bin/console cache:clear
```

then go refresh. *Now* we get a 404 on this and `/api`.

So to fully disable your docs without a 500 error, you need *both* of these keys.
To make this *only* happen in production, copy them, delete them, and, in the
`config/packages/prod` directory, create a new `api_platform.yaml` file. Inside,
start with `api_platform:` then paste.

[[[ code('aaf2f407f6') ]]]

If we changed to the `prod` environment and rebuilt the cache, we'd be done!

But instead... I'm going to comment this out... for two reasons. First, I like the
documentation, I like the machine-readable documentation and I like the "entrypoint":
the documentation homepage. And second, more importantly, if you want to hide your
documentation so that nobody will use your API, that's a bad plan. That's security
through obscurity. If your API lives out on the web, you need to assume people will
find it and you need to *properly* secure it. Hey! That's the topic of this tutorial!

Rebuild the cache one more time.

At the end of the day, if you're ok keeping the machine-readable docs and the
entrypoint stuff, but you *really* don't want to expose the pretty HTML docs,
you *could* always create an event subscriber on the `kernel.request` event -
now called the `RequestEvent` in Symfony 4.3 - and, if the URL is `/api` *and*
the request format is HTML, return a 404.

***TIP
You can see an example here: [https://bit.ly/2YmR6Uh](https://bit.ly/2YmR6Uh)
***

Ok, let's get into the *real* action: let's start figuring out how we're going to
let users of our API log in.
