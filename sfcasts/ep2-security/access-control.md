# Access Control

Coming soon...

When it comes to security, there are really two big pieces. The first piece is how
are you going to authenticate, how are your users going to log in? And that's what
we've been talking about so far and that is really the trickiest part and it has
nothing to do with API platform. It's just a complicated topic, um, in general and
something that a Symfony solves for you. In our case, we've decided to log in via
Ajax using the JSON Login authenticator and then we're relying on our server to send
back a cookie so that we're automatically logged in. That is a great solution for our
type of application, but I'm already getting so many questions about different types
of applications. Like if you have microservices that talk to each other over an API
or you have multiple API APIs with a JavaScript front end or that later in this
tutorial, or maybe even actually an extra to a whole extra tutorial access.

We're going to talk a lot more about authentication. So if I haven't talked about
your authentication use case yet, don't worry about it. We're going to cover it
later. The important thing right now is that regardless of how you authenticate, once
you authenticate, regardless of how you authenticate, then you get to step two of
security, which is off authorization. Authorization is all about denying users access
to some resource and you do that independently of how you log of how you login. So
even if I haven't talked about your authentication use case yet, everything else is
going to be relevant. Talking about authorization, the great thing about API platform
is once you've handled that is that authorization is really just done by Symfony
security system. For example, once you're logged in, the easiest way if you wanted to
protect part of your API is to use access control. We could, for example, say that a

all the end point under `/api/cheeses` require `ROLE_ADMIN`. We can do that with access
control, just like normal Symfony, but most of the time in normal Symfony, most of
the time, instead of access control, I usually protect my resources on a controller
level. And of course for the API platform, we are not the ones building the majority
of our controllers. Uh, it's happening. Uh, API platforms hammering that
automatically. So an API platform, what you think of is um, really the way you think
about it is protecting your API on an operation by operation basis. So for example,
you might make this operation require you to be logged in and this other operation,
not the `POST` operation being logged in but to `GET` operation maybe to be anonymous. So
check this out. That's what we're going to start

with here

with the cheeses, the `GET` endpoint for the collection I want to route, I want to
keep that public so that anyone can fetch all the cheese listings in the system. But in
order to create a cheese listing me `POST` operation, you need to be authenticated. We
need to know who is actually creating that. So over in source, open up 
`src/Entity/CheeseListing.php` and you say you've already listened our item item
operations here we have a get to item operation at Pope and put item operation. We've
temporarily remove the delete item operation so you can't delete cheese listings down
below. I'm going to repeat this by saying `collectionOperations`. And right now to
start, I'm just going to say `get` and `post` what that's doing is just repeating the
operations that we already have. So this will make no changes on the frontend.

Oh, except I will get a syntax error. Forgot my comma. This will make no change to my
API because we already have the get and pillows, post collection operations. So
repeating them here has no effect, but now I can start customizing them. So for the
`POST` collection operation, that's the operation that actually creates a new cheese
listing. We want the user to be logged in so we can actually say `"post"={}`
 And here we can put a number of different options just like we did up
here, um, for the good item operation. But instead of normalization context, this
time we're going to say `access_control` and set that to in quotes, a little expression
`is_granted()`. And then with single quotes, `ROLE_USER`. Cool. So let's try that right
now. If you look down here, I am actually not logged in. So if I tried to make a
post, so if I tried to make a `POST` here, what else said, owner `/api/user/6` with
my new user, Bryce, 10 bucks, title food, doesn't matter. We're good on here. Hit
execute and perfect `401` error 

> Full authentication is required to access this resource.
 
that'd be logged in. That would, that would work.

So let's tie things up a little bit more here. Um, if you look at our item
operations, our put item operation is interesting because that should, that's
something that's that w that's something where you should also need to be logged in
in order to edit a cheese listing. So up here on my item, I'm going to repeat,
actually copied this and I'm going to say put requires you to be logged in. And
actually now I'm going to put the delete operation back and we'll say `delete`. But
let's say that you can only delete if you are an admin user. So `ROLE_ADMIN`.

Oh, this put endpoint. This, put security isn't quite correct because what I really
want to do is make a cheese listing editable only if you are the owner of this cheese
listings. You can't update other peoples, but we'll worry about that in a second. So
for your refresh now, perfect. The delete end points back and we try to put end
point, for example, while just update, she's listing one. I think that's a valid
cheese listing. Only include the title and perfect for one. Now, one little Gotcha. I
do want you to be aware about at this point. No, I'm not going to talk about that.
All right, let's just stop.`