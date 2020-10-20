# Uuid

Coming soon...

What's that one

Bonus thing to our app. UUIDs. So right now the idea of each resource is a auto
increment database ID. And we see this on all of our end points, no matter what we
do, if you, uh, execute for example, full this, the cheeses it's /api/cheeses/1
And if you want to fetch with these, you're going to fetch it by ID Juan. If
you want to update it, and you're going to use the database ID. And in some cases,
most cases, a lot of cases, that's fine, but some cases that can have some downsides
first, it exposes some information. For example, it could allow someone to, uh, to
just fetch user, try to patch the user data one by one, by just incrementing the ID.
Obviously you should have security to prevent that, but it can expose maybe how many
users you have in your database or something else.

The other, the real, another real significant common downside is that when you use it
auto increment database IDs, as your ID and your API, then the only then only your
server can create those IBS. And a lot of times it can be convenient. If something
else can create the ID, like if your JavaScript could create the, the ID and yes,
JavaScript can create UUIDs who you ideas can be created in any language. And if
JavaScript could create UUID and then send that when it's creating a new resource
that can often simplify your JavaScript, because it doesn't need to wait for the
response to know what the ID is.

Let's try changing the ID of user to be a UUID. So here's the goal. We're going to
add a user ID to the user entity, and we're gonna do that by creating a new `$uuid`
property that will store the UUID string. And then we're gonna make API platform
use this as the identifier, instead of the auto increment ID Symfony 5.2, we'll have
a new you UID component, which should allow us to easily generate you UUIDs and do
some of this work. But since that hasn't been released yet, we can use the Ramsey UUID
library, which is honestly awesome, and has been allowing it around for a long times.
So find your terminal and run `composer require ramsey`, which has been Ramsey, good
friend of mine from Nashville `uuid-doctrine`. 

```terminal-silent
composer require ramsey/uuid-doctrine
```

Now the actual library is `ramsey/uuid`
This installs a library that allows us to have a UUID doctrine type, and
you'll see that it will also install the underlying `ramsey/uuid` library.

This one's still a recipe from the contribute pository so make sure you say yes to
that or yes, permanently. And since I committed before I ran this `composer require`
I'll run and 

```terminal
git status
```
 
here. So you can see what that did. It modified the normal
files, but it also added a configuration file. So let's go over and check that out.
`config/packages/ramsey_uuid_doctrine.yaml`, and this adds a new UUID doctrine field type to
doctrine. Just cool. So what that allows us to do is create a new property in here
with a uuid type. So check this out. We can say private `$uuid`, and then up here,
I'll say `@ORM\Column()` and then type = oops.

And I'll say `@ORM\Column(type="uuid")`, which would not have worked a second ago. Now
I'm also going to say `unique=true`, cause this will be a, a key in the database.
Now, if you're wondering why I'm not going to use this as the actual primary key by
like removing living at RMID down here and deleting, this is that you actually can do
that. But some databases, like MySQL have performance problems when you have a
string primary key, uh, with foreign keys. So use, if you have a good database like
Postgres, which supports you, you had these correctly, feel free to do that, but
there's no huge disadvantage to having a true ID property. And then, uh, you, you UID
that we use in our API. Alright, so we added a new property. So now let's generate
the migration for it. So I'll go over here and I'll run

```terminal
symfony console make:migration
```

Okay, let's go check that out. And
the `migrations/` directory down here, there is my new migration file. And since I'm
using my SQL perfect, you can see it as a UID, char 36, not at all. Perfect. Now the
only tricky thing is that because we do already have a database with users in it. Uh,
the fact that this is

Not no is going to make this migration fail. If I try this right now, it would
actually fail because all the roads would have a Knoll, a UID. So what I'm gonna do
is be careful with this. I'm actually going to change this to `DEFAULT NULL`.

And in a later migration in a second, I will then change that to a, to B, not at all.
Then the next line after this, we can actually set that `$this->addSql()` and we can say,
`UPDATE user SET uuid = UUID()`  that's a, MySQL function that we can
fortunately use. Alright, so let's try this. I'll run over and I'll run 

```terminal
symfony console doctrine:migrations:migrate
```

Yes, it works. And then I'm going to generate
one more migration to actually now that that column is sets to actually change it to
normal. So I ran `make:migration` again. 

```terminal-silent
symfony console make:migration
```

And if you look at the new new migration,
perfect, this just changed the UID from default note to not know what should now
work. So I'll go back over here and run the migrations one more time. Got it. Okay.
So at this point we have a new column, but nobody is sending it and nobody is using
it.

And actually let's run our tests right now, 

```terminal
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And I'll specifically run my user resource test and it explodes like crazy. And it's a feeling
cause column you UID can not be known. It's required in the database. We're never
setting it. So unlike an auto increment ID, the UID is not going to be automatically
set, which is fine. It just means that we need to initialize it in our constructor.
So if I scroll down here, we already have a constructor. I will say 
`$this->uuid = Uuid` get the one from `Ramsey\`. You, All `Uuid::uuid4()`
right, run the test again. Now they're happy. Of course, we're not actually using the
UID in the API. It's not exposed as a field. We're not using as our identifier yet,
but let's do that next.

