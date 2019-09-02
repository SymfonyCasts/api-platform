# Custom Normalizer

Coming soon...

We now know how to add dynamic groups. We added this `admin:read`
group have a phone number is only added. It's dynamically added via our context
builder if the users and Admin. So if the user has `ROLE_ADMIN`, we always add either
`admin:read`, `admin:write` So now I have a really cool way to run around and hide or
include fields or not include fields based on that admin role. But what about, but
one of the shortcomings of a context builder is that it's global. It doesn't allow us
to change groups on an object. So what if but there's a limitation in the context
builder.

but the `$phoneNumber` field, what if we also wanted the owner of this user, the user
itself to be able to see the phone number but nobody else wants to do that. We could,
you might think, okay, well let's add another group here

called `owner:reed`. You say, okay, well we'll do as well. We'll add this group
dynamically. Um, if the current user is trying to, if the a is trying to read this
object. But the problem is if you look in the context builder, we're not past which
object we're currently working with.

Complex builders are not the way to dynamically add or remove groups on an object by
object basis. They're just a way to do it on it, on a global or a class by class
basis. So to do this, we need another strategy and we, what we need is a custom
normalizer. So check this out, let's go over and we'll use 

```terminal
php bin/console make:serializer:normalizer
```

Let's create one called `UserNormalizer`. So the idea is that whenever in
user object is changed and JSON, it goes through a normalizer and there's already a
core normalizer that takes care of that, we're now going to hook into that process so
that we can change the groups that are being used for normalization.

So if checkout `src/Serializer/Normalizer`, we have our new `UserNormalizer` and this
works similar to the context builder. We have a method down here called 
`supportsNormalization()`. In this, to put this time, we're actually past these specific object
that's being normalized. If we return `true` from `supportsNormalization()`, it says that
we normalize this object. And so then we the normalization logic inside of `normalize()`.
And notice the way it's set up now is where we've actually, um, we're autowiring the
`ObjectNormalizer` that's the kind of the main normalizer that's used in the
serializer. And so right now this normalizer is basically just offloading all the
work to it. So in our case, let's make data, uh, for `supportsNormalization()` or return
`$data` instance of `User`. So if the users being s normalized, um, we should be called
then when this returns `true` it we'll call `normalize()`. So we know the only time
normalize is going to be called is if `$object` is the instance of `User`. So let's
actually add some PHP doc above this that says that object will be a user. And then
on top above this with you, something like if `$this->userIsOwner()`, that's a method
we'll create in a second. We'll pass this `$object`

`$context` cause no surpassed B context. We can modify the context. So love 
`['groups']` and then we will add to that `owner:read` in this case, this is a
`UserNormalizer`. It's not used for de normalization. So we can always use `owner:read`
I'm also assuming there's a group's key here already because we've built it in
our system. We're always specifying groups. So that key will already be initialized
for the select `userIsOwner()`. I'll go down here and we'll just make a 
`private function userIsOwner()`. That takes a user object, it turns a `bool`. And just for now
I'm gonna just return `rand(0, 10) > 5`. We'll put some real security
logic in here, but for now we'll just, uh, return randomly.

cool. And that's it. This is one of those cases where just by implementing normalize
your interface, a API platform is going to see our normalizer and start using it. So
I've just been over here right now I am anonymous, so that actually means I don't
even have access to a fetch the user objects. So let's go back to my front end here
and we'll log in as `quesolover@example.com` password `foo`. This is just a, a user I
created earlier.

So let's refresh this page. I'm actually going to insert a new user into the database
just to make sure. So I'll say a

`goudadude@example.com` password `foo`. Good. It is, no has listings and yeah, let's put a
`phoneNumber`, execute and perfect two oh one so I'm going to copy that `email` so we
can immediately go log in as that user. So I'll go to our front end log in as 
`goudadude@example.com` and password `foo`. Cool. We're authenticated and if I go back
to `/api`, you can see down here we are authenticated. So if we get the collection of
users right now execute. You can see down here the first user, there is no 
`phoneNumber`, second user, there's no `phoneNumber` and the third user has no `phoneNumber`.
So let's try that again. That could've just been by chance. If I try it again. Yes.
Now you can see phone numbers showed up on two of them. So `phoneNumber` is randomly
showing up. This is great.

So by using a normalizer, we are very easily able to add dynamic groups so that we
can hide or show fields on an object by object basis. And we can do the same thing
for a d normalize it. There's not a make serializer d normalizer you'd have to write
it by hand, but it's the same process, except if you look closely, something bad just
happened. Okay. We're missing the JSON LD information from our users. Like, okay.
Yeah. We have the JSON LD stuff on top `@id` and even the, uh, the cheese listings
here has them. But before a second ago, each individual user had `@id` on it and also
an `@type`. So if someone actually killed our JSON LD information from this, let's
talk about why next. Learn more about normalizes and fix it.