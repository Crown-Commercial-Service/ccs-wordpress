# Government Commercial Agency WordPress CMS 

WordPress CMS to manage the Government Commercial Agency public website at https://www.crowncommercial.gov.uk/

Please see [further web documentation](https://github.com/Crown-Commercial-Service/ccsweb-docs/tree/master/web) (this is a private repo).

## Deployment

### Testing changes

1. Test in a feature branch.
2. Merge to `development` branch to test in Development environment.
3. Merge to `preprod` branch to test in PreProd (UAT) environment.
4. Get client to test and approve change.

### Deploy a change to Production

1. Create Pull Request to merge changes into `master`, ensure you add details of tickets you are fixing in the PR.
2. Code must pass automatic tests & be approved by one other person.
3. Email internal-it@crowncommercial.gov.uk to ask approval of this PR.
4. Once approved, merge into master. This deploys to Production. 

See details on [Environments](https://github.com/Crown-Commercial-Service/ccsweb-docs/blob/master/web/ENVIRONMENTS.md) (private docs).

### Production checks

Post launch, we will have a number of deployment checks before merging new code into production, notably:

* Code must pass static code analysis tests & automated tests (Travis).
* Manual review by GCA TechOps to approve Pull Request.

## Installation

A step-by-step guide to get a development environment running on your machine.

### Requirements

* PHP 7.3+
* MySQL 5.7+ 

### Database

You will need to import an up-to-date version of the database into your local environment.

### ENV File

This repository contains an example ENV file named `.env.example`. You need to copy this file and rename it to just `.env`.

Within this `.env` file you will need to configure the various empty environment variables from the example file, these include the site URL (`WP_SITEURL`) which will need to match the domain defined in your local hosting setup. This file also includes the database and other WordPress configuration.

For the Salesforce import to work locally, you will also need to specify the correct connection details in this file (these environment variables are specified at the top of the file, and separated from the rest by clear comments)

## Continuous integration

We use [Travis CI](https://travis-ci.org/Crown-Commercial-Service/ccs-wordpress) to run automated tests on all merges into development, preprod and master. 

### PHP CodeSniffer

Application code (in the `src/` folder) must meet the ([PSR12](https://www.php-fig.org/psr/psr-12/)) coding standard. 
Please note WordPress code is not checked for coding standards at present. We currently ignore
long line lengths, though we can fix this in the future if desired. PHPCS configuration can be found 
in `phpcs.xml.dist`.

You can test for this via:

```
# Summary report
vendor/bin/phpcs --report=summary

# Full details
vendor/bin/phpcs
```

Where possible you can auto-fix code via:

```
vendor/bin/phpcbf
```
