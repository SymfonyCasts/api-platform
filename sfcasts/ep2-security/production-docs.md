# Production Docs

Coming soon...

Welcome to episode two, part two of our API platform tutorial. In this part we're talking all about ice cream. Um, security. We're talking about security, not ice cream. Uh, and it's going to be almost as awesome as ice cream because we are going to crush, uh, both authentication talking about when we should use API tokens or when we should use session based authentication. And then we're going to lock down every single part of our API down to the really small details, like letting different types of users see different fields and other like super complex stuff. So we're going to take this wonderful base that API platform has given us and we're going to carve it to look and act exactly like we need from a security perspective. The great thing about API platform is that it's basically just symphony security. It didn't reinvent the wheel at all. So if you haven't gone through symphony security yet, I highly recommend going through that tutorial then coming back here and things are going to feel very, very good. Of course, there's a lot to know about security of Tokens, CSRF and other stuff. So let's dive in. Step one is do download the course code from this page and get that. When you unzip it, you'll find a start directory that has the same code that you see here. Open up this remi.md file.

Okay.

To get set up instructions on how to get this application running. If you code it through part one of this tutorial, I recommend downloading the new course, the new code because I mean we, I upgraded a few dependencies and added a new front end to the site. The two most important steps that you're going to see in the read me is a step to run encore. We're just going to power a small javascript front end that we're going to play with a little bit cause I want to show, I want to actually dive into a little bit about how our javascript or whoever's using our API is going to do some of the authentication stuff and then open up another tab and run symphony serve using the symphony built in, um, built in web server.

Okay.

Once you've done that, you should be able to flip over and open up a local host Colon, 8,000 Saeilo to cheese whiz and actually this is our fancy front end. What I want to first look at is go to slash API because this is the API that we've been building in part one we have a cheese resource, a used resource that related to each other and we've done actually quite a lot of work to really customize this to the way that we want it. Now if you go back to the homepage, this is a small front end that's built in vjs and it's going to help us actually play with our authentication system.

Okay.

Now before we jump into actually talking about authentication and how we're going to do it, one of the questions that I know I'm going to get is this page here slash. API. This is beautiful. I can play with all my endpoints. People are going to ask, okay, this is great for development, but how can I disable this in production? And the answer is you shouldn't.

Yeah.

Well first, let me show you how you can and then I'm going to talk about, we're going to try to convince you why you should keep this on production.

Okay.

Overnight code, open up config packages.

Okay.

API Platform Dot Yammel a API platform has a lot of configuration. As a reminder, if you go over to your terminal, open up a new tab,

okay,

we can run DBA, config [inaudible] platform any time to see what our current configuration is. This includes defaults or config dump to see sort of an example tree of all the things that you can have in there. Now, one things you're going to see inside of here is enabled docs. Some of the copy of this and what I want to do is only disable the docs in the production environment. But first just to see what happens. I'm actually going to do it in my main configuration files. So enabled docs,

false.

As soon as I do that, I want to go back and refresh. There's absolutely no changes. This is due to a small bug with a couple of these uh, options here. That control routing. You actually need to,

okay.

Manually clear your cash in order to see those changes. So I'll run bin Console clash and cache clear.

That finishes refresh over here and excellent. The documentation has gone well sort of in and instead of a four oh four, it's actually a 500. Um, the really turns out that going to slash API and seeing the documentation was just a convenience thing. The documentation was really stored at slash abs, slash, docs and this is now four four. You can also go to slash API slash docs that Jason to get that same documentation and Jason Format and you can see all that stuff is gone. Now one of the real bummers about, um, about killing your documentation is that our documentation is not just html documentation. One, we talked about Jason LD and Hydra. There's actually a machine readable that can be really handy for API clients to read. Sorry. Now if I go to slash API slash cheeses dot Jason LD.

Okay.

Your end point might be empty right now.

Yeah.

But it actually has information here where if you follow a kind of a tuck context information, we should be able to get more information about what the significance of a cheeses and as soon as you disabled the documentation, this stuff doesn't work anymore. This is documentation so it just simply does not work without the documentation. So if you disabled documentation, you just need to realize you're actually disabling your nice machine readable documentation as well. One thing that we do still have though,

yeah,

and of course we have this 500 air on slash. API. If you want to remove, if you want to fix this you have to disable what's called deep entry point because right now if you go to slash index slash API slash index that Jason LD, this is called the entry point for your API and actually gives you some information about like the URLs you can follow to get more information. So the entry points actually are nice, but if you do want to sit in a disabled entry point, you can say enable on your point, false. Go back and rebuilt the cash. Then when you come over here, the entry points going to stop working and the real win here is that if you go to slash API, you actually now get your proper four four. So to fully disable your docs you actually need to disabled both the docs and the entry point, both of which are kind of a bummer. But if you want to do this, uh, to do this properly, I would actually not put these files in here, but I'd go on my prod directory, create a new file, they're called API underscore platform dot Yammel.

Then I'd paste then might've put API platform and I paste those things there. So if I change into the prod environment, cleared my cache, I would see that same result here. But as I mentioned, I am not going to do that. And there's a couple of reasons for this. First of all, as I just mentioned, having a documentation, the machine readable documentation and this entry point is actually a nice thing and it can be useful for your own javascript even if you're the only one consuming it. The other thing is disabling your documentation. Like this is really um, security through obscurity. If you don't want other people to use your API, like it doesn't mean that your API doesn't exist.

Yeah,

your API totally exist and if you're not, even if it, and if it's not properly secured, there's, it's always possible that someone will get at it. So if you're trying to hide this so that people won't find your API so they won't use it, that's not a super a great option. It's not a super great way to do that. Now, at the end of the day, if you really do want to keep the docs and entry point, but you simply just don't want slash API to load, actually let me go back and clear the cache again so we get that back in the Dev environment. Another option is that you could create an event. Nope, I still got an air there. Kept clearing cash again. There we go. Um, you could create an event subscriber on colonel that request and if the URL slash API and the format is html, then you can hide this page. Not. The reason it's not that easy is because it's really goes against the concept of having this API that has all this machine read of that condition. Anyways, do you have any questions about that? Let me know. Let's go.