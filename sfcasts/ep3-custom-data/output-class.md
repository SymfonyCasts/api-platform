# Output Class

Coming soon...

So far, every API resource that we have is either been an entity like cheese and
user, or a completely custom class, like daily stats with an entity. We can add
complex custom fields with some work. We did this in user with our custom is me and
his MVP fields. And of course, with a custom class like daily stats, that's not an
entity. We can do whatever we want. We can make this look exactly how we want our
resource to look, but we lose automatic features like Paige nation and filtering, and
it just takes more work to get this set up. But there is actually a third option,
which is kind of in between these tool to check out she's listing the input data, the
input fields on this actually look quite different from the output fields. For
example, you can only read the description, But we have a

[inaudible]

For example, the published field is actually rideable, but it's not readable. And
the, also the description properties readable, but not writeable directly, but we
have a set text description method, which is writeable, and actually has the
serialized name description.

We've accomplished all of this

By leveraging some smart serialization groups and some custom methods. And this is
one upside it's simple. It's all inside this one class and we can control it with
these groups. It also has one downside. Our entity serialization rules are more
complex. You can't just look at cheese listing and quickly see which fields are going
to be readable in which fields are going to be writeable. You got to kind of think
about it a little bit. Another option is to have a separate class for your input and
output. Basically we would transform a cheese listing into another object. And then
that object is what is serialized and return to the user. We can also do the same
thing for the input class, which we'll talk about later. This is called input and
output DT, owes data transfer objects. And I love this approach in theory,
implementing it is pretty clean and it gives you a lot of flexibility, but it's also
not a feature that is heavily used by the core API platform devs. And I found some
quirks, some of those are already being fixed and I'll walk you through them. So how
do we start?

Our first goal was going to be to create a custom output class for cheese listing
that has the exact fields we want when we read the cheese listing. So we're going to
start by creating a class with the exact fields and source. Let's create a new
directory called DTO and inside here, I'm going to create a new PHP class called
cheese listing output.

And for now let's just have a public title. So I'm gonna use public fields again, uh,
properties again for simplicity, but also because this, these classes are going to be
dead, simple themselves. So I'm actually okay with that. And we'll add the other
fields later. Let's just start with title and see if we can get this working to tell
API platform that we want to use this as the alpha class, we need to go back into
CI's listing and inside the API resource annotation, it doesn't matter where, but I
like to put it on top. Cause it's kind of important. Let's add output = and then take
off the quotes. Cause I'm going to say she's listing output, ::class. And as we know,
and we do this, we actually need to go and add the use statement manually use jeez
listing output and perfect. Alright so before we do anything else, let's try it. I'll
head over here. And actually let's go to open a new tab and go to /API /cheeses dot
JSON LD and Oh, and air because I love forgetting my comma. All right, let's try that
again. Refresh. And

It works sort of, it doesn't work. We get the exact same result as before. Why?
Because right now APAC platform is thinking, Hey, you told me you want to use CI's
listening output as your output representation. That's cool. But how do I create that
object? Yep. Something it needs to transform our cheese listing object into a cheese
listing output object. So that API platform can serialize she's listing output. What
does that a data transformer? Let's create one next.

