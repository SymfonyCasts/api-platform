# Data Page Load

Coming soon...

Hey, we can log in and our application even knows that we logged in when we do it and it's already printing a little log out link here that goes to slash log out. That doesn't actually work yet. We haven't set that up in symphony so I want to make sure we do that real quick if we have.

Okay,

so to do that I'm going to add log out and below that I'll say path app underscore log out, which just like app owners for login it's going to be a route that we're just going to create in a second. This log out built in lock up mechanism is really nice because it's just going to destroy the authentication in the session. If we were doing some sort of token based authentication, we would need to um,

okay.

Actually have a user send that token and then we would destroy it.

Okay.

All right. Once we've done that, I'm going to go over to my source security controller and we're going to create a little public function log out. I'll put my at rout above that was slash long out and name equals app underscore log out. So just like before, this just needs to exist. As long as this route exists, we don't even need a controller for it. The route just needs to exist for this log out key to be able to do its magic. So in fact, in this case it will never, the controller will never get reached and we can just say should not

be reached.

Cool. So now we go over, I can hit log out and you'll see it's on my web. Do you have a tuber analysis? I'm logged in. I'm gonna come back. I am logged out. Cool. Alright. There is one other, a sort of interesting problem between our Java script and our front end and it is that if I log in our, our view application now once the Ajax call finishes, it sees that we're logged in. But as soon as we refresh, that's gone. Our Web, our trooper knows we're logged in, but our view instance doesn't know where logged in. And that sort of makes sense. If you think about it, when it first starts loading the entire page, if you look at the html off your, you can ignore all the web depot toolbar stuff down here. The entire application looks basically like this. There's nothing in the html that says who we're logged in as. And so one option is that as soon as the page loads, we could actually have our view application, make an Ajax requests to some endpoint that says, Hey, who am I logged in as? This is the classic slash me endpoint.

This last man points are very useful, they're super not restful. So again, don't let uh, following restful rules get in the way of being practical with your application. A but slash me URLs are not restful. Um, so what is the other option?

Okay,

the other option is actually quite nice. It's just to hint to your view application on page load, who your user is. And you can do this in two different ways.

Okay.

Basically if you look, if you go to the templates directory, frontend and open homepage, daddy's still on a twig. This is the page we're looking at and you can see that there's nothing here at all except for the actual view application. Though I have past in something called an entry point, um, which we're not using right now, but uh, this, I'm actually passing this piece of data in there. That's something that could be useful on Semi Java javascript application. So basically here this is, this is actually gives us an opportunity to pass in, um, some data. So one of the things we can do here is we can just pass the user, I, I r I and then instead of our application here, we would be able to read that data off and make an Ajax recall Ajax call to get that user's information just like this or only downside to this is there might be a slight delay before we actually figure out who's logged in.

So another option is actually inside of our twig template to dump the entire user, Jason LD, a structure. So basically save it from making that second Ajax, that Ajax request on page load. Here's how this looks. A first, I'm going to go to source controller, front end controller. This is the controller behind that homepage. Not very impressive. Here I'm gonna add a serializer interface serializer argument. And now I'm going to pass an argument into my a controller called user. And I'm gonna set this to serializer Arrow serialized passing it, this Arrow get user and then Jason l d so that we get the, this turns into the, so if they're not logged in, this will um, be null. If we are logged in, this should be done. Big, Nice Jason LD structure being passed as a variable under our template. Now that we have that,

okay,

we can create a script tag very simply, I'm going to say, when did I user equals curly curly user pipe raw. Literally dumped that Jason Structure as a javascript object.

Okay.

And finally over here in our cheese whiz app,

inside my javascript, I don't usually like to reference global variables, but as long as I do it near the top level of my component, I'm pretty, I'm okay with it. I'm going to add a new mounted callback. This is something that view will automatically call right after this components and mounted into the screen and here we can say if window dot user just to be safe, and then this dot user equals window dot user and it's just that simple snuck over and refresh. Did you application instantly knows that I'm logged in. If I log out and knows I'm logged out,

okay

and when I login it knows instantly that I'm logged in, so that was a fully nice ready to go login system. All right, authentication is done. It really is that simple. If you had some, if you have some more custom authentication system that is more complex than email and password, what you can, what you're going to want to do is instead of leveraging the Jason Login, you're going to create a custom guard authenticator, but the whole end purpose is the same. You're going to want to return the same information on success, the same type of thing on failure and ultimately you don't need to return a token or anything like that. You just need to make sure that your authenticator logs the user end and then allow the firewall to set the session cookie like normal. Later we're going to talk about API token authentication proof. Now we have an authentication system. We are logged in. So now we can turn to what we really need to talk about with API Platform, which is how we locked down the resources, how we make sure that certain users can only see certain, um, certain, uh, access, certain end points or how different users see different fields. Everything we can think of with authorization controlling the actual access, uh, of our API to different users.