#!/usr/bin/env bash
echo "coverage = $COVERAGE"
if [ -n "$COVERAGE" ]; then
	cd shop
	wget https://scrutinizer-ci.com/ocular.phar
	php ocular.phar code-coverage:upload -v --format=php-clover ~/builds/ss/shop/coverage.xml
fi
