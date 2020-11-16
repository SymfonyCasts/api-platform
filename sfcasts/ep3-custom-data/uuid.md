# UUID's

Let's add a *bonus* feature to our app. Right now, the id of each resource is its
auto-increment database ID. We can see this on all of our endpoints. If you Execute
the cheese collection endpoint... the IRIs are `/api/cheeses/1`. You'll also use
the database id to update or do anything else.

Using auto-increment id's like this is *fine*. But it *can* have a few downsides.
For example, it can expose some info - like how *many* users you have...
or - by just changing the ids to 1, 2 or 3, you could easily browse through *all*
of the users... though you should - ya know - use *security* to avoid this if
it's a problem.

Auto-increment IDs have another downside: when you use an auto-increment
database id as the key in your API, it means that *only* your *server* can create
them. But if your API clients - like JavaScript - could *instead* choose
their *own* id, it would actually simplify their lives. Think about it:
if you're creating a new resource in JavaScript, you normally need to send the
AJAX call and wait for the response so that you can *then* use the new id:

```javascript
const userData = {
    // ...
};

axios.post('/api/users', userData).then((response) => {
    // response.data contains the user data WITH the id
    this.users.push(response.data);
});
```

That's especially common with frontend frameworks when managing state.

Another option is to use a UUID: a, sort of, randomly-generated string that *anyone* -
including JavaScript - can create. If we allowed that, our *JavaScript* could
generate that UUID, send the AJAX request *with* that UUID and then not have to
wait for the response to update the state:

```javascript
import { v4 as uuidv4 } from 'uuid';

const userData = {
    uuid: uuidv4(),
    // ... all the other fields
};
this.users.push(userData);
axios.post('/api/users', userData);
```

## Installing ramsey/uuid

So that's our next mission: replace the auto-increment id with a UUID in our API
so that API clients have the *option* to generate the id themselves. We'll do
this for our `User` resource.

So... how *do* we generate these UUID strings? Symfony 5.2 will come with a
new UUID component, which should allow us to easily generate UUID's and store them
in Doctrine. But since that hasn't been released yet, we can use `ramsey/uuid`,
which is honestly awesome and has been around for a long time. Also, Ben Ramsey
is a *really* nice dude and an old friend from Nashville. Ben generates the *best*
UUIDs.

Find your terminal and run:

```terminal
composer require ramsey/uuid-doctrine
```

The actual library that generate UUID's is `ramsey/uuid`. The library *we're*
installing *requires* that, but also adds a new UUID doctrine *type*.

This will execute a recipe from the contrib repository so make sure you say "yes"
to that or yes permanently. I committed before I ran `composer require`, so we
can see the changes with:

```terminal
git status
```

Ok: it modified the normal files, but *also* added a configuration file. Let's
go check that out: `config/packages/ramsey_uuid_doctrine.yaml`:

[[[ code('ad41b721b3') ]]]

## The UUID Doctrine Type

Ah! *This* adds the new UUID Doctrine type I was talking about. What this
allows us to do - back in `User` - is add a new property - private `$uuid` -
and, above this, say `@ORM\Column()` with `type="uuid"`. That would *not* have
worked before we installed that library and got the new config file. Also set
this to `unique=true`:

[[[ code('56bccea139') ]]]

UUID's are strings, but the `uuid` type will make sure to store the UUID in the
*best* possible way for whatever database system you're using. And when we query,
it will turn that string *back* into a UUID *object*, which is ultimately what's
stored on this property. You'll see that in a minute.

Could we know *remove* the auto-increment column and make *this* the primary
key in Doctrine?

[[[ code('b023ce8ffa') ]]]

Yes, you *could*. But I won't. Why? Some databases - like MySQL - have performance
problems with foreign keys when your primary key is a string. PostgreSQL
does *not* have this problem, so do whatever is best for you. But there's
no real disadvantage to having the auto-increment primary key, but a UUID as
your *API* identifier.

## Generating a Safe Migration

Ok: we added a new property. So let's generate the migration for it. Find your
terminal and run:

```terminal
symfony console make:migration
```

Let's go check that out. Go into the `migrations/` directory... and open the
latest file:

[[[ code('be2c1c7dfd') ]]]

Since I'm using MySQL, you can see that it's storing this as a `char` field with
a length of 36:

[[[ code('22d488941f') ]]]

The only tricky thing is that because we *do* already have a database with
users in it - the fact that this column is `NOT NULL` will make the migration
fail because the existing records will have *no* value.

To fix this, temporarily change it to `DEFAULT NULL`:

[[[ code('49a631f124') ]]]

Then, right after this, say `$this->addSql()` with `UPDATE user SET uuid = UUID()`:

[[[ code('76811916a6') ]]]

That's a MySQL function to generate UUID's.

Let's try this! Back at your terminal, run the migration:

```terminal
symfony console doctrine:migrations:migrate
```

It works! Now generate *one* more migration as a lazy way to set the field *back*
to `NOT NULL`:

```terminal-silent
symfony console make:migration
```

If you look at the new migration:

[[[ code('9024ebb415') ]]]

Perfect! This changes `uuid` from `DEFAULT NULL` to `NOT NULL`:

[[[ code('b377f8dcbd') ]]]

Run the migrations one last time:

```terminal-silent
symfony console doctrine:migrations:migrate
```

## Auto-Setting the UUID

Got it! So at this point, we have a new column... but nobody is using it. Let's
run the user tests:

```terminal
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

And... ah! It explodes like crazy! Of course:

> Column uuid cannot be null.

It's required in the database... but we're never setting it.

Unlike an auto-increment ID, the UUID is *not* automatically set, which is fine.
We can set it *ourselves* in the constructor. Scroll down... we already have
a constructor. Add `$this->uuid = Uuid` - auto-complete the one from
`Ramsey\` - then call `uuid4()`, which is how you get a *random* UUID string:

[[[ code('875cdf03d0') ]]]

Run the tests again:

```terminal-silent
symfony php bin/phpunit tests/Functional/UserResourceTest.php
```

*Now* they're happy!

Next, the UUID is *not* part of our API at *all* yet. Let's tell API Platform
to start using *it* as the identifier.
