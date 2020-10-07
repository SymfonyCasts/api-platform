# Pagination Context

Coming soon...

We sort of have page nation working except that the number of pages and the items per
page are hard coded. And we're always returning all of the daily stats, no matter
which page we're on. So it's not really working time to make our page Nader actually
work to do that. There are two things that we need to know inside of our daily stats
page Nader. We need to know what the current page is, which is normally in the URL
and the max results per page that we should be showing. If we add those two pieces of
information here, we could fill in all of the methods. So let's actually have these
be past in past in the constructor. So I will add a intercurrent page and an int max
results to the constructor, and I'll even add a comma to separate those arguments.
Then I'll hit alt enter and go to, and this last properties to create those two
properties on top.

Perfect. So forget current page. This is now return this->current page and get items
per page is now return this->max results. So those two methods are simple, but even
the other methods we can now fill in. So for example, get last page. We need to do a
little calculation here. Now, actually paste that in. So we can actually return this
return. The ceiling of this arrow, get total items divided by this arrow, get items
per page. And then for some reason that is basically zero. We will set it to one and
one.is a nice way to get a float and then get total items. This is actually the total
number of items out of all the pages. And we here again, return this->stats helper,
and whether there's a method on there called count. And what that column does is it
returns the total number of results, not just the number of results on this page.
Then finally down here, the last method here yep. Is going to be in get iterator. So
here we need to actually figure out what are the based on the current page, which
results should we actually be showing? So first thing I'll do is I'll say is we can
say offset equals. This is again, one of those kinds of calculations, which I'll
paste in.

Is there offsetting was the current page minus one times items per page. And thanks
to that down here in fetch money, I already said the federal money to actually allow
a limit and an offset. So here I'm actually going to pass this arrow, get items per
page, which is the limit. And then the offset is the offset variable. Phew. Okay. Our
page Nader is now 100% ready to see if things are working over in daily SAS provider.
We now need to pass it the current page and the max results to start. Let's just
hard-code those. So for a current page, let's put one and formats per page. Let's put
three. So that the page nation is really obvious. Alright, let's try it. As soon as I
go over refresh and let's see. Awesome. Yes. Three results here, total items 30,
which is correct. And you can see that we're currently the first page is page one.
The next page is page two, and apparently this will go, it'll take us 10 pages to get
through all 30 results. Our page Nader is alive.

All we need to do now is remove these hard core values. So how do we figure out what
the current pages or the max item per page that we should be showing what we could
just choose to? Hard-code any number that we want here, but technically the max items
per page is something that is configurable inside of our API resource annotation. And
the same is kind of true for the page. The current page, we get technically read that
off as a read the query parameter off of the request, but API platform already has
the information. So where is this information? It's hiding in a service called page a
nation, a service that we can auto wire. So at a second argument to daily SAS
provider called page and nation, the one from the data provider page nation, I'll hit
all to enter and go to initialize properties to create that property and set it not
down here and get collection.

The pagination object is just kind of a, an object that it knows everything about the
current page nation situation. So as methods on it, for example, like get a get page
or get offset, we're going to use a kind of strange method on there called get to
page nation where it returns all the information at once. So what we can say here is
actually the list. I know that's a method I basically never use, but list page offset
limit = this->page. Nation->gets page, get, get to page nation and here we're going
to pass it the resource class and the operation name now real quick. Before I talk
about this method, notice there's a third argument here, which is the context. I'm
not going to pass that because I don't have a context in this method, but if you did
want the kind of full features of the page nation system, um, there are a couple
educates situations where the pagination will change based on the context.

And so if you want to take those into account, you just need to make sure that your
class implements a context, aware collection, data provider interface, and then you
will have a context argument here and you can pass context to get page nation.
Anyways, we'll get paid donation actually returns. I'll actually hold command to jump
into it. Is it returns an array or the first thing is the current page. The second is
the offset. And the third thing is the limit. So we're using the kind of strange list
here, which actually creates three new variables page set to the first, the zero
index, offsets that to the one index and limit set of the two index, a strange
function. But we can use that down here because now we can say page, and then we can
say limit, and we don't actually need to use the offset because we're calculating the
offset, uh, inside of the daily state stats page.

And it are already all right, let's try it, move over. Refresh this time we get. So
let's see here. Hmm. It looks like it is not paging anything. And in fact of the
problem is that API platform by default allows 30 items per page. We have 30 items,
so we're not seeing we don't need page nation with this. So let's actually limit this
to showing seven results per page, which kind of makes sense. It would be a we'll
show, the last show one week of results per page. So to do that, go over to our daily
stats, uh, object. And one of the items that you can pass here is page a nation items
per page. And let's set that to seven. That is why we read the max per page from API
platform is that'll hard coding it so it can read this configuration. Awesome. Now,
when we move over and try it, beautiful, seven items, five pages total say hello to
our home rolled page and nation. Next we have our get item, get collection,
operation, working our get item, operation working, um, even with page nation. Let's
see if we can get the put operation to work the update operation for our daily stats.

