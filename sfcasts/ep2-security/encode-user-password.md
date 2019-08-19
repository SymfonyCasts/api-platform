# Encode User Password

Coming soon...

As a few people have already noticed and we actually talked about a little bit
earlier, our `POST` endpoint for `/api/users` doesn't really work well yet. The biggest
problem is that the a password field that we need to pass to it a isn't the plain tax
pass for like food. We actually need to put in the fully encoded password which makes
no sense. Obviously we are not expecting people to use our API and pass us a pre
hashed password. That's completely ridiculous. But the question is how do we fix
this? You know, cause we know the deserialization process just takes these `email`
`password` and `username` fields and just deserializes them and calls our centers on
here. You know like `setPassword()`, `setUsername()` and `setEmail()` and the way that you
encode passwords and Symfonys, you need a service and we don't have access to you
services inside of our entity.

What we need is a way to sort of intercept this process. You know like when one B
between the time that the user object is deserealizes and it's saved to the database
and we need some hook where we can actually encode the plain password into the final
password. One of the ways you can do this as just doctrine events, you know where
this is, this is being saved to doctrine. So you could use a doctrine event to go with
the password. Uh, I'm going to do a different way however that actually is uses some
API platform, a specific functionality. So first thing I'm actually going to do is
let's actually write a test for this so we can describe exactly how we want this end
point to look. So in my `test/Functional/` directory, I'm going to create a 
`UserResourceTest` and we'll make this extend our nice `CustomApiTestCase`. While it's
going to, you use my `ReloadDatabaseTrait` that are reloads, it will say 
`public function testCreateUser()`. Perfect. We'll start at the same way as normal
`$client = self::createClient()`.

And then we don't need any database set up. We don't need to create a user or
anything like that. So we can go straight to the `$client->request()` to make a `POST`
request to `/api/users` and of course we're going to need to pass some JSON Data. So
I'll pass the `json` Key. And in our case the fields that we're going to need a pass
here. If you look at our documentation, our `email`, `password` and `username`, those are
the three required fields. So let's do `email` set to `cheeseplease@example.com`
`username` set, two `cheeseplease`.

And the big difference here is `password`. We're not going to set the encoded password.
We're going to this to what we actually want to pass pastor to beat `brie`. And then
finally down here. So this is a post end point to create a resource. After you create
a resource, you should expect a `201` status code as success. So we'll say 
`$this->assertResponseStatusCodeSame(201)`  and then to make things to really make
sure that the password actually encoded correctly, it didn't just save to the
database like this. We'll say this error, walk in and try and make sure that we can
log in as `cheeseplease@example.com` password `brie`. So remember built into that
`logIn()` shortcut. Look at that as a assertion to make sure that that act log in is
actually successful. Perfect. So on a copy of that `testCreateUser()` method name,
we'll go over my 

```terminal
php bin/phpunit --filter=testCreateUser
```

and perfect
that fails. So you can see it fails when it tries to log in as the user

> Invalid credentials. 

All right, so let's get to work on here. As you notice here, we
want the password field actually called a password. However, the password property on
our user object is meant to be the Incode password, not the plaintext password. We
could use it for both. We could temporarily store the plaintext password on the
password field, but then encoded before the saves the database. But I don't love to
do that just because it feels dirty to me. And if something went wrong, we might
accidentally start storing plain text passers the database, which is definitely not a
good idea. So instead I'm going to find my password property up here and I'm gonna
create a new property below it called `$plainPassword`. And I don't expose this with
the `@Groups({"user:write"})` So run, expose it just like we
did up here for our password field. And then we will stop exposing our password
field. And yes, this is going to be in the field temporarily, as in with all plain
password, but we'll fix that in a second. Now I'm going to go to 
Code -> Generate menu, or Command+N on a Mac and go and get her in center 
and we'll populate the getter and
setter methods for that. Oh, except that I don't want those up here. I God, I want
those to go all the way down here at the bottom.

And then I'll customize these a little bit. So this is actually gonna return a
 `?string` and then I'll get a type end on the plain password and I'll say this
will return `self` because most of my centers, all my centers have a `return $this` on the
end.

Perfect. So we've now created this new `$plainPassword` field. It's part of my API
password is no longer part of my API. And you could see this if you move over and
refresh right now and look at our documentation for our post point. Yeah, of course.
To the point now has a plain password field. And before we actually get into how we
encode the plain password and the password is one more thing I'm going to do in here.
If you scroll down, eventually you find an `eraseCredentials()` method. This is
something that the `UserInterface` forces us to have. And the idea here is that
anytime your system is about to maybe persist a user or serialize the user to the
session `eraseCredentials()` is automatically called. And it's your opportunity to make
sure that any, uh, sensitive information on the user isn't, uh, is, is cleared off.

