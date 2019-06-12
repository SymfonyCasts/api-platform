# User API Resource

I want to expose our new `User` entity as an API resource. And we know how to do
that! Add... `@ApiResource`!

[[[ code('d8770f0f4c') ]]]

Just like that! Yes! Our API docs show one new resource with five new
endpoints, or operations. And at the bottom, here's the new `User` model.

Hmm, but it's a bit strange: both the hashed `password` field and `roles` array
are part of the API. Yea, we could create a new user right now and pass whatever
roles *we* think that user should have! That might be ok for an admin user to be
able to do, but not anyone. Let's take control of things.

## UUID's?

Oh, one thing I want you to notice is that, so far, the primary key is always being
used as the "id" in our API. This *is* something that's flexible in API Platform.
In fact, instead of using an auto-increment id, *one* option is to use a UUID.
We're not going to use them in this tutorial, but using a UUID as your identifier
*is* something that's supported by Doctrine and API Platform. UUIDs work with
any database, but they *are* stored more efficiently in PostgreSQL than MySQL,
though we use some UUID's in MySQL in some parts of SymfonyCasts.

But... why am I telling you about UUID's? What's wrong with auto-increment ids?
Nothing... but.... UUID's *may* help simplify your JavaScript code. Suppose we
write some JavaScript to create a new `CheeseListing`. With auto-increment ids,
the process looks like this: make a POST request to `/api/cheeses`, wait for the
response, then read the `@id` off of the response and store it somewhere... because
you'll usually need to know the id of each cheese listing. With UUID's, the process
looks like this: generate a UUID in JavaScript - that's *totally* legal - send the
POST request and... that's it! With UUID's, you don't need to wait for the AJAX
call to finish so you can read the id: *you* created the UUID in JavaScript, so
you already know it. *That* is why UUID's can often be really nice.

To make this all work, you'll need to configure your entity to use a UUID *and*
add a `setId()` method so that it's possible for API Platform to set it. Or
you can create the auto-increment id and add a *separate* UUID property. API Platform
has an annotation to mark a field as the "identifier".

## Normalization & Denormalization Groups

*Anyways*, let's take control of the serialization process so we can remove any
weird fields - like having the encoded password be returned. We'll do the *exact*
same thing we did in `CheeseListing`: add normalization and denormalization groups.
Copy the two context lines, open up `User` and paste. I'm going to remove the
`swagger_definition_name` part - we don't really need that. For normalization, use
`user:read` and for denormalization, `user:write`.

[[[ code('4df33d1186') ]]]

We're following the same pattern we've been using. Now... let's think: what fields
do we need to expose? For `$email`, add `@Groups({})` with `"user:read", "user:write"`:
this is a readable and writable field. Copy that, paste above `password` and make
it only `user:write`.

[[[ code('7a3f4eac0d') ]]]

This... doesn't really make sense yet. I mean, it's not *readable* anymore, which
makes *perfect* sense. But this will eventually store the *encoded* password, which
is *not* something that an API client will set directly. But... we're going to
worry about all of that in our security tutorial. For now, because password is a
required field in the database, let's temporarily make it writable so it doesn't
get in our way.

Finally, make `username` readable and writable as well.

[[[ code('810da34460') ]]]

Let's try it! Refresh the docs. *Just* like with `CheeseListing` we now have *two*
models: we can read `email` and `username` and we can *write* `email`, `password`
and `username`.

The only other thing we need to make this a *fully* functional API resource is
validation. To start, both `$email` and `$username` need to be unique. At the top
of the class, add `@UniqueEntity()` with `fields={"username"}`, and *another*
`@UniqueEntity()` with `fields={"email"}`.

[[[ code('df6f353d84') ]]]

Then, let's see, `$email` should be `@Assert\NotBlank()` and `@Assert\Email()`,
and `$username` needs to be `@Assert\NotBlank()`. I won't worry about password yet,
that needs to be properly fixed anyways in the security tutorial.

[[[ code('0fc83dba55') ]]]

So, I think we're good! Refresh the documentation and let's start creating users!
Click "Try it out". I'll use my real-life personal email address:
`cheeselover1@example.com`. The password doesn't matter... and let's make the username
match the email without the domain... so I don't confuse myself. Execute!

Woohoo! 201 success! Let's create *one* more user... *just* to have some better
data to play with.

## Failing Validation

Oh, and what if we send up empty JSON? Try that. Yea! 400 status code.

Ok... we're done! We have 1 new resource, *five* new operations, control over the
input and output fields, validation, pagination and we could easily add filtering.
Um... that's amazing! *This* is the power of API Platform. And as you get better
and better at using it, you'll develop even faster.

But ultimately, we created the new `User` API resource *not* just because creating
users is fun: we did it so we could *relate* each `CheeseListing` to the `User` that
"owns" it. In an API, relations are a *key* concept. And you're going to *love*
how they work in API Platform.
