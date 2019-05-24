# Serialization Tricks

Coming soon...

I mentioned earlier that it's not critical that your output data, your output fields
match your input fields. Right now we have description when we read the cheese
listing resource, but when we send data it's called text description and that's
technically fine. And we can, we have, and we can already tell how these properties
are made. Um, the keys inside the JSON literally matched the properties inside of our
class. And in the case of a fake property like text description.

Okay.

It's usually using a similar convention where it just basically strips off the set
and lower camel cases. By the way, that is something you can control. It's called a

field name or,

and you can override that at a global level. But anyways, it would be kind of Nice
even if this were actually just description. So input, description, output
description. Internally we realized that one of them would be calling set description
and the other one would be calling get description. Um, but the user doesn't need to
really worry about this and we can control that really easily. Well the special
annotation called at serialized name, so we can say that. So your last name,
description and that's it.

Okay.

When we refresh the documentation, of course the more we get back from the good end
point hasn't changed. It's still description. That's still what it was before, but
when he posts now we can send a description and that's going to go to set to the text
description. All right, so talking more about the serializer. We know that the
serialized it works by going through the getters and setters and I also imagined that
the property access component has a couple other pro of a cause. A couple of other
super powers like the ability to do hazard is their methods add or remove removers,
things like that. But what if I wanted a constructor? Right now we do have a
constructor but it doesn't have any required arguments. You might just for code
organization want to, for example, make the title.

Okay.

It required arguments, your constructors so that every time you create a new cheese
listing, you must pass the title because that's a required field. And once you do
that, might say, look, I don't want to have a, might even say, I don't want to have a
set title method anymore. Maybe once a title set it is set permanently

in your code.

The question is, is the serializer gonna like that. Oh and of course in the
constructor say this air title = title question is, is the Syrian losers are going to
like that because there's no set title method anymore. And behind scenes when we send
a post request to create a new cheese somewhere, it needs to actually say new cheese
listing before it calls to senators and saves it. Well let's try it.

Okay.

Title, we'll sell some crumbs of some blue cheese for $5.

Yeah.

And when we hit execute, it works. You can see the title is correct. Should I go?
Hold on. How does that work? How did it take title and set it onto the object? And
the answer is it actually matched it by this argument name. So check this out. Let's
say I change this to name, right from the object oriented perspective, that shouldn't
change anything. Um, we should sell that, the construct this in the same exact way.
But now when we hit execute, we get an air in order to say 400 air says cannot create
an instance of cheese listing from serialized data because it's constructed requires
parameter name to be present. So the way this works is you are allowed to have
constructor arguments.

Yeah.

When the serializer sees this constructor argument name, it looks for a name key in
what we're sending. If there's not one, then you get an exception. So if we, if the
user is going to send us a title key, as long as we have a title argument here that
is actually going to match it up.

Okay.

So the nice thing is you have this kind of rule where you had to name your argument,
uh, any sane way. But otherwise if you want construct arguments, you can have them.
But there is kind of one edge case here and it's this. Let's pretend that we are
creating a new cheese listing and we forget to send the title field at all.

Okay.

Imagine we're making Ajax requests and we have some sort of bug in our JavaScript. If
you execute this, you're gonna be back by 400 air, which is the correct air. 400
error means that the client, the person making the request had something wrong with
the request and you see an error occurred. This hydrocodone description, that is
something that is only shown in development mode. So basically what the user would
see is just an error occurred because it can't figure out how to what's passed at
that title. For most aps, this is actually probably not an issue if your, uh, if
you're not, if you're building an API just for your own JavaScript, this is a bug in
your JavaScript. As long as you configure out what's going wrong, then you can just
fix it. But if for creating a public API, what you really probably want to have
happen is you probably want your normal validation rules to hit. Do you want this?

Yeah.

We haven't talked about validation yet, but in a little bit we're going to make title
required and when we do that, the user is going to start getting nice back. Nice
validation errors. But only if, but the validation errors can happen if the
serializer can't even create our t's listing object. So to make that work a little
better, you need to set this to know what this allows. Let me try it again, is for
the object actually to the JSON to be turned into a serialized object. And this guys,
I got a 500 air insert into cheese listing. It's having a problem with it because the
database and the database, because title cannot be null in a few minutes after we add
validation, this will actually give you a nice validation error and the user will see
something like the title is required. Ah, if you're using a 74.3, this is another
situation where, um, you might not see a database thing here, you might already see a
validation error because, uh, validation rules are automatically added from doctrine
metadata.

Okay.