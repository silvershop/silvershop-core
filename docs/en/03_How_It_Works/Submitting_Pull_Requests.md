Here is a quick guide for submitting a pull request via the command line:

Fork the project: Visit the [shop project page](https://github.com/burnbright/silverstripe-shop), then click the "Fork" button.

Clone to your local machine, indie your silverstripe root directory.

    $ cd www/silverstripe
    $ git clone git@github.com:jedateach/silverstripe-shop.git shop

Change folder

    $ cd shop

Create a branch for your changes

    $ git branch test-change

Change to that new branch (checkout)

    $ git checkout test-change

Make changes to code

    $ edit README.md

Add + commit changes

    $ git diff
    $ git add README.md
    $ git commit -m "MINOR: updated readme with further docs info"

Push new branch to your own github

    $ git push origin test-change


Visit your github page, and you should see a new message "your recently pushed branches", with the option to generate a pull request. If you don't see that, click the branches tab, and choose your new branch, then click the pull request button at the top.

Write an explanation of the changes, and click "send pull request".

Done.

The owner will receive your request, and merge in your code, or reject it, with some reason.

Here it is: https://github.com/burnbright/silverstripe-shop/pull/57

## Why make changes on a separate branch?

This helps keep all changes in one place, and it is very easy to do with git. Make sure you only commit changes that relate to the new feature, bug fix.

Having all relevant changes on a seperate branch also means you can squash all those changes into a single change. eg: you might add a section of code in one commit, then remove part of it in another. The irrelevant code will be ignored if the commits are squashed.