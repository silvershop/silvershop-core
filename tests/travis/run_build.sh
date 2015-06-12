#!/usr/bin/env bash
#if [ "$COVERAGE" = "1" ] && [ "$TRAVIS_BRANCH" = "master" ]; then
if [ -n "$COVERAGE" ]; then
	vendor/bin/phpunit -c ~/builds/ss/shop/phpunit.xml.dist --testsuite Split$COVERAGE --coverage-clover ~/builds/ss/shop/coverage.xml
else
	vendor/bin/phpunit -c ~/builds/ss/shop/phpunit.xml.dist
fi
