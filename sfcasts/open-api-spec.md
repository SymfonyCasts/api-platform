# Open Api Spec

Coming soon...

This story is a lot more than just how to use Api platform because APEC platform that
leverages so many things about modern API APIs and modern API standards that this, it
makes this tutorial. Let's turn. Story is really all about all of the modern concepts
and exciting things happening around building an API, so we talked about this
interface here. This is called that swagger and what swagger is, it is truly just an
interface. It's just a basically an API documentation interface. If you Google for
swagger and open their site, you can actually see this on their tools. It's called,
this is called these swagger Ui.

Okay.

It's a little late, just a user interface to visualize and interact with your API or
resources. It even says without having any of the implementation logic in place. Let
me show you what I mean by that. They have a live demo here where you can see
something that looks very similar to what we've been using here. The way that swagger
works is that up here you pointed at a swagger dot JSON File. I copy that you were
on, put up my browser. You can check this out. This is a huge JSON file that
describes an API. This API might not even exist. This could just be a uh, a uh,
document that you build at the beginning of your Api. It talks about all of your
paths down here, all of the, um, description of those, the parameters on the input,
what happens on the output, uh, things related to security on there. It basically
tries to completely describe how to use your API. So if you have one of these, JSON,
if you have one of these configuration files, you can plug it into swagger in swagger
will generate itself automatically.

Yeah.

Now this configuration format is actually called open API. So swagger is the user
interface, but what it's consuming here as a format and agreed upon official SPEC
format called the open API specification to make things more confusing, that
specification itself, you still also be called swagger, but starting an open API 3.0
it's called open API. And then swagger is, it just is just the user interface for it.

Of course this is a really cool idea, but you know it's already enough work just to
build an Api, let alone build an API and to try to like build and maintain giant
document about your API, which is why we don't have to do this inside of Api Platform
and API platform. The whole thing is about creating these resources and configuring
them. We haven't done any configuration yet, but we're going to be able to decide.
We're gonna be able to customize how our resources are exposed. He kept bought from
then takes that configuration would give it to both create our API, but also to
create one of these open API specifications. Check this out. Go to /API /docs dot.
JSON, we have one of those open API documentation things. Yeah, it's been great for
us automatically and actually you notice it says swagger 2.0 up here. You can also do
question mark. SPEC_version = three. This actually gives you an open API three so
this is the latest version of the format, not that that is that important on our
documentation homepage. If you view source on this, you'll see that the data from
this end point here was already being included on the page and a little swag or data
div and our documentation was set up to automatically read that. So that's why this
page is being, is generating is generated automatically based off of our open API
specification, which Oh, a API platform is giving us for free.

You can also add the question mark Spec on a score version = three to this page.
You'll see it changes to open API SPEC three not much really changes in the API and
the uh, and the front end of this. But uh, this technically uses behind the scenes.
Now, uh, it's using the uh, latest open API information which has an old couple of
extra things in it.

Okay.

So being able to have this, having this open API specification automatically gives us
the swagger Ui automatically, which is incredible. But just to kind of get you
thinking about the importance of this swagger is actually more than just this Ui.
They also other tools or for example, the Swagger Code Generation, which is where you
can actually generate an Sdk in many different languages. Based off of that. This is
something that we're going to talk about later.

Yeah,

there was, the last thing I want to point out is in addition to the end points, the
open API specification also has information about your what are called models. Right
now we have a cheese listing, a model, and it exposes it to her information on the
bottom, since we have a cheese listening model and check the Sada already knows that
id an integer and somehow knows that this is a read only. We'll talk about why this
is read only a little bit. It also knows that a price is an integer, which is
amazing.

Okay,

and that to create that is a string, but it's a string and a date time.

Okay.

The second I'll show you where some of that information is coming from, but it's
actually being read from our code. Before we get there, we need to talk about one
other really important thing that we're already seeing in that is the JSON Ld and
hydro format.

Okay.

That is coming back and our API responses.