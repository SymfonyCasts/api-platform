# Login Success & the Session

Let's see if we can log in for real. But first... we, uh, need to put some users
in our database. Head to `/api` - we'll use our API to do that! Eating our own
dog food.

I *do* have a few users in my database already... but you probably don't... and
I don't think I set any of these with real passwords anyways. So let's create a
brand new shiny user.

But... our API currently has a big shortcoming. I'll close this and open up the
POST endpoint. When someone uses our API to create a user, they will eventually
send the plain-text password on the `password` field. But... in the database,
this field needs to be set to an *encoded* version of that password. So far,
we don't have any mechanism to intercept the plain text password and encode it
before it gets to the database.

We'll fix this soon, but we're going to cheat for now. Find your terminal and
run:

```terminal
php bin/console security:encode
```

This is a fun utility where you can give it a plain-text password - I'll use `foo` -
and it will give us back an *encoded* version of that password. Copy that.

*Now* we can use our endpoint: I'll use the `POST` endpoint with email set to
`quesolover@example.com`, password set to the long, encoded password string,
`username` set to `quesolover` and I'll remove the `cheeseListings` field: we
don't need to create any cheese listings right now. Hit "Execute" and... perfect!
A 201 status code. Say hello to `quesolover@example.com`!

Copy that email address, then go back to our homepage. On the web debug toolbar,
you can see that we are *not* logged in: we are anonymous.

Ok, let me open my browser's debugger again... then try to log in:
`quesolover@example.com`, password `foo` and... nothing updates on our Vue.js
app yet... but let's see what happened with that AJAX request.

Yea! It returned a 200 status code with a `user` key set to 6! It worked! And
that response is coming from our `SecurityController`: we're returning that data.

## Wait, Where's my API Token?

But wait, it gets better! If we refresh the homepage, we *are* now logged in as
`quesolover`. And our important job number one is done! Just because we're creating
an API doesn't mean that we now need to start thinking about some crazy API token
system where the authentication endpoint returns a token string, we store that in
JavaScript and then we send that as an `Authorization` header on all future requests.
No, forget that! We're done! Starting now, all future AJAX requests will automatically
send the session cookie and we'll be authenticated like normal. It's just that simple.

And yes, we *are* going to talk a bit about API token authentication later. But...
there's a good chance you don't need it. And if you don't need it... but try to use
it anyways, you'll complicate your app & *may* make it less secure. As a general
rule, while you can *use* API tokens in your JavaScript, you should never *store*
them anywhere - like local storage of cookies due to security. That makes using
API tokens in JavaScript... tricky.

So... if we're not going to return an API token from the authentication endpoint...
what *should* we return? Just returning the number 6... probably isn't very useful:
our JavaScript won't know the email, username or *any* other information about
*who* just logged in. So... what *should* we return? There's not a perfect answer
to that question, but I'll show you what I recommend next.
