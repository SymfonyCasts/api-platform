# Input DTO Class

If you liked the *output* DTO, we can do the *same* thing for handling the
*input* data for a resource. Basically, we create a class that looks *just* like
the input fields that are sent when creating our updating a `CheeseListing` and
then API Platform will start deserializing the JSON to create *that* object. Then,
our job - via a data transformer - will be to convert that input object into the
final `CheeseListing` so that API Platform can save it.

## Creating the CheeseListingInput

So... it's the *exact* same idea as the output, just... the other direction.
Though, there will be a few new, interesting and tricky pieces.

Let's get started! In the `src/Dto/` directory, create a new class called
`CheeseListingInput`. This time, let's move *all* of the fields that we can
currently *send* to create or update a `CheeseListing` - like `title` and `price`,
into here.

Start with a `public $title`, and put some PHPDoc on it. The in `CheeseListing`,
steal `@Groups`, delete it - we won't need any groups here anymore - and paste
the `@Group` on top of the new `title` property. Oh, but, I'll re-type `up` on
`Group` and hit tab so that PhpStorm adds the `use` statement for me.

The other fields we need are `public price$`, `owner` and `isPublished`. Let's
go steal *their* groups: find `price`, move its `@Groups` over, then for `owner`,
do the same... and finally, grab the `@Groups` for `isPublished`.

Now, there is *one* other field: search for `groups`. Yep, `setTextDescription`.
This allows the user to send a `description` field... but ultimately the
deserialization process calls `setTextDescription`, and then we call `nl2br` on
it. We want to do the *exact* same thing in the input class. So, copy this method,
delete it, and paste it at the bottom of `CheeseListingInput`. Re-type the end
of `@SerializedName` and auto-complete to get its `use` statement.

Of course, when the deserializer *calls* this method, we're storing the end result
on a `description` property... which doesn't exist yep. Let's add it:
`public $description`. But we're *not* going to put this in any groups because
we *don't* want this field to be writable directly - it's just there to store
data.

Ok! Back in `CheeseListing`, if we search for "Groups", cool! The gray means that
both the `Groups` and `SerializedName` `use` statements are *not* needed anymore
because we have moved *all* of this stuff. Remove both.

There is now *nothing* inside of `CheeeListing` about serializing or deserializing.

Ok! Our `CheeseListingInput` is ready! To tell API Platform to *use* it, it's
the same as `output`. On `CheeesListing,` add `input=`, remove the quotes, and
say `CheeseListingInput::class`. Don't forget to add the `use` statement manually:
`use CheeseListingInput`.

## How Deserializing Works

Ok! We don't have a data transformer yet, but this *should* be enough to get this
to show up in our docs. Find your browser and refresh the docs homepage. Go
down to the POST endpoint for cheeses and hit "Try it". And... Oh! Interesting.
It only shows the `description` field here, which is odd... but let's ignore that
for now.

Try the endpoint with a `title`, `description` and a valid `owner`: `/api/users/1`.
Double-check your database to make sure that's a *real* user.

Testing time! Hit Execute and... 400 error! We didn't expect it to work yet, but
the *way* it doesn't work is the cool part: we get a bunch of "this value should
not be blank" errors on title, description and price... even though we actually
*sent* some of these fields!

Here's what's going on: thanks to the `input=` that we just added, when we send
JSON to a `CheeseListing` operation, the serializer is now taking that JSON and
using it to create a `CheeseListingInput` object - *not* a `CheeseListing` object.

But... because we haven't created a data transformer yet, nothing ever *takes*
that `CheeseListingInput` object and converts it into a `CheeseListing`. So...
API Platform just creates an *empty* `CheeseListing`... then runs validation on
that empty object and - if that had passed - it would try to save it.

To fix this, we know the answer: we need a data transformer.

## Creating the DataTransformer

Inside of the `DataTransformer` directory, create a new PHP class called
`CheeseListingInputDataTransformer`. Make this implement, of course,
`DataTransformerInterface` and then go to Code -> Generate - or Command + N on
a Mac - and generate the two methods we need.

This time, the `supports()` method will look a *little* bit different. Dump
all three arguments... and actually use *dump()* instead of `dd()` because we're
going to test this inside the interactive docs... and reading HTML inside the
response looks ugly there. But `dump()` will save the HTML to the *profiler* so
we easily look ata it.

Anyways, dump `$data`, `$to` and `$context`. And at the bottom return `false`
just so this method doesn't cause an error: it *needs* to return a boolean.

Ok: move over, hit "Execute" and... huh? It... actually *did* dump the variables
right in the response... this is what I was trying to avoid? Normally, if you
use `dump()` instead of `dd()`, it doesn't dump in the *response*, it instead
saves it to the profiler.

The reason this isn't happening is that I'm missing a bundle that adds the
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
