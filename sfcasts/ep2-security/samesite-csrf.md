# SameSite Cookies & CSRF Attacks

Before we go further into API platform, we need to have a quick heart-to-heart about
CSRF attacks. This is a complex topic... so I'll try to hit the highlights.
If you're consuming your API from JavaScript, you have two basic options for
authentication. First, you can use HttpOnly cookies, which is how sessions work.
Or second, you could return some sort of "access token" on login and store that
in JavaScript. Generally speaking, storing access tokens in JavaScript is
a dangerous practice because it can be stolen if some bad JavaScript somehow runs
on your page. If the access token have a short lifetime, that helps... but then
they're less useful... because your user will need to constantly log in. Sheesh,
this stuff is complex!

Anyways, *that* is the principle reason why I recommend using HttpOnly cookie-based
authentication - like a session - for your JavaScript frontend. And, like I mentioned
earlier, if you need this for your JavaScript front-end, it can also be used in
other situations, like for authenticating a mobile app that you're building.

But... using an HttpOnly cookie - whether that's a session cookie or something clever
like a JWT - is vulnerable to its *own* type of attack: a CSRF attack. Boo! The
simplest example of a CSRF attack is this: an attacker puts an HTML form on *their*
site but makes the `action` attribute submit to *our* site. Then, when someone
who is logged into our site visits the bad site, the attacker tricks that user
into filling out this form - making it look like they're ordering free ice cream,
when in fact that endpoint on our site sends the *attacker* ice cream instead.
The user has been tricked into taking some authenticated action on our site
that they didn't intend.

## Avoiding CSRF Attacks

This problem has traditionally been solved with a CSRF token - an extra field that
must be sent on that form submit that proves that the request originated from the
*real* site - not from somewhere else. Symfony's form component adds CSRF tokens
automatically.

And if you Google for "dunglas csrf", you'll find a bundle called
`DunglasAngularCsrfBundle` which helps generate and use CSRF tokens in your
API. Yea, the name says "Angular", but it works with anything.

The *downside* is that using CSRF tokens in an API is... annoying: you need
to manage CSRF tokens and send that field manually from your JavaScript on *every*
request. If you're using cookie-based authentication and need to 100% prevent
a CSRF attack for an endpoint, this is the time-tested way to do that.

## Hello SameSite Cookies

But... there is a *new* way to prevent CSRF attacks that is emerging... a solution
that is implemented inside browsers themselves. It's called a "SameSite" cookie...
which you can read *all* about on the Internet.

The basic reason that CSRF attacks are possible is that when a user submits the form
that lives on the "bad" site, any cookies that *our* domain set are sent with
that request to our app... even though the request isn't "originating" from
our domain. For most cookies that... should probably not happen. Instead, we
should be able to say:

> Hey browsers! See this session cookie that my Symfony app is setting? I want
> you to *only* send that back to my app if the request *originates* from
> my domain.

That *is* now possible by setting a special "attribute" when you add a cookie
called "SameSite".

So... because Symfony is responsible for creating the session cookie... how can
we tell it to use this cool SameSite attribute? It already is. Open up
`config/packages/framework.yaml`. If you've started a Symfony project any time
recently - like we did for this tutorial - then you probably already have
this key: `framework.session.cookie_samesite` set to `lax`. Yep, our session
cookie is *already* setting `SameSite` to `lax`.

[[[ code('557789855c') ]]]

What does `lax` mean? Well, the other possible setting is `strict`. If `SameSite`
is set to `strict`, then the cookie will *never* be sent when a third-party
initiates a request to our site... even if they literally click a link to visit
our site... meaning... they wouldn't be logged in when they arrive. The `lax`
setting is different because the cookie *will* be sent for "top-level" GET requests...
meaning GET requests where the address bar changes. That "more or less" means...
when the user clicks a link to your site - though there are a few other, less
visible ways that another site could covertly make a GET request to your site
and have the cookie sent.

## Caveats with SameSite

Anyways, `lax` is probably what you need and it *should* protect your API from CSRF
attacks... as long as you're aware of two things. First, you need to make sure
that your GET endpoints never *do* anything - they should just return data. If
you, for example, allowed users to send ice cream via a GET request, then that
endpoint is vulnerable to CSRF attacks. It's ok to *return* information on a GET
request because, while third parties can *initiate* GET requests, they can't
read the returned data, unless you go out of your way to make this possible.

And second... most... but not *all* browsers support SameSite cookies. If a user
visits your site in a browser that does *not* support `SameSite` cookies, it will
treat it like a normal cookie. That means your site will work fine, but *that*
user *would* be vulnerable to a CSRF attack.

If that's a problem, you'll either need to implement CSRF tokens *or* block old
browsers from using your site... since they're basically using a vulnerable browser.
As you can see from [caniuse](https://caniuse.com/#feat=same-site-cookie-attribute),
the browsers that don't support it include Opera Mini, Blackberry browser and a
few other minor ones. IE 11 *does* support it on Windows 10.

I know... it's crazy! If you use API tokens in JavaScript, they can be stolen
by other JavaScript. If you use the safer HttpOnly cookies, then you need to worry
about CSRF protection... unless you use SameSite cookies... which protects *almost*
every browser... or you could use CSRF tokens to be safest... but it complicates
your life.

Oh... the world of API authentication. The typical recommendation is, regardless
of what you choose to do, be aware of what you're protected against and what
you're not.

*Now* it's time to turn to *authorization*, which answers questions like: how can
we lock down certain resources or operations? How can we hide fields from some
users or allow only some types of users to update specific fields? Ah...
we're going to dream up *every* way of customizing access to our API.
