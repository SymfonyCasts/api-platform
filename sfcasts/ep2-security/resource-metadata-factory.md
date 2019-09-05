# Resource Metadata Factory: Dynamic ApiResource Options

Using a context builder to dynamically add groups is a great option when the
groups you're adding are contextual to who is authenticated, like we add
`admin:read` *only* if the user is an admin. The reason is that the context builder
isn't taken into account when you're documentation is built. For "extra" admin
fields that may be returned... that's maybe not a huge deal. But the more you
put into the context builder, the less perfect your docs become.

However, if you're using a context builder to do something like what *we're*
trying now - adding a bunch of groups in *all* situations - then things really
start to fall apart. Our docs are now *very* inaccurate for *all* users.

How *can* we customize the normalization and denormalization groups *and* have
the docs notice the changes? The answer is with a "resource metadata factory"...
which is... at least at first... as dark and scary as the name sounds.

## Creating the Resource Metadata Factory

Inside the  `ApiPlatform/` directory, create a new class called
`AutoGroupResourceMetadataFactory`. Make this implement
`ResourceMetadataFactoryInterface` and then go to Code -> Generate - or Command+N
on a Mac - and select "Implement Methods". This interface only requires one method.

So... what the heck does this class do? It's job is simple: given an API Resource
class - like `App\Entity\User` - it's job is to read all the API Platform metadata
for that class - usually via annotations - and return it as a `ResourceMetadata`
object. Yep, this `ResourceMetadata` object contains *all* of the configuration
from our `ApiResource` annotation, which API Platform then uses to power... pretty
much *everything* related to how this resource should behave.

## Service Decoration

Just like with the context builder, API Platform only has *one* resource metadata
factory. This means that instead of *adding* this class to API Platform, we need
to use this to completely *replace* the core resource metadata factory... which we
can do via decoration.

The first step to class decoration has *nothing* to do with Symfony: it's the
implementation of the decorator pattern. Create a `public function __construct()`
where the first argument will be the "decorated", "core" object. This means that
it will have the same interface as this class:
`ResourceMetadataFactoryInterface $decorated`. Hit Alt + Enter and go to
"Initialize Fields" to create that property and set it.

Inside the method, call the decorated class so it can do all the heavy-lifting
for us: `$resourceMetadata = $this->decorated->create($resourceClass)`. Then,
return this at the bottom: we won't make any modifications yet.

The *second* step of decoration is *all* about Symfony: we need to tell it to
use *our* class as the core "resource metadata factory" instead of the normal
one... but to pass us the original one as as our first argument. Open up
`config/services.yaml`. We've done all this before with the context builder:
override the `App\ApiPlatform\AutoGroupResourceMetadataFactory` service... then
I'll copy the first two options from above... and paste here. We actually don't
need this `autoconfigure` option - that's a mistake in the documentation. It
doesn't hurt... but we don't need it.

Ok, for decoration to work, we need to know what the core service id is that we're
trying to replace. To know this, you'll need to read the docs... or maybe even
dig a bit deeper if it's not documented. What we're doing is so advanced that
you *won't* find this on the docs. The service we're decorating is
`api_platform.metadata.resource.metadata_factory`. And for the "inner" thing,
copy our service id and paste below to make:
`@App\ApiPlatform\AutoGroupResourceMetadataFactory.inner`.

Cool! Since our resource metadata factory isn't *doing* anything yet... everything
should still work *exactly* like before. Let's see if that's true! Find your
terminal and run the tests:

```terminal
php bin/phpunit
```

And... so far... nothing broke.

## Pasting in the Groups Logic

For the guts of this class, I'm going to paste two private functions on the bottom.
These are low-level, boring functions that will do the hard work for us:
`updateContextOnOperations()` and `getDefaultGroups()`, which is more or less
the same as the function we added to our context builder. You can copy both of
these functions from the code block on this page.

Next, up in `create()`, I'm going to paste a little bit more code. This is *way*
more code than I like to just... paste in magically... but adding all the groups
takes some pretty ugly & boring code. We start by getting the `ResourceMetadata`
object from the core, decorated resource metadata factory. That `ResourceMetadata`
object has a method on it called `getItemOperations()`, which returns an array
of configuration that matches the `itemOperations` for whatever resource we're
working on. Next, I call the `updateContextOnOperations()` method down here, which
contains *all* the big, hairy code to loop over all the different operations and
make sure the `normalization_context` has our "default groups" and that the
`denormalization_context` *also* has the default groups.

The end result is that, by the bottom of this function, the `ResourceMetadata`
has all of the different "default" groups that we wanted to add. Honestly, this
whole idea is... kind of an experiment... and there might even be a bug somewhere
in this code. But, this *should* work.

And *thanks* to this new stuff, the code in `AdminGroupsContextBuilder` is
redundant: let's remove the private function on the bottom... and the line on top
that called that.

Ok... let's see what happens! Refresh the docs. The *first* thing you'll notice
is on the bottom: there are not *tons* of models! This is the downside to this
approach: it's *total* overkill for the models: Swagger shows *every* possible
combination of the groups... even if none of our operations uses them.

Let's look at what a specific operation - like GETing the collection of cheeses.
Oh... actually - that's *not* a good example - the `CheeseListing` resource is
temporarily broken - I'll show you why in a few minutes. Let's check out the
a `User` operation instead. Yep! It shows us *exactly* what we're going to get back.

So... we did it! We added dynamic groups that our API documentation knows about.
Except... there are a few problems. It's possible that when you refreshed your
docs, this did *not* work for you... due to cache. Let's talk more about that next
and fix the `CheeseListing` resource.
