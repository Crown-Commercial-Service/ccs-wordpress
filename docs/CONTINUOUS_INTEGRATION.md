# Continuous integration

This document summarises the Continuous Integration (CI) setup of this project.

Also see [Frontend CI docs](https://github.com/Crown-Commercial-Service/ccs-frontend/blob/master/docs/CONTINUOUS_INTEGRATION.md).

## WordPress (backend)

* [Travis CI: ccs-wordpress](https://travis-ci.org/Crown-Commercial-Service/ccs-wordpress)
* [Travis config](https://github.com/Crown-Commercial-Service/ccs-wordpress/blob/master/.travis.yml)

The following CI logic is currently used:

* Runs on all commits to development, preprod and master branches
* If CI tests fail you cannot merge into the branch (e.g. make changes live)

The following tests are run:

* Runs as PHP 7.3 and 7.4
* Runs PHP code linting on 7.3 to ensure code syntax is valid
* Runs PHP Code Sniffer to check code is compliant with PSR-2, this only checks code in the following folders:
    * src/
