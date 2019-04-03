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

## Example theme
* [clawrock/magento2-theme-blank-sass](https://github.com/clawrock/magento2-theme-blank-sass)

## Works with
#### Preprocessor
* Magento 2.2 - 2.3
* PHP 7.0 - 7.2
#### Gulp
* Node.js 10+

## Gulp
1. Install Node.js
2. Install Gulp configuration `php bin/magento dev:gulp:install`
3. Install Gulp and required dependencies `npm install`
4. Define theme configuration `php bin/magento dev:gulp:theme`
5. Symlink theme to pub/static folder `gulp exec:[theme_key]`
6. Compile SCSS `gulp scss:[theme_key]`
7. Watch for changes `gulp watch:[theme_key]`

It also supports LESS, instead of SCSS use less like `gulp less:[theme_key]`

Use additional flags to enable more watchers:
- `--phtml`: reload when phtml file is changed
- `--js`: reload when js file is changed

#### Configure theme
You can manually configure theme like in Gruntfile which is shipped with Magento or use `php bin/magento dev:gulp:theme` command which will configure it for you.

Reference: [Grunt configuration file](https://devdocs.magento.com/guides/v2.3/frontend-dev-guide/tools/using_grunt.html#grunt_config)

#### Commands
| Shortcut                  | Full command                                                   |
| ------------------------- | -------------------------------------------------------------- |
| `gulp build:scss:[theme]` | `gulp exec:[theme] && gulp scss:[theme]`                       |
| `gulp dev:scss:[theme]`   | `gulp exec:[theme] && gulp scss:[theme] && gulp watch:[theme]` |

List of gulp commands:
- `gulp clean:[theme_key]`
- `gulp deploy:[theme_key]`
- `gulp exec:[theme_key]`
- `gulp scss:[theme_key]`
- `gulp less:[theme_key]`
- `gulp watch:[theme_key]`
- `gulp build:scss:[theme_key]`
- `gulp build:less:[theme_key]`
- `gulp dev:scss:[theme_key]`
- `gulp dev:less:[theme_key]`

#### BrowserSync
Pass `--proxy http://magento.test` argument to `gulp watch:[theme_key]` or `gulp dev:scss[theme_key]` where http://magento.test is Magento base url and BrowserSync will be enabled.

There are some BrowserSync options available (only string and boolean values are supported):
- `--bs-ui`: [ui](https://www.browsersync.io/docs/options#option-ui)
- `--bs-port`: [port](https://www.browsersync.io/docs/options#option-port)
- `--bs-logLevel`: [logLevel](https://www.browsersync.io/docs/options#option-logLevel)
- `--bs-logPrefix`: [logPrefix](https://www.browsersync.io/docs/options#option-logPrefix)
- `--bs-logConnections`: [logConnections](https://www.browsersync.io/docs/options#option-logConnections)
- `--bs-logFileChanges`: [logFileChanges](https://www.browsersync.io/docs/options#option-logFileChanges)
- `--bs-open`: [open](https://www.browsersync.io/docs/options#option-open)
- `--bs-browser`: [browser](https://www.browsersync.io/docs/options#option-browser)
- `--bs-notify`: [notify](https://www.browsersync.io/docs/options#option-notify)
- `--bs-scrollProportionally`: [scrollProportionally](https://www.browsersync.io/docs/options#option-scrollProportionally)
- `--bs-scrollThrottle`: [scrollThrottle](https://www.browsersync.io/docs/options#option-scrollThrottle)
- `--bs-reloadDelay`: [reloadDelay](https://www.browsersync.io/docs/options#option-reloadDelay)
- `--bs-reloadDebounce`: [reloadDebounce](https://www.browsersync.io/docs/options#option-reloadDebounce)
- `--bs-reloadThrottle`: [reloadThrottle](https://www.browsersync.io/docs/options#option-reloadThrottle)
- `--bs-injectChanges`: [injectChanges](https://www.browsersync.io/docs/options#option-injectChanges)
- `--bs-startPath`: [startPath](https://www.browsersync.io/docs/options#option-startPath)

#### Example usage
`gulp dev:scss:my_theme --proxy http://m2.test --phtml --bs-port=8080 --bs-open=0`

## Troubleshooting
If you had previously installed Grunt, please make sure you have removed package-lock.json and node_modules folder. Then run `npm install`.
