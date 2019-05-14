# Json Ld Hydra

Coming soon...

Your typical Api returns, JSON, like if we go to a /API /cheese listings /two dot.
JSON, this is traditionally what we think of as an API response, but there's a lot of
missing context from this. Like what are the significance of these fields? Like what
does that title mean? What does description mean? What does price mean? Is price in
dollars or cents and what are the types? This an integer, is this a string? If so,
what type of string? So sure. If you're a human and you can maybe find some human
readable documentation, you might be able to figure out the significance of these
fields. But there's no way for a computer to understand the significance of these
fields. Or what if there's a title field on this resource and a title field on a
different resource? Those mean the same thing? Or is one kind of a title of a book
and another one's a title of a person like Mister and misses.

This is what JSON Ld aims to solve. JSON LD stands for JSON linked data. So if I had
that, that LDM and event, we can see what we've been seeing so far, which is our
fields, but a couple of extra fields here which are at context, at ID and at type. So
the first thing, the Ad Id dead is that every single an ID is the unique identifier
to this resource, which you kind of think it might be redundant because we have a two
down here, but in JSON Ld, every thing is done with URLs or really I our eyes. And
the reason is saying them at an ID is two is cool, but saying that an ID is /API
/cheese listing /too, it's just more useful. It's unique within your full Api. It's
not. And we can actually just copy this URL and make a request to it and we're going
to get this resource.

So the kind of person that JSON LD brings us is this idea that every resource should
have an ad id that points to a real you. I R I that represents that resource. It just
kind of makes sense. And you'll see this in this tutorial. We're not, we're not going
to use ids like integer ids. We're always gonna be referencing the Iri of a resource.
The other really important thing, the next important thing is this at context cause
JSON Ld is all about giving your data context meaning, and again, this is an IRI.
This is actually pointing to another document that we can go to to get more context
about this resource. I'm literally gonna put that up in my browser /aps as context
/she's listening and we get this, this little context document which almost acts like
a Schema in the same way that we have, uh, uh, you know, an entity Schema and set of
our code. This is a Schema that describes the significance of these fields and an API
and there's actually not much here. It says that the title field is a cheese listing
/title type description as cheese listening session description and so on. And then
at points to another document with this ad vocab. That's basically what I'm saying.
Hey, if you want to find out the significance of what a cheese listing /title field
type is, then you should go to this other document to see what it means.

So we can click that to get the slot to get to /API /docs dot. JSON Ld, which is a
full description of our API in JSON l d. So check it out. Down here you actually have
supported classes. You can see our cheese listing class, which has all the different
properties under there. You can see cheese listening slash, title and you can see
that it has a label. It has, um, whether or not it's required, whether or not it's
readable, whether or not it's writeable

down for price, it already knows that this is an integer. So it's super well defined.
And if you're already thinking, wait, is it this one open API gave us, isn't this
kind of a duplication of what it gave us? Yes. But I'll talk about that in a little
bit, but just like the open API document that was generated, uh, Api platform is
getting this data from our code, which is awesome. So for example, if you look down
here on the price property, it has a title and already has some data for it. Now if
we move over and let's say above price, we want to give a little more information. We
want to say the price of this delicious cheese in that sense, which is kind of an
important thing to know. Now if you go back and refresh this page, boom. Suddenly we
have a hydrate description. I'm going to talk about what that word hydro means in
just a second.

So this already gives us this definition of classes and if we kind of go back to
spots here you can see it. Originally it said was at type cheese listing and by
following the context and following the vocab almost the same way that we follow
links inside of a browser, we can eventually find out more context about what that
actually means. Pretty cool. And also know, notice a couple of other different
classes that are down here. There's one called an entry point, the API entry point,
and another one down here called constraint violation, which is gonna, which is
describing how our validation errors, once we start talking about Api validation, how
our validation errors are going to look. Let's talk about this entry 0.1. This is
actually pretty cool, so we already know that if you go to /API you get to are kind
of document or kind of API homepage, but there's also a JSON LD version of this page.

