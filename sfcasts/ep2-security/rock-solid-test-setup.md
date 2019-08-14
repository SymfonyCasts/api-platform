# Rock Solid Test Setup

Coming soon...

So one of the trickiest things about functional tasks, no matter how you do them as
controlling the database. Ideally in my opinion, at the start of every single task in
my database would be empty so that then if I need any `User` objects or `CheeseListing`
objects, I create them inside of my test and our test reads like a story. You can
actually see here that I get a `401` status code on this request. Then I create
the user, then I log in and I get `201`. So it reads like a complete story. There
are a couple of different ways to clear your database at the beginning of every test.
One of the easiest ones that you'll see, especially that you'll see in the API
platform world is to use a library called Alice. Some were on a 

```terminal
composer require alice --dev
```

This is going to install an old look, AliceBundle. And what
did that bundle actually does? I've talked about it a few times in the past on
SymfonyCasts is it actually allows you to specify your fixtures in YAML.

So I'll show you that library very quickly just to give an example of what I'm
talking about here. If I click basic usage yet you're allowed to actually create
YAML files and actually kind of specify your, um, your fixtures that you want your
application YAML files. It has nothing to do with testing technically. Um, it's
just a way to have fixtures in your application. I really like this bundle in many
ways because it's just a really cute and handy way to make fixtures. On the other
hand, sometimes you want to get it a little bit more advanced use cases. Um, it can
be a little bit hard to do those things. So that's typically why I don't show Alice
being used at this point. So why am I installing it? Massaging it? Because the offers
of API platform actually puts a nice database test utilities into AliceBundle.

So we're not gonna use it for this kind of YAML fixture stuff. Instead of we're
gonna use it to help us reset our database and the test environment. So perfect. So
you can see it actually in grid and fixtures directory and a command. You load those
fixtures. We're not going to do that. What we are going to do instead is instead our
test class near the top, at the top, we're going to say once PHPStorm finishes
building my index, `use ReloadDatabaseTrait`. Well that's going to do is and
before every single test in this test class, it's going to clear my database tables
and then reload my Alice fixtures, which of course we don't have any Alice fixtures.
So this is just going to reload the database. Let's try run 

```terminal
php bin/phpunit
```

and this
time can see some more deprecation warnings, but it works. So there's a couple more
deprecation warnings that actually come from Miss Bundles and it makes even a little
bit more annoying to look at, but you can see it works and we can run it over and
over and over again. And it's always going to work because it's clearing out our
database. So we're not, we're no longer creating duplicate entries inside of here. So
that just a really nice thing to do. Now notice one of the things I've mentioned is
that we're getting these, um, this actually the test logs are printing out to the
screen.

This is actually because so far in our application we haven't actually installed a
lager yet. So Symfony in its core always comes with a lager service so that other
services can use it. And by default if I'm, if there's an a, if you don't install a
specific longer library than that default logger, it just logs things to standard out
and standard error. So in this case, whenever you have an actual air in your
application and just like prints to your screen, it's kind of cool in some ways, but
it's also kind of annoying. So let's actually install a proper or am I saying

```terminal
composer require logger
```

This one installed the monolog logger and as soon as this is
done, when you read around those tests, you're not going to see that output on top
anymore, which is kind of Nice. You want those. Those are now being stored in Var
lock test out lock for the test environment.

So you could sell tail that if you want it to look inside of here for stuff so you
can see that air inside of there. All right, so because we're going to be doing quite
a lot of testing, I'm going to make our tests set up a little bit smoother here. One
of the things we're going to doing a lot is we're going to need to be created a lot
of users and we're going to need to also like log in before a lot of tests. So in in
my source directory I'm going to create a new directory called `Test/`.

Okay.

