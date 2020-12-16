# CCS ElasticSearch Documentation

## Framework Search 
The search term is compared against the following fields and results are displayed in decreasing order of score. The score can be determined by the number of fields matched to the search term. A boost (or multiplier, as indicated below) is applied to specific fields to increase the weight or relevance of that match. 

- title ^3
- rm_number_numerical ^3
- description ^2
- keywords ^2
- summary
- benefits
- how_to_buy

### Fuzziness
Fuzziness is set to '1' : A typo of a single character is allowed in the string matches

## Supplier Search 
The search term is only compared against the name of the supplier. 


## Implementation

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
