# Login with json_login

If your login system looks similar to the traditional email & password or username
& password setup, Symfony has a nice, built-in authentication mechanism to help.
In `config/packages/security.yaml`, under the `main` firewall, add a new key:
`json_login`. Below that, set `check_path` to `app_login`.

[[[ code('94b833a560') ]]]

This is the name of a route that we're going to create in a second - and we'll
set its URL to `/login`. Below this, set `username_path` to `email` - because
that's what we'll use to log in, and `password_path` set to `password`.

[[[ code('d7ea2326a7') ]]]

With this setup, when we send a `POST` request to `/login`, the `json_login`
authenticator will automatically start running, look for JSON in the request,
decode it, and use the `email` and `password` keys inside to log us in.

How does it know to load the user from the database... and which field to use
for that query? The answer is: the `providers` section. This was added in the
last tutorial *for* us by the `make:user` command. It tells the security system
that our `User` lives in Doctrine and it should query for the user via the `email`
property. If you have a more complex query... or you need to load users from somewhere
totally different, you'll need to create a custom user provider or an entirely
custom Guard authenticator, instead of using `json_login`. Basically, `json_login`
works great if you fit into this system. If not, you can throw it in the trash
and create your own authenticator.

So, there *may* be some differences between your setup and what we have here.
But the *really* important part - what we're going to do on authentication success
and failure - will probably be the same.

## The SecurityController

To get the `json_login` system fully working, we need to create that `app_login`
route. In `src/Controller` create a new PHP class called, how about,
`SecurityController`. Make it extend the normal `AbstractController` and then
create `public function login()`. Above that, I'll put the `@Route` annotation
and hit tab to auto-complete that and add the `use` statement. Set the URL to
`/login`, then `name="app_login"` and also `methods={"POST"}`: nobody needs to
make a GET request to this.

[[[ code('1d27b305ac') ]]]

Initially, you need to have this route here *just* because that's the way
Symfony works: you can't POST to `/login` and have the `json_login` authenticator
do its magic unless you *at least* have a route. If you don't have a route, the
request will 404 before `json_login` can get started.

But also, by default, after we log in successfully, `json_login` does... nothing!
I mean, it will authenticate us, but then it will allow the request to continue
and hit our controller. So the easiest way to control what data we return after
a successful authentication is to return something from this controller!

But... hmm... I don't really know what we should return yet - I haven't thought
about what might be useful. For now, let's return `$this->json()` with an array,
and a `user` key set to either the authenticated user's id or null.

[[[ code('1f068f4e37') ]]]

## AJAX Login in Vue.js

Let's try this! When we go to `https://localhost:8000`, we see a small frontend
built with Vue.js. Don't worry, you don't need to know Vue.js - I just wanted to
use something a bit more realistic. This login form comes from
`assets/js/components/LoginForm.vue`.

It's mostly HTML: the only real functionality is that, when we submit the form,
it won't *actually* submit. Instead, Vue will call the `handleSubmit()` function.
Inside, uncomment that big axios block. Axios is a really nice utility for making
AJAX requests. This will make a POST request to `/login` and send up two fields
of data `email` and `password`. `this.email` and `this.password` will be whatever
the user entered into those boxes.

[[[ code('f29530f5a8') ]]]

One important detail about axios is that it will automatically encode these two fields
as JSON. A lot of AJAX libraries do *not* do this... and it'll make a *big* difference.
More on that later.

Anyways, on success, I'm logging the data from the response and on error - that's
`.catch()` - I'm doing the same thing.

Since we haven't even *tried* to add real users to the database yet... let's see
what failure feels like! Log in as `quesolover@example.com`, any password and...
huh... nothing happens?

Hmm, if you get this, first check that Webpack Encore is running in the background:
otherwise you might still be executing the old, commented-out JavaScript. Mine is
running. I'll do a force refresh - I think my browser is messing with me! Let's try
that again: `queso_lover@example.com`, password `foo` and... yes! We get a 401
status code and it logged `error` `Invalid credentials.`. If you look at the
response itself... on failure, the `json_login` system gives us this simple, but
perfectly useful API response.

Next, let's hook up our frontend to use this and learn how `json_login` behaves
when we accidentally send it a, let's say, less-well-formed login request.
