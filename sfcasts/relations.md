# Relations

Coming soon...

We have a cheese resource, we have a user resource, let's link them together. And the
reason we're doing this is that every cheese listing internal in the database needs
to have an owner. It needs to know, no, we need to know who created that she's
listing. So let's go over to our terminal around Ben Console, Make Cohen and see,
let's update the cheese listing resource and we're going to add a new owner property.
This will be a relation over to the user entity. In this case, it's going to be a
many to one relationship every cheese listing has at one user.

Okay.

Uh, and this PR, this relationship should not to be nullable. The database, it should
be a required property and the database and let's say yes to mapping both sides of
the relationships is something that's optional and doctrine. And as you'll see in a
pair platform, if you map both sides of the relationship, it's going to allow you to
fetch that data from both sides of the relationship. We'll call the property cheese
listings and we'll say no to orphan removal because that's not something that we are
going to need in this case. And I'll enter and we're good. So as usual, this attitude
things, it added an owner of property that she is listing along with a good owner and
a set owner. Then over on user, this added a cheese listings property with a good
cheese listings and an add cheese listings. Any removed cheese listings. So there's
no set or on that side.

Yeah,

so listen, right. The migration for this Bin Console make migration.

Okay.

And as usual I'll open that up just to make sure that looks good. Yeah. To alter
tables for the foreign key and then spin back over and execute that with in console
doctrine call and migrations migrate.

Okay.

Oh, but that's right. This explodes. CanNot Update. Add or update a child row, a
foreign key constraint fails. So it fails because we made that new owner id, that new
owner field on cheese listing.

Yeah.

We said to Noah [inaudible] = false. It's required in the database. So when we try to
add this column, the database, it fails. If this site we're already on production, we
would need to do some fancy or migrations to give this to work. In this case, the
easiest thing to do is just to drop all of our data, so I'm going to say doctrine and
colon scheme, uncle and drop dash dash full dash dash help because this has an
argument I can remember that's right. Full database is what we want. So we run that
again with full date.

Yeah,

Dash Dash pull database because that's going to drop and then dash dash force, that
will drop the entire database including our migrations table. That's what the full
database is for. And then make a run. Migrations migrate again. This time it works.
All right, so let's go look at what's, so we have these new properties and these new
getters and setters, but because we are using on normalization a groups, we have
normalization context and de normalization context. These properties aren't going to
show up automatically. We need to just like normal properties, we need to include
them in the groups. So let's start with cheese listing. Let's say that when we create
a cheese listing, we need to set the owner. And when we view a cheese listing, we're
going to want to see who owns it. Pretty simple situation. So let's go down. I'm
actually in a copy of the ad groups off of price. Let's go down the owner and we'll
put those into the read and write group for cheese listing. So now if we move over
and refresh, and the first thing I'm actually going to do is ignore cheese list and
let's actually create a user. So we have something to play with.

Yeah.

So I'll give the uh, an email, the password, it doesn't matter at all right now we're
not using that. And then a username and we'll execute that to a one. And I'll just
make sure that I add a couple. Let's add a second user just to make things a bit more
interesting than one also worked. Okay, great. So now let's go up and create a new
cheese listing. So I'm gonna go to the post end point. Hit tried out. The first thing
you're going to notice is that it thinks the owner is a string. We're gonna talk more
about that. Why in a second? Where's you might think that what should be an I key. So
let's try adding a cheese listing here.

Yeah.

Cell block one node cheese, two of 70 brave for 20 bucks.

Okay.

And then for the owner, what do we put here? Well, if you'd go cut sheet, if we go
back and look when we created that user a second ago that created um, id too. So we
have id one and I need to in the database. So most obvious thing to do here is to put
id one and had execute. When we do that, that fails I 400 air. It says expected IRI
or nested document for Attri owner integer given. Oh, that and that makes, this is
one of the weirdest things about API platform, but also one of the things I love the
most, this is the modern way to do API APIs using an id. Remember I mentioned earlier
that instead of using ids, we're always going to use, you are else to call it I our
eyes. We can see this if we down in are going to be created. Our user, when it
returns the user, it returns. It's an ID, the IRI. So even when you, uh, whenever you
reference a resource, you're going to reference it with the IRS. So this is actually
/API /users /one. And when you execute that, this time it works and check it out, it
passes us back and owner, that is an eye our eye. It's a relationship. That's why
swagger thought that this was a string because it actually technically is a string.
You can see this down in the models. If you scroll all the way down the bottom, if
you look at cheeses, right? For example, you'll see owner is a string.

But actually if you went and looked at the, uh,

yeah,

API documentation.

Okay.

So locals 8,000 /API /Dr that JSON Ld, if you went and looked at the JSON LD
documentation and search for owner, you'll see that it's a little bit smarter. It
actually knows this is a link and it has some fancy stuff in here to basically say
this is a link to a user. So technically a string but not actually a strength. So
yeah, without really doing anything, uh, it exposes that um, relationship to us. The
only thing we need to know is that it's exposed to be an IRI. So what about the other
side? Um,

we can now fudge a cheese listing with id one and we can get back the information,
including the owner that has the IRI. What if we want to get to the go the other
direction? So right now if we go, let me close a few things up. This is getting kind
of shout refresh to close everything up. Let's get user with id one as you'd see,
comes back with email and username. It might also be useful for us to have the
cheeses, things for the user, maybe not. But let's pretend that it is. So this is
just as easy. We need to go over to user and on our cheese listing property. We need
to add a group to that. And actually for now I'm just going to add the user read
group. We'll talk about the user right group later. But let's pretend we just want to
read this.

Okay,

now for refresh and open up the get on point, hit try it out. You can say it's
already advertised and it's going to return an array of strings, which is
interesting. So let's look at user one. And yes, if you look at it, it actually
returns cheese listings, cheeses, and cause an array, but it doesn't. But when it
actually gives you as an array of the I our eyes in a second, we're talking about
embedding relationships, but by default in Apa platform, when you're, you're always
going to eat, when you relate to things, you're always going to get an IRI or an
array of eye or eyes, which is really, really nice. All right, so next, let's talk
about embedding relationships.