Basically it's your way of making sure that the `$plainPassword` is set back to `null`.
In our case, it's to make sure that that the plain password isn't serialized at the
session. It's just an extra security mechanism. It's not really that important. Just
do it. All right. So if we tried our end point right now, we could set this plain
password property, but there's nothing that's serializing that and storing it on the
password field. So we'd ultimately explode in the database because our `$password` field
is going to be `null`. So how can we kind of do something before our a cheat? Our user
is safe. The database and the answer is with an API platform data per sister. So if
you have platform, right? A, it comes with a data position, comes with a doctrine
data per sister. So whenever doctrine finishes a deserealizing the object, it says,
okay, I need to store this. I need to store this somewhere because it realizes that
our `User` and our `CheeseListing` are doctrine entities. It passes it to the doctrine
persister and that persister knows how to call `persist()` and `flush()` on the 
`EntityManager`. So the cool thing is, is we can put our own data persisters into this system
and those data publishers have a hook allow us to hook into the saving process so
that we can do something different. So check us out in the `src/` directory. It
doesn't matter where, but I'll create a `DataPersister/` directory.

Okay. And a new data persister called `UserDataPersister`. This is going to be a
class that just responsible for kind of a, just for that per, for persisting `User`
objects. All data positioners must implement `DataPersisterInterface`
Next I'll go to Code -> Generate menu, or Command+N on a Mac

Go to "Implement Methods" and then I'll select the three methods that this needs to
happen. So here's how this works. As soon as we implement this `DataPersisterInterface`
whenever doctrine is cit persisting an object, it's now going to be aware
of our data persister it then loops over all of the data persisters and calls,
`supports()` and passes at the object. That's about to be saved. So in our case, if the
data is a `User` object, we do support this and we do want to save it. So we can say
`return $data instanceof User`.

As soon as API platform finds the first data per sister that who supports returns.
`true` it calls `persist()` and calls none of the other data persisters. Now that doctrine
data persister I talked about in core, it has a very low priority on the data per
sister. So it's always asked last. So if we create a data persister and return true
from supports, our data persistence is going to be called instead of doctrines data
persistent. So forgetting about encoding the uh, password, the first thing we need to
do is actually make sure that our data processor saves this thing to the database.

So for that we're going to need the entity manager. So I'll do 
`public function __construct()` and we'll type hint, the `EntityManagerInterface $entityManager`
into this class. I'll hit Alt + Enter, go to Initialize fields to create
that property and set it. And then down here it's pretty simple. It's going to be
`$this->entityManager()->persist($data)`, and then `$this->entityManager->flush()`. And
then same thing down here for `remove()`. This would be called them the delete endpoint.
`$this->entityManager->remove($data)` and then `$this->entityManager->flush()`. So we
now basically we now have a data persister that basically is identical to the core
doctrine data persistent, but now we can take in persist, we can encode that
password. So to do that, first thing we're gonna need is a to pass in the password
encoder.

If you went to a 

```terminal
php bin/console debug:autowiring pass
```

, you'd see
that the type in for this is `UserPasswordEncoderInterface`. So let's say 
`UserPasswordEncoderInterface $userPasswordEncoder`, and will the Alt + Enter again
Initialize fields to create that property and set it. Now down here in persist, we
know that data is going to be an instance of our user object because that's the only
time our `supports()` method returns. `true`. I'm going to add a little PHPdoc
above this just to help my editor say, hey, this is a `User` object. And then inside of
persist, the first thing I'll do is say if `$data->getPlainPassword()`. So if the 
`$planPassword` field is set, then we want to encode it cause it might not always be set. If
you think of an edit endpoint, if I don't pass a `plainPassword`, it just means that I
don't want to update my password.

So if there's no `plainPassword`, we should do nothing. So if `$data->getPlainPassword()`
 then `$data->setPassword()` will set the password field to 
`$this->userPasswordEncoder->encodePassword()` passing it. The object that is being that we're going
to set the password on. So that's actually `$data`. It's our `User` object and then the
plain password itself, which is `$data->getPlainPassword()` and that's it. And
just be extra safe down here. I'll call `$data->eraseCredentials()` and we don't need
that pass for that `plainPassword` anymore. So we'll make sure that it's not on there.
All right, and that should be it. Then the only last detail here is that our field is
still called a `plainPassword`, and in our test, you know it would really be better if
it was called `password`, but we already know how to fix that in the `User` class. If you
find the plain password field, we can say `@SerializedName()` and call it `password`.
All right. When we do that and refresh our documentation and look at the post
endpoint, yes, it looks like we expect, so let's go see if the test pass right in 

```terminal
php bin/phpunit --filter=testCreateUser
```

and I scroll up. Yes, we've got it.
So now our API has properly encoding the password and you have a really nice way a
data persister to hook in and do something special before you user is saves the
database.