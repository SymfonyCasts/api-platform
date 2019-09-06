# Context Builder & Service Decoration

When you make a `GET` request for a collection of users or a single user, API Platform
will use the same normalization group: `user:read`. This means the response will
*always* contain the `email`, `username` and `cheeseListings` fields.

Now we need to do something smarter: we need to be able to *also* normalize using
another group - `admin:read` - but *only* if the authenticated user has `ROLE_ADMIN`.
The key to doing this is something called a "context builder".

Remember: when API Platform, or really, when Symfony's serializer goes through its
normalization or denormalization process, it has something called a "context",
which is a fancy word for "options that are passed to the serializer". The most
common "option", or "context" is `groups`. The context is normally hardcoded
via annotations but we can *also* tweak it dynamically.

## Creating the Context Builder

Google for "API Platform context builder" and find the serialization page. If we
scroll down a bit... it talks about changing the serialization context dynamically
and gives an example. Steal this `BookContextBuilder` code. This class
can live anywhere in `src/`, but to follow the docs, let's create a `Serializer/`
directory and a new PHP class inside of that called `AdminGroupsContextBuilder`...
because the purpose of this context builder will be to add an `admin:read`
group or an `admin:write` group to *every* resource if the authenticated user
is an admin.

Paste the code and rename the class to `AdminGroupsContextBuilder`.

[[[ code('49f5824f86') ]]]

## Service Declaration & Decoration

A lot of times in Symfony, when you're "hooking" into some existing process, like
hooking into the "context building" process, all you need to do is create a class,
make it implement some interface or extend some base class and... boom! Symfony
or API Platform magically sees it and uses it. That happened earlier when we
created the voter: no config was needed beyond the class itself.

But, that's *not* true for a context builder: this needs some service config...
some interesting service config. Open `config/services.yaml` and go the bottom.
Our new class *is* already registered as a service... that happens automatically.
But we need to override that service definition to add some extra config. Start
with the class name... which is also the service id:
`App\Serializer\AdminGroupsContextBuilder`. Then, I'll look back at the docs,
copy these three lines... and paste. Change the `BookContextBuilder` part of the
argument to `AdminGroupsContextBuilder`.

[[[ code('0c02c6302f') ]]]

If you've never seen this `decorates` option before, welcome to service decoration!
It's a slightly advanced feature of Symfony's container but it is *incredibly*
powerful... and API Platform uses it in several places.

Internally, API Platform already has a "context builder": it has a *single*
service that it calls that's responsible for building the "context" in every situation.
That service is what reads our `normalizationContext` annotation config.

But now, *we* want to hook into that process. But... we don't want to *replace*
the core functionality. No, we want to *add* to it. We do this via service
decoration. The *id* of the *core* "context builder" service is
`api_platform.serializer.context_builder`. So our config says:

> Please register a new service called `App\Serializer\AdminGroupsContextBuilder`
> and make it *replace* the core `api_platform.serializer.context_builder` service.

Yep, this means that, whenever API Platform needs to build the "context", it will
now use *our* service *instead* of the original, *core* service. If we *only* did
this, our class would *replace* the core class - not something we want. Fortunately,
the decoration feature allows us to pass the *original* service as an argument,
by using the same id as our service plus `.inner`. Yep, this weird string is a
magic way to reference the original, core context builder service.

If you look in `AdminGroupsContextBuilder`, it implements
`SerializerContextBuilderInterface`. That's the interface we must implement to be
a "context builder". The first constructor argument *also*
implements `SerializerContextBuilderInterface` and is called `$decorated`.
*This* is the original, core API Platform context builder service.

The only method this interface requires is `createFromRequest()`, which API
Platform calls when it's building the context. Check out that first line:
`$context = $this->decorated->createFromRequest()`.

Yep, we're calling the *core* context builder, passing it all the arguments, and
letting *it* do its normal logic, like reading the `normalizationContext` and
`denormalizationContext` config off of our annotations. *Then*, below this, we
can *extend* the context with our own logic.

Phew! This may look complicated the first time you see it, but I *love* this feature.
On an object-oriented level, this is the "decorator" pattern: the recommended way
to "extend" the functionality of a class. The config in `services.yaml` is Symfony's
way of letting you "decorate" any core service.

At this point, *our* service *is* being used as the context builder. Next, let's
fill in our custom logic to add the dynamic groups.
