# Validation Groups

We're missing some validation related to the new password setup. If we send an
empty POST request to `/api/users`, I get a `400` error because we're missing
the email and username fields. But what I *don't* see is a validation error for
the missing password!

No problem. We know that the password field in our API is actually the
`plainPassword` property in `User`. Above this, add `@Assert\NotBlank()`.

[[[ code('acd739c8d4') ]]]

We're good! If we try that operation again... `password` *is* now required.

Sigh. But like many things in programming, fixing one problem... creates a
*new* problem. This will also make the `password` field required when *editing* a
user. Think about it: since the `plainPassword` field isn't persisted to the database,
at the beginning of each request, after API Platform queries the database for the
`User`, `plainPassword` will always be null. If an API client only sends the
`username` field... because that's all they want to update... the `plainPassword`
property will *remain* null and we'll get the validation error.

## Testing the User Update

Before we fix this, let's add a quick test. In `UserResourceTest`, add a new
`public function testUpdateUser()` with the usual `$client = self::createClient()`
start. Then, create a user and login at the same time with
`$this->createUserAndLogin()`. Pass that the `$client` and the
normal `cheeseplease@example.com` with password `foo`.

[[[ code('3bd1cbb3fc') ]]]

Great! Let's see if we can update *just* the username: use `$client->request()`
to make a `PUT` request to `/api/users/` `$user->getId()`. For the `json` data,
pass only `username` set to `newusername`.

[[[ code('5659ed7c9a') ]]]

This should be a totally valid PUT request. To make sure it works, use
`$this->assertResponseIsSuccessful()`... which is a nice assertion to make sure
the response is *any* 200 level status code, like 200, 201, 204 or whatever.

And... to be extra cool, let's assert that the response *does* contain the updated
username: we'll test that the field *did* update. For that, there's a really nice
assertion: `$this->assertJsonContains()`. You can pass this any subset of fields you
want to check. We want to assert that the json *contains* a `username` field
set to `newusername`.

[[[ code('29a7afde36') ]]]

It's gorgeous! Copy the method name, find your terminal, and run:

```terminal
php bin/phpunit --filter=testUpdateUser
```

And... it fails! 400 bad request because of the validation error on `password`.

## Validation Groups

So... how *do* we fix this? We want this field to be required for the `POST`
operation... but *not* for the PUT operation. The answer is validation groups.
Check this out: *every* constraint has an option called `groups`. These are kinda
like normalization groups: you just make up a name. Let's put this into a... I
don't know... group called `create`.

[[[ code('d26373536d') ]]]

If you *don't* specify `groups` on a constraint, the validator automatically puts
that constraint into a group called `Default`. And... by... default... the validator
only executes constraints that are in this `Default` group.

We can see this. If you rerun the test now:

```terminal-silent
php bin/phpunit --filter=testUpdateUser
```

It passes! The `NotBlank` constraint above `plainPassword` is now *only* in a group
called `create`. And because the validator only executes constraints in the `Default`
group, it's not included. The `NotBlank` constraint is now *never* used.

Which... is not exactly what we want. We don't want it to be included on the
`PUT` operation but we *do* want it to be included on the `POST` operation.
Fortunately, we can specify validation groups on an operation-by-operation basis.

Let's break this `access_control` onto the next line for readability. Add a comma
then say `"validation_groups"={}`. Inside, put `Default` then `create`.

[[[ code('218a47c9e9') ]]]

The POST operation should execute all validation constraints in *both* the
`Default` and `create` groups.

Find your terminal and, this time, run *all* the user tests:

```terminal
php bin/phpunit test/Functional/UserResourceTest.php
```

Green!

Next, sometimes, based on who is logged in, you might need to show additional fields
or *hide* some fields. The same is true when creating or updating a resource: an
admin user might have access to *write* a field that normal users can't.

Let's start getting this all set up!
