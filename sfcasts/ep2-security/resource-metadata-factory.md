# Resource Metadata Factory: Dynamic ApiResource Options

Using a context builder to dynamically add groups is a great option when the
groups you're adding are *contextual* to who is authenticated... like we add
`admin:read` *only* if the user is an admin. That's because the context builder
isn't taken into account when your documentation is built. For these "extra"
admin fields... that may not be a huge deal. But the more you put into the
context builder, the less perfect your docs become.

However, if you're using a context builder to do something crazy like what *we're*
trying now - adding a bunch of groups in *all* situations - then things really
start to fall apart. Our docs are now *very* inaccurate for *all* users.

How *can* we customize the normalization and denormalization groups *and* have
the docs notice the changes? The answer is with a "resource metadata factory"...
which is... at least at first... as dark and scary as the name sounds.

## Creating the Resource Metadata Factory

Inside the  `ApiPlatform/` directory, create a new class called
`AutoGroupResourceMetadataFactory`. Make this implement `ResourceMetadataFactoryInterface` and then take a break... cause we just created
one *seriously* scary-looking class declaration line.

[[[ code('17745c019c') ]]]

Next, go to Code -> Generate - or Command+N on a Mac - and select "Implement Methods". This interface only requires one method.

[[[ code('7c72f91b23') ]]]

So... what the heck does this class do? It's job is pretty simple: given an
API Resource class - like `App\Entity\User` - its job is to read all the API
Platform *metadata* for that class - usually via annotations - and return it as
a `ResourceMetadata` object. Yep, this `ResourceMetadata` object contains *all* of
the configuration from our `ApiResource` annotation... which API Platform *then*
uses to power... pretty much *everything*.

## Service Decoration

Just like with the context builder, API Platform only has *one* core resource metadata
factory. This means that instead of, sort of, *adding* this as some *additional*
resource metadata factory, we need to completely *replace* the core resource metadata
factory with our own. Yep, it's service decoration to the rescue!

The first step to decoration has... *nothing* to do with Symfony: it's the
implementation of the decorator pattern. That sounds fancy. Create a
`public function __construct()` where the first argument will be the "decorated",
"core" object. This means that it will have the same interface as this class:
`ResourceMetadataFactoryInterface $decorated`. Hit Alt + Enter and go to
"Initialize Fields" to create that property and set it.

[[[ code('b9fdcf7dcd') ]]]

Inside the method, call the decorated class so it can do all the heavy-lifting:
`$resourceMetadata = $this->decorated->create($resourceClass)`. Then,
return this at the bottom: we won't make any modifications yet.

[[[ code('b6738c7c8c') ]]]

The *second* step to decoration is *all* about Symfony: we need to tell it to
use *our* class as the core "resource metadata factory" instead of the normal
one... but to pass us the normal one as our first argument. Open up
`config/services.yaml`. We've done all this before with the context builder:
override the `App\ApiPlatform\AutoGroupResourceMetadataFactory` service... then
I'll copy the first two options from above... and paste here. We actually don't
need this `autoconfigure` option - that's a mistake in the documentation. It
doesn't hurt... but we don't need it.

Ok, for decoration to work, we need to know what the core service id is that we're
replacing. To find this, you'll need to read the docs... or maybe even dig a
bit deeper if it's not documented. What we're doing is *so* advanced that you
*won't* find it on the docs. The service we're decorating is
`api_platform.metadata.resource.metadata_factory`. And for the "inner" thing,
copy our service id and paste below to make:
`@App\ApiPlatform\AutoGroupResourceMetadataFactory.inner`.

[[[ code('4e70b6217c') ]]]

Cool! Since our resource metadata factory isn't *doing* anything yet... everything
should still work *exactly* like before. Let's see if that's true! Find your
terminal and run the tests:

```terminal
php bin/phpunit
```

And... huh... nothing broke! I, uh... didn't mean to sound so surprised.

## Pasting in the Groups Logic

For the guts of this class, I'm going to paste two private functions on the bottom.
These are low-level, boring functions that will do the hard work for us:
`updateContextOnOperations()` and `getDefaultGroups()`, which is nearly
identical to the method we copied into our context builder. You can copy both of
these from the code block on this page.

[[[ code('b610d31864') ]]]

Next, up in `create()`, I'll paste in a bit more code. 

[[[ code('51aa40206c') ]]]

This is *way* more code than I normally like to paste in magically... but adding
all the groups requires some pretty ugly & boring code. We start by getting the `ResourceMetadata` object
from the core, decorated resource metadata factory. That `ResourceMetadata` object
has a method on it called `getItemOperations()`, which returns an array of
configuration that matches the `itemOperations` for whatever resource we're
working on. Next, I call the `updateContextOnOperations()` method down here, which
contains *all* the big, hairy code to loop over the different operations and make
sure the `normalization_context` has our "automatic groups"... and that the
`denormalization_context` *also* has the automatic groups.

The end result is that, by the bottom of this function, the `ResourceMetadata`
object contains *all* the "automatic" groups we want for *all* the operations.
Honestly, this whole idea is... kind of an experiment... and there might even be
some subtle bug in my logic. But... it *should* work.

And *thanks* to this new stuff, the code in `AdminGroupsContextBuilder` is
redundant: remove the private function on the bottom... and the line on top
that called it.

[[[ code('08fc1037bb') ]]]

Ok... let's see what happens! Refresh the docs. The *first* thing you'll notice
is on the bottom: there are now *tons* of models! This is the downside of this
approach: it's *total* overkill for the models: Swagger shows *every* possible
combination of the groups... even if none of our operations uses them.

Let's look at a specific operation - like GETing the collection of cheeses.
Oh... actually - that's *not* a good example - the `CheeseListing` resource is
temporarily broken - I'll show you why in a few minutes. Let's check out
a `User` operation instead. Yep! It shows us *exactly* what we're going to get back.

So... we did it! We added dynamic groups that our API documentation knows about.
Except... there are a few problems. It's possible that when you refreshed your
docs, this did *not* work for you... due to *caching*. Let's talk more about that
next and fix the `CheeseListing` resource.
