# API Platform Installation!

Yo friends! It's time to talk about... drum roll... how to bake a delicious cake
that looks like an Oreo. Wait... ah! Wrong tutorial. It's time to talk about
API Platform... so fun, it's *almost* as delicious as a cake shaped like an Oreo.

API Platform is *crushing* it these days. I feel like everywhere I turn, someone
is *raving* about it! Its lead developer - Kévin Dunglas - is a core contributor
of Symfony, *super* nice guy and is absolutely pushing the boundaries of what API's
can do. We're going to see that first-hand. He was also nice enough to guide us
on this tutorial!

## Modern APIs are Hard. API Platform is not

If you only need to build a few API endpoints *just* to support some JavaScript,
you might be thinking:

> What's the big deal? Returning some JSON here and there is already pretty easy!

I've had this same opinion for awhile. But little-by-little, I think this is
becoming less and less true. *Just* like how frameworks were born when web
apps became more and more complex, tools like API Platform have been created
because the same things is currently happening with APIs.

These days, API's are more than just returning JSON: it's about being able to
serialize and deserialize your models consistently, maybe into multiple formats,
like JSON or XML, but also JSON-LD or HAL JSON. Then there's hypermedia, linked
data, status codes, error formats, documentation - including API spec
documentation that can power Swagger. Then there's security, CORS, access control
and other important features like pagination, filtering, validation,
content-type negotiation, GraphQL... and... honestly, I could keep going.

*This* is why API Platform exists: to allow us to build *killer* APIs and *love*
the process! Oh, and that big list of stuff I just mentioned that an API needs?
API Platform comes with *all* of it. And it's not just for building a *huge* API.
It really is the perfect tool, even if you only need a few endpoints to power your
own JavaScript.

## API Platform Distribution

So let's do this! API Platform is an independent PHP library that's built on top
of the Symfony components. You don't *need* to use it from inside a Symfony app,
but, as you can see here, that's how they recommend using it, which is great for us.

If you follow their docs, they have their *own* API Platform *distribution*: a
custom directory structure with a *bunch* of stuff: one directory for your
Symfony-powered API, another for your JavaScript frontend, *another* for an
admin frontend *all* wired together with Docker! Woh! It can feel a bit "big" to
start with, but you get *all* of the features out-of-the-box... even more than
I just described. If that sounds awesome, you can totally use that.

But we're going to do something different: we're going to install API Platform as
a bundle into a normal, traditional Symfony app. It makes learning API Platform a
bit easier. Once you're confident, for your project, you can do it this same way
*or* jump in and use the official distribution. Like I said, it's *super* powerful.

## Project Setup

Anyways, to become the API hero that we all need, you should *totally* code along
with me by downloading the course code from this page. After you unzip it, you'll
find a `start/` directory inside with the same code that you see here... which is
*actually* just a new Symfony 4.2 skeleton project: there is *nothing* special
installed or configured yet. Follow the `README.md` file for the setup instructions.

The *last* step will be to open a terminal, move into the project and start the
Symfony server with:

```terminal
symfony serve -d
```

This uses the `symfony` executable - an awesome little dev tool that you can
get at https://symfony.com/download. This starts a web server on port 8000
that runs in the background. Which means that *we* can find our browser, head to
`localhost:8000` and see... well, basically nothing! Just the nice welcome page
you see in an empty Symfony app.

## Installing API Platform

Now that we have our empty Symfony app, how can we install API Platform? Oh,
it's so awesome. Find your terminal and run:

```terminal
composer require api
```

That's it. You'll notice that this is installing something called
`api-platform/api-pack`. If you remember from our Symfony series, a "pack" is sort
of a "fake" library that helps install several thing at once.

Heck, you can see this at `https://github.com/api-platform/api-pack`: it's a single
`composer.json` file that requires several libraries, like Doctrine, a CORS
bundle that we'll talk about later, annotations, API Platform itself and a few
parts of Symfony, like the validation system, security component and even twig,
which is used to generate some really cool documentation that we'll see in a minute.

But, there's nothing *that* interesting yet: just API Platform and some standard
Symfony packages.

Back in the terminal, it's done! And has some details on how to get started. A
few recipes also ran that gave us some config files.
Before we do *anything* else, go back to the browser and head to
`https://localhost:8000/api` to see... woh! We have API documentation! Well, we
don't even *have* any API yet... so there's nothing here. But this is going to be
a *huge*, free feature you get with API Platform: as we build our API, this page
will automatically update.

Let's see that next by creating and *exposing* our first API Resource.
