# Validation

Coming soon...

In a traditional Symfony APP with forms validation is so simple. Unfortunately with
API Platform is just as simple and it kind of takes care of things we don't even
need to think about. So first of all, let's try this. What's, what's one way that you
can screw up as an API? We can actually send invalid JSON, I'll kill that last little
curly brace. That's Ellen invalid JSON, which if you're creating your own API end
points, you would actually need the handle and give back. And I say our message with
this, if we just get it automatically when you check out comes back with an
`@context` of `/api/context/error`. And this is a `hydra:Error` type. So this is kind of a
built in a clients understand hydra and understand that this was some sort of an
air that happened and it's a 400 status code automatically, which is correct. And it
tells you that there's a syntax error. The trace here will only show, um, when we're
in development mode. So that's awesome. And what happens if we just delete
everything?

well I actually don't really everything everything do at least a `{}`.

perfect. This time we get a 500 error. It's actually giving us an air from our database
because of a couple of our columns cannot be null. By the way, in Symfony 4.3 as I
mentioned earlier, you might already get a validation error from this because it uses
the doctrine metadata to automatically add some, some validation rules. But even if
you do are in this situation this week, this is something we want to prevent. It's
the same thing when you build a Symfony form. If you allow the foreign to be
completely blank because you forgot to add cirrus, I validation you are going to get
a 500 error or weird things are gonna happen. So we need to add validation, which
turns out is just super beautiful. It's the exact way we've been doing all along.
It's just with annotation. So, uh, above `title`, let's say, let's `@Assert\NotBlank()`
Let's also add an `@Assert\Length()` here. I'll say that the `min=` needs to be
2 the `max=` is going to be 50 and we'll even uh, control the `maxMessage=""` to say,
"Describe your cheese and 50 chars or less". And let's see the other fields.

How about `@Assert\NotBlank()` about `$description`? That is a little bit odd because we
actually are using the tech, right? It's actually calling, `setDescription()`, 
`setTextDescription()` from behind the seat and you haven't seen,


This will cause the air to be attached to the `$description` of property, which, which
should make sense. And then down here, let's put `@Assert\NotBlank()` on price and you
could also maybe put something to make that it's above zero.

All right. So, so for, if we switched back over now and we still have our empty
response, I mean you're not sending any fields. Yes. Look at this. It's awesome. It's
 `@type` of `ConstraintViolationList`. That's one of the types that was described by
our JSON-LD documentation at `/api/docs.jsonld`.

See down here under `supportedClasses`, there's entry point, but there's also a
`ConstraintViolation` and `ConstraintViolationList` to kind of describes what these
are supposed to look like and what properties they're supposed to have. So it is
actually a type of resource where were you turning in? Air Resource has a `violations`
key and blow their kind of shows you the `propertyPath` where it's coming from and the
`message`. So just kind of like works automatically out of the box. It's super, super
nice. Um, and if we did pass a `title`, And that was way too long.

Yup. And our custom message shows up right there. So one thing you might be thinking
about that was about this `price`, right? The only annotation I added here was at, not
blank, but what's preventing us from adding a word to that? Something that's not even
a valid, uh, a key. So let's try that.

Let's say `price` is going to be `apple`, totally invalid setting.

Let me execute this. It actually fails where they 400 air and you can see that it
says the type or the price price. Attri it must be into string given it fails during
the, you kind of look down at the trace here. It fails during the deserealization
process. So it's not actually a validation air. Uh, it's a DC realization air, but to
the user, it actually looks the same. It's a 400 air and um, and they get like a good
description, let's talking about it. So kind of someone with forms, you don't even
need to worry about some of the type of safe sort of a rules. A API platform knows
the types of your fields and it's going to make sure that something insane doesn't
get pushed there. You'll need need to do business rule validation. Basically making
sure that fields aren't blank, are within some range that you need. So you have
validation works exactly like it's always worked in Symfony and a pamphlet from maps
it to the API responses and messages that we want without doing any work.