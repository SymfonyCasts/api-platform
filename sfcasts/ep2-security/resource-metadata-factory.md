# Resource Metadata Factory

Coming soon...

Yeah.

So are you using a context builder to dynamically add groups is a really, really good
option when what you're adding is like specific to the user, like an admin colon
regroup if the user is an admin or not. And the reason that's a good fit is because
the, the context builder is not taken into account when you um, in your documentation
and that sort of makes sense. Your documentation is meant to be kind of a static
documentation that works for everyone. Um, so the fact that it doesn't, it's not
smart enough to, you know, change to know that an Admin user is going to get back an
extra field is maybe not ideal but not the end of the world. But if you start using
something like what we're doing now, where we are like adding a whole bunch of
groups, always just as a, as a way of just kind of, uh, systemizing our groups, that
doesn't work so well because now our documentation is way off. Our documentation
thinks that we're going to get back very different fields than we actually are
because the documentation is not using this at all. So if you want to add kind of
like custom groups,

if you want to add a custom groups and you want your documentation update, context
builder is not the way to go. Instead you're going to create a resource metadata
factory interface, which is kind of a crazy thing. So inside the API flat from
directory, I'm accrediting PHP class called auto group resource Meta data factory.
And this is a pretty advanced feature inside API platform. We're gonna make this
implement the resource metadata factor interface. Then I'm hit command and go to
"Implement Methods". And this only needs to have one method. The resource metadata.
That factory interface is responsible for given a resource class. So like the app
entity user class, we're supposed to return this resource metadata object and the
resource metadata object is the object that knows all of the configuration from our
API resource annotation. So it is what builds all of this configuration into an
actual object that API platform can use. Now of course in similar to the context
builder, you can't have multiple metadata factories. You can only have one, but we
can use decoration to add functionality to it. So like before I'm going to start by
creating a public function_in a square construct and we're going to take the
decorated object, the resource metadata factory interface as an argument called
decorate it all to enter. Go to initialize fields to create that property and Senate.

And then down here we can say resource metadata = this->decorated arrow. Create more
pass at the resource class. Then at the bottom just for now, we are going to return
that so we won't make any modifications to it yet. Second, now just like with a
context builder, this is not something that an API platform is going to automatically
and see use. We need to use decoration. So and config service .yaml will register
will override our s our app /API platform /auto group resource,

Metta auto group resource met at that factory. I'm actually in a copy of the
decorates and arguments key. You actually don't need this auto configure. That's a
mistake in the documentation. It doesn't hurt anything, but I'm gonna remove that.
And this case, what we're going to decorate, and this is something that you would
need actually do some research into APM platform to figure this out. But the name of
the service that we're decorating is API platform dot metadata dot resource dot
meta-data_factory. Oh and then here for the inner thing, I'll copy my service name so
that my argument is app /APA platform /auto group Resource Metadata, factory dot
enters. That will pass the inner,

the original metadata factory interface. Okay. So right now since our med, that
factory is not doing anything yet. Everything should still work. So let's run over
here and just run our tests. PHP Bin /Phd unit and good, it didn't break anything.
Okay, so for the guts of this class, I'm going to paste in two private functions in
the bottom here, which are just like super low level and boring ones called update
context on operations. And the other one is called get the fault groups, which is a
more or less a what we had before in that other class is responsible for adding those
groups.

You can get this code on me, the code blocks on this page. Next one I'm gonna do is
actually up inside of my credit function on a paste a little bit more code. This is,
I know this is way more code than I'd like to just drop on you, but this is really
big and complex. So basically what happens here is we start by getting the resource
meta-data from the kind of the core class. That's what normally has the all the
stuff, the resource metadata has an a, a methadone called gift item operations that
returns an array metadata about the item operations of whatever resource we're
talking about right now. I then call this update context on operations class down
here, which is just a bunch of big [inaudible] code to loop over all the different
operations and then uh, make sure that the normalization, that group's context is,
has the default groups and then the de normalization context that it groups also have
the default groups. So the end result of all of this is that by the bottom of this
function, the resource metadata has all of those, all of the, um, different groups
that it should have. And I could even be a bug in here and this is, I'll say, so
complex that's a, that a, you know, it's possible I did something wrong, but you
hopefully get the idea of what we're doing.

So now because we have this, we can go and do our admin and context builder and this
is now redundant. I'm gonna get rid of add the fall groups and then I will get at rid
of this line up here. That was adding those default groups. So have you ever know and
refresh the documentation? You're going to see that at the bottom here with models.
There are now and top cons of models and this is the downside of this approach. It's
like total overkill if you look at the models, but if you look at any of the
individual um, uh, points, it's not going to show you exactly.

