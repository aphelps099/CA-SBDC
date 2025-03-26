# Social Wall

## Contents
- [Installation](#installation)
- [Project Architecture](#project-architecture)
    - [Plugin Structure](#plugin-structure)
    - [Folder Structure](#folder-structure)
    - [Build Process](#build-process)
    - [Plugin Core](#plugin-scripts)
        - [Dependency Injection Container](#dependency-injection-container)
        - [Services Provider](#services-provider)
    - [Other Dependencies](#other-dependencies)
    - [Unit Tests](#unit-tests)
    - [Release Process](#zip-process)
    - [Other Support](#other-support)

## Installation
- Git clone the repository
    - `git clone https://github.com/awesomemotive/social-wall.git`
- Go to the root directory of the repository
    - `cd social-wall`
- Run `composer install`
- **Optional steps for development**:
    - Run `npm install`
    - Run `npm run build`
    - Run `npm run start` for watch mode

## Project Architecture

### Plugin Structure

#### Birdseye view of the plugin
[![XUHO3N.md.jpg](https://iili.io/XUHO3N.md.jpg)](https://freeimage.host/i/XUHO3N)

#### Plugin Bootstrap Process
[![X8GZ92.md.jpg](https://iili.io/X8GZ92.md.jpg)](https://freeimage.host/i/X8GZ92)

### Folder Structure
```
assets/
  ├─ src/ ---------- React App ----------------
  ├─ js/  ---------- Vanilla JS / JQuery ------
  ├─ css/ ---------- Stylesheets --------------
build/
languages/
src/ ---------------- PHP App -----------------
  ├─ Cache/
  ├─ Core/ ---------- Plugin Core --------------
    ├─ Abstracts/
    ├─ Container/
    ├─ Exceptions/
    ├─ Interfaces/
    ├─ Traits/
  ├─ Frontend/
    ├─ Services/
    FrontendProvider.php
  ├─ Admin/
    ├─ Services/
    AdminProvider.php
  ├─ Models/
  ├─ Requests/
  Activate.php
  Bootstrap.php
  Deactivate.php
  PluginClass.php
  ...
  
tests/
  ├─ php/
```

### Build Process
For build process, this plugin uses the npm package [@wordpress/scripts](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/).
We can use extend the config and also have the control over the build process by creating our own webpack config.

### Plugin Core

#### Dependency Injection Container
While there's some good solution DI container like [PHP-DI 6](https://php-di.org/) or [thephpleague
/
container](https://container.thephpleague.com/) package, this plugin implemented its own simple yet powerful PSR 11 [DI Container](https://github.com/xaviranik/we-meal/tree/main/src/Core/Container).

#### Services Provider
The plugin streamlines the process of registering services and injecting them into the plugin. The provider abstraction layer is responsible for registering services and putting them into the container.

#### Cache
The plugin uses its own cache abstraction layer to store the data from the Remote API for a period of time.

## Other Dependencies
- [PHPUnit](https://phpunit.de/manual/current/en/installation.html)

## Unit Tests
This plugin uses [phpunit](https://phpunit.de/) for unit testing.
To run the unit tests, run `composer phpunit` or `vendor/bin/phpunit` in the root directory of the plugin.

## Release Process
The plugin is released as a zip file.
For the release process, this plugin uses custom script [build.sh](https://github.com/xaviranik/boostimer/blob/develop/bin/build.sh) which takes care of building the plugin and bundling it into a zip file for release.

## Other support
- Code style
- JS linting and formatting
- PHP Codesniffer
- PHP CBF
- Tailwind CSS
