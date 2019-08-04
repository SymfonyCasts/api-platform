# On Authentication Success

but there's a couple of
options. One is that we could actually return the `User` object, the serialized version
of the user object. Basically the same thing that we get if we went and fetched a
specific user. So for example, I don't know what these ideas are, but `users/5.json`
that's a valid user on my system. We could return this JSON, or even
better, we can return the JSON LD for it. That's actually very useful. It's not, it's
not restful because really only one URL should be responsible. One, URI should
be responsible for returning the user resource, returning it from our auth. Our
authentication endpoint is not very restful. However, never let usefulness get in the
way of, um, of just being perfectly restful. Uh, so if that's something that you want
to do and it's useful for you, do it. But I have another suggestion.

What if we returned the IRI `/api/users/5`, which happens to be the URL that you
could use to go get more information about that user. It turns out there's a standard
or at least somewhat standard way of doing this. So at the bottom, my controller, I'm
gonna return a new `Response()`, the one from `HttpFoundation` and I'm actually going to
return a content of nothing. `null`, which is totally valid. As long as you put it with a
204 status code, this means successful, but I have nothing to say back then.
The IRI, I'm actually going to put this on a location resource. I'm going to location
header. Now how do we get the URL to `/api/users/5` I mean, uh, usually we
generate URLs, um, uh, in Symfony and maybe there's a route that we could do that
too.

But you know, for the most part, API platform is taken care of or routes for us. So
is there an API platform way to say, Hey, I want to generate the IRI to this exact
object and the answer is eh. Yeah. And it's actually really useful to do that. We're
going to type hint and Inter F. We're going to type, add an argument here with a con
`IriConverterInterface`. We'll set that to `$iriConverter`. Now down below for the
`Location`, however we can say `$iriConverter->getIriFromItem()`. You can see
there's a number of other useful classes down there and we'll say `$this->getUser()`. All
right, let's what this looks like.

Let's go back to our log and formed that view and you can see right now on success we
were doing a `console.log()` of `response.data`, let's change that to
`response.headers` so we can see what the headers look like.

Let's go back over here. I'll go back to my homepage refresh and by the way you can
see that it says you are currently not authenticated. That's because my view
application on page load doesn't know that I'm authenticated. We're going to work on
that really soon.

This summer when we log in, we get a `204` status code. And if you check it
out, look at this `location: "/api/users/6"`. So now we can make another request for
that if we needed more user information to figure out what that is. And in fact my,
my site is actually already set up like this. So I'm going to look at my, um, my view
app. If you open the cheese was app, this is actually what is rendering the entire
page. You can see cheese whiz here because someone wants your leftover cheese. This
is what's showing up on top. And then the login form is actually, um, embedded inside
of here. So this is actually where we in bed, that other login form component that
you see over here.

And this top level thing, it actually has a spot here where it tells us whether or
not we're authenticated. It has a little user variable,


And not to get too much into the VUE details here, but
when we render the `LoginForm` component, we actually pass it a callback. We actually
pass it some information that says, hey, `onUserAuthenticated`. You can, um,
you can emit a `user-authenticated` event. Well that's going to do is it's going to
cause,

And what we're supposed to pass to this is not actually like the user data, but the
`userUri`, this would then go fetch the user data, update that user
information, and it should make us look logged in on the left here. Let me actually
show you what this looks inside the `LoginForm`. I'm going to, I'm count out these
three lines here. And the point is that all we need to do is admit this
`user-authenticated `event. And then whenever you pass here for `userUri` is going to
get past as the argument here. So it needs to be the user's URI. We know that this is
`response.headers.location`.

I'll take out my `console.log()`

All right, I'm going to go back. We'll do quesolover, again, type in foo and
watch the left side here. Oh, it failed. Yay.

> TypeError: Cannot read property 'substr' of undefined.

oh yeah, I know. Promise. Oh Huh. Oh, and actually before I tried, I need to remember
that the headers are all a lowercase to normalize it as lowercased. I'm on the front
end here, so now I'm going back over. I'm going to refresh the whole page here. Okay.
So `quesolover@example.com` password `foo` and watched the left side. Boom. It says

> You are currently authenticated as quesolover Log out.

To the point of showing this is that I'm in two things, is that you need to think
about how you stay authenticated. We're going to use session based authentication and
you also need to think about like what your endpoint actually returns. We're gonna
return to URI.
