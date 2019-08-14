# Logout & Passing API Data to JS on Page Load

Hey! We can log in and our JavaScript even *knows* when we log in, and who we are:
it prints our username & a log out link that goes to `/logout`... which doesn't
actually work yet... cause we haven't enabled that in Symfony.

## Adding Logout

Wait... but what does "logging out" even *mean* in an API context? Whelp, like
everything, it depends on *how* you authenticate. Because we're using a session
cookie, logging out basically means removing the user information from the session.
If you were using some sort of API token, it would mean *invalidating* that token
on your authentication server - like, removing it from some database table for
tokens... again, it depends on your setup. We'll talk more about that type of
authentication a bit later - on a special security part 2 of this tutorial.

Anyways, no surprise that Symfony has built-in support for logging the user out
of the session. In `config/packages/security.yaml`, under our firewall add `logout:`
then, below, say `path: app_logout`. Just like `app_login`, this is the name of
a route that we're going to create next. When a user accesses this route, they'll
be logged out.

[[[ code('1d9033f0ff') ]]]

To create that, open `src/Controller/SecurityController.php` and add
`public function logout()` with `@Route()` above. Set the URL to `/logout`
and name it `app_logout`.

Just like with the `app_login` route, the route just... *needs* to exist... otherwise
the user will see a 404 when they go to `/logout`. As long as it *does* exist,
when the user goes to `/logout`, the logout mechanism will intercept the request,
remove the user from the session, then redirect them to the homepage... which
is configurable.

This means that, unless we've messed something up, the controller will *never*
be reached. Let's *scream* in case it somehow *is* executed: Throw an exception
with:

> should not be reached

[[[ code('4453014d80') ]]]

Let's try the flow: move over, hit log out and... before it loads, you *can* see
that we're currently logged in. And now... gone! We are anonymous.

## Passing Data to JavaScript on Page Load

Before we keep going with all this API & security goodness, our app has a bug.
If we log in... as soon as the AJAX call finishes, we've made our Vue.js frontend
smart enough to update and say that we're logged in. But when we refresh, that's
gone! Gasp! Our web debug toolbar knows we're logged in... but our JavaScript
does not. And... it makes sense: when we first load the page... if you look at
the HTML source - you can ignore all the web debug toolbar stuff down here... the
entire application looks like this. There's no HTML or JavaScript data that
hints to Vue that we're authenticated.

How can we fix that? There are basically two options. First, as soon as the page
loads, we could make an AJAX request to some endpoint and say:

> Hey! Who am I logged in as? Cause... I forgot?

To make that happen, we would need some sort of a `/me` endpoint: something that
would return information about *who* we are. A useful, but not-so-RESTful endpoint.

The *other* option is quite nice: send the user data from your HTML *into* Vue on
page load.

Open up the template for this page: `templates/frontend/homepage.html.twig`. Yep,
nothing here but some small HTML to bootstrap the Vue app... though I *am* doing
one interesting thing: I'm passing a prop to Vue called `entrypoint`. I'm not using
this anywhere... but it's a cool example: `entrypoint` is the URL to our
documentation "homepage". In theory, we could use that dynamic URL in our Vue app
to figure out what *other* URLs we could call... we would use our API like a
browser: surfing through links. Anyways, this shows a nice way to pass simple
data into Vue.

And.. we *could* pass the current user's IRI as another prop. Inside Vue, we
would then make an AJAX call to *that* URL to get the user data... so, no need for
the `/me` endpoint. That's really the *simplest* option, though it does have a
minor downside: there will be a *slight* delay on page load before our app knows
who's logged in.

## Serializing Data Directly to JavaScript

To avoid that AJAX call, we can dump that data *directly* into Vue. Check it out:
start in `src/Controller/FrontendController.php`. This is the controller behind
the `homepage.html.twig` template.. and it's not *super* impressive. Add a
`SerializerInterface $serializer` argument... and then pass a new variable to
the template called `user`. I want this to be the JSON-LD version of our user - the
*exact* same thing we would get from making an AJAX request. Set this to
`$serializer->serialize()` passing it `$this->getUser()` and `jsonld` for the
format. If the user is *not* logged in, the new `user` variable will be null...
but if they *are* logged in, we'll get our big, nice JSON-LD structure.

[[[ code('744966023f') ]]]

Now that we have this, create a `script` tag and set the data on a global variable...
how about: `window.user = {{ user|raw }}`.

[[[ code('c58901280f') ]]]

Hey! Our user data is accessible to JavaScript!

Head over to `CheeseWhizApp` to use it. Generally speaking, I try not to use
or reference global variables from my JavaScript. As a compromise, I like to
*only* reference global variables from my *top* level component. If a deeper
component needs it, I'll pass it down as a prop.

Create a new `mounted()` function - Vue will automatically call this after the component is "mounted"
to the page - and if `window.user`, so, if it's *not* null, then `this.user = window.user`.

[[[ code('048a424728') ]]]

It's that simple! Sneak over and refresh your browser. And... our JavaScript
*instantly* knows we're logged in. If we log out... yep! Our app doesn't explode.
Woo! Yea... this is kinda fun!

## More Complex Authentication

Ok! Authentication... including logging out and the frontend is done! If you're
feeling great about this approach for you app, awesome! But if you're screaming:

> Ryan! My app is more complex... I have multiple APIs that talk to each other...
> or... my API will be exposed publicly... or I have some other reason that
> prevents me from this simple session-based authentication!

Then... don't worry. I've already gotten *so* many questions & feedback from the
start of this tutorial that we're planning to create a separate, part 2 of
this security tutorial where we'll talk about other common API use-cases and
the right way to authenticate within those. We'll also talk about OAuth.

But in general, if you need a more custom authentication system - perhaps you
can't use `json_login` because your login is more complex than just handling an
email & password... or you're already planning some sort of token-based
authentication - you can build that system by creating a custom Guard authenticator,
which is something we talk about in our security tutorial.

Next, before we dive deep into denying and granting access to our API for different
users, we need to talk about CSRF attacks and SameSite cookies. It turns out, if
you use cookie-based authentication like we are, you may be vulnerable to
CSRF attacks. Fortunately, there's a new, beautiful way to mitigate that.
