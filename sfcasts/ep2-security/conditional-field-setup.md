# Conditional Field Setup

Coming soon...

So far, we've been talking about granting or denying access to something entirely.
Uh, for example, in our `CheeseListing` entity that was the most complicated one. We
actually use a customized voter here to deny access, to edit a resource, um, unless
you are the owner or you are an admin user. But this is a several other different ways
that you can control access to your API. For example, what if there's a field that
you only want viewable or maybe editable by certain types of users? A great example
of this is in user the `$roles` field. Right now the `$roles` field is not part of the API
at all. And for most users that makes sense. But we might want to allow admin users
only to be able to edit this field. So the question is how can we do that
temporarily? I'm going to add this to my API by adding `@Groups()` and I'm going to put
it into my `user:write`

So if I just did that, this actually makes it part of our API, but of course it makes
it, um, writeable by everyone, which is ultimately not what we are going to want. But
we'll worry about that in a second. Let's give a think of another example. Let's
suppose that our user has a phone number field and want that phone number field to
be, to be writeable by anyone that can edit a user. So basically the, uh, I can, I
can change the uh, set or change the phone number on my account, but I only want it
to be viewable by admin users. I don't want the phone number to show up whenever
somebody is, uh, getting information about a specific user or listing a specific
user. So let's start by actually adding that to our entity and we'll say 

```terminal
php bin/console make:entity
```

We will update the `User` entity, add a new `phoneNumber` field. This
will be a `string`.

for field length I'll say `50`. It can probably be shorter than that and we'll say yes,
this can be nullable, this will be an optional field. Perfect. That finishes, I'll have
an answer. And now when I use her class, we have a nice new phone number field and to
get her in, etc. Let's finish this by running 

```terminal
php bin/console make:migration
```

Then we'll just go double check that migration file over here and yeah, it looks good. 
Adding the phone number field, nothing extra there. So let's run that with 

```terminal
php bin/console doctrine:migrations:migrate
```

And then also I'll run the 

```terminal
php bin/console doctrine:schema:update --force --env=test
```

So that that's also added to our test environment
database. Otherwise that's going to make our tests fail. All right, so if this were
just a normal field that we wanted to readable and writeable on our API, that's very
easy. We all even steal the uh,

all right,

the a `@Groups()` or above and say `user:read` and `user:write` So just like
that. This is now entirely part of our API, both through reading and writing. But as
I said, I only want admin users to be able to read this field. So let's start
actually by running a test for this, it's gonna clarify exactly the behavior that we
want. So I will say `public function testGetUser()` and we'll start with our 

`$client = self::createClient()` Then we'll create a user that we want to
edit. So I'll say `$user = $this->createUserAndLogin()`. But the normal user that we've
been using pass her food. Oh, when I asked him the past, the `$client` here. Perfect.
And then by default when you create a user like this, it just sets the `username`,
`email` and `password` fields, just kind of a minimum. This time I also want to set the
`phoneNumber` on the user. So I'm gonna see `$user->setPhoneNumber()` and we'll just say
`555.123.4567`. And then when I say that to the
database, so we'll get the answer manager with `$this->getEntityManager()`. And
then all I need to do here is called `$em->flush()`. Because this has already been
persistent before. So now we have a `User` object in the database that has a phone
number. Yeah, because we were logged in as um, we're not logged as an admin. We're
actually logged in by the user that owns this user. Um, but according to the rules
that I want to do is only admin users are going to be able to see the phone number,
not even for our purposes, not even the own, uh, not even the owner's going to be
able to see it. So you can say here is if we make a re `GET` request to 
`/api/users/` and then `$user->getId()`, then we can say in this, we'll just first say
`$this->assertJsonContains()` and we'll just kind of do a sanity check here and say, you
know, we should have the `'username' => "cheeseplease"`. But what we want to assert here is
that we don't have access that the phone field is not there and there's no really,
there's no, like a search JSON does not contain. So what I'm gonna do here is say
`$data = $client->getResponse->toArray()`. That's when I saw a function that if it's a
JSON, um, response, then it's going to `json_decode()` that into an array for you.
Lauren's gonna throw an error if it's not in array. And then down here I can say
`$this->assertArrayNotHasKey('phoneNumber', $data)` right there. That's going
to be enough to make this test to fail because right now we are returning yet. So
I'll copy the `testGetUser` method name and let's just try that up. 

```terminal
php bin/phpunit --filter=testGetUser
```

and it, let's see. Yep, it fails, failed
asserting that array does not have the key `phoneNumber`. It is returning their phone
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