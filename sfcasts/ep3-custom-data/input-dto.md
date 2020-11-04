# Input DTO Class

If you liked the *output* DTO, we can do the *same* thing for handling the
*input* data for a resource. Basically, we create a class that looks *just* like
the input fields that are sent when creating or updating a `CheeseListing` and
then API Platform will start deserializing the JSON to create *that* object. Then,
our job - via a data transformer - will be to convert that input object into the
final `CheeseListing` so that API Platform can save it.

## Creating the CheeseListingInput

So... it's the *exact* same idea as the output, just... the other direction.
Though, there will be a few interesting and tricky pieces.

Let's get started! In the `src/Dto/` directory, create a new class called
`CheeseListingInput`:

[[[ code('91da4d499a') ]]]

This time, let's move *all* of the fields that we can currently *send* to create
or update a `CheeseListing` - like `title` and `price`,  into here.

Start with `public $title` and put some PHPDoc on it. Then in `CheeseListing`,
steal `@Groups`, delete it - we won't need any groups here anymore - and paste
the `@Groups` on top of the new `title` property:

[[[ code('e188854af1') ]]]

Oh, but, I'll re-type the end  of this and hit tab so that PhpStorm adds the
`use` statement for me:

[[[ code('85e80c2bfc') ]]]

The other fields we need are `public $price`, `owner` and `isPublished`. Let's
go steal *their* groups: find `price`, move its `@Groups` over, then for `owner`,
do the same... and finally, grab the `@Groups` for `isPublished`:

[[[ code('f10fdaffb9') ]]]

There *is* one other field: search for `groups`. Yep, `setTextDescription()`:

[[[ code('be02f08130') ]]]

This allows the user to send a `description` field... but ultimately the
deserialization process calls `setTextDescription()` and then *we* call `nl2br` on
it. We want to do the *exact* same thing in the input class. So, copy this method,
delete it, and paste it at the bottom of `CheeseListingInput`. Re-type the end
of `@SerializedName` and auto-complete it to get the `use` statement:

[[[ code('b6132dba95') ]]]

Of course, when the deserializer *calls* this method, we're storing the end result
on a `description` property... which doesn't exist yet. Let's add it:
`public $description`:

[[[ code('6634fc372a') ]]]

But we're *not* going to put this in any groups because we *don't* want this
field to be writable directly: it's just there to store data.

Ok! Back in `CheeseListing`, if we search for "Groups", cool! The gray means that
both the `Groups` and `SerializedName` `use` statements are *not* needed anymore
because we have moved *all* of this stuff. Delete both:

[[[ code('77593afa40') ]]]

There is now *nothing* inside of `CheeseListing` about serializing or deserializing.

Ok! Our `CheeseListingInput` is ready! To tell API Platform to *use* it, it's
the same as `output`. On `CheeseListing,` add `input=`, remove the quotes, and
say `CheeseListingInput::class`. Don't forget to add the `use` statement manually:
`use CheeseListingInput`:

[[[ code('22124444a4') ]]]

## How Deserializing Works

We don't have a data transformer yet, but this *should* be enough to get this
to show up in our docs. Find your browser and refresh the docs homepage. Go
down to the POST endpoint for cheeses and hit "Try it". And... Oh! Interesting.
It only shows the `description` field here, which is odd... but let's ignore that
for now.

Try the endpoint with `title`, `description` and a valid `owner`: `/api/users/1`.
Double-check your database to make sure that's a *real* user.

Testing time! Hit Execute and... 400 error! We didn't expect it to work yet, but
the *way* it doesn't work is the cool part: we get a bunch of "this value should
not be blank" errors on title, description and price... even though we *did*
send some of these fields!

Here's what's going on: thanks to the `input=` we added, when we send
JSON to a `CheeseListing` operation, the serializer is now taking that JSON and
deserializing it into a `CheeseListingInput` object - *not* a `CheeseListing` object.

But... because we haven't created a data transformer yet, nothing ever *takes*
that `CheeseListingInput` and converts it into a `CheeseListing`. So...
API Platform just creates an *empty* `CheeseListing`... then runs validation on
that empty object.

To fix this, we know the answer: we need a data transformer!

## Creating the DataTransformer

Inside of the `DataTransformer` directory, create a new PHP class called
`CheeseListingInputDataTransformer`. Make this implement, of course,
`DataTransformerInterface`:

[[[ code('3a2fec6b9f') ]]]

And then go to "Code"->"Generate" - or `Command` + `N` on a Mac - to generate
the two methods we need:

[[[ code('8f17f67464') ]]]

This time, the `supportsTransformation()` method will look a *little* bit different.
Dump all three arguments... and actually use *dump()* instead of `dd()` because we're
going to test this inside the interactive docs... and reading HTML inside the
response is ugly there. Using `dump()` will save the HTML to the *profiler* so
we can easily look at it.

Anyways, dump `$data`, `$to` and `$context`. And at the bottom return `false`
so this method doesn't cause an error: it *needs* to return a boolean:

[[[ code('55d09919b9') ]]]

Ok: move over, hit "Execute" and... huh? It... actually *did* dump the variables
right in the response... which is what I was trying to avoid! Normally, if you
use `dump()`, it doesn't dump in the *response*, it instead saves it to the profiler.

The reason this isn't happening... is that I'm missing a bundle that adds the
integration between the `dump()` function and the profiler. To install it, find
your terminal and run:

```terminal
composer require symfony/debug-bundle
```

Once this finishes... we *should* be able to go back to the browser, hit Execute
again and... perfect! A 400 error JSON response, but *no* HTML.

To see the dumped variables, go down to the web debug toolbar and open the last
request's profiler in a new tab. Nice! It automatically took me to the Debug
section: here are the `$data`, `$to` and `$context` variables.

Next: let's use this information to finish our data transformer and get this
thing working!
