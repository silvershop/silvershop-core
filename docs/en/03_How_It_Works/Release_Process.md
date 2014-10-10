Adopting this model: http://nvie.com/posts/a-successful-git-branching-model/

Here is a quick checklist of what needs to be done to create a new release of shop:

## Testing

Run the test suite, and ensure sure all tests pass.
Complete a test order to see that everything is fine for the user.

## Generate Change Log

	cd shop
	git log --oneline | grep -i 'enhance\|NEW'
	git log --oneline | grep -i 'api\|API'
	git log --oneline | grep -i 'bug\|BUG'

copy into change log up to last release commit.

## Versioning

Version numbering will try to follow [Semantic Versioning](http://semver.org/) as best as possible.
Releases will exist as tags in git.

## Create git tag

    cd shop
    git tag -a [version]
    git push --tags

## Update Installer

Update [installer](https://github.com/burnbright/silverstripe-installer/tree/shop), create zip, and save to [downloads](https://github.com/burnbright/silverstripe-shop/downloads)

Make any changes, commit, push.

    cd shopinstaller
    zip -9 -r ../shopinstaller-0.8.5.zip . -x '.git/*' '*.git/*' 'sapphire/docs/*' '.gitignore' '.gitmodules' '.project' '.buildpath' '.settings/*' 'assets/*'

Upload to [github](https://github.com/burnbright/silverstripe-shop/downloads)

## Update sites

* [website](http://ss-shop.org) info
* [demo site](http://demo.ss-shop.org)
* silverstripe [extensions page](http://www.silverstripe.org/shop-module/)

## Announce

Include some release commentary, highlights of main big changes. Include a link to
upgrading docs.

* [forums](http://silverstripe.org/e-commerce-module-forum/)
* [mailing list](http://groups.google.com/group/silverstripe-ecommerce)