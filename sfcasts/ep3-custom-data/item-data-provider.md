# Item Data Provider

Coming soon...

We've not successfully used our collection data provider to add a custom is me field
to our collection. End point. So meaning if we go to /API /users, we can see it
there. But if we go to an individual item, we get this air because the ismy field is
not being set there. And we can see this in our tests. So I've got to use as a
reminder and use a resource test I'll copy test, get user. We run Symfony PHP, bin
feeds, unit that's just filter = test, get user. We're going to see that same 500 air
that we were just seeing in our browser.

Oh, and actually we don't instead of fails in a slightly different way, you know,
this is actually a proof of a little improvement. I need to make my test. I make the
request here, but what I should really do is do a little, this assert response status
code is equal to 200. Now, if we were on that test, we'll see that it wasn't that our
JSON was actually wrong, but it was that the entire page is failing with a 500 air.
And of course the error, JSON didn't contain that field. Anyways, we have this test
failing and we need to add initialize. The is me field there as well. So what we need
then is simple. We have a collection data provider. We now need an item data
provider. So you can do this in two separate classes, but it's totally fine to add
this, the, both the collection data provider and the item data provider to the same
class.

So I'm going to implement a third interface here called de normalized, aware the
normalized identifiers, aware item, data provider interface. Wow. On that name,
right? The reason I'm using this crazy class, which if you open it, you can see
extends item data provider interface is because the core doctrine item made provider
implements this. So I want to implement the same thing so I can pass it the same
arguments. All right. Now, down here, I'm going to implement that method. So I'll go
to implement command code, generate or Command + N on a Mac and implement get item.
And then immediately what we're going to do is inject that core doctrine item
provider.

So up here, I'm going to say item data, provider interface, and I'll call this item
data provider. And then I'll initialize properties on that as well. And because I'm a
little anal, I'll make sure that that is the second argument by order. And then down
here inside, get item, we will just return this arrow, get item, resource, class ID,
operation, name, and also context. Alright. To tell Symfony to pass us this specific
doctrine item provider, we just need to go back to services .yaml and we're going to
bind another argument here. So the name of the argument is item data provider. So
I'll copy that. I had a data provider set to copy the same service ID. And if you
look this up, no surprise. You're going to find out that it's the same name, but
called item data provider. Beautiful.

So now that this should be working, let's get in there and add the custom is me
field. So first instead of returning, this will say item = and I'll put a little PHP
doc above this that we know at this point, this is going to be a user object or no,
because it's possible that this is an ID. That's not fond the database. So it could
be no. And to prevent that being a problem, we'll say, if not, item, then, then
return. No down here. We know we have a user object. So we can say item->set is me.
This->security->get user = = = item. And at the bottom we will return item. Phew. All
right, let's try the test now. And Oh, it's still fails.

Look at that maximum function. Nesting level of 256 reached a boarding. This tells me
that we are not injecting the correct item. Let's go back and see what's going on. So
specifically the problem is coming on user data provider line 43. Oh, you probably
spotted the error when I did it. Item data, provider arrow, get item. Come on, Ryan.
All right. Now when we try it. Got it. Perfect. So great. Thanks to this. We now have
an ismy custom plate, that custom field on both the item called item end points, the
collection of points, and it appears inside of our API properly documented beautiful,
but there is one last spot where we're missing it. Check this out and user resource
test. The first test in here is to create a user. So I'm actually going to copy that
method name and we'll rerun our test command with dash filter = test create user, and
it explodes 500 air. And it says the is me field. Hasn't been initialized. So if you
think about it, this makes sense.

That's the one situation where there's the user data providers never called. This is
called only. This is called when you are loading many existing items or an existing
item, but when you're creating a new item, the data provider is never called. So the
fix for this is actually to also make sure that we set this in the data per sister.
So when you use your data, persister the idea is that if the item is new, we want to
set that field. So up here, I'm going to add one more argument, which is security,
security. Then I'll initialize that property.

And then down here, I actually could put it in this. If statement here, that's where
we know it's new. It doesn't hurt to set it every time something has saved. So I'll
say data->set is me. This->security->get user = = = data. Now when you're on it,
we've got it. So this is a really kind of natural way inside of API platform to
happen entity, but also have the ability to add some custom fields that are truly
custom, that requires services to initialize that data. It's not the simplest thing.
It does require a little bit of work because you need to have a user data provider
that both provides collections and items. And you also need to make sure you set it
in the user data per sister, if you need it to show up in all those situations. So I
mentioned before we did this, that there was a couple other ways to, uh, accomplish
this. And really what I meant was let's talk about those next.

