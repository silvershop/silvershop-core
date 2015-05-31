Here is a quick checklist of what needs to be done to create a new release of shop:

## Testing

Run the test suite, and ensure sure all tests pass.
Complete a test order to see that everything is fine for the user.

## Generate Change Log
Create change logs since last tag:
```sh
cd shop
#New stuff
git log --pretty=format:" * %s" --since="$(git show -s --format=%ad `git rev-list --tags --max-count=1`)" --grep='^enhance\|^new\|^added' -i
#API changes
git log --pretty=format:" * %s" --since="$(git show -s --format=%ad `git rev-list --tags --max-count=1`)" --grep='^api'  -i
#Bug fixes
git log --pretty=format:" * %s" --since="$(git show -s --format=%ad `git rev-list --tags --max-count=1`)" --grep='^bug\|^fix' -i
```
Copy output into change log.

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
* silverstripe [extensions page](http://addons.silverstripe.org/add-ons/burnbright/silverstripe-shop)

## Announce

Include some release commentary, highlights of main big changes. Include a link to upgrading docs.

* [forums](http://silverstripe.org/e-commerce-module-forum/)
* [mailing list](http://groups.google.com/group/silverstripe-ecommerce)