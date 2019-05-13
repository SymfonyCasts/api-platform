# Api Resource

Coming soon...

The whole reason I want to use API Platform cause I have an awesome idea for a
site. Have you ever had a situation where you bought too much cheese?

Okay.

And you need somewhere to sell it. Or maybe you're hosting a party and you need to
buy a bunch of cheese. That's our billion dollar idea. We're calling it cheese whiz.
A marketplace where people can post cheese and all their viewer can contact them and
buy it.

We're focusing on, we want to build an API for this because we want to have a uh, a
really rich frontend. Maybe it's going to be a single page APP, front end, or maybe
it's going to be a mixture of some html on the front end. And then some parts of our
front end that, um, are in JavaScript. But one way or another, we know that we need
to have an API. And so that's what we're developing first. It's actually not going to
be a front end. They're all in this, we're all going to focus on building our API.

Okay,

so actually to start, pretend like we're not building there. Pretend like this is
just a normal boring Symfony application. Don't think about API Platform or anything
like that. Right now we have an empty application and probably the first thing we're
going to want to do is start building some um, uh, adding some database entities. So
I'm gonna open up my `.env` file here and down on her score under database URL. I'm
going to change this to be `root` with no password and we'll change the database to
`cheese_whiz`. Um, you can also create a `.env.local` file and override this
there. This is pretty standard configs so I'm cool with committing this into my
project.

Yeah.

Next to build an entity, I'm going to move her to my terminal and say 

```terminal
composer require maker --dev
```

to get Symfonys maker bundles so that we can be lazy and
generate our entity. Now the first entity that we need is called the `CheeseListing`.
This is going to be the be individual items on the site that people are selling.
Sluts, run 

```terminal
php bin/console make:entity
```

and we'll call it `CheeseListing`. The first thing
you'll notice because API platform was installed, you actually get past an extra
question mark this class as an API Platform resource, which means do we want to
expose an API for it? We're actually going to say `yes`. I'll show you exactly what
that did and even before I've added any fields here, I can move over and I'll show
you what that did. So,

okay,

of course created our `CheeseListing` and our `CheeseListingRepository`. Nothing
special there. We don't have any fields except for id. The thing that first question
did is it added this annotation right there. That's it. And we'll see what that does
in just a second. Well, it's finishing attic. Let's finish adding some fields. Let's
add a `title`, which will be `string`, `255` not nullable `description`. I'll make that a
`text` field.

Okay.

`price`, which is actually going to be an `integer`. This will be the price incense. So
$10 would be 1000 okay. And then how about any nays `createdAt`.

Okay.

And also and `isPublished`. All right, finally enter at the end to exit that. Perfect.
So now I have a `CheeseListing` and it's very boring. It just has all of those
properties in it.

Okay,

next, because we've just made them a entity that's run 

```terminal
php bin/console make:migration
```

and, oh, except for this fails because we don't have migrations and installed. Yeah,
that's not one of the things that we get out of the box from API platform. At least
right now. No problem. 

```terminal
composer require migrations
```

that will get us the doctrine migrations bundle.

Okay,

now we can run, `make:migration`

```terminal-silent
php bin/console make:migration
```

. Oh, of course I have an extra credit my
database yet. Oh well before run, `make:migration` again. Don't. It's actually makes
sure we create our database 

```terminal
php bin/console doctrine:database:create
```

We go, now we can run. `make:migration`

```terminal-silent
php bin/console make:migration
```

Okay,

and it should we look in our `Migrations/` directory?

Yep. `CREATE TABLE cheese_listing...` all the basic stuff. Close that and then run 

```terminal
php bin/console doctrine:migrations:migrate
```

Brilliant. Now here's the kicker.
This is the same thing as a boring normal entity except for this `@ApiResource()`
annotation. And that changes everything that tells API Platform that we want this
entity, this model class to be exposed as an API. And you can see this immediately
when you refresh the `/api` page. Whoa. Suddenly this big page of documentation changes
and it's telling us that we have now five new endpoints, a `GET` endpoint to retrieve
all of the `CheeseListing`, a `POST` end point to create one a `GET` endpoint to retrieve
a `CheeseListing`, a `DELETE` end point for deleting a single one. And they `PUT` an end
point for updating and `CheeseListing`. And these are not just documentation. This
stuff actually works.

