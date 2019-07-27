# Login with json_login

How *do* we want our users to log into our API? There are about a million possible
answers for this. To figure out the answer, don't think about your API, just ask:

> What action will users take to log into my app?

Most likely, your users will do something super traditional: they'll type an email
and password into a form and submit. And it doesn't matter if they're typing that
into a traditional HTML form on your site, a single page application on your site
or even via a mobile app. In *all* those situations, the user of your API will
be *sending* an email & password. By the way, if you do *not* have this situation,
that doesn't change much! I'll talk more about why later, but most of what we'll
do will transfer 100% to another authentication scheme.

## Sessions or Tokens?

It turns out that the *more* important question - more important than what pieces
of data your users will send to authenticate - is what *happens* when your API
receives that data? How does it respond when your successfully authenticate?

And you might be thinking:

> Hey! We're building an RESTful API... and APIs are supposed to be "stateless",
> so that means don't use sessions... and so that means our API will return some
> sort of API token.

Yep! That's not... super true. Session-based authentication - the type of login
system you've known for *years* is just a token-based system in disguise! When
you perform a traditional login, the server sends back a cookie. This is your
"token". Then, every future request sends that token and becomes authenticated.

The *great* thing about session-based authentication is that it's been around
*forever*: it's super secure and time-tested. If you're building an API for your
own JavaScript to consume, you should probably use session-based authentication.
If you're building your API for a mobile app to use, well, mobile apps *also*
support session-based authentication.

Listener: later on, we *are* going to dive into some details about how to create
a token-based authentication system. But there's a good chance you don't need it.
Using sessions will not only be *easier*, it'll probably be more secure.

So here's our goal: build a super-nice, API-friendly, session-based authentication
system where we POST the email and password as a JSON string to an endpoint. Then,
instead of returning an API token, that endpoint will start the session and send
back the session cookie.

## Setting up json_login

If your login system looks similar to the traditional email & password or username
& password setup, Symfony has a nice, built-in authentication mechanism to help.
In `config/packages/security.yaml`, under the `main` firewall, add a new key:
`json_login`. Below that, set `check_path` to `app_login`.

This is the name of a route that we're going to create in a second - and we'll
set it up to be `/login`. Below this, set `username_path` to `email` - because
that's how we're going to log in, and `password_path` set to `password`.

With this setup, when we send a `POST` request to `/login`, the `json_login`
feature will automatically start running, look for JSON in the request, decode
it, and use the `email` and `password` keys inside to log us in.

How does it know to load the user from the database... and which field to use
for that query? The answer is: the `providers` section. This was added in the
last tutorial for us by the `make:user` command. This tells the security system
that our `User` lives in Doctrine and it should query for the user via the `email`
field. If you have a more complex query... or you need to load users from somewhere
totally different, you'll need to create a custom user provider or an entirely
custom Guard authentication system, instead of using `json_login`. Basically,
`json_login` works great if you fit into this system. If not, you can throw it in
the trash and create your own authenticator. More on that later.

So, there *may* be some differences between your setup and what we have here.
But the *really* important part - what we're going to do on authentication success
and failure - will probably be the same.

## The SecurityController

To get the `json_login` system fully working, we need to create that `app_login`
route. In `src/Controller` create a new PHP class called, how about,
`SecurityController`. Make it, extend the normal `AbstractController` and then
create a `public function login()`. And above that, I'll put the `@Route` annotation
and hit Tab to auto-complete that and add the `use` statement. Set the URL to
`/login`, then `name="app_login"` and also `methods={"POST"}`: nobody needs to
make a GET request to this.

Initially, you need to have this route here because *just* because that's the way
Symfony works: you can POST to `/login` and have the `json_login` authenticator
do its magic unless you *at least* have a route. If you don't have a route, the
request will 404 before `json_login` even gets started.

But also, by default, once we log in successfully, `json_login` does... nothing!
I mean, it will authenticate us, but then it will allow the request to continue
and hit our controller. So the easiest way to return something on authentication
success is to return something from this controller!

Hmm... I don't really know what we should return yet - I haven't thought about
what's useful. For now, let's return `$this->json()` with an array, and a `user`
key set to either the authenticated user's id or null.

## AJAX Login in Vue.js

Ok, let's try this! When we go to `https://localhost:8000`, we see a small frontend
built with Vue.js. Don't worry, you don't need to know Vue.js - I just wanted to
use something a bit more realistic. This login form comes from
`assets/js/components/LoginForm.vue`.

It's mostly HTML: the only real functionality I've created is that, when we submit
the form, it won't *actually* submit: Vue will call the `handleSubmit()` function.
Inside, uncomment that big axios block. Axios is a really nice utility for making
AJAX requests. This will make a PIST requerst to `/login` and send up two fields
of data `email` and `password`. `this.email` and `this.password` will be whatever
is typed into those boxes.

One important detail about axios is that will automatically encode these two fields
as JSON. A lot of AJAX libraries do *not* do this... and it'll make a *big* difference.
I'll show you exactly how later.

Anyways, on success, I'm logging the data from the response and on error - that's
`.catch()` I'm doing the same thing.

Since we haven't even tried to add any real users to the database yet... let's see
what failure feels like! Login as `quesolover@example.com`, any password and...
huh... nothing happens?

Hmm, if you get this, first check that Webpack Encore is running in the background:
otherwise you might still be running the old, commented-out JavaScript. Mine is
running. I'll do a force refresh - my browser might be messing with me. Let's try
that again: `queso_lover@example.com`, password `foo` and... yes! We got a 401
status code and it logged `error` `Invalid credentials.`. If you look at the
response itself... on failure, the `json_login` system gives us this simple, but
perfectly useful API response.

Next, let's hook up our frontend to use this and learn how `json_login` behaves
when we accidentally send it a, let's say, less-well-formed login request.
