# Auto Groups

Coming soon...

I want to show you guys something kind of scary. Something that I wouldn't normally
do. Normally. I try to show how to use something, how to, how to use great tools and
great ways to build something. And I try not to like hack too much into the tools
cause if anytime you're, anytime you're, cause if you're hacking way, way into tools,
it's more likely that those kind of hacks are gonna are going to break later. But 
API Platform does a really good job with backwards dependability. So I feel a little
safer showing you, uh, this hacks a hold on your seats cause this is gonna get a
little bit crazy. So what are we going to do? Well we've been following the standard
inside of our entities of a naming convention for our groups. So for normalization
we're using `cheese_listing:read` and four denormalization. We're using it
`cheese_listing:write` And then when we need even more control,
like we want the get operation to have an extra view. We're following another
convention by adding another thing called `cheese_listing:item:get`
So if you have lots of different behaviors on your different
operations, you're going to end up with a lot of these normalization context in 
denormalization contexts.

And it's going to get a little bit ugly. We're trying to keep things very, very
systemized. Um, but it's easy to kind of make a mistake and, and uh, break out of
these, especially if you are going to be repeating this kind of kind of customer
behavior on multiple entities. So here's the idea. We not an `AdminGroupsContextBuilder`
We have the ability to dynamically and PHP code `@Groups()`. So could we also
add all of these groups automatically? Could we, you know, see that this is when
we're normalizing a cheese listing, can we automatically add `cheese_listing:read`
and could we automatically also add `cheese_listing:item:get`, since we are
getting the item for a `CheeseListing`, the answer is yes, but that might not be
exactly like you expect it to look. So let's start an `AdminGroupsContextBuilder`.

I'm going to start by at the bottom of `AdminGroupsContextBuilder` in the wrong
class at the bottom of the function, I'm gonna Paste it a new function called 
`private function addDefaultGroups()`. This is something you can get from your code blocks
in this page. Basically what this is gonna do is this is going to look at which
entity is being serialized, whether or not it's normalization or denormalization and
exactly which operations going to take. And it's going to add three groups
automatically every single time. One of them is going to be easy. It's gonna be kind
of a `{classname}:{read/write}`. So `user:read` or `cheese_listing:read` or
`cheese_listing:write` And so that's gonna fill out kind of a normal, uh,
normalization of denormalization group we've been using. It's then also gonna add
another one that's a little more specific, which is, um, the class name user and then
item or collection, whether or not you are performing an item Operation or
collection operation and then recall and right on that. So allow us to do different
things based on a foyer, reading a collection of users versus um, maybe like reading
a single item of a user. And the last thing to get really specific is lad something
where it's the class name. So user and then it's `item/collection` again, but
instead of just read or write this time it would actually be the operation name. So
`GET`, `POST`, `PUT` those types of things. So that would allow us to really get down and um,
and do something custom.

So to add this stuff up here, basically we're going to say

`$context['groups'] = $context['groups']  ?? [];`
So this is basically to make sure that the groups key is
set and if it's not set it's going to set it to an empty `array`. Then we can say 
`$context['groups'] = array_merge($context['groups'], `
and then `$this->addDefaultGroups()` and that needs to be passed `$context`. And also
whether or not it's `$normalization`, which is an argument. So normalization, that's it
not on here. I'll get rid of this if statement for `$context['groups']`. Cause now 
`$context['groups']` is always going to be set. And down here, just as a of a matter of cleanup,
I'll say `$context['groups'] = array_unique($context['groups'])`

And that's it. So at this point I could actually go into `CheeseListing` for example,
and remove and remove the normalization of denormalization context. In fact, let's
even just try to temporarily ship should the run over here and 
 
```terminal
php bin/phpunit
```
 
 We had
been slashed piece of unit and even though I drastically changed my normalization
groups as I should

and it's just and Yep, that works. So that was easy, right? Well the problem with
this is that, remember I said a second ago when your documentation loads, it does not
read your context builder. So I've just removed that normalization context and de
normalization context, you're actually going to start to get really inaccurate data
about what your end are going to return. So when to refresh the documentation, if I
go and look at the good end point for a cheese for example, it's not going to tell me
it's really [inaudible]. It's returning everything. `description`, `shortDescription`
`owner`, um, `createdAtAgo` Uh, because now, now it's thinking the documentation is
thinking that uh, the, there is no normalization context so it's actually going to
return every single field in here.

No, I said that wrong. So if I refresh the page, if you looked at the individual get
she's on point and it actually says it's going to return the correct fields because
this, this, this is the one spot where we still have the normalization context on the
item operation. Forget. But if you looked at, for example, the collection operation
for get, it's going to tell you it's gonna return everything. `Id`, `title`, `description`,
`shortDescription` and `price` `createdAt` that `createdAtAgo` `isPublished` `owner`. It's not
actually going to return that if you, uh, if you tried this and it execute, you're
only going to get back to the actual fields that you expect. But the documentation is
wrong wrongs. The documentation doesn't read this stuff correctly. So how can we fix
that? Well, we're going to, we are going to fix it with something called a resource
metadata factory, which is kind of a wild thing. So let's do that next.