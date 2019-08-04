# Samesite Csrf

Coming soon...

Before we go further to API platform itself. I wanted to do a very quick, important
aside here about CSRF tokens and CSRF attacks. Long story short, because this is a
very complicated topic, if you use session based authentication, it doesn't matter if
you're using an API or building a traditional web app, you are vulnerable to CSRF
attacks at CSRF attacks, which basically is an attack where,

the simplest example is someone submits someone creates a form on another, on another
site, but they make it submit back to your site. So if someone happens to be logged
into your site and they go fill out that form thinking they're filling it out on that
other site, it actually submits to your site and there since they are logged into
your site, the session cookie is sent to your site and they have now been tricked
into taking some action on your behalf, on, on someone else's behalf.

This is a real risk because it means that if you're, if your API is not protected,
bad users could make bad people, could make your users do things on your site, could
take control of users on your site and make them do anything that they wanted.
This is why traditionally in a form with Symfonys Form component, we have CSRF
tokens. And if you Google for douglas csrf after actually is a bundle out there called
`DouglasAngularCsrfBundle`, which is something that helps you protect against CSRF
attacks. Run an API. It says angular, but it's just a generic bundle. But
unfortunately what this requires you to do is actually requires you to manage CSRF
tokens on your JavaScript and like send a CSRF token, um,
with every single law endpoint that you do.

but it is the safest thing to do if you want CSRF protection is to do this. However,
recently browsers have come and finally saved the day and allowed us to build a
session based application without CSRF tokens that is a safe from CSRF attacks. The
technology behind it is something called a `samesite cookie`.

Which you can read about on the web. It's actually a kind of taking over right now.
Basically the problem with the reason that CSRF was a problem is that when another
site,

when you submit that form on the other site, the request goes back to our site in the
session cookie is sent with that request. That really shouldn't happen. That's
actually a bit of a security risk. We should be able to say that only requests that
originated from my domain should actually send the session cookie. If an a request
originates somehow from another domain, like they've submitted a form over to our
sites, then um, then a it wouldn't, uh, then it wouldn't be sent and this basically
more or less kills CSRF attacks as the caveat being that we need to make sure that
our get our safe methods like `GET`, `HEAD` don't have side effects because someone could
still, um, because somebody could still have a goes if you click a link on someone
else's site to make a hit request, that would still send the cookie on over, over on
our side. So all you need to do to use this is two things. First, if you open up a
`config/packages/framework.yaml`. If you started a new Symfony project, like we just
did, you already have this, it's this `framework.session.cookie_samesite: lax`, you
agree more about the lax versus strict but lax is probably the one you want because
the strict setting means that if somebody even clicks a link, an external link over
to your site, they won't be logged in on the first request.

The only Gotcha on this one is that not all browsers support, not all browsers
support `samesite` cookies. And if a browser doesn't support a same site cookie, it
means that they're going to treat it like a normal cookie. So you need to look long
and hard at, um, what, uh, so the only true way to, to, uh, prevent CSRF tokens with
the samesite cookie is actually to block of to your site from any browsers that don't
support it. Now you can see the browsers that don't support it or opera mini black,
blackberry browser, um, I 11 does support it, but some like very earlier versions of
it don't. So it really is getting down to just like the last few ser servers. So the
bulletproof thing here is to use samesite cookies. And if you really need to prevent
CSRF token attacks, even for the couple of users you have that are using older
browsers, you need to prevent them from coming to your site. This is the future of,
um, of CSRF tax in the samesite cookies. And it is a wonderful way to, uh, to do
things. If for some reason you need to allow those, uh, old, um, browsers access,

then you're going to need to use true CSRF tokens or you're not going to be able to
use session based authentication. You're going to need to use a token based
authentication, which as we'll talk about later for JavaScript, has its own attack
vectors. If you have more questions about this, ask me. It's really, we're really in
a spot right now where the samesite cookies and disabling, uh, older browsers is the
best path forward. Um, but it's very nuanced in their pros and cons to all the
different approaches from a security standpoint.