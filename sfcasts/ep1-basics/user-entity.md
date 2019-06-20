# Creating the User Entity

We're not going to talk *specifically* about security in this tutorial - we'll
do that in our next course and give it proper attention. But, even forgetting
about security and logging in and all that, there's a *pretty* good chance that
your API will have some concept of "users". In our case, a "user" will post a
cheese listing and becomes its "owner". And maybe later, in order to buy a
CheeseListing, one User might send a message to another User. It's time to take
our app to the next level by creating that entity.

## make:user

And even though I'm telling you not to think about security, instead of
creating the user entity with `make:entity` like I normally would, I'm
actually going to use `make:user`,

```terminal-silent
php bin/console make:user
```

Yea, this *will* set up a few security-related things... but nothing that we'll
use yet. Watch part 2 of this series for all that stuff.

Anyways, call the class `User`, and I *do* want to store users in the database.
For the unique display name, I'm going to have users log in via email, so use
that. And then:

> Does this app need to hash or check user passwords?

We'll talk more about this in the security tutorial. But *if* users
will need to log in to your site via a password *and* your app will be responsible
for checking to see if that password is valid - you're not just sending the password
to some *other* service to be verified - then answer yes. It doesn't matter
if the user will enter the password via an iPhone app that talks to your API or
via a login form - answer yes if your app is responsible for managing user passwords.

I'll use the Argon2i password hasher. But! If you don't see this
question, that's ok! Starting in Symfony 4.3, you don't need to choose a password
hashing algorithm because Symfony can choose the best available automatically.
Really cool stuff.

Let's go see what this did! I'm happy to say... not much! First, we now have a
`User` entity. And... there's nothing special about it: it *does* have a few
extra security-related methods, like `getRoles()`, `getPassword()`, `getSalt()`
and `eraseCredentials()`, but they won't affect what we're doing. Mostly we have
a normal, boring entity with `$id`, `$email`, a `$roles` array property,
and `$password`, which will eventually store the hashed password.

[[[ code('10d7c23982') ]]]

This also created the normal `UserRepository` and made a *couple* of changes to
`security.yaml`: it set up `encoders` - this might say `auto` for you, thanks
to the new Symfony 4.3 feature - and the user provider. All things to talk more
about later. So... just forget they're here and instead say... yay! We have a
`User` entity!

[[[ code('fb035fa9ca') ]]]

[[[ code('99e381dc01') ]]]

## Adding username Field

Thanks to the command, the entity has an `email` property, and I'm planning to
make users log in by using that. But I *also* want each user to have a "username"
that we can display publicly. Let's add that: find your terminal and run:

```terminal
php bin/console make:entity
```

Update `User` and add `username` as a `string`, 255, not nullable in the database,
and hit enter to finish.

*Now* open up `User`... and scroll down to `getUsername()`. The `make:user`
command generated this and returned `$this->email`... because that's what I chose
as my "display" name for security. Now that we really *do* have a username field,
return `$this->username`.

[[[ code('1239a1c605') ]]]

Oh, and while we're making this class, just, *amazing*, the `make:user` command
knew that `email` should be unique, so it added `unique=true`. Let's *also* add
that to `username`: `unique=true`.

[[[ code('290e49035c') ]]]

That is a *nice* entity! Let's sync up our database by running:

```terminal
php bin/console make:migration
```

Move over... and double-check the SQL: `CREATE TABLE user` - looks good! 

[[[ code('d509f044ae') ]]]

Run it with:

```terminal
php bin/console doctrine:migration:migrate
```

Perfect! We have a *gorgeous* new Doctrine entity... but as far as API Platform
is concerned, we still only have one API resource: `CheeseListing`.

Next: let's expose `User` as an API Resource and use all of our new knowledge to
*perfect* that new resource in... about 5 minutes.