So I'm gonna go over to my terminal Amr, use a curl command to get to this and a curl
that's x. Get someone could get requests to local host colon, a thousand /API. So
simply a request to our homepage, but I don't pass Dash Age to pass a header and
we'll say, except application /LD plus JSON. So we're going to advertise, we're going
to make requests that the /Api, we're going to advertise it. We want JSON Ld back and
I'm a pipe this to a cooler utility called Jq. If you don't have that installed, then
just skip that last part. Just formats the response a little bit and look, we get
back, this is actually the response market homepage. It's kind of a cool idea to have
an API homepage and then your client can go there and it can discover the end points.

So the same thing. It's got the same thing where it says that actually we actually
kind of pretend like this is a resource. It's a resource and it's URL, it's ideas
slash, Api, it's type his entry point. So you can go and find out what that means and
you get more context by going to this URL. But also advertise that we have a cheese
listing entry point. We have achieved listing property. So the entry point is almost
like an index and it's saying, hey, if we want to go find more information about the
cheese listing, we can go to that. Uh, you were out and this was one of the things
that actually surprised it, uh, described under the entry point class is one of its
properties is called a cheese listing. And it's how you get more information about
cheese listings. Pretty wild.

Okay.

So JSON Ld is all about adding more context of your data and it does that by adding a
number of, by by basically saying you should expect certain keys in your response.
The most important ones are context ID and type. But there are a number of other ones
that can give more context. So it's still JSON, but it's saying, look, this JSON
expected this JSON to follow these rules. And that means that if you have an API
client that understands JSON Ld, it's going to be able to get a lot of information
from your API as a machine. So the standardized format for doing this. Now when you
talk about JSON Ld inside of Api Platform, you're also going to be talking about
something called hydro. And if we kind of go back to r /API /docs dot JSON
[inaudible], you can see that even this document points to an contacts called hydro.

Well Hydro does it is ads, more special keys. So ID and type. These are things that
are just stock to JSON. Ld Hydro says, okay, we also are going to add a whole bunch
of other special keys like hydro colon supported classes. So the idea was that JSON
Ld was kind of a, the, gave you the base information for ways to add more context of
documents. Hydro said, hold on a second. To really make our uh, the information
useful. I need a way to, for example, define classes within my API and those classes
need to have properties in my API. I also need a way to define, um, uh, path. So if
you kind of scroll down to the bottom, nevermind doesn't do that.

Did you'd be able to strap on other things that are readable are writeable well
beyond the data, we also need to be able to, uh, get information about different
operations. So for example, down here under our resources, they've actually
operations like there's a good operation for retrieving a cheese listing. Um, there's
a delete method that you can make to do this. So it added additional and edit
additional s, uh, vocabulary to JSON Ld so that you can fully specify your entire API
in this JSON LD format. Now at this point, you're probably definitely thinking, hold
on a second. This is the same thing as we got with our open API doc. So for example,
you know, if we go back to um,

API /docs dot. JSON, this is the open API specification. And if we had changed that
to JSON Ld, suddenly we have the JSON LD specification with hydro that's doing the
exact same thing. So why have two? The answer is there totally is overlap. The Nice
thing is they API platform allows you to use both. The reason there's overlap is that
the JSON Ld format is actually more powerful there. There's metadata and ways to
describe relationships between resources that isn't possible or is just awkward
inside of a open Api. However, a open API is a lot more common and more powerful and
there's more tooling around it. So in some cases, having an open API specification is
going to be useful for you. In other, other times a JSON LD hydra a is going to be
useful for you. So with API Open Api platform, you can take advantage of both of
them.

Okay.

And for the most part, these are things that are just going to build behind the
scenes. You're not really gonna think about them, but all of our tooling is gonna be
based off of this wonderful description of how our API works. All right. Next, let's
actually get into configuring how API platform works and customizing it to our needs.