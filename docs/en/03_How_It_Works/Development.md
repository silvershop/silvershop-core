This information should be useful when developing with the shop module.

## Mission
What the module authors hope to accomplish for users, store admins, and fellow developers.

**For developers**: A well thought, clearly defined and documented API that can be used to easily extend the module.

**For store admins**: Ability to easily manage the shop's products and orders. Useful reporting to understand and improve sales.

**For users**: A fast, secure, easy to use interface. Provided options and flexability with the browsing and ordering process.

## History

From the maintainer:

I copied this project to google code in June 2010 (as the ecommerce module 0.6.1), and then in late 2011, due conflicting ideals and development approaches, forked to create my own ‘silverstripe shop’ module. I’ve learned a lot in this time, and I’ve even tried as much as possible to adopt the open source project management approaches described in Karl Fogel’s free book: Producing Open Source Software - How to Run a Successful Free Software Project (http://producingoss.com/).

I reserve my right to make the big decisions for the project and decide the overall direction of the module, but welcome every bit of contribution from the community. The community also has the opportunity to fork and maintain their own version of the module if they so desire. I may change approach in future, but until I see and know how a project can be successfully maintained by more than one person, I will continue in this way.

##Characteristics of the module/project

These are some intentionally decided characteristics that the module/project will exhibit. These could be touted as the points of difference for the system.

###Developer Focused

Developers write software they are the ones who (should) know how the system works the most. Developers do most of the system setup and configuration.

SS shop module provides a flexible framework for developers to build custom ecommerce sites with. This module provides a core set of functionality that can be modified by developers and designers to create any design, support any features, and sell any kind of product, using any kind of payment mechanism.
The only restriction is that the module is built on php.
Documentation for customising is provided.
Website owners are very important, but it is the developer who supports them.

###Sleek API

Unit test writing has revealed how difficult it is to programmatically create and manipulate orders. That is, writing code to construct and process orders is not easy.

A modified API has been imagined based on what classes, methods, and functions would be useful.

The API is not only accessible thorugh PHP classes, but also through a RESTful API for manipulation via other web apps, mobile apps and javascript (json,xml).

Research:

 * http://stackoverflow.com/questions/6708728/magento-create-an-order-programmatically-in-backend-code
 * http://www.commerceguys.com/resources/articles/245

### Modular

With the core kept minimal and extensible, submodules can be added to introduce further functionality.

### Flexible Engine

The module can sit on top of the Sappire framework and run as an engine for ecommerce.
Multiple possibilities for front-ends:
 * Website
 * Javascript
 * Mobile/desktop app
 * Interactive phone call app
 * SMS

These won’t be provided with the core system, but should be possible.

### Graceful Scaling / Hidden Complexity

Change the system to handle small or large, simple or complex. Or at least make it easy for developers to configure.



See also:

 * [Release Process](../03_How_It_Works/Release_Process.md)
