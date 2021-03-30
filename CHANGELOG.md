# Change Log

All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## [1.0.0-beta.1] - 2021-03-30

### Added

- New `AllowedCountableFields` rule, which can be created via the `Rule::countable` and `Rule::countableForPolymorph`
  methods.

## [1.0.0-alpha.5] - 2021-03-12

### Added

- New `Rule::includePathsForPolymorph()` method. This returns an include path rule that is configured to allow all the
  include paths for every inverse schema that a polymorphic relation has.

### Fixed

- The correct message is now set as the error detail when a query parameter is not supported. Previously the translation
  key was displayed instead of the translated message.

## [1.0.0-alpha.4] - 2021-02-27

### Added

- The sparse field sets rule now rejects with a specific message indicating if any of the supplied resource types in the
  `fields` query parameter are not recognised.

## [1.0.0-alpha.3] - 2021-02-09

### Added

- Added French translations for error messages.

## [1.0.0-alpha.2] - 2021-02-02

### Bugfix

- [#1](https://github.com/laravel-json-api/validation/issues/1)
  Added package discovery configuration to the `composer.json`.

## [1.0.0-alpha.1] - 2021-01-25

Initial release.
