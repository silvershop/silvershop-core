# Contributing to the shop module

Your contribution to this project is highly encouraged.

### Here is a quick list of ways you can contribute:

 * __Test the latest code__. Find out what branch is currently being worked on (usually 'develop'). Install it and try it out.
 * __Code new features and bug fixes__. Submit github pull requests. Don't forget to write PHPUnit tests that ensure your code runs. All pull requests are automatically tested [via TravisCI](https://travis-ci.org/burnbright/silverstripe-shop/pull_requests).
 * __Submit issues and ideas__. These can be bugs, or ideas. Be descriptive and detailed. Its better to discuss and design ideas before writing code. Please first check the [list of existing issues](https://github.com/burnbright/silverstripe-shop/issues) to make sure an identical one doesn't exist already.
 * __Write documentation__. Both the developer and user documentation can have pieces missing. Documentation is stored in the repository under `/shop/docs` ,and `/shop/docs_user`. Documentation gets displayed at http://docs.ss-shop.org
 * __Provide translations__. This will allow people speaking other languages to use the shop module.
 * __Financial contribution__. Giving a donation, or financing the development of some features will help this module go further, faster.

If you would like to contribute code, please [fork this project](https://github.com/burnbright/silverstripe-shop). You can then make changes in feature branches via git, and submit pull requests, which will be reviewed and merged into the code base. If merge is not appropriate, instruction will be given on the best action(s) to take.

## Development Guidelines

We try to match [SilverStripe's guidelines](http://docs.silverstripe.org/en/contributing/) 
as closely as possible. In some ways our approach will differ, but it is a good idea to read their guidelines first.

## Workflow

We will try to follow this branching approach http://nvie.com/posts/a-successful-git-branching-model/

## Political

This project political model used for this project is a [Benevolent Dictatorship](http://producingoss.com/en/social-infrastructure.html#benevolent-dictator). This basically means the project owner will have ultimate say in decision making. Discussion is still very much welcomed however, and if agreement can not be found, anyone can fork the project and start their own version.

## Good development practices

* Write a new unit test for a new bugs or features
* Unit tests MUST pass before submitting new contributions
* Maintain backwards compatibility, or provide migration scripts, and help
* Full support of standard SilverStripe framework features
* Prefer SilverStripe core framework features over 3rd-party add-ons
* Graceful degradation of javascript
* Make use of design patterns
* Comment code thoroughly
* Write and update documentation along with changes
* Major changes need to be backed up with solid reasoning
* Consult external sources, such as google groups when consensus can't be reached
* Modular code: high cohesion, low coupling
* Keep a record of everything - prices changes, transactions (posting table)
* Allow site owner to change all prices. One-time configurations are done in code.
* Install an [EditorConfig](http://editorconfig.org/#download) plugin for your editor or IDE.

See also:

 * [Development](../03_How_It_Works/Development.md)
