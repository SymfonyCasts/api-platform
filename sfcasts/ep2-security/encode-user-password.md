# Encode User Password

Coming soon...

As a few people have already noticed and we actually talked about a little bit
earlier, our post endpoint for /apss users doesn't really work well yet. The biggest
problem is that the a password field that we need to pass to it a isn't the plain tax
pass for like food. We actually need to put in the fully encoded password which makes
no sense. Obviously we are not expecting people to use our API and pass us a pre
hashed password. That's completely ridiculous. But the question is how do we fix
this? You know, cause we know the d d serialization process just takes these email
password and username fields and just d serializes them and calls our centers on
here. You know like set password, set username and said email and the way that you
encode passwords and Symfonys, you need a service and we don't have access to you
services inside of our entity.

What we need is a way to sort of intercept this process. You know like when one B
between the time that the user object is DC realized and it's saved to the database
and we need some hook where we can actually encode the plain password into the final
password. One of the ways you can do this as just doctrine events, you know where
this is, this is being saved to doctors. So you could use a doctrine event to go with
the password. Uh, I'm going to do a different way however that actually is uses some
API platform, a specific functionality. So first thing I'm actually going to do is
let's actually write a test for this so we can describe exactly how we want this end
point to look. So in my test functional directory, I'm going to create a user
resource test and we'll make this extend our nice custom API test case. While it's
going to, you use my reload at database traits that are reloads, it will say public
function. That test create a user. Perfect. We'll start at the same way as normal
client = self call and call and create client.

And then we don't need any database set up. We don't need to create a user or
anything like that. So we can go straight to the client error request to make a post
reclass to /API /users and of course we're going to need to pass some JSON Data. So
I'll pass the JSON Key. And in our case the fields that we're going to need a pass
here. If you look at our documentation, our email, password and username, those are
the three required fields. So let's do email set to cheese please. At example.com
username set, two cheese please.

And the big difference here is password. We're not going to set the encoded password.
We're going to this to what we actually want to pass pastor to beat Bree. And then
finally down here. So this is a post end point to create a resource. After you create
a resource, you should expect a two Oh one status code as success. So we'll say this,
assert response status code same as two oh one and then to make things to really make
sure that the password actually encoded correctly, it didn't just save to the
database like this. We'll say this error, walk in and try and make sure that we can
log in as cheese. please@example.com password three. So remember built into that
login shortcut. Look at that as a assertion to make sure that that act log in is
actually successful. Perfect. So on a copy of that test to create user method name,
we'll go over my PHP bin /PHP unit, batch jest filter = test, create user and perfect
that fails. So you can see it fails when it tries to log in as the user

invalid credentials. All right, so let's get to work on here. As you notice here, we
want the password field actually called a password. However, the password property on
our user object is meant to be the Incode password, not the plaintext password. We
could use it for both. We could temporarily store the plaintext password on the
password field, but then encoded before the saves the database. But I don't love to
do that just because it feels dirty to me. And if something went wrong, we might
accidentally start storing plain text passers the database, which is definitely not a
good idea. So instead I'm going to find my password property up here and I'm gonna
create a new property below it called plain password. And I don't expose this with
the app groups. Um, user, colon, user, Colon, right? So run, expose it just like we
did up here for our password field. And then we will stop exposing our password
field. And yes, this is going to be in the field temporarily, as in with all plain
password, but we'll fix that in a second. Now I'm going to go to code generate or
Command + N on a Mac and go and get her in center and we'll populate the getter and
setter methods for that. Oh, except that I don't want those up here. I God, I want
those to go all the way down here at the bottom.

And then I'll customize these a little bit. So this is actually gonna return a
nullable string and then I'll get a type end on the plain password and I'll say this
will return self because most of my centers, all my centers have a return this on the
end.

Perfect. So we've now created this new plain password field. It's part of my API
password is no longer part of my API. And you could see this if you move over and
refresh right now and look at our documentation for our post point. Yeah, of course.
To the point now has a plain password field. And before we actually get into how we
encode the plain password and the password is one more thing I'm going to do in here.
If you scroll down, eventually you find an erase credentials method. This is
something that the user interface forces us to have. And the idea here is that
anytime your system is about to maybe persist a user or serialize the user to the
session or res credentials is automatically called. And it's your opportunity to make
sure that any, uh, sensitive information on the user isn't, uh, is, is cleared off.

