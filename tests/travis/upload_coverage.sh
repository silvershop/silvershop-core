#!/usr/bin/env bash
echo "coverage = $COVERAGE, slug = $TRAVIS_REPO_SLUG, commit=$TRAVIS_COMMIT"
if [ -n "$COVERAGE" ]; then
	cd shop
	wget https://scrutinizer-ci.com/ocular.phar
	php ocular.phar code-coverage:upload -v --format=php-clover --repository=g/$TRAVIS_REPO_SLUG --revision=$TRAVIS_COMMIT ~/builds/ss/shop/coverage.xml
fi