And inside of there a new base class for my tests called `CustomApiTestCase`. I'm
going to make this extend to the `ApiTestCase` class. If using API platform 2.5 then
API test case will come from an `ApiPlatform\` namespace. Instead of here we're going
gonna create a couple of protected functions like a `createUser()`. We pass it the
`$email`, we want, the `$password` we want, and it's going to return. It's going to say
that user the database and then return it. Then I can go steal that logic from over
here. We'll grab everything from `$user = new User()` to a actually flushing into the
database. So I'll paste that bottom. Let's return that user and then we just need to
make a couple of things dynamic. What we use the `$email` variable for `$password`. This is
still temporarily going to be the uh, the kind of encoded password that we're going
to fix that in a second. And then for the username, I'll just use a little trick
here. We just use the, he's a substring of the email from the zero position to
wherever the position inside of email where we see the @ symbol. So basically
everything before the at symbol.

Then the other thing we're going to do is create a function for logging in. So I'll
say `protected function logIn` this case because logging in actually is something that
makes an a, a request on client. We'll actually need the `Client` object itself.

And then that same `string $email` and `string $password`, this time will be the actual

plain text password

and we won't return anything. So I'll go over here and I'll just copy these last two
lines, paste those in and then make sure that we make the email

and password dynamic. Cool. Now with that

we can shorten things quite a bit. I'll change my base class here to use our 
`CustomApiTestCase` and then down here or remove all the user stuff and say 
`$this->createUser()`. cheeseplease@example.com

oh man. Actually

before that I should've copied my long knowing password here. There we go. And then
we'll pass that long and code a password as a second argument. And then we can rid of
the log and stuff too. And we will say `$this->login()` past the `$client`. That same, she's
`cheeseplease@example.com`

and this time we'll pass it the paint plaintext password, which is going to be `foo`.
So this being the encoded version of the food password. All right, so make sure we
didn't break anything. Run 

```terminal
php bin/phpunit
```

and if you skip those, uh,
deprecation warnings, it does pass. All right, so I'm liking this start. Let's, let's
do a couple other things here. Uh, passing around this long and coded password here
is annoying. We can do better than that. So I'm actually gonna replace that with just
food here. Then instead of my base class, we're actually are going to encode that
password properly. So I'll remove this app password here and our place with `$encoded =`
and what we need to do here, especially the service out of the container that's
responsible for encoding the password. We can get that with 
`self::$container->get()` And the idea that service is called `security.password_encoder`
 And then we can say in `->encodePassword()` pass in the user object and then the
plain tax password we want. So I'll pass that `$password`. Now he'd say 
`$user->setPassword($encoded)`

Perfect.

And then the last thing we can do is a lot of times we actually are going to be
creating a user and immediately logging in as that user. So that's also something
that we can even make a little bit shorter down here. Offering a 
`protected function createUserAndLogIn()`. And actually we'll need the same three arguments as the above
functional need. The `$client`, the `$email` and `$password` called pesos. Down here we'll
return the `User`, we'll say `$user = $this->createUser()`, pass him the `$email` and
`$password`.

And then `$this->logIn()` has a `$client`, `$email`, `$password`. And then about we'll 
`return $user`. Okay, cool. So a couple of little setup steps there, but is this really gonna
pay off in the long run? And then down here I can just say `$this->createUserAndLogIn()`
That's going to log in that user. Make sure everything works perfectly.
All right, so let's try that.

```terminal-silent
php bin/phpunit
``` 
 
Make sure we didn't break anything and it looks good.
All right, so looking back at our tests here, the whole point of this was to
originally assert that hey, if you try to make an anonymous request to this end
point, you'll get a `401` status code. But after you log in down here, it
should work. Let's actually assert that a copy, the original request again.

And down here we'll make the same exact request. Except this time we're going to
expect to have access. So it's actually what I'm going to check here for is a `400`
status code. Why 400? Well `400` because I'm not actually passing real data to the
JSON. If you want to make this test a little bit better, you could actually really
pass it, you know, like a title key here. Actually give it real data. But since we're
passing it an empty response, we should actually get a `400` status code, um, as a, uh,
a validation error. So let's spin over and try that on the test again and try that.

```terminal-silent
php bin/phpunit
``` 

And it works. And the last little bit of cleanup we can do here is see this heteros
key. We can actually remove that

and we have one more in the custom APN test case and we can remove the headers right
there. The reason is that one of the nice things is as soon as you specify and JSON
King the, the client's gonna know, okay, you are sending JSON. So I will set the
content type header for you. So that's gonna happen now automatically. So if we run
the task one last time,

```terminal-silent
php bin/phpunit
```  
 
 Yep. Everything works perfectly. So now with this great
setup, we can get to what we actually wanted to do, which is that right now, Jesus,
in order to edit a cheese listing, you just simply need to be locked in. We need to
make that smarter so that the only people that can make a, like an use, the put end
point is the actual owner of this cheese listing. And to do that, make sure that
works. We can now leverage our really nice test suite to run a test for that. Then
make sure it works. Let's do that next.