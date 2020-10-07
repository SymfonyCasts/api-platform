# Custom Resource Put

Coming soon...

Back to the API documentation. Let's pretend that we can also update a stats entry.
If you were an admin, maybe we have some process that double-checks some data and
updates it specifically. I want to make the total visitors updatable. Alright cool.
So when update is the put operation, so let's bend over to daily stats and under item
operations, we will add put then, because this is the first time we will be
denormalizing I'll copy the normalization context and we'll add a de normalization
context with a groups of daily stats. Right? And then we can take this daily stats,
right class and above total visitors. We will also put that in the daily stats,
right. Group. Sweet. All right, let's give it a try. I know it's not going to work
yet, but it might kind of look like it's going to work. So first we do see our foot
operation here. That's awesome. Let me just execute the collection end point so we
can get a valid ID. Perfect. I'll copy this 2029 Oh three and down in the put
actually let me scroll back up so we can see that the total visitors on that. So the
one I copied the total visitors right now is 1,500.

So we go down here, I'll hit, try it out. I'll put that ID inside of there. And we'll
say total visitors a 500. And when we hit execute, it works. I'm just getting a
Durley doesn't work. It looks like it works because it is the total visitors is being
updated on the object because the serialization process is working, but it's not
actually saving. If we went back up here to our collection endpoint and hit execute
on this, you would still see that the first item has a total visitors of 1,500. What
we need is a data per sister. There is currently no data per sister for daily stats.
Awesome. We know how to do this. So in the source data processor directory, let's
create a new PHP class and we will call it daily stats per sister, or you got daily
stats, data per sister. If you want it to be a little more consistent than I'm being,
and we'll make this implement data per sister interface, then I'll go to code
generate or Command + N on a Mac and go to "Implement Methods" to add the three
methods that we need now, as usual supports is pretty easy. If a daily status is
being returned, we want to operate on it. So return data instance of daily stats.

Now for persistent remove, we don't actually need removed because we haven't added
the delete operation. We could add it if we want it to, but we don't, but I'm not
going to, Oh, over here, I mistyped my Deon data. So down and remove, I'm going to
just say, throw new exception, not supported that shouldn't ever be called anyways,
but in case we do something silly and add the delete operation, we'll get this
exception so that we know we need to implement it now for persist to really make this
work. What we should do is actually update the, um, the fake stats that JSON file
that we're reading from now doing that would be pretty boring and not that
interesting. So instead of doing that, I just want to see if this is working. If we
can get our put operation fully functional. So instead I'm going to log inside of
this persist method and log what the new total visitors should be. So to do that,
let's add a public function,_underscore construct, well auto wire, the lager
interface, lager argument, I'll hit all to enter and go to initialize properties to
create that property and set it now down a persist. Um, we know that this data here
is actually going to be in daily stats object. So I'm going to add a little bit of
piece Radack above this. I don't need the ad return, but I do want to say that the
data variable is going to be a daily stats object.

Now, inside of here, we can log with this era logger->info. I'll pass this sprint F
with update the visitors 2% D and for that I'll pass data arrow, total visitors. All
right, well, let's see if it works. We'll spin back over. I still have my
documentation or my documentation open. So let's just hit execute again on this. And
when it finishes, okay, no air, it still says total visitors, 500 to prove that our
data persistent was actually called. I'm going to go down here to the web debug
toolbar and open up the last request that was made here. So I'll hold the I'll right,
click and open this in a new tab. That takes me to the profile for that request. Now
I can go down to logs and perfect update the visitors to 500. So adding the put
operation was dead simple, and we can also use this obviously to, uh, to, to support
the post operation if we want it to support critic nuance. So next for cheese
listings, we added a bunch of built and filters to help us, uh, do things like, uh,
like cert, uh, searching and filtering the results. But what if you need completely
custom filter logic on an entity like this? Like you need to be able to search or
filter in a completely new way, or what if we want to add a filter to daily stats
where we don't have access to those builtin filters that only work for doctrine. It's
time to talk about creating completely custom filters.

