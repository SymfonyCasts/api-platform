# Logging in Inside the Test

Note about 400 on deserialization error before security.

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