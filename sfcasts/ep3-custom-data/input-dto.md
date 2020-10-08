# Input Dto

Coming soon...

If you like this output class thing, we can do the same thing for your input format.
Basically, we create a class that looks just like the input fields that are sent to
create our update HES listing, and then API platform will DC realize that JSON, to
create that object, then our job via a data transformer will be to convert that input
object into a cheese listing so that API platform can finally save it. So it's the
exact same idea as the output, but just in the other direction. So let's get started
in the source DTO directory, let's create a new class called she's listing input. Now
this time I'm actually going to move over all of the fields that I, that are in my
input field input, uh, immediately like title, um, price and summer, all the ones. So
I'm actually going to start here with a public title and then put some PHP doc on
there.

And then over on the title field, I'm going to steal the at groups off of that and
delete it. We don't need ad groups on here at all anymore. And I'll paste the ad
group over on top of the title field. Uh, but notice I need to actually re type the P
here and hit tab to add the use statement for, to auto complete it. The other fields
that we have inside of here are public price owner and is published. All of those you
would find over here. If you look at it, a price down here, I'll steal the ag groups
off of that and then delete them.

And then owner looks that owner, if you look down here, actually has this cheese
collection post group. So I'll copy that and delete it and go move that over to my
input class. And then finally is published. That is a neat she's gone, right groups.
I'll go steal that group and delete it and put it above is published. Now there is
actually one other field in here. If I search for groups, it does actually set text
description. So this actually allows us to send a description field, but ultimately
when we send new, send it, it calls set, text description, and then we call an L to
BR on it. So we're gonna do the exact same thing inside of our input class. So now we
need to move this into our input class. It's all, copy it, delete it, and then paste
it in here.

And then ultimately, and then this uses assay realize names. I'll read type the M
there had tab to add the Euston before that. Now it's only when we call set
description, we set this on a description property. So I'm going to add a public
description property up here. We're not going to add a group on there because we're
not allowing that field to be written directly. It's just that we need to store the
data. One set text description is called snow over in she's listing. If I search for
groups, there are no groups. Even the annotation even isn't even used anymore. I can
you remove ad groups and the serialized name use Damon. There's nothing inside here
about a serialization of our properties. Okay. So we have Archie's listing input to
actually tell API offering to use it. It's the same as output up here and on our
jesus' intensity, we'll say input = remove the class and we'll say cheese linear
listing input, Hong Kong class. And of course we'll need a use statement for that. So
up here, I'll say use cheese listing input. Okay. We don't have any data transformer
yet, but this should be enough to get this to show up in our docs. So if we go over
here, so we move over to our browser, I'm going to refresh our docs homepage and go
down to our post and point for cheeses and hit try it. And, Oh, interesting.

You know, only has the description of fear field in here, which is odd, but let's
ignore that for now. Let's actually try this end point. So I'll pass a title and wave
the description, just set a string. And I'm also going to pass a valid owner. So I'll
say owner to /API /users /one. I know that one of my users in the database is
actually, this one might want to double check in your database. All right. So let me
try this. I had execute 400 air. It doesn't work, but the way it doesn't work as the
coolest part, we get a bunch of this value should not be blank on title, description,
price, even though we actually sent the title property. So thanks to our input =
behind the scenes. When we send this JSON up here, APAP, Javier is now D serializing
that into HES listing input object.

Then after that, it creates a new cheese listing entity object, but nothing ever
takes the Jesus thing, input object that has all the data and actually puts that data
onto the cheese listing. So ultimately API platform validates and then tries to save
a Jesus thing that has no data on it. So to fix this, we need our data transform. So
inside of our data, transformer directory create a new PHP class called jeez listing
input data transformer. And I'm going to make this, uh, implement of course, data
transformer interface. And now I'll go into code generate or Command + N on a Mac and
implement the two methods that we need.

And as we know, this will instantly start being called now to support the
transformation. This will look a little bit different for an input format. So I
actually want to dump these three variables so we can see what they look like now,
instead of using DD, I'm gonna use dump here. So dump data to in context, I'm not
using DD because I don't actually want it to dump and die and print out the HTML
because I'm not, that's not going to be very easy for me to, to read inside of this
box down here. So instead I'm going to use dump so that this actually saves to the
profiler. You'll see, in a second, if you don't remember how that works and then down
at the bottom here, it will just return false. So that, uh, this, this method doesn't
cause an air cause it needs to return a Boolean.

Alright, let's go over here and execute and, Oh, huh? It actually did do the dump in
line. Now, normally when you use dump without dye, it doesn't dump it on the page. It
just saves it so that you can look at it in the profiler. The reason we're seeing the
dump here is I'm actually missing the little bundle that does that integration
between the dump a function and the web debug toolbar. So to install it, find your
terminal and run a composer require Symfony /debug bundle. So I have the bundle for
the web debug toolbar, but I don't have the debug bundle, which as a couple of extra
debugging tools, [inaudible]

Once this finishes, we should be able to go back over here and execute again and
perfect 400 air JSON response, no HTML in there, but I can go down here to the, uh,
the web, my web toolbar. And I'm actually gonna open this link here in new tab and
perfect. It automatically took me down to the, uh, the debug columns so I can see the
things being dumped right there and these correspond to the data too, and context. So
next let's actually fuse this information to finish our data, data transformer and
get this thing working.

