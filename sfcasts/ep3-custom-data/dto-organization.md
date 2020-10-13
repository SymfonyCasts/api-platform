# Dto Organization

Coming soon...

It took a little bit of work, especially getting the update to work before APAP
platform 2.6, but our input output DTO system is alive, but our logic for converting
from keys listing to input and input and she's listing and Jesus, and your output is
not super organized. There's kind of code that looks sort of like this all over the
place. So let's do some cleanup. There's no right or wrong way to exactly organize
this kind of data transformation code, but let's see what we configure out. Let's
start in cheese listing input data transformer. This is where we're going from
[inaudible] listing input object into a Jesus thing, entity object. I'm going to
actually put all of this transformation going into the DTO objects themselves classes
themselves, because that's really their job. So start again, Jesus. The input I'm
going to create a new public function, create or update entity that takes eight
nullable he's listing she's listing argument and returns a cheese listing. And the
reason this is nullable is because, uh, inside of our data transformer, um, you know,
we might already have a choosing object or we might not have achieved listing object.

So actually we're going to start inside of our cheeses to input with that statement.
I'll basically say, if not, she's listening, if we were not past one and I'll say
cheese listing = new cheese listing. And of course this is where he passed in the
title. So now it's this->title. Most of the rest of the logic inside of here is just
this stuff down here. So I'm gonna copy of these four setter statements, move those
into the, uh, input class and then change input to this on all of those cases. As
soon as we have that at the bottom, we can return jesus' name. So that's a really
nice function. You could even unit test that very easily. If you want it to over in
our data transformer now to use this, it's pretty simple. We can start to buy a copy
of this, a first cheese listing line here, and actually I'm going to, um, lead those
if statements and just say Jesus = context.

Object to populate question Mark question Mark. No. So at this point, Jesus will
either be that Jesus didn't object or it will be no. And then down here we can return
straight to input, arrow, create or update entity and pass it cheese listing. That is
beautiful. All right. So next go to the D normalizer. This is where we go. The other
direction we go from from a cheese listing, it's the object, which we may or may not
have one into a, an input object. So I'm once again to go put this inside of my
cheeses to input this time, it's actually be a static function. So public static
function create from entity. So once again, take in a nullable cheese listing and
it's going to return itself.

Now I'll go steal some code from this other class. I'm basically going to steal kind
of the center section of the code here, move that, paste that into here, and then
update the entity here too. She's listing. So if not, she's listing return T to DTO,
and then I will delete this instance of Jack. We don't need that anymore. We know
we'll get a cheese listing. We'll keep this check inside of the, uh, D normalizer and
down here, we just want to change this to cheese entity, the cheese listing on these
five variables at the bottom, we can return DTO. Now, did you normalize their lives a
lot simpler? I'll keep this first line that gets the entity or sets it to know I'll
delete the next part. And then for the, um, if statement I'm actually going to
change, let's say if entity, and it's not an instance that she's listing that way, we
don't throw an exception when there's no.

And then down here on the bottom, we can go straight to returning she's listing
input, colon, colon create from entity. We'll pass it, that entity. So again, really,
really satisfying end result that can be easily unit tested. All right, let's do one
more thing. This is actually in the, uh, jeez listing output, a data transformer.
This is where we go from HES listing into H she's listing output. So I'll put this
code into cheese listing output. Once again, it's gonna be a static function because
we are creating a new one of these. So pelvic static function create from entity.

So exactly like we just had a second ago with a, this time, we know we'll have a
she's listing is we're printing an existing one and we will of course, return to
self. And it's out of here. I'll go steal the code from our output entirely. I'll
steal all of these lines of code, paste them in here. If you want, you can change
this to new self if you want it to, but that basically nothing else needs to change
on here and then transform. And our data transformer it's of course, as simple as
return she's listing output, colon, colon, cryptocurrency, and we pass it. She's
listing. Phew. So a little bit of work there, but isn't that a nice system. If you're
going to use DTO is like this. I really like having these things centralized into our
DTO classes. All right. So I did just change a lot of stuff. So I'm just going to go
over here and run our tests, Symfony PHP bin /PHP unit, because I know that I like to
make lots of mistakes and Hey, no mistakes this time. Awesome. So next one kind of
aspect of the input that we haven't talked about yet is how validation happens. Do we
validate the input object? Do we validate the entity object? How does that work? So
let's make our validation rock solid and next.

