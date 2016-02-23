#!/usr/bin/env bash
echo "coverage = $COVERAGE, slug = $TRAVIS_REPO_SLUG, commit=$TRAVIS_COMMIT"
if [ -n "$COVERAGE" ]; then
	cd shop
	# this is needed because currently the travis-support module uses --prefer-dist and the real .git seems to get overwritten
	tar xf ~/gitbackup.tar
	wget https://scrutinizer-ci.com/ocular.phar
	php ocular.phar code-coverage:upload -v --format=php-clover ~/builds/ss/silvershop/coverage.xml
fi
