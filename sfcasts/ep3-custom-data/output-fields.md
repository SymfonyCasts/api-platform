# Output Fields

Coming soon...

Let's go see how having an output class affects our documentation. So I'll refresh
the documentation homepage because one of the things that the documentation does, it
actually tells you what to expect back from an end point. So for example, if we look
at the good item operation for cheeses, this is now going to return something
different than it did a second ago. And if you go down here in his schema, yeah,
awesome. It's actually working. You can see it that the only field that advertises
that's going back is title. So that's awesome. Now you will notice it has this really
weird name up here. This is referring to the models and the bottom. So if you scroll
down to the bottom iPad platform kind of creates a unique model based on all the
serialization groups you have. And when you create an output even uses this kind of
like hash here.

I don't know the full story behind why that's there, but technically you can
configure an output, a class on an operation by operation basis. So basically it's
using a hash so that I can have at least a unique model name for each of those
separate outputs. So a little ugly, but I'm not sure that it's actually a problem.
Anyways, the point is APF Auburn correctly knows that we're using an output glass.
And so actually looks on our output class to figure out what fields we're going to
have, which is right now is only title, but it doesn't have any documentation on
title. It doesn't know what's type

Oh,

Or what it is. And this is expected when we were serializing, our cheese listing
entity APEC platform could look at the doctrine that metadata above the title
property to figure out its type. But now that's gone. When it looks at title, it
doesn't get any information from it. And that's no problem. We just need to add more
info ourselves. One way is by using PHP 7.4 types. So for example, I can say public
string title. Now my editor thinks that this is invalid because it thinks I'm using
PHP 7.3. I'm actually using PHP 7.4. So this will work. If you're not used to feature
7.4, you can also use at VAR, which also is

Give me a second.

Alright, so let's refresh the docs now. And when we look at the same, get item,
operation and going down to schema, Oh, it did not work. It still doesn't have the
tight next to title. This is our first cork with the output system EFL platform
doesn't realize that it needs to rebuild its property. Meta-data cap

cache.

Our output class was modified. So normally if we modify something on cheese listing,
it realizes that it needs to rebuild and get fresh property, metadata information,
but that's not happening right now. So we can kind of trigger it manually just by
changing anything inside this class, I'll hit, save and go back and hit save again.
Now, if we move over and refresh, the refresh takes slightly longer, cause it's
rebuilding that cache. And let's go and look at that end point and go to schema and
now to see title string. So not a big deal, but you have to be aware of that bug.

Now, back over in the class, I'm going to actually remove the Petri 7.4 type and
instead use a at VAR just in case, not everyone is using PHP 7.4 and I'll even put a
little description on here. It says the title of this listing because we know that it
will use this also in the documentation. All right, let's add two more fields inside
of here. So if you look at cheese listing, we also output a description as part of
our output. And so is price. So I'll copy the title property here and let's add
description and I'll remove the, which is a string. I'll copy that and make one more
called price.

And this needs to be an integer. And of course, now that we added those two new
fields, we need to go into our data transformer and also set those. So
output->description = jeez listing arrow, get description, and I'll put->price = jeez
listing, arrow, get price. Uh, these transformer classes in can get a little boring,
which I actually love. We're just transferring data from one object to another. Uh,
before we try this, let's actually grab a couple other fields and she's listing. If
you kind of search for cheese, colon read, you'll see, I'll actually go into ignore
owner for now. We'll come back to this in a second. We also may get short description
method. All right. So let's copy that whole thing. And eventually we won't need this
method in this class at all. We'll do some cleanup in a few minutes and she has this
thing output.

I'm going to paste this on the bottom and that will work exactly like before it's
referencing the description property. It has the group on it. So that's going to be
perfect and over and she's listing. If I search again, there is one more field it's
get created at a go. So I'll copy this and paste this at the bottom. And each storm
asks me if I want to import the carbon class. I do perfect. Now the only, uh, kind of
promise this is that this actually references the created that field, which we do not
have inside of this object right now. So we need to add it. So I'll add a public
created that, but I'm not going to put any groups above this because this isn't a
field that we expose directly. We just need access to its data. And then down here,
instead of using get created, that we can just reference that property directly.

And of course, we're going to need to make sure that this is set VR data transformer.
So over here, we'll say output->created that = jeez listing arrow, get to create it
that awesome. Okay. So first if we refresh the document, [inaudible], we'll see if it
actually refreshed or not. If I opened the item operation and go to schema. Yes.
Okay. So it did rebuild the cache there and you'd see it as all of our custom fields,
all of the types. Beautiful. And if we go over and refresh our end point, we got it.
How awesome is that? We now have five custom five fields on it. And what's really
great about this is that we can look at our output class and it's so simple. You
don't have to look very hard here to figure out all of the fields that we're exposing
in our API. So next, the one field that we don't have on here yet, if we searched for
cheese, read again, is the owner field. Let's add that though. When we add it,
there's going to be one slight problem that we're going to need to fix. Let's do that
next.

