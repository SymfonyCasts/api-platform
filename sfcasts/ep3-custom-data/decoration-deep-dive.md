# Decorating Data Persisters vs Context Builders

To do the actual *saving* of the `User`, we want to call the *specific* core
ApiPlatform data persister that *normally* saves entities. So... how can we do that?

To start, we need to figure out which service is responsible for that. Let's
do some digging: run `bin/console debug:container` and search for `api_platform`.

```terminal-silent
php bin/console debug:container api_platform
```

This shows all of the services from ApiPlatform... which is a bunch. Search
for "doctrine". Ok: a metadata factory and... ah:
`api_platform.doctrine.orm.data_persister`. I'm *pretty* sure that's what we want.
I'll enter the number next to it - 105 - to get more details.

Ok, the class is called just `DataPersister`. Back in PhpStorm, hit
Shift+Shift, search for `DataPersister` and make sure to include "non-project items".
Select the one from ApiPlatform's Doctrine Bridge.

And... cool! This looks pretty much exactly like we expected: it persists and
flushes. *This* is what we want to inject into *our* data persister.

## Injecting the Doctrine Persister

Let's do it! Back at the terminal, copy the service id. Instead of relying on
autowiring, we can *explicitly* configure this argument on `UserDataPersister`.
To do that, open `config/services.yaml`, head to the bottom, and override the
service decoration for `App\DataPersister\UserDataPersister`. Beneath, add `bind`
and then the argument name, which is `decoratedDataPersister`. So
`$decoratedDataPersister` set to an `@` symbol and then the service id.

Thanks to this, Symfony knows to inject *this* specific service instead of the
normal chain data persister. To prove it, try the tests again:

```terminal
symfony run bin/phpunit --filter=testCreateUser
```

And... got it!

## Class Decoration vs Service Decoration

What we just did got pretty deep into ApiPlatform, Symfony and services... but
this is the kind of stuff that we're going to be doing in this tutorial.

And, I want to point out a *subtle* difference between what we *just* did and
how we hooked into a different part of ApiPlatform in the last tutorial. Specifically,
the context builder system. In both cases, we wanted to run custom code
inside part of ApiPlatform. For `AdminGroupsContextBuilder`, we used a
`decorates` option. But in this case, we didn't. Why?

## How the Context Builder System Works

Let's talk about how the context builder system works in Symfony's
service container. Open up our custom class: `App\Serializer\AdminGroupsContextBuilder`.

The class itself is pretty simple: it implements `SerializerContextBuilderInterface`
and adds some conditional serialization groups based on the current user. We also
inject a `$decorated` object that has the same `SerializerContextBuilderInterface`.

So, on a high-level, this is class decoration: we are *decorating* some other
serializer context builder, calling it, but also adding our own logic. In
`UserDataPersister`, we are *also* doing class decoration in the exact same
way.

## 2 Systems: Different Extension Points

But... the way we get this working in Symfony's service config *is*
different. Why? Because the way these systems *work* in ApiPlatform is different,
requiring two distinct solutions.

Check this out, run:

```terminal
php bin/console debug:container context
```

One of the results is a service called `api_platform.serializer.context_builder`.
This is the *one* context builder service in ApiPlatform. Or, to say that differently,
when ApiPlatform needs to "build the context" at the beginning of the request,
it calls this *one* service to do that job.

Copy the service id and re-run `debug:container` with that id *and*
`--show-arguments`:

```terminal-silent
php bin/console debug:container api_platform.serializer.context_builder --show-arguments
```

Perfect! Notice the name: `SerializerFilterContextBuilder`. I don't know what
that is, but it does *not* sound like a "chain" context builder. And check out
that third argument: it's passed *our* `AdminGroupsContextBuilder` service!

Here's what's going on. Inside ApiPlatform, there is only *one* context builder
service. But unlike the "data persister" system, this *one* context builder is
*not* a "chain" class that calls many *other* context builders. Nope, there is
simply just *one* context builder service... and that's it.

## Service Decoration: Services inside Services

That creates a problem: if we want to *add* logic to the context builder
service, the *only* way we can do that is by *replacing* the core service entirely.
*That* is the job of Symfony's service decoration, and ApiPlatform relies on it in
several different places.

Yep, the `decorates` option basically says this:

> I want to *replace* the core context builder service. I literally want to
> *become* the service that you get when someone asks for the
> `api_platform.serializer.context_builder` service. But, I *also* want that
> original context builder object to be passed to me as an argument.

What you ultimately get is a bit like a Matryoshka doll, where you
have *one* context builder service, with another inside and another inside it!
In this case, the "outer" context builder is apparently a
`SerializerFilterContextBuilder`. But then, it does its work and calls *our*
`AdminGroupsContextBuilder`.

And if you run this same `debug:container` command on our service:

```terminal-silent
php bin/console debug:container App\Serializer\AdminGroupsContextBuilder --show-arguments
```

You'll see that *our* service is passed another one with the same name, but `.inner`.
If you debug that:

```terminal-silent
php bin/console debug:container App\Serializer\AdminGroupsContextBuilder.inner --show-arguments
```

Ah, its class is `SerializerContextBuilder`! *This* is the *true*, original context
builder service. It's been decorated twice - once by us and once by ApiPlatform
itself - to add more features.

The point is: by leveraging service decoration, we can create a, sort of "chain"
of context builders... even though there isn't *technically* a chain class.
But for the data persister system, this... simply isn't necessary! That system
*has* a "chain data persister" and we can *freely* add however many data persisters
we want *simply* by creating a class that implements `DataPersisterInterface`.
Well, technically, we need to create a service and tag it with
`api_platform.data_persister`... but that happens automatically thanks to
auto-configuration.

The point is, thanks to how the data persister system is built in ApiPlatform,
we didn't need to *replace* the core data persister: we could just add our's to
the chain. If we want to call one of the *other* data persisters, we can inject it.
But it's not service decoration: we're not *replacing* that service.

To me, the data persister system is a better design: it makes life a lot simpler.
Service decoration is a bit more confusing, but it *is* a valid option when...
it's the *only* option, like in the case of context builders. *That* is why
you see two different solutions to "hooking into" a core part of ApiPlatform.

Next: the super power of a custom data persister is the ability to do something
before or after an entity is saved. But what if the code we need to run should
*only* be called when an object is created? Or updated? Or only for a specific
operation? Let's find out how we can do that!
