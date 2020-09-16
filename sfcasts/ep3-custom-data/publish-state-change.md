# Detecting the "Published" State Change

Here's the plan: whenever a `CheeseListing` becomes published, we need to create a
notification that is sent to certain users... maybe some users have subscribed
to hear about *all* the latest new cheeses. To help, sort of, "fake" this system,
I've created a `CheeseNotification` entity:

[[[ code('23ff90d392') ]]]

The goal is to insert a new record into this table each time a `CheeseListing`
is published. It's not a *real* notification system... but it'll be an easy way
for us to figure out how to run custom code in *just* the right situation.

## Asserting the Database has Notifications

Let's start - like usual - with a test! We want to assert that after we send the
`PUT` request, there is exactly one `CheeseNotification` in the database. Now,
thanks to Foundry - the library that helps us insert dummy data both in
our data fixtures and in our tests - before each test case, our database is
emptied. That will make it *very* easy to count the number of rows inside the
the table. Oh, and if you *love* this "database resetting" feature of Foundry
and want a performance boost in your tests, check out
[DAMADoctrineTestBundle](https://github.com/dmaicher/doctrine-test-bundle).

Anyways, thanks to *another* cool feature from Foundry, doing this count is easy:
`CheeseNotificationFactory::repository()->assertCount(1)`.

How nice is that? A dead-simple way to count the records in a table. I'll even
give this a nice message in case it fails:

> There should be one notification about being published

[[[ code('df1b6d9637') ]]]

I love it! But before we try this... and make sure it fails, let's go one step further.
Copy the PUT call from up here and paste it below... because we want to make sure that
if we publish the same listing *again*, it should *not* create a *second* notification
because... we're not *really* publishing it again. We're... not doing *anything*
with this second call!

[[[ code('e7c4063e71') ]]]

Use the same assert: `CheeseNotificationFactory::repository()->assertCount(1)`:

[[[ code('11c86a1296') ]]]

Ok! Let's try it! Find your terminal and run *just* this test:

```terminal
symfony php bin/phpunit --filter=testPublishCheeseListing
```

And... failed asserting that 0 is identical to 1:

> There should be one notification about being published.

## Creating the Data Persister

Ok, so... how *can* we accomplish this? Well, we know that we need to run some
code right before or after the `CheeseListing` is saved. That means that we need
a custom data persister. Cool! We've done that before with
`UserDataPersister`!

Create a new PHP class and call it `CheeseListingDataPersister`:

[[[ code('c60f8bfc7e') ]]]

Poetry. Let's keep this as *simple* as possible: implement the normal
`DataPersisterInterface`:

[[[ code('b84cfa0fe8') ]]]

Go to "Code"->"Generate" - or `Command`+`N` on a Mac - select "Implement Methods"
and add the three method that we need:

[[[ code('8e70bf5285') ]]]

Before we fill in the code, let's immediately inject the doctrine data
persister so that we can use it to do the *actual* saving. Add
`public function __construct()` with `DataPersisterInterface $decoratedDataPersister`:

[[[ code('faef9d1e65') ]]]

I'll hit `Alt`+`Enter` and use "Initialize properties" to add that property and set
it in the method:

[[[ code('291f541c51') ]]]

Down in `supports()`... wait. I almost forgot! We need to make sure the
*right* data persister is passed to us. Open `config/services.yaml` and we can
just copy the `UserDataPersister` service and change it to
`CheeseListingDatePersister`, because we need the same doctrine data persister service:

[[[ code('bb76b9f773') ]]]

Ok, back in the persister, the `supports()` method is easy: return
`$data instanceof CheeseListing`:

[[[ code('bb3c4e16d4') ]]]

Yep, if a `CheeseListing` is being saved, *we* want to handle it. In `persist()`,
call `$this->decoratedDataPersister->persist($data)`:

[[[ code('64cd78f8aa') ]]]

And in `remove()`, `$this->decoratedDataPersister->remove($data)`:

[[[ code('f56936266b') ]]]

Congratulations! We just created a data persister that does... the exact same thing
as before. We can prove it by running the test:

```terminal-silent
symfony php bin/phpunit --filter=testPublishCheeseListing
```

And enjoying the exact same failure! There are still no notifications.

## Fetching the Original Data

Back in `persist()`, the question is: how can we detect if the item was just
published? I'll add a little PHPDoc above the method to help my editor:
we know that `$data` will definitely be a `CheeseListing` object:

[[[ code('d62663ce6f') ]]]

So, the *easiest* thing to do would be to say: if `$data->isPublished()`, then...
the listing was just published! Right?

[[[ code('430d4fc2a3') ]]]

I'm sure you see the problem: that *would* tell us that the listing *is* published...
but not that it was necessarily *just* published... it may have *already*
been published... and the user was just changing the description or something.
We simply don't have enough information.

What we *really* need is access to the *original* data: the way it looked
*before* it was changed by ApiPlatform. If we had that original data, we could
compare that with the current data and determine if the `isPublished` field *did*
in fact *change* from false to true. But... how can we get that?

Actually, thanks to Doctrine, this is easy! Doctrine *already*
keeps track of the original data: the data that came back when it originally
queried the database for the `CheeseListing`!

To get the original data, we need the entity manager. Add a second argument to
the constructor: `EntityManagerInterface $entityManager`. Hit `Alt`+`Enter`
to initialize that new property:

[[[ code('953cd21cba') ]]]

Then, in `persist()`, we can say
`$originalData = $this->entityManager->getUnitOfWork()` - that's a *very* core
object in Doctrine - `->getOriginalEntityData()` and pass it `$data`:

[[[ code('8046b21d17') ]]]

How cool is that? Let's dump that `$originalData` to see what it looks like:

[[[ code('b24168863c') ]]]

Let's go! Run the test:

```terminal-silent
symfony php bin/phpunit --filter=testPublishCheeseListing
```

And... cool, cool, cool! It's an array where each key represents a field of
*original* data. It shows `isPublished` *was* `false` at the start. We are
*so* dangerous now.

## Using the Original Data

Remove the dump and add a new variable: `$wasAlreadyPublished` set to
`$originalData['isPublished'] ?? false`:

[[[ code('9ac86cd543') ]]]

Because if this is a *new* entity, `$originalData` will an empty array.
And if this is a new `CheeseListing`, then it was - of course - *not*
already published.

Finally, if `$data->getIsPublished()` and *not* `$wasAlreadyPublished`,
we know that it *was* - in fact - *just* published!

[[[ code('71a0b1812e') ]]]

So let's create a new notification: `$notification = new CheeseNotification()`
and its constructor accepts the related `CheeseListing` object and
the notification text:

> Cheese listing was created!

[[[ code('6ad21b0003') ]]]

Oh, and we need to save this to the database:
`$this->entityManager->persist($notification)` and `$this->entityManager->flush()`:

[[[ code('c5311f9e2c') ]]]

And yes, you could skip the `flush()` because, a few lines later, the Doctrine
data persister will call that for us. We're... technically saving both the
notification *and* the `CheeseListing` on this line... but it doesn't really matter.

So... did we do it? Let's find out! At your terminal, go test go!

```terminal-silent
symfony php bin/phpunit --filter=testPublishCheeseListing
```

It passes! Woo!

This "original data" trick is *really* the key to doing custom things on a simple,
RESTful state change, like `isPublished` going from `false` to `true`.

Next: let's use this same trick to accomplish something *else*. Let's
add complex rules around *who* can publish a `CheeseListing` and under what
conditions. Like... maybe the owner can publish the listing... but maybe
only if the `description` is longer than 100 characters... except that an admin
can *always* publish... even if the description is too short. Yikes! Typical,
awesome, confusing business logic. Let's crush it next!
