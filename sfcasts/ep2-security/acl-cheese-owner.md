# ACL: Only Owners can PUT a CheeseListing

Back to security! We need to make sure that you can *only* make a PUT request
to update a `CheeseListing` if you are the *owner* of that `CheeseListing`. As
a reminder, each `CheeseListing` is related to one `User` via an `$owner` property.
Only *that* `User` should be able to update this `CheeseListing`.

Let's start by writing a test. In the test class, add
`public function testUpdateCheeseListing()` with the normal
`$client = self::createClient()` and `$this->createUser()` passing
`cheeseplease@example.com` and password `foo`. Wait, I only want to use
`createUser()` - we'll log in manually a bit later.

[[[ code('c0ce1ecb66') ]]]

## Always Start with self::createClient()

Notice that the *first* line of my test is `$client = self::createClient()`...
even though we haven't needed to *use* that `$client` variable yet. It turns
out, making this the first line of *every* test method is important. Yes, this
of course creates a `$client` object that will help us make requests into our API.
But it *also* boots Symfony's container, which is what gives us access to the
entity manager and all other services. If we swapped these two lines and put
`$this->createUser()` first... it would totally *not* work! The container wouldn't
be available yet. The moral of the story is: always start with `self::createClient()`.

## Testing PUT /api/cheeses/{id}

Ok, let's think about this: in order to test updating a `CheeseListing`, we
*first* need to make sure there's a `CheeseListing` in the database to update!
Cool! `$cheeseListing = new CheeseListing()` and we can pass a
title right here: "Block of Cheddar". Next say `$cheeseListing->setOwner()` and
make sure this `CheeseListing` is *owned* by the user we just created. Now fill in
the last required fields: `setPrice()` to $10 and `setDescription()`.

[[[ code('c9c519765a') ]]]

To save, we need the entity manager! Go back to `CustomApiTestCase`... and copy
the code we used to get the entity manager. Needing the entity manager
is *so* common, let's create another shortcut for it:
`protected function getEntityManager()` that will return `EntityManagerInterface`.
Inside, `return self::$container->get('doctrine')->getManager()`.

[[[ code('bfce946574') ]]]

Let's use that: `$em = $this->getEntityManager()`, `$em->persist($cheeseListing)`
and `$em->batman()`. Kidding. But wouldn't that be awesome? `$em->flush()`.

[[[ code('f6ccc18588') ]]]

Great setup! Now... to the *real* work. Let's test the "happy" case first:
let's test that *if* we log in with this user and try to make a `PUT` request
to update a cheese listing, we'll get a 200 status code.

Easy peasy: `$this->logIn()` passing `$client`, the email and password. Now
that we're authenticated, use `$client->request()` to make a `PUT` request to
`/api/cheeses/` and then the id of that `CheeseListing`: `$cheeseListing->getId()`.

[[[ code('1b2bea5087') ]]]

For the options, most of the time, the only thing you'll need here is the `json`
option set to the data you need to send. Let's *just* send a `title` field set
to `updated`. That's enough data for a valid PUT request.

[[[ code('d196436fd7') ]]]

What status code will we get back on success? You don't have to guess. Down on
the docs... it tells us: 200 on success.

Assert that `$this->assertResponseStatusCodeSame(200)`.

[[[ code('05d8d554d8') ]]]

Perfect start! Copy the method name so we can execute *just* this test. At your
terminal, run:

```terminal
php bin/phpunit --filter=testUpdateCheeseListing
```

And... above those deprecation warnings... yes! It works.

But.. that's no surprise! We haven't *really* tested the security case we're
worried about. What we *really* want to test is what happens if I login and try
to edit a `CheeseListing` that I do *not* own. Ooooo.

Rename this `$user` variable to `$user1`, change the email to
`user1@example.com` and update the email below on the `logIn()` call. That'll
keep things easier to read... because *now* I'm going to create a *second* user:
`$user2 = $this->createUser()` with `user2@example.com` and the same password.

[[[ code('67ad9dfdf9') ]]]

Now, copy the *entire* login, request, assert-response-status-code stuff and paste
it right *above* here: before we test the "happy" case where the *owner* tries to
edit their *own* `CheeseListing`, let's first see what happens when a *non-owner*
tries this.

Log in this time as `user2@example.com`. We're going to make the exact same request,
but *this* time we're expecting a `403` status code, which means we *are* logged in,
but we do *not* have access to perform this operation.

[[[ code('ddeb21a2e8') ]]]

I *love* it! With any luck, this should *fail*: our `access_control` is *not*
smart enough to prevent this yet. Try the test:

```terminal-silent
php bin/phpunit --filter=testUpdateCheeseListing
```

And... yes! We expected a 403 status code but got back 200.

## Using object.owner in access_control

Ok, let's fix this!

The `access_control` option - which will probably be renamed to `security` in
API Platform 2.5 - allows you to write an "expression" inside using Symfony's
expression language. This `is_granted()` thing is a *function* that's available
in that, sort of, Twig-like expression language.

We can make this expression more interesting by saying `and` to add *more* logic.
API Platform gives us a few *variables* to work with inside the expression, including
one that represents the *object* we're working with on this operation... in other
words, the `CheeseListing` object. That variable is called... `object`! Another
is `user`, which is the currently-authenticated `User` or `null` if the user is anonymous.

Knowing that, we can say `and object.getOwner() == user`.

[[[ code('e241b84872') ]]]

Yea... that's it! Try the test again and...

```terminal-silent
php bin/phpunit --filter=testUpdateCheeseListing
```

It passes! I told you the security part of this was going to be easy! *Most*
of the work was the test, but I *love* that I can *prove* this works.

## access_control_message

While we're here, there's one other related option called `access_control_message`.
Set this to:

> only the creator can edit a cheese listing

... and make sure you have a comma after the previous line.

[[[ code('8f546e6680') ]]]

If you run the test... this makes *no* difference. But this option *did* just
change the message the user sees. Check it out: after the 403 status code,
`var_dump()` `$client->getResponse()->getContent()` and pass that `false`.
Normally, if you call `getContent()` on an "error" response - a 400 or 500 level
response - it throws an exception. This tells it *not* to, which will let us
see that response's content. Try the test:

[[[ code('c619c58582') ]]]

```terminal-silent
php bin/phpunit --filter=testUpdateCheeseListing
```

The `hydra:title` says "An error occurred" but the `hydra:description` says:

> only the creator can edit a cheese listing.

So, the `access_control_message` is a nice way to improve the error your user
sees. By the way, in API Platform 2.5, it'll probably be renamed to
`security_message`.

Remove the `var_dump()`. Next, there's a bug in our security! Ahh!!! It's subtle.
Let's find it and squash it!
