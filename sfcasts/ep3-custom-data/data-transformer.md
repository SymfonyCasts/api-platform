# Data Transformer

Coming soon...

To get an output class to actually work. We need to write some code that converts our
keys, listing object into HES listing output objects so that API platform can
serialize it. Those things are called data transformers. Let's create one in the
source directory. Let's create a new directory called a data transformer. Okay. And
inside there a new class called she's listing out to put data transformer

As usual with a class that hooks an API platform. This needs to implement an
interface. So that's that implements in this case, it is surprise data transformer
interface. Then inside the method, I'll go to code generate or Command + N on a Mac
and go to "Implement Methods" to implement the two methods that we need. So here's
how this works. This data transformer system is entirely there to support the input
and output DTO is that we're working with whenever API platform is about to serialize
an object. It calls every data transformer in the system. It calls these supports
transformation method on every transformer in the system, and basically asks and
asks. For example, do you know how to transform a cheese listing into HES listing
output things to auto configuration are new classes instantly. Part of that data
transforming system and the support transformation method will already be called.
Let's prove this I'm actually going to DD data. And to Now let me go over and refresh
our endpoints. Cool. There we go. You can see that it's passing us the Jesus and
object. And then for the two argument it's, it's asking us, does anybody know how to
convert this Jesus thing into a cheese dusting output? And of course we do. So for
the support transformation, we're going to return data instance of she's listing. So
we support transformation. If we're passing a cheese listing object and the two class
is equal to she's listing output,

::class.

Now, as soon as one of these data transformers returns true from supports
transformation, it's then going to call transforms so we can do our work. So is
anything here. That's actually DD object and two

Move over and refresh. And that

Dumps the exact same thing. Okay. So we didn't transform. We know that object is
actually going to be achieved listing object. So I'm going to rename the object keys
listing, and then up above this, let's add a little documentation. Say that cheese
listing will be a cheese listing object. Okay. Our job in transforming is pretty
simple. We need to return HES listing output object. So let's do it as simply as we
can output = new jeez listing outputs. And then the only field we have on our cheese
listing opera right now is the title we'll add more later. So we just need to make
sure that's populated. So I'll put->title = she's listing arrow, get title that easy
at the bottom return output. Okay. Let's see if this works, move over refresh and

It works, uh, kind of, we can see that we're getting some results here, but it's only
the ad ID. So two questions, first of all, where did the ad type go? Usually it's an
ID and at type, we're gonna talk more about where that went later, but first, why
don't I see my title property? The reason is our normalization groups. Remember on
cheese listing, we've set a normalization context with groups = cheese colon read,
just because where these groups still apply. Even though what's actually being, see,
realized now is a cheese listing output. In other words on this class, and we need to
add, we need to add that group above our property. So I'll add at groups then curly,
curly, and inside of here, we'll go copy that. We need cheese colon read above our
title property. Now when we try it, godheads our title is there. So next let's add
the rest of our, out of our output properties to CI's listing output and see how this
all looks in our documentation. Because much like with our daily stats, custom API
resource, since this isn't an entity, we'll need to do a bit more work to help API
platform understand the types of each of our properties. That's next.

