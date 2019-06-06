# Relations

Coming soon...

We have a cheese resource, we have a user resource, let's link them together. And the
reason we're doing this is that every Cheese listing internal in the database needs
to have an owner. It needs to know, no, we need to know who created that she's
listing. So let's go over to our terminal around 

```terminal
php bin/console make:entity
```

let's update the `CheeseListing` resource and we're going to add a new `owner` property.
This will be a `relation` over to the `User` entity. In this case, it's going to be a
`ManyToOne` relationship every `CheeseListing` has at one `User`.

Uh, and this relationship should not to be nullable. The database, it should
be a required property and the database and let's say yes to mapping both sides of
the relationships is something that's optional and doctrine. And as you'll see in a
API Platform, if you map both sides of the relationship, it's going to allow you to
fetch that data from both sides of the relationship. We'll call the property 
`cheeseListings` and we'll say no to orphan removal because that's not something that we are
going to need in this case. And I'll enter and we're good. So as usual, this attitude
things, it added an `$owner` of property that `CheeseListing` along with a `getOwner()` and
a `setOwner()`. Then over on `User`, this added a `$cheeseListings` property with a 
`getCheeseListings()` and an `addCheeseListing()`. Any `removeCheeseListing()`. So there's
no set or on that side. so listen, right. The migration for this 

```terminal
php bin/console make:migration
```

And as usual I'll open that up just to make sure that looks good. Yeah. To alter
tables for the foreign key and then spin back over and execute that with 

```terminal
php bin/console doctrine:migrations:migrate
```

Oh, but that's right. This explodes. CanNot Update. Add or update a child row, a
foreign key constraint fails. So it fails because we made that new owner id, that new
`$owner` field on `CheeseListing`.

We said to `nullable=false`. It's required in the database. So when we try to
add this column, the database, it fails. If this site we're already on production, we
would need to do some fancy or migrations to give this to work. In this case, the
easiest thing to do is just to drop all of our data, so I'm going to say 

```terminal
php bin/console doctrine:schema:drop --help
```

because this has an argument I can remember that's right. Full database is what we want. 
So we run that again with `--full-database`

```terminal-silent
php bin/console doctrine:schema:drop --full-database --force
```

that will drop the entire database including our migrations table. That's what the full
database is for. And then make a run `migrations:migrate` again

```terminal-silent
php bin/console doctrine:migrations:migrate
```

This time it works. All right, so let's go look at what's, so we have these new properties 
and these new getters and setters, but because we are using on normalization a groups, 
we have `normalizationContext` and `denormalizationContext`. These properties aren't going 
to show up automatically. We need to just like normal properties, we need to include
them in the groups. So let's start with cheese listing. Let's say that when we create
a cheese listing, we need to set the owner. And when we view a cheese listing, we're
going to want to see who owns it. Pretty simple situation. So let's go down. I'm
actually in a copy of the `@Groups()` off of `$price`. Let's go down the `$owner` and we'll
put those into the read and write group for `CheeseListing`. So now if we move over
and refresh, and the first thing I'm actually going to do is ignore cheese list and
let's actually create a `User`. So we have something to play with.

So I'll give the uh, an email, the password, it doesn't matter at all right now we're
not using that. And then a username and we'll execute that to a one. And I'll just
make sure that I add a couple. Let's add a second user just to make things a bit more
interesting than one also worked. Okay, great. So now let's go up and create a new
`CheeseListing`. So I'm gonna go to the post end point. Hit tried out. The first thing
you're going to notice is that it thinks the owner is a string. We're gonna talk more
about that. Why in a second? Where's you might think that what should be an I key. So
let's try adding a `CheeseListing` here.

Cell block one node cheese, two of 70 brave for 20 bucks.

And then for the owner, what do we put here? Well, if you'd go cut sheet, if we go
back and look when we created that user a second ago that created um, id too. So we
have id one and I need to in the database. So most obvious thing to do here is to put
id one and had execute. When we do that, that fails I 400 error. It says expected IRI
or nested document for attribute owner integer given. Oh, that and that makes, this is
one of the weirdest things about API Platform, but also one of the things I love the
most, this is the modern way to do API APIs using an id. Remember I mentioned earlier
that instead of using ids, we're always going to use, URLs called IRI
eyes. We can see this if we down in are going to be created. Our user, when it
returns the user, it returns. It's `@id`, the IRI. So even when you, uh, whenever you
reference a resource, you're going to reference it with the IRS. So this is actually
`/api/users/1`. And when you execute that, this time it works and check it out, it
passes us back and `owner`, that is an IRI. It's a relationship. That's why
swagger thought that this was a string because it actually technically is a string.
You can see this down in the models. If you scroll all the way down the bottom, if
you look at `cheeses-Write`? For example, you'll see owner is a `string`.

But actually if you went and looked at the, uh, API documentation.

So `localhost:8000/api/docs.jsonld`, if you went and looked at the JSON-LD
documentation and search for owner, you'll see that it's a little bit smarter. It
actually knows this is a link and it has some fancy stuff in here to basically say
this is a link to a user. So technically a `string` but not actually a `string`. So
yeah, without really doing anything, uh, it exposes that um, relationship to us. The
only thing we need to know is that it's exposed to be an IRI. So what about the other
side? Um,

we can now fudge a `CheeseListing` with id = 1 and we can get back the information,
including the owner that has the IRI. What if we want to get to the go the other
direction? So right now if we go, let me close a few things up. This is getting kind
of shout refresh to close everything up. Let's get `User` with id = 1 as you'd see,
comes back with email and username. It might also be useful for us to have the
`CheeseListing` for the user, maybe not. But let's pretend that it is. So this is
just as easy. We need to go over to `User` and on our `$cheeseListings` property. We need
to add `@Groups()` to that. And actually for now I'm just going to add the `user:read`
group. We'll talk about the `user:write` group later. But let's pretend we just want to
read this.

Okay,

now for refresh and open up the get on point, hit try it out. You can say it's
already advertised and it's going to return an array of strings, which is
interesting. So let's look at user one. And yes, if you look at it, it actually
returns `cheeseListings`, cheeses, and cause an array, but it doesn't. But when it
actually gives you as an array of the IRIs in a second, we're talking about
embedding relationships, but by default in API Platform, when you're, you're always
going to eat, when you relate to things, you're always going to get an IRI or an
array of IRI, which is really, really nice. All right, so next, let's talk
about embedding relationships.