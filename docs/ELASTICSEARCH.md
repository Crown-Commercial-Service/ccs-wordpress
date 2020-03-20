# CCS ElasticSearch Documentation

The code for the Elastic Search endpoints can be found here:

```public/search-api```

## Manually indexing

Please note that you will need the [WP CLI](https://wp-cli.org/) installed on your machine for these indexing commands to work.

Supplier data can be manually indexed via the following CLI command (please run this from the `public` directory):

```
wp salesforce import updateSupplierSearchIndex
```

Framework data can be manually indexed via the following CLI command (please run this from the `public` directory):

```
wp salesforce import updateFrameworkSearchIndex
```