Basically it's your way of making sure that the plain password is set back to null.
In our case, it's to make sure that that the plain password isn't serialized at the
session. It's just an extra security mechanism. It's not really that important. Just
do it. All right. So if we tried our end point right now, we could set this plain
password property, but there's nothing that's serializing that and storing it on the
password field. So we'd ultimately explode in the database because our password field
is going to be not. So how can we kind of do something before our a cheat? Our user
is safe. The database and the answer is with an AK platform data per sister. So if
you have platform, right? A, it comes with a data position, comes with a doctrine
data per sister. So whenever doctrine finishes a DC realizing the object, it says,
okay, I need to store this. I need to store this somewhere because it realizes that
our user and our cheese listing are doctrine entities. It passes it to the doctrine
persister and that persister knows how to call persist and flush on the entity
manager. So the cool thing is, is we can put our own data persisters into this system
and those data publishers have a hook allow us to hook into the saving process so
that we can do something different. So check us out in the source directory. It
doesn't matter where, but I'll create a data persister directory.

Okay. And a new data persister called user data per sister. This is going to be a
class that just responsible for kind of a, just for that per, for persisting user
objects. All data positioners must implement user data. Okay. Data persister
interface. Next I'll go to code generate or command and, and a Mac.

Okay.

Go to "Implement Methods" and then I'll select the three methods that this needs to
happen. So here's how this works. As soon as we implement this data persister
interface, whenever doctrine is cit persisting an object, it's now going to be aware
of our data persister it then loops over all of the data persisters and calls,
supports and passes at the object. That's about to be saved. So in our case, if the
data is a user object, we do support this and we do want to save it. So we can say
return data. Instance of user.

As soon as API platform finds the first data per sister that who supports returns.
True it calls persist and calls none of the other data persisters. Now that doctrine
data persister I talked about in core, it has a very low priority on the data per
sister. So it's always asked last. So if we create a data persister and return true
from supports, our data persistence is going to be called instead of doctrines data
persistent. So forgetting about encoding the uh, password, the first thing we need to
do is actually make sure that our data processor saves this thing to the database.

So for that we're going to need the entity manager. So I'll do public function
underscore,_construct and we'll type hint, the entity manager, interface entity
manager into this class. I'll hit all to enter, go to initialize fields to create
that property and set it. And then down here it's pretty simple. It's going to be
this->entity manager error persist data, and then this->entity manager->flush. And
then same thing down here for remove. This would be called them the delete endpoint.
This error entity manager->remove data and then this->into manager air flush. So we
now basically we now have a data persister that basically is identical to the core
doctrine data persistent, but now we can take in persist, we can encode that
password. So to do that, first thing we're gonna need is a to pass in the password
encoder.

If you went to a bend in console, debug auto wiring and searched for pass, you'd see
that the type in for this is user password encoder interface. So let's say user
password, encoder interface, user password encoder, and will the alt enter again
initialize fields to create that property and set it. Now down here in persist, we
know that data is going to be an instance of our user object because that's the only
time our supports method returns. True. I'm going to add a little piece of wood up
above this just to help my editor say, hey, this is a user object. And then inside of
persist, the first thing I'll do is say if data->get plain passwords. So if the plan
password field is set, then we want to encode it cause it might not always be set. If
you think of an edit endpoint, if I don't pass a plain password, it just means that I
don't want to update my password.

So if there's no plain password, we should do nothing. So if data->get plain
password, then data->set password will set the password field to this->user password
encoder error in code password passing it. The object that is being that we're going
to set the password on. So that's actually data. It's our user object and then the
plain password itself, which is data Arrow, get plain password and that's it. And
just be extra safe down here. I'll call data = erase credentials and we don't need
that pass for that plane bastard anymore. So we'll make sure that it's not on there.
All right, and that should be it. Then the only last detail here is that our field is
still called a plain password, and in our test, you know it would really be better if
it was called password, but we already know how to fix that in the user class. If you
find the plain password field, we can say at serialized name and call it password.
All right. When we do that and refresh our documentation and look at the post
endpoint, yes, it looks like we expect, so let's go see if the test pass right in PHP
bin /PHP unit dash dash filter = test create user and I scroll up. Yes, we've got it.
So now our API has properly encoding the password and you have a really nice way a
data persister to hook in and do something special before you user is saves the
database.