[![Packagist](https://img.shields.io/packagist/v/clawrock/magento2-sass-preprocessor.svg)](https://packagist.org/packages/clawrock/magento2-sass-preprocessor)
[![Packagist](https://img.shields.io/packagist/dt/clawrock/magento2-sass-preprocessor.svg)](https://packagist.org/packages/clawrock/magento2-sass-preprocessor)
[![Build Status](https://travis-ci.org/clawrock/magento2-sass-preprocessor.svg?branch=master)](https://travis-ci.org/clawrock/magento2-sass-preprocessor)
[![Coverage Status](https://coveralls.io/repos/github/clawrock/magento2-sass-preprocessor/badge.svg)](https://coveralls.io/github/clawrock/magento2-sass-preprocessor)

# Magento 2 - Sass Preprocessor module
Module for Sass processing during static content deployment with additional Gulp workflow to improve Magento 2 development speed. It compiles SCSS using `scssphp` and process standard `@import` instruction as well as `@magento_import`. 

## Installation
1. Install module via composer `composer require clawrock/magento2-sass-preprocessor`
2. Register module `php bin/magento setup:upgrade`
3. Compile Sass theme using `php bin/magento setup:static-content:deploy -f`

## Compatibility
* Magento 2.2 - 2.3
* PHP 7.0 - 7.2
* Gulp 3.9
* Node.js v8 or later

## Gulp workflow
1. Install Node.js
2. Install Gulp configuration `php bin/magento dev:gulp:install`
3. Install Gulp and required dependencies `npm install`
4. Define theme configuration `php bin/magento dev:gulp:theme`
5. Symlink theme to pub/static folder `gulp exec:[theme_key]`
6. Compile Scss `gulp scss:[theme_key]`
7. Watch for changes `gulp watch:[theme_key]`

## Browsersync
Pass `--proxy http://magento.test` argument to `gulp watch` or `gulp watch:[theme_key]` where http://magento.test is Magento base url and Browsersync will be automatically enabled.

## Compatible themes
* [clawrock/magento2-theme-blank-sass](https://github.com/clawrock/magento2-theme-blank-sass)

## Troubleshooting
* If you have previously installed Grunt, please make sure you have removed package-lock.json and node_modules folder. Then run `npm install`.
