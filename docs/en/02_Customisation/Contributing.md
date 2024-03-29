# Contributing to the shop module

This module only moves forward as we each build the features we need. We love pull requests!

### Here is a quick list of ways you can contribute:

 * __Test the latest code__. Find out what branch is currently being worked on (usually 'master'). Install it and try it out.
 * __Code new features and bug fixes__. Submit github pull requests. Don't forget to write PHPUnit tests that ensure your code runs.
 * __Submit issues and ideas__. These can be bugs, or ideas. Be descriptive and detailed. Its better to discuss and design ideas before writing code. Please first check the [list of existing issues](https://github.com/burnbright/silverstripe-shop/issues) to make sure an identical one doesn't exist already.
 * __Write documentation__. Both the developer and user documentation can have pieces missing. Documentation is stored in the repository under `/shop/docs`, and `/shop/docs_user`. Documentation gets displayed at http://docs.ss-shop.org
 * __Provide translations__. This will allow people speaking other languages to use the shop module: https://www.transifex.com/silvershop/silverstripe-shop/
 * __Financial contribution__. Giving a donation, or financing the development of some features will help this module go further, faster.

If you would like to contribute code, please [fork this project](https://github.com/silvershop/silvershop-core). You
can then make changes in feature branches via git, and submit pull requests, which will be reviewed and merged into the
code base. If merge is not appropriate, instruction will be given on the best action(s) to take.

## Development Guidelines

We try to match [SilverStripe's guidelines](http://docs.silverstripe.org/en/contributing/)
as closely as possible. In some ways our approach will differ, but it is a good idea to read their guidelines first.

We are moving toward [PSR-2 compliance](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
and as such any new code submitted should be fully compliant if possible.

## Workflow

We use [Github Flow](https://guides.github.com/introduction/flow/) which basically means:

1. Create your own fork
2. Create a feature branch on your fork per feature or unit of work
3. Submit a pull request

### Branches

* `master` will always be the newest development. Use it for more PR's.
* `2.0` is kept up to date with master but also contains breaking changes for the upcoming 2.0 release. Only
   base your work on this branch if it definitely includes a backwards-incompatible change.

### Releases

Releases will be frequent and follow Semantic Versioning. Patches for specific older versions can be made by creating
a branch from the appropriate tag.

## Political

This project political model used for this project is a [Benevolent Dictatorship](http://producingoss.com/en/social-infrastructure.html#benevolent-dictator).
This basically means the project owner will have ultimate say in decision making. Discussion is still very much welcomed
however, and if agreement can not be found, anyone can fork the project and start their own version.

We are actively moving towards creating a team of core committers and moving away from depending on one maintainer.
Expect more news soon and watch this [Trello board](https://trello.com/b/85ZyINqI/shop-development-planning)

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
