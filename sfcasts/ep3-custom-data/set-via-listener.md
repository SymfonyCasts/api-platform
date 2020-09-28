# Setting a Custom Field Via a Listener

When you need to add a custom field... and you need a service to *populate* the
data on that field, you have 3 possible solutions: you can create a totally
custom API resource class that's not an entity, you can create an output DTO *or*
you can do what we did: add a non-persisted field to your entity. I like this last
option because it's the least... nuclear. If most of your fields come from normal
persisted properties on your entity, creating a custom resource is overkill and
output DTO's - which are *really* cool - come with a few drawbacks.

So that's what we did: we created a non-persisted, normal property on our entity,
exposed in our API:

[[[ code('7a953e2c7f') ]]]

And populated it in a data provider:

[[[ code('b189af275e') ]]]

But in reality, there are *multiple* ways that we could *set* that field. The data
provider solution is the *pure* API Platform solution. The downside is that if
you use your `User` object in some code that runs *outside* of an API Platform API
call, the `$isMe` field won't be set!

That might be ok... or you might not even have that situation. But let's look
at another idea. What if we create a normal, boring Symfony event listener that's
executed early during the request and we set the `$isMe` field from *there*.

Let's try it! First, remove our current solution: in `UserDataPersister` I'll
comment-out the `$data->setIsMe()` and add a comment that this will now be set in
a listener:

[[[ code('b41132ec61') ]]]

Then over in `UserDataProvider`, I'll do the same thing with the first
`setIsMe()`... and the second:

[[[ code('15927b2b27') ]]]

Sweet! We are back to the broken state where the `$isMe` field is never set.

## Creating the Event Subscriber

To create the event listener, find your terminal and run:

```terminal
php bin/console make:subscriber
```

Well, this will *really* create an event *subscriber*, which I like a bit better.
Let's call it `SetIsMeOnCurrentUserSubscriber`. And for the event, we want
`kernel.request`. Well, that's its old name. Its *new* name is this
`RequestEvent` class. Copy that and paste below.

Perfect! Let's go check out the new class in `src/EventSubscriber/`. And...
brilliant! The `onRequestEvent()` will now be called when the `RequestEvent`
is dispatched, which is early in Symfony:

[[[ code('713d886287') ]]]

## Populating the Field

So our job is fairly simple! We need to find the authenticated `User` if there
is one, and *if* there is, call `setIsMe(true)` on it.

Add public function `__construct()` with a `Security $security` argument. I'll
hit `Alt`+`Enter` and go to "Initialize properties" to create that property
and set it:

[[[ code('cbc05ae2d7') ]]]

Then down in `onRequestEvent()`, start with: if not `$event->isMasterRequest()`,
then return:

[[[ code('8ed0e455e6') ]]]

That's not *super* important, but if your app uses sub-requests, there's no reason
for this code to *also* run for those. If you don't know what I'm talking about
and *want* to, check out our
[Symfony Deep Dive Tutorial](https://symfonycasts.com/screencast/deep-dive/sub-request).

Anyways, get the user with `$user = $this->security->getUser()` and add some
PHPDoc above this to help my editor: we know this will be a `User` object or
`null` if the user isn't logged in:

[[[ code('e7814daddf') ]]]

If there is *no* user, just return. But if there *is* a user, call
`$user->setIsMe(true)`:

[[[ code('be22f94fe1') ]]]

Cool! We just set the `$isMe` field on the authenticated `User` object. One cool
thing about Doctrine is that if API platform *later* queries for that *same* user,
Doctrine will return this *exact* object in memory, which means that the
`$isMe` field *will* be set to `true`.

We're now setting the `$isMe` field for the *current* user, but purposely *not*
setting it for all other `User` objects. In the `User` class, let's now default
`$isMe` to `false` to mean:

> Hey! If we did not set this, it must mean that this is *not* the
> currently-authenticated user.

[[[ code('76fa2d7562') ]]]

Down in `getIsMe()`, the `LogicException` is no longer needed:

[[[ code('5d0884e5da') ]]]

Testing time! At your browser, refresh the item endpoint and... got it! And
if we go to `/api/cheeses.jsonld`... the first item has `isMe: true` and the
others are `isMe: false`. *Love* it!

But... what if the object that we need to set the data on is *not* the `User`
object? How could we get access to the "current API resource object" from
inside of our listener? And what if it's a collection endpoint? How could we
get access to *all* the objects that are about to be serialized? Let's chat
about the *core* API Platform event listeners next.
