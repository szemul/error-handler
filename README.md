# Error handler

![CI pipeline](https://github.com/szemul/error-handler/actions/workflows/php.yml/badge.svg)
[![codecov](https://codecov.io/gh/szemul/error-handler/branch/main/graph/badge.svg?token=JS61P0XIP7)](https://codecov.io/gh/szemul/error-handler)

Simple extensible error handler

### Functional Testing of an application
For functional testing it is advised to Mock the ErrorHandlerRegistry in the sut to always stop on unhandled errors/exceptions and display them.
This helps identify errors.
Just use the `\Szemul\ErrorHandler\Test\ErrorHandlerRegistryMock` instead of the real one!
