# CCS ElasticSearch Documentation

## Indexing
All framework and supplier data is imported into Wordpress from Salesforce. 

Suppliers are indexed after everything has been imported and saved, towards the end of a successful import run.

Frameworks are indexed one by one, after each Framework has been imported/updated.

Indexing is applied by Elasticsearch. 

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

## Framework Search 
The search term is compared against the following fields and results are displayed in decreasing order of score. The score can be determined by the number of fields matched to the search term. A boost (or multiplier, as indicated below) is applied to specific fields to increase the weight or relevance of that match. 

 - 'id'                         
 - 'title' ^3              
 - 'start_date'      
 - 'end_date'            
 - 'rm_number'          
 - 'rm_number_numerical’ ^3
 - 'summary'             
 - 'description' ^2         
 - 'terms'              
 - 'pillar'             
 - 'category'           
 - 'status'             
 - 'published_status' 
 - 'benefits'            
 - 'how_to_buy'          
 - 'keywords' ^2


## Supplier Search 
The search term is compared against the following fields and results are displayed in decreasing order of score. The score can be determined by the number of fields matched to the search term. A boost (or multiplier, as indicated below) is applied to specific fields to increase the weight or relevance of that match.

- 'id'                        
- 'salesforce_id'            
- 'name' ^2                   
- ‘encoded_name'             
- 'duns_number'               
- 'trading_name'              
- 'alternative_trading_names' 
- 'city'                      
- 'postcode'                 
- 'have_guarantor'   

### Fuzziness
Fuzziness is set to '1' : meaning 1 character can be out of place at the end or start of a string

## Implementation

The code for the Elastic Search endpoints can be found here:

```public/search-api```
