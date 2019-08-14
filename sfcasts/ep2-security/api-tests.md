# Api Tests

Coming soon...

To create a national test. Here we are going to create a client object with 
`$client = self::createClient()`, little http client that has made available from the
API platform from `ApiTestCase`. We're just going to use it to make requests into it. And
this uses the same interfaces, Symfonys, new http client. So it's super easy to use.
So let's check this out. What we're going to do here is let's make a `POST` request to
`/api/cheeses`.

And if we just did that, we just made a post request. That `/api/cheeses`,
she says we're not going to be authenticated. And so what I'm really going to be
testing focusing on in my test is the security aspect of things. So what I'm gonna,
what we want here is if we make a `POST` request in its endpoint and we are not logged
in, we should be denied access. And since we're anonymous, that should be `401`
that status code. Let's try that. `assertResponseStatusCodeSame()`. This is one of
those built in.

Uh, this is actually something that we get from our API platform. Um, uh, based test
class. And here we can say `401`. Perfect. So run over. Let's try run the test
again 

```terminal-silent
php bin/phpunit
```

and oh, interesting. Two things happen. The first thing is on the bottom here,
you're going to notice that you're going to see some deprecation warnings. This is a
feature of the simple PHP unit library. If your code hits deprecated code, you're
going to get a report on the bottom right. Now there are some deprecations that
you're gonna see coming from API platform itself. Um, these particular ones are fixed
in the next version of API platform. So basically it's something that we don't need
to worry about. It's just going to be an annoying thing you're going to see at the
bottom when we run our tests for now. But above this Oh, interesting. See 

> Call to undefined method a Client::prepareRequest()

right now. And this may be
something that changes or this, they might just give you a better error message in
the future. But right now if you want to use the Api Platform from testing tools, you
need to run 

```terminal
composer require symfony/http-client
```

as the new age to be client library and Symfony and API
platforms. Testing tools rely on a small part of it and that's why he got that air
notice. I could put `--dev` and they ended that because we technically own any
of this. We're a test tools but the HD to be clients. Just a great tool that I might
use my application to do other things. So I'm just going to install it as a normal
dependency. All right, now let me try those tests again. 

```terminal-silent
php bin/phpunit
```

Oh, check out this giant
air. So the nice thing is when you get failures, you get this really nice thing.
We're actually shows you what the response looks like. You can see we're getting back
a 

> 406 Not acceptable 

response And if you look down here, you know it's
JSON all kind of thrown together here, but you can see we have an error occurred.

The content type `application/x-www-form-urlencoded` is not supported. This is
actually something that we talked a little about, a little bit about earlier when we
were talking about the axios library and JavaScript. When you make a post request,
most API clients, we'll set the, we'll set a `Content-Type` header that says that the
data that we're sending is `applications/x-www-form-urlencoded`. That's
the format that data is sent on when you submit an html form through your browser.
Now, right now we're not actually sending any data with this, but if we did actually
add some fields to send, it would actually encode it in a certain way. Um, uh, in the
same way that a html forms aren't coded when you submit, and of course our API, you
look at our documentation here, you know, we're literally sending JSON.

So what we need to send to our end point as JSON and yes, even though we're not
sending data right now, API platform was already saying, hey, it looks like you're
trying to send me a, uh, according to the `Content-Type` header of the requests. You're
trying to send me data that's in the wrong type. So you get back this four oh six not
acceptable. So the fix for this actually you'll see two fixes, but the kind of more
straightforward fix for now is to add a `headers` scheme and set `Content-Type` to
`application/json`. So that says, Hey, we are sending JSON Up to the end point. If
you try this, you will get a slightly different air, which makes sense, which is a

> 400 Bad Request

And down here it says syntax error.

And you can see down here it's coming from JSON and JSON Decode. So now that we've
told it, we're sending content type, we are not sending it any body. Um, so that's
actually invalid JSON. So what I'm going to do down here is actually add another
thing called `json` and sent that to an empty array. This JSON option here is a really
nice option from that basically says, here's the JSON Data I'm going to send. And uh,
the client will automatically JSON and code that for you. So it very similar to what
axios does. So we're not sending any data up there, but that should ideally be enough
to fix the error. Now notice one thing about security with API platform is that even
though we shouldn't have access to this endpoint, it did JSON Decode the data first
and send a `400 Bad Request` before it denied access.

So there actually is the d the deserealization process actually happens before
security. So there actually are situations where um, you can get a `400` air instead of
`401`. Maybe I'll talk about that more. It's not what our code this time. Yes,
we've got it. And you can actually see what there's some log messages dumped out
above here. Um, coming for applications as full authentication is required to access
this resource. We'll talk about where this is coming from in a little bit and
actually how to remove it because it gets a little bit annoying up there. Okay. So we
had a four oh one status code. We've proven that you actually need to log in to hit
this endpoint. So the next thing I want to test is actually let's log in and then see
if we get, if, if uh, if we get a 200 status code or two oh one status code. So to do
that we need a user in the database and we're always gonna assume that our database
is empty to start. And so if we want to use her, we're going to create one. So 
`$user = new User()`

and then I'll just set some information on that like `setEmail()`, an email, we'll call
it `setUsername()`. And then the only other field that we need right now is the
a password. Now remember right now or to keep things simple, the password is password
field is actually the encoded password field in the database. We haven't set up any
mechanism yet to encode the password. So once again, I'm going to go over here and
run bin Console security and code password

```terminal
php bin/console security:encode-password
```

[inaudible] and code dash password taken Fu. It will give me string. I can pat use
here and I'll pass that as my password. [inaudible].

Now next thing we need to do is actually save this to the database. Now normally in
Symfony we use autowiring everywhere to get our services. The test is unique
environment where you're not going to have to use autowiring. Instead you're going
to get the container infects the services you need off by their id. So to get the NC
manager you can say `$em = self::$container` So that's a nice
property. That Symfony sets up with the container and they can say `->get()` kit and you
can say the name of the service at d. And the easiest way to get the entity managers
get a service called `doctrine` and say `->getManager()` on the end of that. Then we'll do
an `$em->persist($user)` 

`$em->flush()`

and we're good. So now that we have this used in the database, we can actually log in
as this user. So I'm going to copy of my client error requests from before. This time
we're going to make a `POST` request

two `/login`.

I'll keep the header and this time we are going to send some JSON Fields. We will
send `email`, set two our `cheeseplease@example.com`

`'password' => 'foo'` and that's it.

I'll also copy my response. Got Up here and we should get back a two Oh four response
code. I'll try copying that again cause that's what we're returning from our security
end point. If you all can controller `SecurityController` on success, return the `204`
status code. All right, so let's try that out. Spin back over

```terminal
php bin/phpunit
```

 and it works and for the more astute, if you've done
testing before, you probably already see a problem which is that if I run it again it
explodes because his duplicate entry `cheeseplease@example.com` so one of the things
that we need to tie, we need to talk about, it's one of the annoying things about
functional tests is that we can't just create the same user in the database every
single time. We need to actually take care of cleaning our database before every
test, making it empty so that when you create this user here, that user is not
already in the database. So let's do that next to actually take our test to the next
level.