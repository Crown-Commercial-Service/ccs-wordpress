# Crown Commercial Service WordPress CMS

WordPress CMS to manage the Crown Commercial public website at https://www.crowncommercial.gov.uk/

Please see [further web documentation](https://github.com/Crown-Commercial-Service/ccsweb-docs/tree/master/web) (this is a private repo).

## Deployment

The process for making new changes to the codebase is:

1. Test in a feature branch
2. Merge to `development` branch to test in Development environment
3. Merge to `preprod` branch to test in PreProd (UAT) environment, get client to test and approve change
4. When ready to go live create Pull Request to merge changes into `master`, once approved this will deploy to Production

See details on [Environments](https://github.com/Crown-Commercial-Service/ccsweb-docs/blob/master/web/ENVIRONMENTS.md) (private docs).

### Production checks

Post launch, we will have a number of deployment checks before merging new code into production, notably:

* Code must pass static code analysis tests & automated tests (Travis)
* Manual review by CCS TechOps to approve Pull Request

## Installation

@todo

### Requirements

* PHP 7.2+
* MySQL 5.7+ 
