# Data Persister: Encoding the Plain Password

When an API client makes a POST request to `/api/users`, we need to be able to run
some code *after* API Platform deserializes the JSON into a `User` object, but
*before* it gets saved to Doctrine. That code will encode the `plainPassword`
and set it on the `password` property.

## Introducing Data Persisters

How can we do that? One great answer is a custom "data persister". OooooOOOo.
API Platform comes with only one data persister out-of-the-box, at least, only
one that we care about for now: the Doctrine data persister. After deserializing
the data into a `User` object, running security checks and executing validation,
API Platform finally says:

> It's time to save this resource!

To figure out *how* to save the object, it loops over *all* of its data persisters...
so... really... just one at this point... and asks:

> Hi data persister! Do you know how to "save" this object?

Because our two API resources - `User` and `CheeseListing` are both Doctrine
entities, the Doctrine data persister says:

> Oh yea, I totally do know how to save that!

And then it happily calls `persist()` and `flush()` on the entity manager.

This... is awesome. Why? Because if you want to hook into the "saving" process...
or if you ever create an API Resource class that is *not* stored in Doctrine, you
can do that *beautifully* with a custom data persister.

Check it out: in the `src/` directory - it doesn't matter where - but let's create
a `DataPersister/` directory with a new class inside: `UserDataPersister`.

This class will be responsible for "persisting" `User` objects. Make it implement
`DataPersisterInterface`. You could also use `ContextAwareDataPersisterInterface`...
which is the same, except that all 3 methods are passed the "context", in case
you need the `$context` to help your logic.

[[[ code('bc5b619599') ]]]

Anyways I'll go to the Code -> Generate menu - or Command+N on a Mac - and select
"Implement Methods" to generate the three methods this interface requires.

[[[ code('bcd91d610c') ]]]

And... we're... ready! As *soon* as you create a class that implements
`DataPersisterInterface`, API Platform will immediately start using that. This
means that, *whenever* an object is saved - or removed - it will *now* call
`supports()` on our data persister to see if we know how to handle it.

In our case, if data is a `User` object, we *do* support saving this object.
Say that with: `return $data instanceof User`.

[[[ code('0502322de1') ]]]

As *soon* as API Platform finds *one* data persister whose `supports()` returns
`true`, it calls `persist()` on that data persister and does *not* call any
other data persisters. The core "Doctrine" data persister we talked about earlier
has a really low "priority" in this system and so its `supports()` method is always
called last. That means that our custom data persister is now *solely* responsible
for saving `User` objects, but the core Doctrine data persister will still handle
*all* other Doctrine entities.

## Saving in the Data Persister

Ok, forget about encoding the password for a minute. Now that our class is *completely*
responsible for saving users... we need to... yea know... make sure we save the
user! We need to call persist and flush on the entity manager.

Add `public function __construct()` with the `EntityManagerInterface $entityManager`
argument to autowire that into our class. I'll hit my favorite Alt + Enter and
select "Initialize fields" to create that property and set it.

[[[ code('8270c0c454') ]]]

Down in `persist()`, it's pretty simple: `$this->entityManager->persist($data)`
and `$this->entityManager->flush()`. Data persisters are also called when an object
is being deleted. In `remove()`, we need `$this->entityManager->remove($data)`
and `$this->entityManager->flush()`.

[[[ code('cb762f7cc4') ]]]

Congrats! We now have a data persister that... does *exactly* the same thing as
the core Doctrine data persister! But... oh yea... now, we're dangerous. *Now*
we can encode the plain password.

## Encoding the Plain Password

To do that, we need to autowire the service responsible for encoding passwords.
If you can't remember the right type-hint, find your terminal and run:

```terminal
php bin/console debug:autowiring pass
```

And... there it is: `UserPasswordEncoderInterface`. Add the argument -
`UserPasswordEncoderInterface $userPasswordEncoder` - hit "Alt + Enter" again
and select "Initialize fields" to create that property and set it.

[[[ code('f52721600a') ]]]

Now, down in `persist()`, we know that `$data` will always be an instance of `User`.
... because that's the only time our `supports()` method returns `true`. I'm going
to add a little PHPdoc above this to help my editor.

> Hey PhpStorm! `$data` is a `User`! Ok?,

[[[ code('ad13a4eee9') ]]]

Let's think. This endpoint will be called both when *creating* a user, but also
when it's being updated. And... when someone *updates* a `User` record, they may
or may *not* send the `plainPassword` field in the PUT data. They would probably
only send this *if* they wanted to *update* the password.

This means that the `plainPassword` field *might* be blank here. And if it is,
we should do nothing. So, if `$data->getPlainPassword()`, then
`$data->setPassword()` to `$this->userPasswordEncoder->encodePassword()` passing
the `User` object - that's `$data` - and the plain password:
`$data->getPlainPassword()`.

That's it friends! Well, to be extra cool, let's call `$data->eraseCredentials()`...
*just* to make sure the plain password doesn't stick around any longer than it
needs to. Again, this is probably not *needed* because this field isn't saved
to the database anyways... but it might avoid the `plainPassword` from being
serialized to the session via the security system.

[[[ code('1458b4c72a') ]]]

And... done! Aren't data persisters positively lovely?

Oh, well, we're not *quite* finished yet. The field in our API is still called
`plainPassword`... but we wrote our test expecting that it would be called just
`password`... which I kinda like better.

No problem. Inside `User`, find the `plainPassword` property and give it a new
identity: `@SerializedName("password")`.

[[[ code('111f9fb735') ]]]

Let's check that on the docs... under the POST operation... perfect!

So... how can we see if this all works? Oh... I don't know... maybe we can run
our awesome test!

```terminal
php bin/phpunit --filter=testCreateUser
```

Above all the noise.. we got it!

Next, our validation rules around the `plainPassword` field... aren't quite right
yet. And it's trickier than it looks at first: `plainPassword` should be required
when *creating* a `User`, but not when *updating* it. Duh, duh, duh!
