# Authentication Errors

We just found out that, if we send a bad email & password to the built-in `json_login`
authenticator, it sends back a nicely-formed JSON response: an error key set to
what went wrong.

I think that's great! I can totall work with that! But, if you *do* need more control,
you can put a key under `json_login` called `failure_handler`. Create a class in
your code, make it implement `AuthenticationFailureHandlerInterface`, then set
that class key here. With that, you'll have *full* control to return *whatever*
response you want on authentication failure.

But this is good! Let's use this to show the error on the frontend. If you're
familiar with Vue.js, I have a data key called `error` which, up here on the login
form, I use to display an error message. In other words, all *we* need to do is
set `this.error` to a message and we're in business!

Let's do that! First, *if* `error.response.data`, then
`this.error = error.response.data.error`. Ah, actually, I messed up here: I
*should* be checking for `error.response.data.error` - I should be checking to
make sure the response data has that key. And, I should be printing *just* that
key. I'll catch half of that mistake in a minute.

Anyways, if we *don't* see an `error` key, something weird happened: set the error
to `Unknown error`.

Move over, refresh... and let's fail login again. Doh! It's printing the *entire*
JSON message. *Now* I'll add the missing `.error` key. But I *should* also include
on the `if` statement above.

Try it again... when we fail login... that's perfect!

## json_login Require a JSON Content-Type

But there's one *other* way we could fail login that we're *not* handling. Axios
is smart... or at least, it's *modern*. We pass it these two fields - `email` and
`password` and it took care of turning them into JSON. You can see this in our
network tab... down here... it set the `Content-Type` header to `application/json`
and turned the body into JSON.

*Most* AJAX clients don't do this. Instead, they send the data in a different
format that matches what happens when you submit a traditional form. If our AJAX
client had done that, what do you think the `json_login` authenticator would have
done? An error?

Let's find out! Temporarily, I'm going to add a *third* argument to `.post()`. This
is an options array and we can use `headers` to set the `Content-Type` header to
`application/x-www-form-urlencoded`. That's the `Content-Type` header your browser
usually sends when you submit a form.

Go refresh the Javascript... and fill out the form again. I'm expecting that we'll
get *some* sort of error. Submit and... huh. A 200 status code? And the response
says `user: null`.

This is coming from our `SecurityController`! Instead of intercepting the request
and then throwing an error when it saw our mistake it... did nothing! It turns out,
the `json_login` authenticator *only* does its work if the `Content-Type` header
contains the word `json`. If you make a request here without that, `json_login`
does nothing and we end up here in `SecurityController`.... which is probably
not what we want. We probably want to return a response that tells the user what
they messed up.

Simple enough! Inside of the `login()` controller, we now know that there are two
situations when we'll get here: either we hit `json_login` and were successfully
authenticated - we'll see that soon - *or* we sent an invalid rqeuest.

Cool: if `!$this->isGranted('IS_AUTHENTICATED_FULLY')` - so if they're not logged
in - return `$this->json()` and follow the same error format that `json_login`
normally uses: an `error` key set to:

> Invalid login request: check that the Content-Type header is "application/json".

And set this to a 400 status code.

I love it! Let's make sure it works. We didn't change any JavaScript, so no refresh
needed. Submit and... we got it! The "error" side of our login is bulletproof.

Head back to our JavaScript and... I guess we should remove that extra header so
things work again. Now... we're back to "Invalid credentials".

Next... I think we should try putting in some *valid* credentials! We'll hack a
user into our database to do this and talk about our session-based authentication.
