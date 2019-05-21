# Serialization Groups

Coming soon...

If the only way to control the input and output I've already API was by controlling
the getters and setters on our entity, it wouldn't be that flexible and actually
would be a little dangerous. You could accidentally add new getter set or methods for
your own code and suddenly be exposing fuels in the API maybe without thinking about,
so the way that the way around this and the way that I recommend doing things is by
using groups, which is a concept inside the serializer works like this.

Let's add, add a new key here called `normalizationContext`. So remember,
normalization is when you're going from your object to your array. So this is
something that's used when you are reading data from your API context is basically
options that you passed to that process. The most calm option by far is called a
`"groups"` which you set again turned away. And I'm going to add, make one in here called
`cheese_listing:read`. What this is going to say is that it's going to,
it's going to say, um, when we are reading from our API, I only want you to serialize
fields that have this `cheese_listing:reed` group because in a second we're
going to start adding in that group to these properties. Right now we haven't added
it anywhere. And so if you go over and try your get collection and point again, oh,
actually going to see a huge air. I knew this was going to happen. Sooner or later
you can see that this air is actually a little bit hard to read

the way around. The way to figure out the way to see this is actually go to 
`localhost:8000/_profiler/`. That trip we saw a few minutes ago and either you're going to
be able to see the list of it profiler things and you're gonna be able to click into
the one you have. Or a lot of times the, the URL is so the errors so fatal that
you're going to get a huge exception. You can see right here. In this case, I'm going
to do this a lot. This is a problem with my uh, annotations because I forgot a comma
at the end. It's now a refresh.

The profiler works so we can go back over here and execute our response again. All
right, perfect. So notice, look, we get no fuels back. We get this ad ID and Andy at
type from JSON Ld, but there's not actually any fields because we are, because none
of our fields have this yet. So I'm gonna copy this `cheese_listing:read` and
now we can do is just start opting into where we want to use that. So we do that with
adding this `@Groups()` annotation and then `{}` and then `""` and paste. That'll 
copy of that. And let's put that also above `description`.
And how about above `price`?

now let me flip back over and try it. Beautiful. We get those three exact fields that
I really like, that control, we can do the same thing with the input data. So we do
that by passing something called `denormalizationContext`. I'm actually going to
copy `normalizationContext` and `de` front of vets and change this to `cheese_listing:write`
This is a convention I'm going to follow with all of my groups to make
it very easy to control the input and output. In this case, let's, I'll copy that
`cheese_listing:write`. Let's see on on the input. Let's just allow right now the
`title` and the `price`. I actually don't want to allow the `description`. We're going to
talk about the description in a second.

Now, if we move over and refresh, you'll see that down here. If you go to post and
try it out, it's it knows from in general documentation that now we can only pass the
`title` and the `price` as fields. So `normalizationContext` and `denormalizationContext`
are two totally separate ideas. One controls reading it. That's normalization context
and Dean Organization context supports, uh, is when you're writing to the field. Now
you also notice that the bottom of the page here that we now have two models. It's
actually separating the read model. That's the normalization context with `title`,
`description` `price` from the write model. The `title` and `price` and it's not really
important, but if you want to control these names, I usually don't do this, but you
can do that as another option to the `normalizationContext` here called 
`swagger_definition_name`.

And you can set this, I'll set this one first one to Read and copy that.
And then paste it on there and call this one, Write. This is just something that
shows up in our swagger API. So `cheeses-Read` and `cheeses-Write`.

Okay. So, um, we're missing a couple of fields here though. So first of all, when we
read something, we get `title`, `description` and `price`, what we're getting to get back.
But I remember we also have a creative that field. In fact, we have two creative that
fields, we have a, the created an actual was the standardized string, but we also
have that `getCreatedAtAgo` we created a second ago. So let's decide for some
reason that we don't want to expose the `createdAt` standard string, which we probably
do instead. We just want to expose the `createdAtAgo`. The problem is that
there's no property to go put this annotation above, which is fine when you have a
situation where the field exists just because of a getter, you'll add any of your
options directly onto that. So we'll say `@Groups({""})` and we'll do that 
`cheese_listing:read` I'm actually also going to add a little bit of documentation
above this. How long ago in text that that this cheese and listening that was added.

snap football [inaudible] I'll refresh it and get the new documentation. And down
here the cheese model, you can see that get created, `createdAtAgo` with suddenly it
and it has even the description on there. And if we try to execute that end point
there, it is `createdAtAgo` and it's same thing for the input. So one of the
fields that we're missing in our post here is we're missing the description. Now I
remember we don't want actually we want the user to actually set set using the 
`setTextDescription()` method. We don't want to expose, we didn't have a `setDescription()`
method. Right now we're gonna use `setDescription()` methods. So I'll do the same thing
I'll do `@Groups({""})`. We'll call this one `cheese_listing:write` And I'll put some
description on there here, which is the description of the cheese as raw text.

And this time when we refresh the documentation, you can see it on the write model
and you can see it inside of the specific thing, which means that we can go back here
now and if we need it inside of our application, we can re add the original 
`setDescription()` number removed a second ago. So that wouldn't be part of our API. We
don't have to do that anymore. If you need a set method, you can have a 
`setDescription()` method. It's not going to modify our API in any way. So let's try all of
this out here. So let's refresh to get a fresh page on a documentation. Let's create
a new cheese. So I'll try it out. The title of this is going to be, tell us just
shovel

trying to cut box. Okay.

And why don't we take advantage of that. A line break thing inside of our text
description. All right, hit execute and Whoa, 500 error, check this out. It says an
exception occurred during the query answered into "is_published" cannot be no. Ah, and
actually in Symfony 4.3 you might not, you might see a different error. You might see a
validation error. That's because in Symfony 4.3, um, doctrine constraints are
automatically turn into validation errors.

so this makes sense because art `$isPublished` method.

Okay?

It's type `boolean`. It's not nullable. Well we don't want this to actually be set by
the user. So we're do this, this is just good object oriented programming. We won't,
we don't want this to be stopped by the user when they initially create it. We're
going to create a different process later for publishing cheese listings. So just
means that we want to initialize it to `false`. It's now let me go over and execute. It
works and isn't that beautiful. We have just the input fields that we want here and
the output fields are very different. We have a created out of go, we have a
delicious, uh, the description which has the, um, line breaks in it and is published
with, isn't, isn't exposed as all, but was set in the background.

Yes.