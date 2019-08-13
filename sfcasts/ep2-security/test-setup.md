# Test Setup

Coming soon...

Because our security requirements are about to get more complicated. Instead of just
continuing to test things manly. With our interface here, I want to bootstrap a
little bit of a test suite and our application using funk functional tests to test
your API is just one of the absolute best use cases, so spin over to your terminal
and run closer to require test that stashed up that almost all Sudanese test pack,
which comes with the simple PHP unit bridge, which is a little wrapper around PHP
unit and it also installs a couple of other things.

Perfect. [inaudible]

as you see down here, we'll put the test and the test directory and then actually run
PHP and or on bin /PHP unit. That was a new file that was actually added by the
recipe because simple PHP unit is a little wrapper around PHP unit. It's also added a
couple of other files, so if you're on get status, you can see of course the updated
the standard files and I added a PHP unit that x amount, that disk file which holds
some configuration about PHP unit itself. One of the things that you may not may or
may not realize is that one of the keys in here is actually says which version of PHV
want to use at this moment. It's a 7.5 um, you can leave that alone or if you want to
use the absolute latest version of PHP and you can use 8.2 the reason we control this
here, and you might expect us to control this maybe in composer.json, usually if you
install a PHP unit, you'd have some entry inside of here that says what version of
PHP PHP unit you're using. But with the simple PHP in a library that was installed,
the way you actually run it is by saying bin /PHP unit.

Yeah,

and in the background, this calls the simple being at library and it installs PHP and
if for you it's not really that important in detail, it's just that it installs PHP
in it in an isolated environment so that it doesn't clash with any of your libraries
dependencies. You can actually see this in your bin Directory. You'll suddenly have a
little dot PHP and a directory. And that literally just a

[inaudible]

downloaded it right there. So that's actually what it's executing. So right now as
you can see it, we don't have any tests graded. Of course. Uh, before we write our
first test, I'm actually gonna set up a couple of other things. One of the other
files that the recipe and it was that end of that test. This is a file that's
obviously, that's only going to be read in the test environment. So we can make tests
specific changes here. So one of the ones that I'm going to make is I'm actually
going to copy my daddy database URL from dot n and paste it in here and add a
little_test on the bottom. So now I'm using a database that's specific to my test
environment.

Now what are the Gotchas? Um, then I watched you to realize that when you're testing
and Symfony is if you have a.in that local file. I don't have one, but if you have
one that's actually not read in a test environment, you'll need to put all of your
test environment specific overrides into that and that test. All right, so now that
we have this, let's bootstrap that database. So except bin Console doctrine database
column creates. But since we want that to happen in the test environment, we'll do
dash dash n beagles test and then I will rerun that with that can schema create to
get the dirt Davies Schema. Perfect. Um, now when we do function on tests or the APR
platform, what we're basically going to be doing is making real http API requests to
our API, getting back a response and asserting different things about it, like the
status code that the response is JSON and the, that JSON has the right keys, API
apart platform has some really nice tools for doing this. But as at the moment of
this tutorial, they're not released yet. They will come out in API Platform 2.5 and
right now we are using API platform.

Okay.

Hey back off from 2.4 0.5 you can see if you're on proposer show API dash platform.
Slash. Core. So if you're using version 2.5 you don't need to do the next few steps.
Cause what I'm gonna do is I've actually, if you downloaded the course code, then you
should have a tutorial directory inside of your application. If you don't have this,
you can just download the course code and steal it from there instead of your Ivan
Test Directory. And this is actually all of the new um, testing functions, a features
from a platform version 2.5. The only difference is that internally I've changed all
their namespaces to be using app instead of the normal API platform /core. Slash.
Test. And that's because we are going to in our service directory create a,

a bad platform directory. And then I am going to copy that test directory and paste
it into there. So I'm basically backporting all this into our, our code. I'm also
going to right click on my tutorial directory and say mark has excluded just Oh PHP
storm doesn't get confused when it sees classes in both places. And then the one
other change that you need to make a to backport those changes is in your config
directory. Ron Ace creative services under sort tests .yaml file inside hearsay
services and then say test got API under soar platform, that

quiet class

API app /API platform /test such client arguments

at test at client

and say public and true. So those two steps, they're creating this services_test
.yaml file and copying things into your source API Bot from test directory, not
something you'll need to do on APL platform 2.5 because that's already done for you
in the core. We're just trying to hack this functionality back so that we can do it.
I don't love doing this because this functionality could change before it's actually
released, but these tools are so useful that I really want to show how to use them so
this is more realistic. All right, finally, we're ready to create our tests. Some of
the test directory. Let's create a functional directory for these functional tests
and it's not there. I'm going to create a cheese listing resource

test class.

So I'm kind of falling in convention here that is made, it's not official, but I
think it makes sense to create my functional tests and the functional directory. And
then what you're really doing here is you're functionally testing your resources. So
my CI's listing resource, that's why I've named a cheese listing resource test. Make
this extend API test case. And again, if you're using API blot from 2.5 you should
find AP test case in here but it'll have a different namespace because it's coming
from APAP farm platform core and just to see if things are working

okay.

Now the first test that we actually want to do here is I'm actually going to test

that the uh, post and point for creating a new cheese listing works and maybe also
that you actually need to be logged in in order to use that. Cause you'll remember in
the last chapter, one of the things that we did is under our collection operations,
we actually added a post access control = is granted role our score user. And
actually when we did this collection operations here, I should have put it up here, I
actually have it duplicated. So our collection operations down there is overriding
this one up here. That was a mistake on my part. I'll delete that. Extra collection
operations. That was just being overwritten down here anyways.

Okay,

so names, we want to test this post end point. So instead of here, I'm going to say
public function test, create cheese listing and to make sure things are working,
we'll say this a sir = 42 42 and we should be good. So football weren't run that with
thin PSB bins. Last page B unit and we've got it. All right, so this is the basic
test setup. Next, let's actually turn this into a functional test. Let's learn about
a couple other things that we need to get set up to get this really working well.