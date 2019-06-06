# User Resource

Coming soon...

We're not going to specifically talk about security in this episode. We're going to
save that for the next episode. But even forgetting about security, there's a pretty
good chance that your API, we'll need some concept of users, like again, don't think
about a security at all, but just thinking about our app right there, there are going
to be people that are going to be posting the cheese listings. So there's probably
going to need to be a, uh, a user data user table in our database and every
individual cheese is probably going to be related to his specific users so that we
know that this cheese listing was added by this user. There's also a pretty good
chance that we're going to want to expose information about those users in our API.
So what I'm saying is,

when do you think, um, even outside of security there's a good chance that your API
probably needs some sort of a user resource. And that's what I want to build now
because we do need to make our, each individual cheese listing, um, related to one
user so that we can see which user created it.

So even though I'm telling you not to think about security at all, instead of
creating our user entity with `bin/console make:entity` like I normally would, I'm
actually going to use it `make:user`, 

```terminal-silent
php bin/console make:user
```

which is going to do a couple of security things
for us, not because we need it now, but then it will be done so we don't have to do
or worry about it in the second episode. So the name of security class shirt, let's
call it `User`.

I do want to store in the database. Yes, I am going to store my user information in
the database and then a unique display name. I'm going to have users, uh, login via
`email`. And then does this app need to hash or check user passwords? We'll talk more
about this in the next episode, but uh, if users need to be able to actually type in
a password and log into your system, even if they're doing that from an iPhone app or
from some JavaScript widget, then and your application is going to be responsible for
checking that password. It's not just going to be sent off to some service. Then you
need to say yes here and I'm also going to use the Argon2i password Hasher. Um,
though in Symfony 4.3, you'll see this option will change and then instead of asking
this, it's going to use a setting called auto that uses whatever the best hash
is available, which is kind of cool.

All right, so let's go check out what they did because it's very, very simple.
it close up my tabs here. We now have a new `User` entity and there's nothing special
about it. It does have a couple of extra security related methods down here like 
`getRoles()` and `getPassword()` and `getSalt()` and `eraseCredentials()`, but they're 
not getting in our way right now and we're not going to use them. Mostly we just have 
a normal boring entity entity with `$id`, `$email`, a, uh, `$roles`, array property, 
and they hashed `$password` field. And of course we have a `UserRepository` and this 
did make a couple of changes to our `security.yaml`, it's set up the `encoders`. 
This is the thing that will soon say `auto` instead of `argon2i` and this thing called a
data provider. Again, all things that are going to be useful later, but they're not
doing anything now. So just kind of forget that they're happening. What we just did
is create a nice new boring `User` entity. Now, even though I have an `$email` field on
here in my Api, also want users to have a username so that we can actually show that
on the front end somewhere. So I'm gonna go back and run 

```terminal
php bin/console make:entity
```

and we will update our `User` entity. And I'm going to add a `username`, which should be
a `string`  255, not nullable the database and then I'll enter it to finish there. And
then because of the way that `make:user`, uh, works, uh, scroll down and you notice the
arrows are already `getUsername()`, um, originally, because this is a method that
security needs and it's returning our `$email` now that we actually have a `$username`
field return that,

And while we're in here, uh, the `make:user` grand knows that email needs to be unique.
So it had a `unique=true`, we also know that to username needs to be unique. So
what you need for `unique=true` on that. So good. So that's a very nice um, uh, entity that we
have set up. And by the way, at this moment, no, I'm not going to circle back on
that.

So now that we have this new entity that's run over, we'll do our 

```terminal
php bin/console make:migration
```

Move over and just double check that. That doesn't contain anything, any unexpected
changes yet. `CREATE TABLE user`. That looks fine. They'd 

```terminal
php bin/console doctrine:migration:migrate
```

Perfect. So at this point we still, as far as API Platform conserved, we still only
have one resource. We have the cheese, the `CheeseListing` resource. The only thing we
need to do to expose our `User` is a resource is to add that same annotations we day
before `@ApiResource`.

