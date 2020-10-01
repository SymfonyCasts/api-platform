# Doctrine postLoad Listener

Let's create *another* custom field on `User`. I know, we're getting crazy, but
I want to *keep* seeing how far we can push API Platform's abilities.

This new field will be *similar* to `isMe`... but *just* different enough
that it will lead us to an *alternate* solution. Suppose some users are
MVPs... but being an MVP isn't as simple as having a boolean `isMvp` column in the
database. Nope, we need to use a super fancy calculation that requires a service
to determine if a `User` is an MVP.

So here's our goal: expose a new `isMvp` field to our API whose value will
be populated via custom logic in a service.

## Creating the isMvp Property

To start, you know the drill, we're going to add this as a *real* property in
our entity but *not* persist it to Doctrine. Call it `$isMvp` and let's add
some documentation that can be used by the API docs:

> Returns true if this user is an MVP

[[[ code('6815400f44') ]]]

Next, down at the bottom, go to "Code"->"Generate" - or `Command`+`N` on a Mac - and
generate the getter and setter:

[[[ code('4c6672a845') ]]]

But rename the method to `getIsMvp()`:

[[[ code('4143d9d587') ]]]

The property-access component *does* support looking for "isser" methods... but
for that to work, the property would have to be called just `mvp` to look for
an `isMvp()` method. Because our *property* is called `isMvp`, we need a
`getIsMvp()` method.

*Anyways*, now if we move over and go to `/api`, we're hoping that the new field
will already be in the docs. If you look under the user endpoint and go to
schema... boom! An `isMvp` boolean field.

## Testing the Behavior

Of course, that field will *always* be `false` because we're *never* setting it.
*That's* our next job. Well, let's start by describing the behavior in a test.

In our app, a user will be an MVP if their username - which is a field on `User` -
contains the word "cheese":

[[[ code('9b68e6b8f2') ]]]

Okay, in reality, we could accomplish this with just a custom getter method
that looks for `cheese` in the `$username` property and returns true or false.
We don't *actually* need a service... but let's pretend we do.

In `UserResourceTest`, scroll down to `testGetUser()`:

[[[ code('0f48307a48') ]]]

When we create this user, we pass it a `phoneNumber`, but the rest of the fields
come from the `UserFactory` class - from the `getDefaults()` method:

[[[ code('a41d26d118') ]]]

Yep, it's using a random `username` from Faker.

Now, let's be a bit more specific: pass `username` set to `cheesehead`:

[[[ code('c665b38022') ]]]

A few lines later, we make a request to GET this user, assert that the response
is 200 and check for the `username` in `assertJsonContains()`. Let's *also* check
for `isMvp` set to `true`:

[[[ code('059446cabb') ]]]

That's good enough for now! Copy the method name and run that test:

```terminal
symfony php bin/phpunit --filter=testGetUser
```

And... it fails! Woo!

## Creating the Entity Listener

To set this field, we already know two solutions: a custom data provider or an
event listener that sets the field early in the request.

But neither of those options will set this value *everywhere*. The data provider
is only used for API requests and the event subscriber won't set the field
inside the CLI - like when running a command.

And maybe that's okay! But this *last* solution will work everywhere: a Doctrine
"postLoad" listener: a function that is called *each* time an entity is loaded
from the database.

The only *downside* to this solution is that the `postLoad` listener is *always*
called when you query for an object... even if you'll never use the `isMvp`
field. Though... I'll show you a trick to get around this.

To create a Doctrine listener, well, you can technically create a Doctrine event
subscriber, listener or something called an entity listener... and they all
basically work the same.

In part 2 of this series, we created an entity listener... and I kind of like
those. In `src/Doctrine`, we created this entity listener to automatically set
the `owner` field to the currently authenticated `User` for cheese listings:

[[[ code('fb6ddf32ff') ]]]

Anyways, create a new PHP class and call it `UserSetIsMvpListener`:

[[[ code('229425a788') ]]]

Listeners don't extend anything: the only rule is that you need to have a public
function with the name of the event that you want to listen to. So
`postLoad()` with a `User $user` argument since we'll hook this up as an entity
listener for *that* class:

[[[ code('2bea29be52') ]]]

We'll do that in a minute.

First, let's finish the logic: `$user->setIsMvp()` with `strpos()` to
look inside of `$user->getUsername()` for the string `cheese`. If this is
not false, then this user *is* an MVP:

[[[ code('feb1a4eef5') ]]]

## LazyString for Lazy Computation

Since this method will be called *every* time *any* `User` object is loaded from
the database, we need to make sure that any logic we run here is *fast*, especially
because the calculated field might not even be needed in all situations! So... if
your logic *isn't* super fast... does that mean you can't use a `postLoad` listener?

Not necessarily. Another option is to create a `LazyString`. That's... literally
a class inside of Symfony that helps you create strings that won't be evaluated
until later - *if* and *when* they are needed. Try `LazyString::fromCallable()` as
a neat way to make this custom field lazy:

```php
$bio = LazyString::fromCallable(function() {
    // heavy calculations

    return $complexBio;
});

$user->setBio($bio);

// ---

class User
{
    public function setBio(\Stringable $bio)
    {
        $this->bio = $bio;
    }
}
```

## Registering the Entity Listener

*Anyways*, entity listeners are *not* one of the situations where Symfony will
magically find our class and start calling the `postLoad()` method. Nope, we
need to *tell* Doctrine about this with some config. First, inside `User`,
all the way on top, add `@ORM\EntityListeners()` and pass this an array
with our listener class `UserSetIsMvpListener::class`:

[[[ code('203b2d952c') ]]]

I also need to add the `use` statement manually because PhpStorm doesn't auto-complete
it. Add `use App\Doctrine\UserSetIsMvpListener;`:

[[[ code('7a92d5b289') ]]]

As soon as I do that, I can hold `Cmd` or `Ctrl` and PhpStorm *does* recognize the
class.

Finally, we need a tiny bit of service configuration to get this working, which is
kind of annoying but necessary. Open `config/services.yaml`... copy the entity
listener from the last tutorial, paste and change the name to `UserSetIsMvpListener`:

[[[ code('d1d83bff46') ]]]

Phew! With any luck, that *should* be enough for our function to be called every
time a user is loaded from the database. Try the test:

```terminal-silent
symfony php bin/phpunit --filter=testGetUser
```

We got it! So that is yet *another* way to populate a custom field when your
API resource is an entity class.

But what if you need an API resource that is *completely* custom: like its data
comes from a *bunch* of different database tables or... it comes from somewhere
else entirely? Yep, it's time to create a 100% custom, non-entity API resource.
