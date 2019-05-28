# Pagination

Coming soon...

Something else. Every API needs is pagination. You don't normally think about that.
But if we have a million or a thousand or even a hundred cheese listings, we can't
return all of them. When you make an API request to cheese listings. So aps have have
pagination for that and an API platform, it's just built in. So let's actually go
to my post end point here and let's create a few more cheese listings. I put in some
kind of simple data here.

You know, execute that multiple times. This is where on a real project, data fixtures
can definitely come in handy.

And this point, we should have about 10 or so from our normal ones before. So if we
go up to our GET endpoint here and hit execute, we get still actually everything.
So by Default API Platform, uh, it's Max per page is 25 and since I don't feel like,
uh, making 25 of these to show that off, let's actually learn how to modify that. So
this is something that you can control on a resource by resource basis. So in my 
`@ApiResource` here, I'm going to add an `attributes={}` key which has some kind of random
configuration that's inside of there. We can say `"pagination_items_per_page":` and
set that to `10` this is what I was talking about earlier where a lot of API Platform,
it's just figuring out the right keys to put here to configure the behavior. So as
soon as you do that, I don't even need to refresh this page. I can just sit, execute.

We get pagination.

Okay.

You can see it says total items 11 but it's actually only showing us up to 10 and now
I have this `hydra:view` thing down here, which actually advertising the pagination
These are basically links that on API client that understands hydra could go,
they could actually figure out there's this hydra view thing and they go to the 
`hydra:first`, `hydra:last` or `hydra:next` to go to the first, last or next page. And you can
see paginations is very simple. It's just `?page=1` `?page=2` and so on. So let's 
actually do this directly.

So we can go to `/api/cheeses.jsonld`

you get the first 10 results. And let me do a `?page=2` to get the
last result, which is just that one thing. And filtering still totally works here. So
`.jsonld?title=cheese`. That's going to return just 10 results. We
don't actually need page and nation here, but if we did have return 11 results, then
it would um, have pagination on that filter and the links would actually have links
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