# On Authentication Success

When our AJAX call to authenticate is successful, our app naturally sends back a
session cookie, which all future AJAX calls will automatically use to become
authenticated. So then... if our API response data doesn't need to contain a token...
what *should* it contain?

## Return the User as JSON on Success?

One option is to return the authenticated `User` object as JSON. For example...
I think one of my users in the database is id 5 - so if you go to `/api/users/5.json`,
we could return *this* JSON. Or even better we could return the JSON-LD representation
of a User.

This has the benefit of being useful: our JavaScript will *then* know some info
about who just logged in. But... if you want to get technical about things... this
solution isn't RESTful: it sort of turns our authentication endpoint into what looks
like a "user" resource. But, don't let that get in your way: if you *do* want to
return the User object as JSON, you can serialize it manually and return it. I'll
show you how to use the serializer to do this in the next chapter.

But... I have another suggestion.

## Returning the User IRI

What if we returned the IRI - `/api/users/5` - which is *also* the URL that a
client can use to get more info about that user? Let's try that!

At the bottom of the controller, return a new `Response()` - the one from
`HttpFoundation` - with *no* content: literally pass this null. Returning an
empty response is *totally* valid, as long as you use a 204 status code, which
means:

> The request was successful... but I have nothing to say to you!

So... where are we putting the IRI? On the `Location` header! That's a semi-standard
way for an API to point to a resource. For the IRI string... hmm... how *can* we
generate the URL to `/api/users/5`? Typically in Symfony, *we* create the route
and then *we* can generate a URL to that route by using its internal name. But...
now, API Platform is creating the routes for us. Is there a way with API Platform
to say:

> Hey! Can you generate the IRI to this exact object?

Yep! And it's a useful trick to know. Add an argument to your controller with
the `IriConverterInterface` type-hint. Now, set the `Location` header to
`$iriConverter->getIriFromItem()` - which is one of a few useful methods on this
class - and pass `$this->getUser()`.

[[[ code('d462cd0049') ]]]

Cool! Let's see what this look like! Go back to `LoginForm.vue`. Right now,
on success, we're logging `response.data`. Change that to  `response.headers` so
we can see what the headers look like.

[[[ code('e948ba488b') ]]]

Back on our browser, refresh the homepage. By the way, you can see that the Vue.js
app is reporting that we are *not* currently authenticated... even though the web
debug toolbar says that we *are*. That's because our backend app & JavaScript aren't
working together on page load to share this information. We'll fix that really
soon.

When we log in this time... we get a `204` status code! Yes! And the logs contain
a *big* array of headers with... `location: "/api/users/6"`.

## Using the IRI in JavaScript

This gives our JavaScript *everything* it needs: we can make a second request to
this URL if we want to know info about the user. We're going to do *exactly* that.

Back in PhpStorm, open up `CheeseWhizApp.vue`. This is the main Vue file that's
responsible for rendering the *entire* page - you can see the CheeseWhiz header
stuff on top. And... further below, it embeds the `LoginForm.vue` component.

This *also* holds the logic that prints whether or not we're authenticated... via
a `user` variable. We're not going to get too much into the details of `Vue.js`,
but when we render the `LoginForm` component, we pass it a callback via
the `v-on` attribute.

[[[ code('197f7a7418') ]]]

This basically means that, inside of `LoginForm.vue`, once the user is authenticated,
we should dispatch an event called `user-authenticated`. When we do that, Vue
will execute this `onUserAuthenticated` method. *That* accepts a `userUri` argument,
which we then use to make an AJAX request for that user's data. On success, it
updates the `user` property, which *should* cause the message on the page to change
and say that we're logged in.

Phew! Let me show you what this looks inside `LoginForm.vue`. Uncomment the last
three lines in the callback. This dispatches the `user-authenticated` event and
passes it the user IRI that it needs. The `userUri` variable doesn't exist, but
we know how to get that: `response.headers.location`. I'll take out my `console.log()`.

[[[ code('958cf78cb8') ]]]

Let's do this! Move over, refresh, then login as `quesolover@example.com`, password
`foo`. And... oh boo:

> TypeError: Cannot read property 'substr' of undefined.

My bad! I forgot that all headers are normalized and lowercased. Make sure the
`location` header has a *lowercase* "L". Refresh the whole page one more time,
put in the email, password and... watch the left side. Boom! It says:

> You are currently authenticated as quesolover Log out.

At this point we're using session-based authentication, which is the *best*
solution in *many* cases. And because we're relying on cookies for authentication,
our authentication endpoint can really return... whatever is useful! Note that
this *also* avoids the need for the very un-RESTful `/me` endpoint that some API's
like Facebook expose as a, sort of "cheating" way for a client to get information
about who you are currently logged in as.

Next - if we refreshed right now, our JavaScript would forget that we're logged in.
Silly JavaScript! Let's leverage the serializer to communicate who is logged
in from the server to JavaScript on page load.
