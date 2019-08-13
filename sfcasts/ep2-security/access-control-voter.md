# Access Control Voter

Coming soon...

What I like about the way that a pair platform does the access control is this
expression instantly gives you power. You can instantly write a somewhat complicated
access control rule here. Uh, and it works. It's very easy to do, but I don't like
about it is that this can get a bit ugly. Even this to me is a little bit ugly,
especially if I started repeating this in various parts of my system. And one if I
had an another complication, what if I said that you should be able to update a user
if you update a cheese listing? If you are the owner of that CIOs testing or any
admin user can update it, well then we'd really need to get kind of fancy here with
parentheses and or statements and things like that. I don't like that. So instead,
let's use a voter to centralize this logic.

It says nothing to do with API platform technically, but it fits really in with
keeping your access controls nice and clean. So run bin Console at your terminal
Redmond console, make voter and what's called this cheese listing voter. I like to
have one voter. Typically per each kind of entity or resource that I need to decide
things on. This is great to source security voter Cheez listing voter and basically
if you're not familiar with the idea behind voters, they are a way to centralize your
security logic. So we're going to do here is instead of saying is granted role user
and previous object dot get owner = = user. I'm just going to end on removing the
second part and just say is granted edit comma previous object. Now that word edit
right there, I just made that up. That could be edit, manage admin. It's just a
string that is going to be passed into your vote or as you see in a second, and I'm
also passing the previous object itself, the object that we want to make the access
decision on.

Now whenever you call is granted, Symfony goes through and loops over all of these
voters in the system and basically asks each one, Hey, do you know how to decide
whether or not to this cheese listing object whether or not the current user has edit
access to this cheese listing object? And really the only core core voters, there's
really only one core voter more or less. And it's the one that um, knows how to
answer the question, uh, is granted with anything that starts with role underscore.
So basically as soon as we create a class that extends us voter class Symfony is
aware of our voter and it's going to call supports and it's going to pass this, this
attribute, which is going to be that string edit and it's going to pass this, this
subject, which in our case will be this, this a cheese listing object.

And our job into to is to basically answer the question, do we know how to decide to
acts on this? Yes or no. So what we can do here is I'm going to change this. We can
say if the attribute is edit, you know, and this will give us some space later to add
other attributes here. So if the attribute is equal to edit and the subject is an
instance of cheese listing, then our voter knows how to decide that situation. If
anything else was passed to us, like this is rural Admin, that's going to be for
another voter to take care of. We're just going to handle this one case. Now down
here, if we return true from supports, then Symfony is going to call vote on
attribute. It's going to pass that same attribute string, edit that same subject,
which we know will be a cheese listing. And also pass us the token object, which is,
allows us to actually get the currently authenticated user. And our job here is
returned true if the user should have access or false if they shouldn't. So first I'm
going to do down here as I'm actually going to help my editor out. I'm gonna say at
bar she's listing dollar sign subject. That's going to be a hint to my editor that
this subject will definitely be a cheesed listing at this point.

And then I'm here, you can see I have a little switch case summit's already kind of
set up for if we have two different, um, a couple of different attributes. Right now
we just have one sob, delete the second one. So if attribute is equal to edit, and
here we put the business logic for making our decision. So I'll say if subject->get
owner cause subject is the cheese listing = = = a user, which is going to be the
currently authentic k user, then return true. That means access granted, otherwise
return false. And by the way, if for some reason we pass an attribute in here that is
not the word edits, we'll end up down here instead of returning false, which would
mean access to tonight. That would actually be a programmer air. That'd be mean.
Meaning word, you know, accidentally going somewhere and Typo in the word edit.

I'm going to make that a little more obvious and throw a big exception that says
unhandled attribute percent s and pass in the attribute there. Just so we know that
we're making a mistake. Cool. So let's try this nice thing as we were just saying is
granted edit previous object and it should call voter and everything should work. So
let's go over here and run our test again. So PHP bin /PHP unit dash dash filter =
test update. She's listing. And let me scroll up. Yes, that actually works perfectly.
So now we have the flexibility to add other attributes later. Like for example, maybe
you can do an is granted delete previous object down here for the delete if it were
up and more complicated or whatever you want. Also we can do, um, what I originally
said it, which is what if we want to make, um, admin users able to edit cheese
listings. Now we can do this in one central spot. So to check if the current user has
a role, instead of a voter, we're going to need to inject these security service. So
I'm gonna say public function on our score on a score construct, and we'll type in
the security class from Symfony security. I'll hit alt enter and go to initialize
fields to create that property and set it

no down here, I'll do it just for the edit attribute. I'll say if you're trying to
edit a cheese listing, and then we can say, if this->is security->is granted, and
let's say that all of our, um, admin users have rural_admin,

then return true.

I don't have a test with this. We could actually read a test instead of our cheeses
listing resource test to, you know, create an a gritty user, set their roles to role
admin. But let's just make sure it didn't break anything

and

scroll up. Yes, it didn't. So that's really my preferred way to handle access control
inside of a API platform. It's going to keep off your access control. Really simple.
Of course, if you just set it checking for a single role, just use is granted role
admin. But if it gets any more complicated than that, I really like to use a voter.