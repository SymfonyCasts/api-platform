# Install

Coming soon...

Yeah.

Hi Friends. Welcome to our tutorial on API platform. I've been looking forward to
this tutorial for a long time because Api platform is just absolutely blowing up
right now. It's incredible what you can do with this because the problem is that
building API has, it's not so easy anymore. It's a lot of work. It's a lot more than
just returning JSON. It's taking your models and it's being able to serialize them
and then de serialize them. There's hypermedia and linking. There's errors and air
format status code generating documentation.

Okay.

Handling security cores.

Okay.

And this is why Api platform exists because these days you really to build any type
of a complex API, you need something to help you put those pieces together. Api
Platform has tons of features. I mean it's ridiculous. It gives you the ability to
build rest. Accreds a hypermedia with something called JSON Ld, which we'll talk
about later. Page nation has built in filtering validation, content negotiation,
schema.org in integration, graph Ql, docker support and Admin and client generators
and a lot more, uh, it can be overwhelming, which is why we're going to go through it
by step by step so we can absolutely master it. So what API platform wants you to do
is

think differently about your application. So in Symfony weren't used to thinking
about routes, controllers, and then our response, which might be html or maybe we re
we returned JSON with the Abi Platform. It wants you to think all about your model.
So if you're used to doctrine, it would be your entity usually. So you have a model
and then you're just able to activate different ways to expose that. You can expose
that model as a rest API or you could expose that model as a graph QL API, which
we'll talk about later. All right, well before we get into any more of that, let's
actually get started. Uh, API platform itself is just an independent PHP library
that's built on top of the Symfony components. But as you can see here, it works best
as a, they recommend using it inside of the Symfony framework, which is great for us.
Now, if you fell along their documentation here for getting started, they actually
have their own

abi platform and distribution, which is actually a whole directory structure that
gives you, uh, an API skeleton, a JavaScript front end, uh, and several other things
and at wires it together all via docker. So it's a little bit of a bigger thing to
start with, but it gives you all of the features and at wires in love with docker. So
if that speaks to you, you can do that. Um, or you can do it. We're going to do,
which is going to be a little better for learning API platform, which is we're just
going to install APAP platform as a bundle into a Symfony application. So as always
to get

okay

the most out of, uh, the tutorial that you should absolutely code along with me. You
can download the course code on this page. After you unzip it, you'll have a
directory structure that looks like what I have here, which is actually just a
Symfony 4.2 skeleton app so it has almost nothing installed into it right now. It's
just tiny Symfony. So now I'll move over it. Let's move over to our terminal. Ah,
follow the read me.md file for setup instructions. The last step will be to open a
terminal move into the project and how to use the Symfony executable, which you can
download at Symfony.com/download and say Symfony serve dash and d. That will start a
little web server listening on port 8,000 I can move back to your browser and go to
local host Colin 8,000 to see to see nothing. Just the nice opening welcome page
cause there's nothing in our project right now. All right, so two in stock component
to install a pair platform. It's so awesome. I'm going to open a shit. I don't need
to do that. It's so awesome. In my terminal. I'm a second post require Api and that's
it.

You'll notice that this is installing something called the Api Platform Api pack. If
you remember from our Symfony series, a pack is just a fake repository that helps you
install several things. So we can pull that up and get hub, we can check out, it's
composed at JSON file and we can see it downloads a number of things. It's going to
download doctrine for us. It also downloads a chorus bundle, which we'll talk about
later, installs, annotations, gets API platform itself and also grabs a couple other
things like decent 70 validation system, uh, the Symfony security system and even
twig, which is used for some really cool documentation that you're going to see in a
second.

Okay.

But it's all just standard Symfony features plus API platform.

Okay.

So if we go back to your terminal, Yup, there it is. It's already telling us to
modify or David would that really nice. Um, the really nice welcome message with some
awesome hints here with some notes on how we can get started. Even even the, um,
hints about graph QL support, which is awesome, but we'll get through all of that.
But as soon as we've done this, we can actually go over to a browser and go to local
Connie thousand /API to see are already made API documentation, which is something we
get absolutely for free with April la farm. It's nothing here yet because we still
have an empty project, but we're going to fix that next.