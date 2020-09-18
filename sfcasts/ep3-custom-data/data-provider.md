# Data Provider

Coming soon...

Coming soon...

Most of the time, the field names and our API, Matt's the properties in our entity,
but we know that this doesn't always need to be the case in the previous. There were
several times when we needed to add a custom field. For example, in cheese listing,
we added a short description API field by adding a good short description method and
putting it in the cheese colon Reed group. We did the same with set text description
this as a text description field that can be read or written, actually it can just be
written, but you can only take this so far. If the STEM field requires a service to
calculate well, you can't do that as declasse because you don't have access to
services.

In the previous tutorial, we had this exhibit back situation. When we wanted to add
an is me Boolean field to user, we solved that by using a custom normalizer source
serializer normalizer user normalizer. We basically added an extra field right before
the JSON was generated, but this solution had a flaw. If you check all the
documentation on the user resource, I'll open the get operation and a down here, I
can kind of look at the schema of what's going to be returned. It does not mention is
me. You will get, it does feel back, but our docs have no idea that it will be there
and maybe you don't care. How could it, well, we add a custom field and have its
property documented. There are a few ways, including a completely custom API resource
class and using an output DTO. We'll talk about both of these, but in most
situations, there is a simpler solution and user resource test.

If you find test, get user, we do have a test that tests for the ismy feel. No, we
don't. I've got to copy it over. If you find tests, get user, this test does look for
that is me field. It's false in the first test. And then true after we log in as that
user copy that method name. And let's just make sure that this test passes. So
Symfony PHP, fin /P unit dash dash filter = test get user. And right now it does,
which proves that our normalizer is adding. That is me feel so now let's break things
and use or not. Normalizer I'm going to remove, this is me property. Actually, we can
just return this arrow. Normalizer->normalized directly. Now this still has a custom
group, but it no longer as the custom field. So let me try the test now.

Beautiful. It is failing. Okay. So what is the other way to solve this? It's really
beautifully simple. The idea is to create a new field inside of user, but not to
persist it to the database. And inside that new field, we will hold the custom data
and then expose it like a normal field in our API. Yep. That's it? Oh, but there is
one problem. If this field isn't stored in the database and we need a service to get
its value, how do we set that feel? Great question. And there are about 47 different
ways. Okay. Not 47, but there are a few ways and we'll look into several of them
because different solutions will work best in different situations. Okay. Well, let's
think about how API platform works. When we make a request for like two /API /users
or any of these EPL platform somehow needs to load the objects or object that we're
requesting.

It does that with a system internally called its data provider system. So we have
data per sisters for saving stuff and data providers for loading stuff. There are
both collection data providers for the collection operations like be getting post and
item data providers that fat's just one object for the item operations. Normally we
don't need to think about this system because API platform has a built in doctrine
data provider that does it all for us. But if we want to load some extra data, a data
provider is the key. Okay. In the source directory, let's see, let's create a data
provider directory

And inside I'll create a new PHP class called user data provider. The idea is that we
will create a custom class and we will now take responsibility for loading user data.
And we'll start with the loading, the collection user data. So I'm going to make this
implement two interfaces, actually context aware collection, data provider interface.
Yep. That's a long name and also restricted data provider interface. Now, before we
talk about those, I'll go to the Code -> Generate menu or Command + N on a Mac and go
to "Implement Methods" and select both methods that are needed.

Okay. So to create a data provider, the only interface that you should need in theory
is actually something just called collection data provider interface. And if you look
at this context aware collection to inter data interface, it extends that one. So
we've seen this kind of thing before. There's the main interface, and then there's an
optional, stronger interface. And what that does is it adds the context argument to
both of our methods. So if your data provider needs the context, then you need to use
this interface. I'll show you why we need the context. In a few minutes. The
restricted data provider interface is actually what requires these supports method.
If we didn't have that API platform would start using our data provider for every
class. But in reality, we only want this to be called for user classes. So in the
supports method, we will return resource class = = = user ::class.

Perfect.

Now up here and get collection as its name suggests our job is to return the array of
users. So simple, right? We just need to queer the database and return every user.
Well, that's not going to be quite right, but let's try that first. First, I'll add a
public function_underscore construct, and we will inject the user repository user
repository. I'll do my trick of hitting all the enter and initializing the property
to create that property and set it up and then get collection. We can say return
this->user repository, arrow, find all, alright, let's go try it. Obviously. We're
not adding the ismy field yet. So I won't run our test, but we should be able to try
this in our API. I'll actually even use a shortcut here. I'll say /API /users dot
JSON, L D and Oh, if you get full authentication is required to access this, that's
our security system in action. And another tab, I'll go back to my homepage and you
can just hit log in right here. There we go. That authenticates us in the session.
And now we can refresh this page and got it.

And sweet it's working. Is it?

But is it actually using our new data provider? Did we need to register this anywhere
with Symfony? Well, if you look closely, you can tell it's using ours because we lost
something. Pagination, look, it was all 52 cheeses. It should only be listening 30,
and then it should be doing page a nation. So two things first, this proves that our
data provider has been used, uh, thanks to auto configuration. As long as you have a
class that implements the collection data provider interface, uh, API platform, we'll
find it and start calling it supports method. So that's great, but we also just lost
page nation in filtering. Sure. Our API might still advertise that page nation and
filtering exists, but it's a lie. None of that would actually work. It turns out that
the court doctrine data provided that normally loads, our data is actually
responsible for reading the page and the filters and changing the query accordingly.
So we do not want to lose that just by adding a new field. So next let's use our
favorite trick class, Def decoration to get that functionality back then we'll add
our custom field.
