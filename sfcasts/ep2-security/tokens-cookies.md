# API Auth 101: Session? Cookies? Tokens?

How *do* we want our users to log into our API? There are about a million possible
answers for this. To figure out *your* answer, don't think about your API, just ask:

> What action will a user take to log into my app?

Most likely, your users will do something super traditional: they'll type an email
and password into a form and submit. It doesn't matter if they're typing that into
a boring HTML form on your site, a single page application built in Hipster.js
or even in a mobile app. In *all* those situations, the user of your API will
be *sending* an email & password. By the way, if you do *not* have this situation,
that doesn't change much! I'll talk more about why later, but most of what we'll
do will transfer to other authentication schemes.

## Sessions or Tokens?

It turns out that the *more* important question - more important than what pieces
of data your users will send to authenticate - is what *happens* when your API
receives that data. How does it respond when you successfully authenticate?

And you might be thinking:

> Hey! We're building a RESTful API... and APIs are supposed to be "stateless"...
> so that means don't use sessions... and so that means our API will return some
> sort of an API token.

Yep! That's not... super true. Session-based authentication - the type of login
system you've known and loved for *years* - is just a token-based system in disguise!
When you perform a traditional login, the server sends back a cookie. This is your
"token". Then, every future request sends that token and becomes authenticated.

Here's what's *really* important. If the "user" of your API is you or your company -
whether that be *your* JavaScript or a mobile app owned by you, then, on
authentication, your API should return an HttpOnly cookie. This type of cookie
is automatically sent with each request but is *not* readable in JavaScript,
which makes it safe from being stolen by other JavaScript. The *contents* of
that cookie, it turns out, are much less important. It could be a session string -
like we're used to in PHP, or it could be some encrypted package of information
that contains authentication details. If you've heard about JSON web tokens - JWT -
that's what those are: strings that actually contain information. In *all* cases,
your API will set an HttpOnly cookie, each future request will naturally send that
back, and your API will *use* that to authenticate the user. Exactly what that
cookie looks like is not really that important.

The great thing about *session-based* authentication with cookies - versus generating
a JWT and storing it in a cookie - is that it's super easy to set up. And all HTTP
clients - even mobile apps - support cookies.

Listen: later on, we *are* going to dive into some details about token-based
authentication systems - the ones where you attach some token string to an
Authorization header when you make the request. And we'll talk about when you
need this. For example, if *third* parties - like someone *else's* mobile app - need
to make requests to your API and be authenticated as users in your system, you
would need OAuth.

So here's our first goal: build a super-nice, API-friendly, session-based
authentication system where we POST the email and password as a JSON string to
an endpoint. Then, instead of returning an API token, that endpoint will start
the session and send back the session cookie.
