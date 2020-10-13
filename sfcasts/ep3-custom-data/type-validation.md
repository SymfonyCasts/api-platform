# Type Validation

Coming soon...

[inaudible]

Let's talk about validation. Even outside of DTS. There are two layers of validation
to think about. First is a piece of data even settable on a property. Like is it a
valid data type for that property? And second, does it pass validation rules? So
let's talk about that first one. Before even thinking about validation, we should ask
can a piece of data even be set onto a property. For example, if the DC realizer sets
a property vs. Setter. Okay. For example, if you pretend we don't have an input class

[inaudible]

Then set price is set. The price field is set via set price. Since this center has a
type pent that is not nullable. If you had tried to set a Knoll price to this, then
the data wouldn't even be settable on this property, and you would get a 400 air.
It's not validation is that the piece of data can't even be set onto the property. We
can actually see this, even though we're using a cheese listening input. So right now
we're actually using a teaser to input and our properties are public, which means
technically we can say anything on them, but we do have this ad bar into on there,
which actually tells a platform that this is supposed to be an in tight. Thanks that
we go over here and it's actually refresh the documentation, go to the post Jesus and
point it, try it out. And let's try setting just the price field to Apple, right?

Want to hit execute.

There you go. 400 air. And it says the type of price attribute for class Jesus. It
must be of any string given. So API platform determines if, if it should show this
air by finding the type of the field, which it doesn't many different ways, like the
type on the setter, PHP documentation like we have in this case or various other
places, and then sees it, the piece of data that it's about to put on it violates
that. So two things about this first, unfortunately, unlike true validation errors,
you don't get a nice list of all of the errors here.

Like if there were three properties that needed to be blank here, you would only, you
wouldn't see all three. Instead. It just fails once. And you get this different type
of air. The API platform folks would like to change this so that I can collect all of
the errors, list them just like validation errors, but someone needs to do the work
in the Symfony serializer first to make it possible. There is a pull request open to
do that. Second, the stack trays on this air will not be shown on where in
production, but the hydro colon description will notice it. It's kind of got
sensitive information. It mentions our, our actual internal class name publicly. This
only happens with input. DTO is not with normal API resource classes, and it's
actually a bug that's been fixed and will be released an API platform, 2.5 0.8.
Anyways, this whole idea of whether or not a piece of data can even be set on a
property is something you just need to be aware of because these sort of sanity
validation, sanity errors are not quite as nice as a validation errors. And if you're
using an input DTO, you need to be even more aware of this idea of can a field to be
set, be set to this piece of data. Why we'll take this out. Let's actually send a
completely empty objects to create a cheese listing on me. It execute 500 air.

It says argument one, pass two set description must be of type string. No given this
is coming from over in our teases to inputs. See here, it said line 66.

So quite literally

We, this, this air description has no role in the set description on cheese listing
does not allow no. So we actually get a PHP level 500 air. If Jesus, the input had a
set description method instead, and that D string type, and it didn't allow no, we
would get a nice 400 air instead of this 500 air. Since we don't have that. And since
setting Knoll on the description, property is technically allowed

[inaudible]

And actually it's set text description with this one. Nope. I need to undo that
setting things it's not actually ever set

Since description is not required. It ends up being no on this obligation and we set
an alarm on jesus' name. So this is just something that you need to be aware of. That
kind of tighter. You write your DTO like with the required arguments and, and not
nullable type hints. The less likely you're going to need to worry about this for us
to fix it. We can just type it here. Let's uh, we'll cast the description to a string
or cast the price to an int. And then up here, we'll pass the title to a string.
That's going to avoid that. That's a PHP error. Now you're thinking, wait a second. I
still don't want description and pricing title to be norm it's useless thing. Hold on
a second. Cause if you try it now, it's not going to allow this. We actually get a
true validation error saying that the title and description should not be blank. This
sort of sanity validation. Next, let's talk more about how validation works with
input details. We clearly have some validation automatically. Um, how does that work?
And can we move the validation and should validation rules live in the entity or
should they live in the input object? That's nice.

