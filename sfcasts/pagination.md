# Pagination

Coming soon...

Something else. Every API needs is page nation. You don't normally think about that.
But if we have a million or a thousand or even a hundred cheese listings, we can't
return all of them. When you make an API request to choose listings. So aps have have
pagan nation for that and an API platform, it's just built in. So let's actually go
to my post end point here and let's create a few more cheese listings. I put in some
kind of simple data here.

Yeah.

You know, execute that multiple times. This is where on a real project, data fixtures
can definitely come in handy.

And this point, we should have about 10 or so from our normal ones before. So if we
go up to our good end point here and hit execute, we get still actually everything.
So by Default Api Platform, uh, it's Max per page is 25 and since I don't feel like,
uh, making 25 of these to show that off, let's actually learn how to modify that. So
this is something that you can control on a resource by resource basis. So in my API
resource here, I'm going to add an attributes key which has some kind of random
configuration that's inside of there. We can say page and nation items per page and
set that to 10 this is what I was talking about earlier where a lot of API platform,
it's just figuring out the right keys to put here to configure the behavior. So as
soon as you do that, I don't even need to refresh this page. I can just sit, execute.

Okay.

We get page and nation.

Okay.

You can see it says total items 11 but it's actually only showing us up to 10 and now
I have this hydrocodone view thing down here, which actually advertising the page and
nation. These are basically links that on API client that understands hydra could go,
they could actually figure out there's this hydra view thing and they go to the hydro
first hijrah last or hydro next to go to the first, last or next page. And you can
see page nations is very simple. It's just question Mark Page = one question. My page
= two and so on. So let's actually do this directly.

Okay.

So we can go to,

okay.

/Api /cheeses that JSON Ld,

you get the first 10 results. And let me do a question Mark Page = two to get the
last result, which is just that one thing. And filtering still totally works here. So
JSON, LD question mark. Title = cheese. That's going to return just 10 results. We
don't actually need page and nation here, but if we did have return 11 results, then
it would um, have page nation on that filter and the links would actually have links
to that particular thing. I don't know if I can, I can actually see this if we go up
here and let's add one more of our cheese listings. Oh, but actually let me modify
that so we have the word

we're cheese in there. I'll have that a couple more times and then up here and
refresh this. There you go. This time. Nice. We have 13 total results. It's only
showing the first 10 and the page nation actually has the filter included in this
man. This really make like powers are front end to, uh, to be awesome. And all this
stuff is configurable. The fact that this is called the page where your parameters
configure all the Max per page is configurable. So anything about this they want to
configure, you can, it just works out of the box and it's very, very friendly to your
front end.