As soon as we do that, whoa, check that out. We have a whole new resource with five
new endpoints in down here you can see there's a new `User` model and even without
even creating anything that you can see, there's a couple of weird things like the
hashed password as part of the API, the roles are part of the API. When I create a
new user, I can actually pass what roles I want. So we needed to do some tweaking
here, but this is actually working. Now, one thing I want you to notice is that the
primary key is always being used as the ID inside of here. And I want to talk a
little bit about that. Um, that is something that is actually flexible inside of Api
Platform. There's two things I want to say about it. The first is that for your
actual id a property, when you're creating an API, a lot of the times, instead of
using an auto incremented ID, it's better to use a UUID.

And we're not gonna go into how to do that. But that is something that's supported by
doctrine and it's something that's supported by API Platform. However it works best
if you are, if your database is Postgres, MySQL doesn't store, um, you do ids as
efficiently. That being said, I've done it in production. It's not that big of a
deal. Why are you UUID is kind of cool. Will you ideas are kind of cool because if you
are writing a JavaScript front end and you want to create a new user instead of
making an Ajax request and then waiting for the server to assign an auto increment id
and then return it to you so you can store it somewhere in JavaScript, you can
actually just generate the UUID and JavaScript and send it up with the request.

This allows your no slides or JavaScript to choose a UUID and it just will simplify
your JavaScript front end. The only thing you need to do to make it work is Ma is
configured this to use a UUID and makes you add a `setId()` method so that you can
actually set the ID, um, when it sent. Another thing is it is also possible if you
want to make the username, um, the UUID in that case you would still want
an, uh, an actual primary key in the database, but you can make, um, the username
what is actually used over here, um, inside of the URLs.

Anyways, the next thing I want to fix is a, all these extra fields that we get if we
a fat something like `roles` and `passwords`. So we're gonna do the same thing that we
did inside of cheese listing, which is we did this `normalizationContext` groups and no, uh,
and `denormalizationContext` groups and a copy of those. And inside of user we're
gonna do something very similar. I'll open up my annotation and I'm going to get rid
of that `swagger_definition_name`. I don't really need that. This case will for a
normalization, we use `user:read` and then for denormalization we'll use `user:write`.
We're going to follow that same pattern that we've been using.

And then let's see down here for `$email`, we'll say `@Groups({})` and we'll put this in
`"user:read", "user:write"`? I'll copy that for `$roles` right now. Let's not make that
readable or writeable for `$password`. I'm actually going to put this in the `"user:write"`
group. This doesn't really make sense yet. This is the hashed password and we would
never expect like art JavaScript frontend to send us the Hash Password, but we're
going to do that for now and we're actually going to do that properly when we
actually taught by our security chapter and if you username we'll make that also
`"user:read", "user:write"` now when we refresh, just like with `CheeseListing`, it's
split into two different resources. You can see when you read during the `email` and
`username` and when we write we can do `email`, `password` and `username`, and then the only
other thing that we really need to do to make this functional is add some validation.
So on `User` `$email` and `$username` meet need to be unique. So I'm going to add 
`@UniqueEntity()` with `fields={"username"}` and then a separate one or `fields={"email"}` as well. 
Now need to be unique in the database.

Then down here above `$email` and we'll say `@Assert\NotBlank()` and `@Assert\Email()` and then for
`username` we will say `@Assert\NotBlank()`. I won't worry about the password yet and we're
going to fix, we're going to change that in the security chapter anyways. All right,
I think we're good. Can refresh the documentation and get a fresh thing and we can
actually start creating users.

So try it out. I use my personal email address, `cheeselover1@example.com` password
doesn't matter and use your name for consistency. I'll put that same thing and
execute and hello our very first user. That's great. Just a couple more here. All
right, one more to make it a little bit interesting and we can also play a
validation. If we send an empty set up there, it execute, we're going to get are
validation back immediately and if we try to fetch those, try out the fet jam point.
We have our two users and as we already know we only have two years of, this is
already an Api end point that supports pagination and later we can add filter
into it. So, uh, holy crap. We just accomplished a huge amount of work, um, in just a
few minutes. So this is the power of API Platform. And as you start getting used to
how it works, you can see how easy it is to spin up new API end points for, for new
resources. But ultimately we did this because we want to link users to Cheese
listings. When I create a cheese listing, when I, when I fetch a cheese listing, I'm
going to see who created that. When I create one. And when I create one, I want to
say who is creating it. It's a better way to say that, which is why this is the rough
audio.

Okay.