So first of all what you're seeing here is something called swagger. Swagger is a,
um, just a documentation interface. So if you build an API and you can actually
configure swagger it to be able to communicate with your, with your API. And if you
do that, then you get this really nice interface that describes your API. And in
API Platform, we get this for free. It preconfigure Swagger for us and it's actually
something that you can interact with. So let's open the `POST` one here and it's
telling you information about how this is going to be used, but we can actually try
it. So here we can play with it.

What's the information in here? I'm just making up some some data and then we can hit
execute. Ejects out down here you can see it came back to the to actually try and hit
our API right there. You can see it's, it kind of tells you what it would do and
occur. Request here, I made a curl request, made a `POST` request to `/cheese_listings`
and kind of sent this JSON data up there. So we send normal JSON Down here you can
see the r server responded with a `201` status code and looking at sat back here,
it's JSON's sort of, but it looks a little weird dude. He's `@context` at `@id` `@type`
of things. But in there there is the data and they're like yeah we, this just hit a
real API end point so I can close this now and we can go up to the `GET` one that gets
all of them. Try this out and what? Execute and check up again. It looks kind of
weird here. This is something called JSON-LD, which is awesome and we're going to
talk about, but it's sent back a single `CheeseListing` right there. So let's actually
create a second one just so it's a little bit more interesting

and we will execute that one. Same thing, we get the `201` status code down
there. Now if we execute our `GET` for our collection, Yup. Awesome. We've got
multiple.

Okay.

And you can see what their ideas are. Ideas to an and ideas one. So it can use that
to actually go play with the end point. That gives you a single cheese listing. So
here I can hit try it out. Actually gives me the box for the ID. So let's go and get
our ID to hit execute. And you can see it's making it very simple. Get request to
`/api/cheese_listings/2` we get back at `200` status code and we get back our JSON.
Yeah, we already have fully functional Api.

Okay.

Now interesting thing is we should right? Just be able to copy this URL and put it on
our browser and see this JSON, right? So if we do that, it doesn't quite work. It
actually takes us right back since this html and just scrolls us down to this end
point. So do we really, you have an API or what's going on here? The answer is that
built into Api platform is something called content type negotiation. You'll notice
down here on the curl requests, it says `-H "accept: application/ld+json"`, when
it makes the request, it's sending its setting and accept header. This is telling Api
platform what format we want to support. So because we sent this in the request, it
knows to send back this JSON-LD version of your code. When we're in a browser and we
just put it up here, this tells a platform that we want an HTML page because that's
what browsers do. And so it actually gives us back the HTML documentation of this
endpoint.

Okay,

I'm sending these accept headers and a browser is kind of impossible unless you have
a, an extension or something. So one of the ways, one of the things you can do is
actually you can cheat and this is done purely for kind of debub and convenience, but
you can actually do `.jsonld` on the end of it and boom. Now we are actually
hitting our endpoint and actually you can also do just `.json`, that we'll
probably look a little bit more familiar. So again, it's our API Platform wants us to
think about us having this resource, this and it's exposing it in different formats.
Right now it's exposing it in JSON and it's also exposing it and as JSON-LD format
that we'll talk about in a second and later, we'll see that very easily. We could
expose this as XML or a a number of different formats. Now just to demystify some of
the magic here, it's pretty abnormal. It's not normal that we can just add an
annotation to an entity in suddenly get a whole bunch of new pages in our APP. So if
you move over to your terminal run 

```terminal
php bin/console debug:router
```

you'll actually
see that API platform is bringing in a number of entry points, a number of routes. So
there's one called `api_entrypoint`, which is um, how we kind of get to our API and
we'll call the `/api/docs`. Okay.

Which if you're doing, when you're using a browser, those two routes to the same
thing. They're basically what do you the documentation, uh, something called 
`api/context`. We'll talk about that soon. And then down here you can see here are five
routes for the five new endpoints.

Okay?

As we had them add more resources later, we're going to get more of those. The way
this is working is in `config/routes/`. When we installed a platform, you brought in an
API Platform, routes file. This isn't that interesting, but you'll notice this 
`type: api_platform`, this is basically saying, hey, I want to, I want API Platform
to be able to dynamically add whatever routes at once. And what it does, it should
put them under the `/api` prefix, which is why everything is `/api`. If you want to all
of your endpoints to live at the root of your domain, you would just change that to
just prefix `/` anyways. There's so much more going on than meets the eye. Alrighty, so
next I want to talk about this `JSON-LD` format and actually something called Open Api
Spec, which is how swagger is generating this. So instantly.