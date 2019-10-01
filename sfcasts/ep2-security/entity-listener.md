# Auto-set the Owner: Entity Listener

We decided to make the `owner` property a field that an API client *must* send
when creating a `CheeseListing`. That gives us some flexibility: an admin user
can send this field set to *any* User, which might be handy for a future admin
section. To make sure the `owner` is valid, we've added a custom validator that
even has an edge-case that allows admin users to do this.

But the most *common* use-case - when a normal user wants to create a
`CheeseListing` under their *own* account - is a bit annoying: they're *forced*
to pass the `owner` field... but it must be set to their *own* user's IRI.
That's perfectly explicit and straightforward. But... couldn't we make life easier
by automatically setting `owner` to the currently-authenticated user if that
field isn't sent?

Let's try it... but start by doing this in our test... which will be a *tiny* change.
When we send a `POST` request to `/api/cheeses` with `title`, `description`
and `price`, we expect it to return a `400` error because we forgot to send the
`owner` field. Let's change this to expect a `201` status code. Once we finish
this feature, only sending `title`, `description` and `price` *will* work.

[[[ code('2388c8ccd8') ]]]

To start, take off the `NotBlank` constraint from `$owner` - we definitely don't
want it to be required anymore.

[[[ code('2acd595fb8') ]]]

If we run the tests now...

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

Yep! It fails... we're getting a 500 error because it's trying to insert into
`cheese_listing` with an `owner_id` that is `null`.

## How to Automatically set the Field?

So, how *can* we automatically set the `owner` on a `CheeseListing` if it's
not already set? We have a few options! Which in programming... is almost never
a good thing. Hmm. Don't worry, I'll tell you which one *I* would use and why.

Our options include an API Platform event listener - a topic we haven't talked
about yet - an API Platform data persister or a Doctrine event listener. The first
two - an API Platform event listener or data persister - have the same possible
downside: the owner would only be automatically set when a `CheeseListing` is
created through the API. Depending on what you're trying to accomplish, that
might be *exactly* what you want - you may want this magic to *only* affect your
API operations.

But... in general... if I save a `CheeseListing` - no matter if it's being saved
as part of an API call or in some *other* part of my system - and the `owner` is
null, I think automatically setting the `owner` makes sense. So, instead of
making this feature *only* work for our API endpoints, let's use a Doctrine
event listener and make it work *everywhere*.

## Event Listener vs Entity Listener

To set this via Doctrine, we can create an event listener *or* an "entity" listener...
which are basically two, effectively *identical* ways to run some code before
or after an entity is saved, updated or deleted. We'll use an "entity" listener.

In the `src/` directory, create a `Doctrine/` directory... though, like usual, the
name of the directory and class doesn't matter. Put a new class inside called, how
about, `CheeseListingSetOwnerListener`. This will be an "entity listener": a class
with one or more functions that Doctrine will call before or after certain things
happen to a specific entity. In our case, we want to run some code *before* a
`CheeseListing` is created. That's called "pre persist" in Doctrine. Add
`public function prePersist()` with a `CheeseListing` argument.

[[[ code('aa74adc28f') ]]]

Two things about this. First, the name of this method *is* important: Doctrine
will look at all the public functions in this class and use the *names* to determine
which methods should be called when. Calling this `prePersist()` will mean
that Doctrine will call us *before* persisting - i.e. inserting - a `CheeseListing`.
You can also add other methods like `postPersist()`, `preUpdate()` or `preRemove()`.

## @ORM\EntityListeners Annotation

Second, this method will *only* be called when a `CheeseListing` is being saved.
How does Doctrine know to only call this entity listener for cheese listings?
Well, it doesn't happen magically thanks to the type-hint. Nope, to hook all of
this up, we need to add some config to the `CheeseListing` entity. At the top,
add a new annotation. Actually... let's reorganize the annotations first... and
move `@ORM\Entity` to the bottom... so it's not mixed up in the middle of all the
API Platform stuff. Now add `@ORM\EntityListeners()` and pass this an array with
one item inside: the *full* class name of the entity listener class:
`App\Doctrine\`... and then I'll get lazy and copy the class name:
`CheeseListingSetOwnerListener`.

[[[ code('67215347cc') ]]]

That's it for the basic setup! Thanks to this annotation and the method being
called `prePersist()`, Doctrine will automatically call this *before* it
persists - meaning *inserts* - a new `CheeseListing`.

## Entity Listener Logic

The logic for *setting* the owner is pretty simple! To find the
currently-authenticated user, add an `__construct()` method, type-hint the
`Security` service and then press Alt + Enter and select "Initialize fields" to
create that property and set it.

[[[ code('88aa1d2d23') ]]]

Next, inside the method, start by seeing if the owner was already set: if
`$cheeseListing->getOwner()`, just return: we don't want to override that.

[[[ code('29cbd58019') ]]]

Then if `$this->security->getUser()` - so *if* there is a currently-authenticated
`User`, call `$cheeseListing->setOwner($this->security->getUser())`.

[[[ code('07a35d3111') ]]]

Cool! Go tests go!

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

And... it passes! I'm kidding... that *exploded*. Hmm, it says:

> Too few arguments to `CheeseListingSetOwnerListener::__construct()` 0 passed.

Huh. Who's instantiating that class? Usually in Symfony, we expect
any "service class" - any class that's *not* a simple data-holding object like
our entities - to be instantiated by Symfony's container. That's important because Symfony's container is responsible for all the autowiring magic.

But... if you look at the stack trace... it looks like Doctrine *itself* is trying
to instantiate the class. Why is Doctrine trying to create this object *instead*
of asking the *container* for it?

## Tagging the Service

The answer is... that's... sort of... just how it works? Um, ok, better explanation.
When used as an independent library, Doctrine typically handles instantiating
these "entity listener" classes itself. However, when integrated with Symfony,
you *can* tell Doctrine to *instead* fetch that service from the container. But...
you need a *little* bit of extra config.

Open `config/services.yaml` and override the automatically-registered service
definition: `App\Doctrine\` and go grab the `CheeseListingSetOwnerListener`
class name again. We're doing this so that we can add a *little* bit of *extra*
service configuration. Specifically, we need to add a *tag* called
`doctrine.orm.entity_listener`.

[[[ code('f3e449e335') ]]]

This says:

> Hey Doctrine! This service is an *entity listener*. So when you need the
> CheeseListingSetOwnerListener object to do the entity listener stuff, use
> *this* service instead of trying to instantiate it yourself.

And *that* will let Symfony do its normal, autowiring logic. Try the
test one last time:

```terminal-silent
php bin/phpunit --filter=testCreateCheeseListing
```

And... we're good! We've got the best of all worlds! The flexibility for an API
client to *send* the `owner` property, validation when they do, and an automatic
fallback if they don't.

Next, let's talk about the last big piece of access control: filtering a collection
result to only the items that an API client should see. For example, when we make
a `GET` request to `/api/cheeses`, we should probably *not* return *unpublished*
cheese listings... unless you're an admin.
