# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.0.2] - 2023-01-13

### Added

- Added `\Szemul\ErrorHandler\Handler\ExceptionThrowingErrorHandler` 
- Added `\Szemul\ErrorHandler\Exception\UnHandledException`

### Changed

- If the set errorHandler throws an `UnhandledException` the `\Szemul\ErrorHandler\ErrorHandlerRegistry::handleException` will rethrow it. 
