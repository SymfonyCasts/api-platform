# Custom Resource Item

Coming soon...

Our gift collection operation for daily stats is working really well. Uh, and even
though it says, we have a way, but you know what? I'd love to be able to also fetch
these stats for a single day. And it says that this is already available, but that
totally doesn't work yet. If you look at your daily stats resource up at the top, we
kind of added this get item operation, but we just made it go to a four Oh four. That
was because we needed to work around a way to generate an I R I for the daily stats.
Now I want to make this actually work. So I'm going to remove all of this custom
configuration. So then now it says I have a normal, good item operation and a normal
collect get collection operation.

Um, okay. Let's see if it works. Spoiler alert. It's not going to work. So I'm going
to cheat up here and go to /API /daily stats, got JSON LD, and then I'll copy the ad
ID for one of the daily stats that we have right now. Oops. And add it. And if four
Oh four is, but actually let me think that JSON LD on there, there we go. Now we can
say the four Oh four in API format. So it says not found. So to get the collection
end point working, we created a daily stats provider that is a collection data
provider, but when you go to an item operation, it uses an item, provide data
provider, and we don't have an item data provider yet. No problem. We've done this
before. So I'm to add a, another interface here called item data provider interface,
and then down here, I'll go to code generate or command and on the Mac, go to
"Implement Methods" and implement the get item method that we need.

Our job here is to read this ID and return the daily stats object for that ID or no,
if there is none now, before we do this right now, our gift collection is just
returning a couple of coded daily stats, which isn't very realistic. So instead,
let's pretend we have a JSON file full of stats, or maybe you have some other API
that you actually make a request to, to get the data for our daily stats in the, if
you download the course code and you should have a tutorial directory with a
fake_stats, that JSON file, we're going to use this as our data source. And right
next to this is a stats helper class, which has already written to read that file.
When did a copy of both of these and for simplicity, we're just going to put these
into a source /service directory. So I'll create a new director here called us inside
there. I will paste.

Perfect. So let's look quickly at this stats helper. I promise you it's nothing, not
very fancy, basically this, um, this class has three public methods, fetch many where
you can pass it a limit offset and even some filtering criteria, which we'll talk
about later, fetch one, where you pass the B string date and also a count method. And
that's basically it. The rest of this file is just fancy ways of reading that, uh,
fake stats dot, JS on file and up and parsing through it and creating the daily stats
object. So this is gonna take care of populating the daily test object from that
file. So in daily stands provider let's use that. So you won't need the cheese
listing repository anymore. That stats helper takes care of that instead of have one
argument called stats, helper, stats, helper, and then I'll say this aerostats helper
= stats helper, and then I'll rename the property to stats helper. I can get rid of
couple of use statements down and get collection. Cause we put all the logic and
another class. It's pretty simple return. This aerostats help her->fetch many and
pass it for now. No arguments.

All right, let's see if that works first. So let me actually go back to my collection
and point refresh and, and yes, it does work here is our big list of Jesus, things of
daily datasets coming from that configuration file. All right. So now we can use this
inside of get item. Now, before we do, just to make sure that this method is being
called, we do have supports down here that we should be called anytime a daily stats
is being requested, but let's just DD ID to make sure first over here, I'll go
forward, refresh and perfect. We have our, our date string here, uh, being dumped
out.

Finally get item. We can just say return this->stats, helper, arrow, fetch one and
pass it. The date string, which is going to be ID. We move to refresh. Now we get a
page not found. That's actually perfect. You look at my date up here. It's 20, 2010
Oh five. That's actually not one of the ones that I have in my true collection right
now. I'll go back and refresh and let's copy a different one like 20, 2009 Oh one. So
if we do /data //2020 dash Oh nine dash Oh one that day, so then I'll be, it works so
we can punch a single item. And if we do something that's not in that JSON file, we
get a four Oh four. So setting up our item item op operation was actually pretty
easy. Our next let's talk about page nation, because if we go back to our daily stats
collection and refresh, there are a lot of items on here because we're not using
doctrine. We're not going to get paged nation for free. So if you need it, he needed
to add it yourself.

