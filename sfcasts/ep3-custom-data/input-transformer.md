# Input Data Transformer

Coming soon...

This also just fixed something in our documentation. Go back to the docs tab...
actually, I'm going to open a new tab so I don't lose my testing data.

On the original tab, until now, when we hit "Try it out", it only listed the
`description` field in the example JSON. The docs didn't think that `title`,
`owner` and price were fields that were *allowed* to be sent.

But now on the new version of the docs, when we hit "Try it out", huh? It *does*
now recognize that `owner` is a field we can set. It looks like there's a little
bug with input DTOs where the documentation doesn't notice that a field exists
until it has some metadata on it. So as soon as we added the type, suddenly the
documentation noticed it.

And... that's fine because we *do* want types on all of our properties. Back in
the class, above `title`, add `@var string`, `@var int` for price and above
`isPublished`, `@var bool`.

By the way, if you're wondering why `description` was always in the docs, remember
that the `description` field comes from the `setTextDescription()` method, which
*does* have metadata above it and an argument with a type-hint.

Let's check the docs now: refresh, go back to the POST endpoint, hit, "Try it out"
and... yes! Now it sees as the fields.

## Finishing the transform Logic

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
