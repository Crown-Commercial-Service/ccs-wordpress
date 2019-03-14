# Crown Commercial Service WordPress CMS

WordPress CMS to manage the Crown Commercial public website at https://www.crowncommercial.gov.uk/

## Deployment

To deploy to Development environment merge to `development` branch.

To deploy to PreProd environment merge to `preprod` branch. 

To deploy to Production environment open a Pull Request and merge to `master` branch.

## Installation

@todo

### Cavalcade (wp-cron)

Uses [Cavalcade](https://github.com/humanmade/Cavalcade) to manage wp-cron

You need the [pcntl](http://php.net/pcntl) extension to run this. If you're on Mac OS install PHP via [Homebrew](https://brew.sh/):

```
brew install php
```

Locally you can then run cron jobs via:
 
```
cd public/

# Show jobs
wp cavalcade jobs 

# Run job 1
wp cavalcade run 1

# With debug
wp --debug cavalcade run 1

# Show logs
wp cavalcade log
```
 
On your production servers you can use the [Cavalcade Runner](https://github.com/humanmade/Cavalcade-Runner)

Thanks to [Human Made](https://hmn.md/).

### Requirements

* PHP 7.2+
* MySQL 5.7+ 