Okay. Better example there. If you look at any individual end points like the user's
endpoint, it's going to show you exactly like what you're going to get back. It's
going to be accurate from those groups. Now, if you're ready to test in that now it's
possible that when you refresh, you did actually didn't see any changes. Uh, and if
you didn't, if you didn't see any changes yet, we can fix that by running vin console
cache, colon clear. Basically this class, this add auto group's resource metadata
factory, its results are cached and it's such a low level function that whenever you
make tweaks to this function, the cache is not automatically rebuilt. You need to
rebuild it by hand. We're actually going to fix that in a few minutes. But for now,
if you're not seeing results or if you're playing around in this class and you're not
seeing an update here, or when you use your API, that's because you need to manually
clear your cache. Now there's one of the problem here and you'll see it if you run
your tests.

Fair enough. It was one of the problem here and you'll see it. If you look at, for
example, the get end point for cheeses, it says that it's going to return nothing.
This is just a small detail. That's a, the way that my metadata factory calculates
the kind of a short name. So I use her colon reed is now actually instead of us. Um,
the way it's actually getting that short name now is it's actually getting it from
the, um, the short name of our uh, resource. So let me actually show you an example
here. If I try out my /cheeses end point, hit execute, okay. That makes an Ajax
requests. You can actually see it makes sense. There's a checks request. And if I
open up that Ajax request, then my profile and go to API platform and I can actually
see the um, resource metadata that looks familiar for that, for this particular
entity.

And if you look at it normalization context, it's groups are, it's still as cheese
under sort. We're listening and reading cheeses thing I didn't get because those are
the things that we are still manually putting on it. But the ones that are being
added now are called cheeses, calling read cheeses, call an item, get and cheeses
item get. So basically because of my new system, I need to update the uh, my code
instead of my cheese list in class to actually put things in that new cheeses thing.
But before I get that, there's actually a little tweak I want to make. Uh, we updated
this short name in episode one of this tutorial so that our URLs instead of being
/API /cheese_listing would be /abs, less cheeses. Well you can actually just make
this short name cheese when you do that.

If you refresh the documentation, all of our URLs are still going to be slashed. APSS
cheeses. That's something the APM platform does automatically. It takes that short
name and actually automatically pluralizes it. That's nice because down here we can,
uh, keep these, a group named singular as well. So I'm actually going to do a find
and replace as soon as I'm, we see cheese under score listing colon here, we're going
to change that to cheese colon and we'll do a replace all on that. And then there's
actually one spot in the user class as well above the username field. I'll change
that to cheese as well.

Perfect. So let's go over refresh again and now they get end point for /aps as
cheeses yas it properly advertises that it's returning the correct thing. And in fact
when we try it second ago, it didn't return any fields. Now it has actually returning
all of those correct fields. So this is an awesome way to um, super advanced but an
awesome way to add groups, um, but to do them dynamically dynamic groups that watch
us show up in your API documentation. The only problem is that as I mentioned this,
the results of this class are cached. So if you tried to make a tweak to any real
logic in here, you're going to need to clear your cache every time you do that. Not
the end of the world, but a bit of a pain in the butt, but actually it's even a bit
crazier than that.

If you did have some situation where you want it to add a dynamic class based on the
user, like maybe based on who is logged in, maybe you decide that you really want
your documentation to update automatically based on this rural admin. Well, if you do
that logic in your bed or that factory, it's going to cast one way and it's not going
to be dynamic. So if you want your metadata factory to not cache, then in services
.yaml, you can add a little option here called decoration priority and set this to
negative 20 this is again, super advanced, but as I mentioned earlier, when we
decorate this core factory, we might not be the only class decorating that. There may
be multiple levels of decoration by default. When you decorate the metadata factory,
we are the a f normally, normally when you decorate it, we would be the first thing
to decorate it. So it would be the most outer call, but eight API platform set up so
that they're cacheed decorators actually before us. [inaudible]

and that's why whenever we update something, the cache we never get called because
the cache, uh, uh, metadata factory, um, decorates us. So I sent in this declaration
priority to negative 20. We're purposely saying that we want to be even before the
cache metadata factory, which means we will dynamically update. So now you actually
can put dynamic logic inside of that phone. For example, if I refresh the page right
now and look down on my models, so you can see here all my different models. But if
we go into the Meta, that effect or interface and we just change, I'll just put food
on the end of one of these a second ago. If we had done that and refreshed, there
would've been no change. But now you can actually see that it is reading that. So
then the thing about this is that all the core logic for reading the annotations,
that's all still cacheed for the Ana, the API resource annotation.

The only thing it's not caching is our logic in auto group, auto group Resource
Metadata, factory interface. So you do need to be aware, uh, not to do much work
inside of here because this class, this function is called many times and every
request. So it needs to be lightning quick. You may even decide to do some sort of
um, uh, internal caching maybe where we keep like a private property up here called
Resource Metadatas and you store the resource meta-data for each class inside of
there and just return it if it's already there, just to save yourself from, uh,
you're doing the same logic for the same resource class on every single request. So
anyways, if that, if this one over your head a little bit, don't worry about it. This
is just a really advanced feature, low-level feature, um, that I thought might come
in handy for some of you. So, um, this is how you take care of it.