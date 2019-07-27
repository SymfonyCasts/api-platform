# Auth Errors

Coming soon...

Okay.

We just found out that if you use the a Jason underscore login built in authentication mechanism in symphony, if you send invalid credentials, then you actually get back a nice little Jason's response that says with an error key that says invalid credentials. And by the way, I think that's great. I can totally work with that. If you do want to control this, there is a key you can put under here called fill your handler, which you can set to the class name of some class that you've created that implements a fun vacation failure handler. And then you can return whatever a response you want.

But this is good. We're going to get the air key. So now we can actually update our Java script here to use that. So long informed dot js down here, I'm gonna remove my console that log in. If you are familiar with a view, I just want to show you, I actually have an error, uh, data here, which is going to control a little, uh, air that shows up on top. So if I had to set that air to the right key, then we're going to be in business. In fact, we put that code back here cause that's what I'll do. I'll say if air that data, air response, that data, then this dot air equals air response to that data. Otherwise, in case we get back some air, uh, data that we didn't expect, we can say this. Dot Error Equals on known

air.

Now for refresh, try that.

Perfect.

Oh, almost perfect. I really shut it down. It is dot error on there. So I actually read the air off, not just the whole Jason String.

All right. That's how I want to try it. Okay. There we go. That's how we want it to look. Now that's easy enough. But I want to highlight one thing. As I mentioned, axios is really smart because it assumes unlike many, um, uh, Ajax clients that you, you want to communicate in Jason. So one of the things it's doing is we just kind of tell it that we want these email and password fields, but when it makes the request, it is actually sending a request. You look down here with contents, I've header of application slash Jason and it is sending the request payload as Jason. That's not something that all eight a lot of the HB clients kind of make it more look more like a form login.

Okay.

In that case this would fail but maybe not in the way that you expect. So just to, to make this, um, so I'm going to add another temporarily, add another argument to that post where I'm going to set header ski and then I content type header set two applications slash x www slash form dash URL encoded. This is, this specifies the header that's used. If you just like submit a form in your browser and it's the format when you submit this, your data doesn't go up as Jason, it goes up as a different format and this is really the format that a lot of things default to. If I go over right now and refresh this

and hit it, something interesting happens. You kind of probably expected an air here, some sort of like an invalid format. Can't read the uh, email or password, but you actually beck a 200 status code. And if you look at it, it says user. No. So this is coming from our security controller. It actually hit our security controller. So one of the key things you need to know about the, uh, Jason Login is that it only does its work if it sees that the content type header of the request is application slash Jason or anything containing Jason. If you make an Ajax request up to this end point, you forget to set that header or you have your data in different format. Jason logins simply does nothing. And we end up here inside of our security controller, which is probably not what we want. We probably want to return to the user and that this was a bad request and they need to fix their content that better.

But that's simple enough. We can actually do this right instead of login. So if we get here and we are, there's this point, there's two ways that we can get to this either. Um, we were logged in successfully, which we're going to see in a second. When that happens, it does execute this controller or our Jason Login was never called for some reason probably because the user forgot the content type header. So say if not this Arrow is granted a is authenticated fully. So if they're not logged in for some reason, we'll return this Arrow Jason, and will follow that same error format that's as being used by default and we'll say invalid log and request check of that. The content type content type header is application slash Jason. And then down here I'm also going to put this as a 400 status code. So it looks like it's actually a failure.

Okay.

All right, so let's try this. This time we don't need to change anything on our front end. We hit it, boom, we hit that instantly. So that's a nice way to really make this bulletproof. Sounds go back. I guess we do not actually want this to be sending those headers.

It's refresh now.

Awesome. We're back to our inbound credentials. So next, let's actually put a really user into the database login and start thinking about like what we actually want to return and how session based storage works.