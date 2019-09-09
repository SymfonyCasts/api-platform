# Auto-set the Owner: Entity Listener

We decided to make the `owner` property a field that an API client *must* send
when creating a `CheeseListing`. That gives us some flexibility: an admin user
can send this field set to *any* User, which might be handy for some future admin
section. To make sure the `owner` is valid, we've added a custom validator that
even has an edge-case that allows admin users to do this.

But the *normal* use case is when a normal user just wants to create a new
`CheeseListing` under their *own* account. To do this, they're *forced* to
pass the `owner` field set to their *own* IRI. That's perfectly explicit and
straightforward. But couldn't we make life easier by automatically setting the
`owner` to the currently-authenticated user if that field isn't sent?

Let's try it! And first we'll do it in our test... which will be a *tiny* change.
When we send a `POST` request to `/api/cheeses` with `title`, `description`
and `price` fields, we're expecting this to return a `400` error because we're
missing the `owner` field. Let's change that to expect a `201` status code.
Once we finish this feature, only sending `title`, `description` and `price`
*will* work.

To start, take off the `NotBlank` constraint from `$owner` - we definitely don't
want to require that anymore.

If we run the tests now...

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

Yep! It fails... we're getting a 500 error because it's trying to insert into
`cheese_listing` with an `owner_id` that is `null`.

## How to Automatically set the Field?

So, how *can* we automatically set the `owner` on a `CheeseListing` if it's
not already set? Well, there are a few options - like an API Platform event listener,
an API Platform data persister or a Doctrine event listener. The first two - an
API Platform event or data persister - have the same possible downside: the owner
would only be automatically set when a `CheeseListing` is created through the
API. Depending on what you're trying to accomplish, that might be *exactly*
what you want. But... in general... if I save a `CheeseListing`... no matter
if it's being saved as part of an API call or via something else in my system,
and the `owner` is null, I think automatically setting the `owner` makes sense.
So, instead of making this feature *only* work for our API endpoints, let's use
a Doctrine event listener and make it work *everywhere*.

## Event Listener vs Entity Listener

To set this via Doctrine, we can create an event listener *or* an "entity" listener...
which are basically two, effectively *identical* ways to run some code before
or after an entity is saved, updated or deleted. We'll use an "entity" listener.

In the `src/` directory, create a `Doctrine/` directory... though, like usual, the
name of the directories and classes doesn't matter. Add anew class called, how
about, `CheeseListingSetOwnerListener`. This will be an "entity listener": a class
with one more more functions that Doctrine will call before or after certain things
happen to a specific entity. In our case, we want to run some code *before* a
`CheeseListing` is created. That's called "pre persist" in Doctrine. Add a
`public function prePersist()` with a `CheeseListing` argument.

Two things about this. First, the name of this method *is* important: Doctrine
will look at all the public functions in this class and use the *names* to determine
which should be called when. Calling this `prePersist()` will mean that Doctrine
will call us *before* persisting - i.e. inserting - a `CheeseListing`. You can
also add other methods like `postPersist()`, `preUpdate()` or `preRemove()`.

## @ORM\EntityListeners Annotation

Second, this method will *only* be called when a `CheeseListing` is being saved
or removed. But that won't happen magically thanks to the type-hint. Nope, to
hook this all up we need to go into our `CheeseListing` entity and, at the top,
add a new annotation. Actually... let's reorganize the annotations first... and
move `@ORM\Entity` to the bottom so it's not mixing in the middle of all the
API Platform stuff. Now add `@ORM\EntityListeners()`, pass an an array and then
put the *full* class name of the entity listener class: `App\Doctrine\`... and
then I'll get lazy and copy the class name: `CheeseListingSetOwnerListener`.

That's it for the basic setup! Thanks to this annotation and the method being
called `prePersist()`, Doctrine will automatically call this method *before*
it persists - meaning *inserts* - a new `CheeseListing`.

## Entity Listener Logic

Now... the logic for setting the owner is pretty simple! To find the
currently-authenticated user, add an `__construct()` method, type-hint the
`Security` service and then press Alt + Enter and select "Initialize fields" to
create that property and set it.

Now, inside the method, start by seeing if the owner was already set: if
`$cheeseListing->getOwner()`, just return: we don't want to override that.

Now, if `$this->security->getUser()` - so *if* there is a currently-authenticated
`User`, then `$cheeseListing->setOwner($this->security->getUser())`.

That's it! Go tests go!

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

And... it passes! I'm getting - that *exploded*. Hmm, it says:

it's doesn't work. It's Kinda close. Look here it says

> Too few arguments to `CheeseListingSetOwnerListener::__construct()` 0 passed.

Huh. Who's instantiating that class? Generally-speaking, in Symfony, we expect
any "service class" - any class that's *not* a simple data-holding object like
our entities - to be instantiated by Symfony's container. But... if you look at
the stack trace, it looks like Doctrine *itself* is trying to instantiate it.
That's important: Symfony's container is responsible for all the autowiring
magic. So... why is Doctrine trying to create this object *instead* of asking
the *container* for it?

## Tagging the Service

The answer is... that's... sort of... just how it works. Um, ok, better explanation.
When used as an independent library, Doctrine typically handles instantiating
these "entity listener" classes itself. However, when integrated with Symfony,
you *can* tell Doctrine to *instead* fetch that service from the container. But,
you need a *little* bit of extra config.

Open `config/services.yaml` and override the automatically-registered service
definition: `App\Doctrine\` and go grab the `CheeseListingSetOwnerListener`
class again. We're doing this so we can add a *little* bit of *extra* service
configuration. Specifically, we need to add a *tag* to this. Add `tags`, then
`doctrine.orm.entity_listener`.

That's enough to basically say:

> Hey Doctrine! This is an *entity listener*. So when you need the
> CheeseListingSetOwnerListener object, use *this* service instead of trying
> to instantiate it yourself.

And once *that* happens, Symfony should autowire that constructor argument. Try
the test one last time:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

And... got it! We've got the best of all worlds! The flexibility for an API
client to *send* the `owner` property, validation when they do, and an automatic
fallback if they don't.

Next, let's talk about the last big piece of access control: filtering a collection
result to only the items that an API client should see. For example, when we make
a `GET` request to `/api/cheeses`, we should probably *not* return *unpublished*
cheese listings... unless you're an admin.
