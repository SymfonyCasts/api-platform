# Input Transformer

Coming soon...

Status update. We have created a cheese listing input class with all the properties
and groups that we need. We've pointed, she's listening to use that. And we've
started creating a data transformer. That's going to take the cheesesteak input and
change it into a cheese listing. When somebody sends JSON to a post or put end point,
we've already started that by filling in part of supports transformation, this
dumping data to in context, when we looked at over here in a profiler, it's slightly
different than what we saw with the output transformer. This time, the data is an
array. This is the array that comes from the JSON's and JSON's DC realized into this
array and the array is passed to us. But the two argument is what we expect. It's
saying that we're going to convert to a jesus' name. The context, this argument down
here also has something interesting in input key with class said to cheese listing
input. That will also be important. So let's fill in supports Jennifer
transformation. First, I'm gonna start by saying if data is an instance of cheese
listing, which in our dump, it is not, it's an array, but if it is an instance of
cheese listing, we're going to return false. And I'll put a note here, uh, already
transformed. I'm not sure if this is needed.

I'm not sure if this is needed, but it's shown in the docs. There might be some edge
case where the transformer's called multiple times. I'm not sure, or it might be
totally worthless, but we'll keep it the real important thing down here. The real
more important is the return statement. Let's return. If the two = she's listing
::class and Zoltar gear context, left bracket input, and then lots of Rebecca class,
colon colon, no = = = cheese listing. What the hell

[inaudible]

= cheese listing input, ::class, then we'll return. True. The first part makes sense.
We want to return true, and we are converting into a Jesus thing. And the second part
isn't needed in our case, because we know that achieves listing will always have a
cheese listing input. We'll always use a as listing input, but technically you can
have different input classes on different operations for a single resource. So the
context tells you which input class should be used for this operation. So we're just
double checking that to make sure anyways, let's now to see if this is working dump
the same are all three arguments again, inside of transform. So if any luck, this
will now be called and then at the bottom, just to avoid an error for now, let's
return a new cheap, empty cheese listing object. Eventually we'll put data inside of
there.

All right. So let's spin over here. I'll leave the profiler open and let's just hit
execute to try it again. Go down here. We have the four same 400 errors before with
validation errors, because which makes sense. Cause we're returning a new cheese
listing. What if we go over to the profiler and hit latest, that should dump us to
the latest profiler and perfect. You can see that it's still dumping those same
things as coming from line 13. So we can say that this is coming from our dump here.
It's still dumping those, which proves that our transformer is being called now for
clarity. I'm actually going to rename this object to input here as cause that's going
to be an input and I'll add a little bit of documentation. Cause we know that this
will actually be a cheese listing input, which will help us with auto completion.

Okay? So now our job is simple, right? We just take this jesus' input object and we
move the data over onto Jesus. Dang easy. Wait a second. Check out the dump over
here. This is the input object that's being passed to us. So at this point, it's DC
realized that JSON into this cheese listing input, but look at owner that is not a
user object, that's a string. So are we supposed to somehow query for that object and
manually? Nope. One of the DC realized his job is to take each piece of data and the
JSON that we're sending and figure out what type it should be like a string date or
user object. And then it needs, it does whatever work is needed to change it into
that type. So API platform already has an item normalizer whose job was to change. I
R I strings into objects by querying the database.

So why isn't that happening in our case, this is after the DC Eliza has done its job.
So why isn't that happening? In our case, we'll check out our input class jesus'
input. If you look at the owner property APL from, well, really the DC realizer has
no idea that the owner property is supposed to be a user object. So let's help it
above this at, at VAR user try. Now I'll go over here again and just hit execute once
that finishes. I'll go back again to the profiler, hit latest, to jump us to the
latest profiler and check it out. Magically. Our owner is now a user object, so the
types are very powerful. When you talk about DC realizing there's also just fixed
something in our documentation, I'm gonna go back to my docs tab. I'm actually gonna
open a new tab. So I don't lose my kind of testing data there.

And a second ago, when we looked at our, our, uh, our tab here, it only actually,
when he hit try it, it only actually listed the description as a field. It didn't
think that title owner price were actually fields that were allowed. If you go in
here now and hit, try it out, you can see that. Now it does see owner in description.
So there's a bug with input DTS where the documentation doesn't notice that the field
exists until it has some metadata on it. So the fact that we added this type here
suddenly made the documentation notice that this is here. So that's fine because we
want really want types on all of our properties. Anyways, you just need to be aware
of this bug. So let's go over here and let's add above title at VAR string and at VAR
int and then above that is published, I'll say at VAR. Well, by the way, if you're
wondering why description was always here, remember description actually comes from
this set description method, which does have metadata above it. And also it's able
to, um, it's able to ring the string, uh, type from the center to know that this is
supposed to be a string column, a field. All right. So now when I refresh this and we
go back to our post endpoint and hit, try it out, you can see, yes, it does actually
see all of those fields now.

All right. So let's finish our job here inside of our data transformer, which is just
put all the data into CI's listing. So instead of returning, let's say she's listing
= new cheese listing and I can pass the titles, the first argument. So I'll say
input,->title, and then she's listing set description, input,->description, she's
listing set price, and put->price. She is listing set owner input,->owner, which we
know will be that user object. And then she is listing->set is published to
input.->is published, and then we will return that she's listing from the bottom.
Okay. Moment of truth. I'll close my extra tab over here, go back to my original
documentation tab, hit execute, and it fails with argument one past two cheeses and
set price must be of type and no given. So actually I'm missing my price field up
here, which is causing there to be a type error.

When I pass Noldus at price, we're actually going to talk about this later. When we
talk about validation for now, let's present, pretend that we're always going to pass
every field that we need. So I'll pass a price set to 2000, try it again. And of
course I have the same thing with set is published really this I meant to default to
false. So the better way to fix that is actually just to have a default value in the
input. Now let's see if I remembered everything and got it. Two Oh one status code
that was just created.

That is awesome. So it's a three step process. First API platform, DC realizes
whatever JSON we send into our CI's listing input object. Second, we transform that
cheese listing input, object image. We real cheese listing and our data transformer.
And then third, the norm doc, the normal doctrine data persister saves things like
always. So this works great, but the put up, but if, look at the docs, this put up
end point here that updates the cheese resource. This will actually not work yet.
Why? Because we're always creating a new cheesing object, which would cause a new
item to be inserted into the database. Just getting the update to work with DTO
input. DTO is a little bit tricky. So let's handle that next.

