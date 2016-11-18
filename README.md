[![Build Status](https://travis-ci.org/dejanbatanjac/die-klinke.svg?branch=master)](https://travis-ci.org/dejanbatanjac/die-klinke)

# WordPress Plugin testing Ajax and Travis CI integration

## Contents

Includes the following important files/folder:

* `.gitignore`. Used to exclude certain files from the repository.
* `CHANGELOG.md`. The list of changes to the core project.
* `README.md`. The file that you’re currently reading.
* `test` directory that contains different testing artifacts.

## Uses

* [Plugin API](http://codex.wordpress.org/Plugin_API).
* [Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards).
* [Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/).
* All classes, functions, and variables are documented.
* The Boilerplate uses a strict file organization scheme.
* The project includes a `.pot` file as a starting point for internationalization.

## Installation

To install the plugin move it directly into the plugins folder and activate from there.
You can use this `wp-cli` command sequnce to activate the plugin.
* `wp pugin install https://github.com/dejanbatanjac/die-klinke/archive/master.zip`
* `wp plugin activate die-klinke`

## Preparation phase decisions ( not exactly followed )

In the preparation phase the following goals have been set.

* To create the basic Ajax example that fits the programming goal.
* To explain different design paths, and decisioning, since any solution may be
done in many different ways.
* To test on the [twentysixteen WordPress theme](https://wordpress.org/themes/twentysixteen/) and using [Theme Unit Tests data](https://codex.wordpress.org/Theme_Unit_Test) or improved test data if needed.
* To use the namespaces, since these are more professional.
* To create write using the JavaDoc syntax and to create document at the end for the plugin.
* To create a document explaining the work on this plugin.
* To test using basic unit tests PHP Unit.
* Support i18n.
* To integrate Travis check from Github, possible including the different linters.
* To additionally set the WebKit browser application for visual testing.
* To add possible Sass styles converters.


## i18n Tools

* [Poedit](http://www.poedit.net/)
* [makepot](http://i18n.svn.wordpress.org/tools/trunk/)
* [i18n](https://github.com/grappler/i18n)

Any of the above tools should provide you with the proper tooling to internationalize the plugin.

## License

Licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `LICENSE`.

## Note on disabling the Travis CI Build

`To disable the Travis build on PUSH action add [ci skip]`
