# Testing, Updating Roles & Refreshing Data

It is returning their phone
number. So before we go and fix that, let's actually finish the second half of this
test, which is going to be that where we actually take this user, make them an admin
and then make the same request and assert that we do get back the `phoneNumber` field.

so what you're going to want to do here is you don't want to say `$user->setRoles()` and
then we will set the role `ROLE_ADMIN` on there. This is when we feel that store in the
database, but you have to be really careful here. One of the properties of the way
the test system works is that every time you make a request it actually boots up
Symfony's container and then fully shuts down all the services. What that means is
that the entity manager up here at this moment, they have the manager here before we
make any requests. It's aware of this `User` object. It just saved this `User` object to
the database. But after we make a request, the entity manager, uh, after this client
area request line is basically empty. It's almost like a fresh entity manager. It has
no idea that we've requested a user object.

It's almost like you want to think of everything above `$client->request()` as code
that runs on one page load and everything after `$client->request()` is code that runs
on another page load. So I wouldn't expect it this way. If a fresh page down here, I
wouldn't expect the `EntityManager` object to be aware of my user. So the the end
result of this is every time you make a request, if you need the user object, you
can't just say `$user->setRoles()` that will work. But if down here, if you said
`$em->flush()`, the entity manager is not going to be aware that it's managing your `User`
object. It's basically going to do nothing. It's going to think it doesn't have to do
anything. So after every request you're going to want to get a fresh `User` object. By
Ca I mean `$em = getRepository(User::class)->find($user->getId())`
Another nest results of that is that if we, for example, made like a `PUT` request
up here to edit the user, then down here this is going to make sure that that `$user`
variable has like the latest data in the database if you want to do some assertions
on it or something like that.

So right now down here we are gonna refresh the user and elevate them to an admin.
And then I'm going to say `$this->logIn()`. Actually I'll login, pass that to the `$client`.
And then past that, the same two arguments, our email address and food. Now that
should seem funny to you because you should be thinking, Hey, I want a second. We're
using session based authentication. So if we're logged in, if we log in on top, by
the time we make the second request, we're still logged in one another property of
Symfony security system and it's on them, honestly a little bit of a bug that
hopefully will fix it. Some point is that um, if you change the roles of a specific
user and that user is already logged in, the user doesn't get those new roles until
they log out and log back in. So the reason I'm logging in here is so that the user
basically gets this new role, otherwise they're going to have it in the database, but
the Symfony security system won't actually see it. So finally down here we can do the
same `$client->request()`. In fact, I'll copy the `$client->request()` and the
`assertJsonContains()`.

but this time we expect the `phoneNumber` field that should be there set to our
`555.123.4567`. Perfect. All right, so we
already know this is going to fail because it fails immediately because when we make
it `GET` requests for a `User`, it is returning the `phoneNumber` field. So it's dying
down here on the assert array, not has key. So how do we handle this in general? So
how do we get this functionality? Basically the idea is that usually when you make a
request for an operation in API platform, the groups are determined on an operation
by operation basis. So in the case of user, we're actually doing `user:read` or
`user:write`? Based on whether or not, or reading or updating the user. But as
we know on `CheeseListing`, you can also control this on an operation by operation
basis.

So when you make it `GET` requests for a single `User`, you get `cheese_listing:read`
and `cheese_listing:item:get` the problem with that system is that it's static.
You can't change the context on a user by user basis. You can't say, oh this is an
admin user. So I want to give, I want to, um, when we, when we normalize this user, I
want to also include a, an additional group that may include additional fields, but
doing that is possible. So on `User` about `$phoneNumber`, we're going to leave
`user:write` Cause we want it to be writeable but instead of `user:read`,
we're going to change this to `admin:read`. That's just a new group that
I made up and if we tried it right now, nothing has that group.

So do actually kind of get the first half of our test to fail to pass because if we
run the test now

```terminal
php bin/phpunit --filter=testGetUser
```

you're going to see it fails asserting that an array has the subset
array. It's failing on, um, use resource lines. Use a resource test line 68. So it's
actually failing down here. So we have successfully made the phone number appear
nowhere, uh, appear, not appear up here, but it's not appearing anywhere. So next
we're going to create something called a context builder, which is going to allow us
to dynamically add this admin regroup if the `User` is an admin user.
