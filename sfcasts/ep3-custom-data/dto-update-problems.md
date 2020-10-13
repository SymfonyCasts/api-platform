# Dto Update Problems

Coming soon...

Our input DTO now works to create new listings, but it does not work for updates yet.
Like if we used the put end point to update AGS listing, and the reason is simple,
our data transformer is always creating new cheese listing objects. What we really
need to do is query for the existing jesus' and object. If this is in for an update
and doing this as easy enough, when we make a put request or really any item, or
really any Ida operation EBIT platform already queries for the underlying keys
listing entity object, we just need to use that in our transformer. How API platform
puts the object, the cheese listening object into the context, checking this out. We
can say, if is set context less for bracket and then use abstract item normalizer
colon, colon object to populate. This is actually a feature that's part of, um, Oh,
let me fix my syntax.

This is actually related to a feature that's just part of Symfony serializer.
Normally, if you DC realize some JSON into an object, it's going to create a new
object, but if you want it to update an existing object, then you actually pass the
object on this key, inside the context, and then it will DC realize and update that
object. So if you got bought from leverages this internally, and in the case of an
input DTO, the object to populate is actually the underlying entity, the cheese
listing object. So here we can say cheese listing = and I'll copy that long context
thing. jesus' thing = context, abstract item normalizer object to populate else. And
then I'll copy and move up. The cheese listing = new cheese listing line. That's it
notice that this means that the title can't be updated won't won't be changeable on
update. And that was actually, and that's because the only way to set the cheese
listing on our title and cheese listing is actually being a constructor. And that was
actually already the case before the title is only settable because the title is only
settable on the cheese listing constructor. There's no set. It's not a constructor,
but there's no set title.

The title was never used on update. APL platform does a great job of making our API
work like our classes work. Anyways, let's try this over here. I still have my post
end point up. I'm actually gonna copy all the data I sent with my post end point. And
let's see this created a new one called /API /jesus' /53. So I'll close up that post
endpoint. Let's open up the put end point. I'll hit try it out. But 53 for the ID and
here I will paste in all of the fields. Well, let's change the price to be 5,000. And
when we execute, Oh, we got 500 air cannot call them. Owner ID cannot be no figuring
out. This takes a little bit of digging. Here's the problem. If you look over and
look at our cheeses and input, the owner is in the cheese collection post group,
which means it's not actually a field that's settable on update only on create so
effectively, even though we're sending this owner a property here, that's not being
a, that's basically being ignored.

The problem is that then is when it's DC realize into the cheese listing input object
inside of our transformer, this cheese listing, this input has its owner is no, which
ultimately sets a no on our cheese listing. And it saves, tries to save with the no
owner. But actually if you back up, there's a bigger problem. That's causing this in
reality, when we're past the cheese listing input object, if a field, if a property
on it is no, we don't really know if that field was no, because we actually sent
Knoll for a field like for example, title Knoll, or if we simply left off that field,
like onerous is effectively not even there because it's being ignored.

So we can't tell on the cheeses and input whether a field is actually set as no, or
whether it's just simply being wasn't sent and should therefore be ignored. Ideally
this cheese was an input object would first be initialized using the data from the
cheese listing on a database, for example, where owner is set to a user object and
then it would be did JSON, the, the JSON Fields that were sent would get serialized
onto, but that doesn't happen. So we basically don't have enough information in this
function to figure out if these fields were actually, if these fields are no, if they
were actually sent or not, this is actually a missing feature in API platform. Well,
it was until we talked to the API platform team about it, and then they added this
feature for us. You can see it if you go to /API platform /core /poll /37 Oh one,
which talks about a new feature called pre hydrate, which is going to be part of
eight HubSpot from 2.6.

And the way it's going to work is by the way I have a cool, uh, get up login is it's
going to allow us to implement a new data transformer initializer interface on our
data transformer, which is going to allow us to have this initialize method. When as
soon as we have this, this is going to be called before our transform method. And the
job here will be to grab the object, to populate off of the context and use it to
create an initialize the data on our input object. So ultimately return our input
object. Then, then now that our input object has the data initialized on it. When it
calls transform this input object, won't be empty. This input object will, um, be the
result of having that prefilled data. And then the JSON, uh, DC realized onto it.
This should be part of API from 2.6, but since it's not available yet, let's
implement this feature ourselves. How by leveraging a trick inside a custom
normalizer that's next.

