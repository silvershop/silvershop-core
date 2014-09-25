#!/usr/bin/env sh
if [ "$COVERAGE" = "1" ] && [ "$TRAVIS_BRANCH" = "master" ]; then
	vendor/bin/phpunit -c shop/phpunit.xml.dist --coverage-clover shop/coverage.xml
else
	vendor/bin/phpunit -c shop/phpunit.xml.dist
fi