# Salesforce to Website data sync process

Salesforce is the master data for many objects used on the website. The core content of the website runs off data stored 
on [Salesforce](https://www.salesforce.com). Data is fetched from Salesforce, manipulated and stored in such a way so 
that the website can easily consume it.

Data imported from Salesforce is saved to:
* Custom database table to store core framework, supplier & lots data
* WordPress table to store content
* Search index (Elasticsearch) for Frameworks and Suppliers


## The data we are interested in

Predominantly we are interested in the following three business entities:

1. Frameworks
2. Lots
3. Suppliers

To support supplier content we also fetch supplier contact details, however we will go into that in more detail later.

## The import process

### Gathering initial data

Before we start to fetch the data, we need to gather a few large datasets which we store locally to help with processing. This helps with processing speed and significantly reduces the number of API requests we have to make to Salesforce.

The initial data we retrieve are from the following Salesforce objects:

1. Contact
2. Master_Framework_Lot_Contact__c

We fetch and store every entry in these objects before running the import, *every time*.

### Importing data

#### Frameworks

The first object we fetch is Frameworks (Salesforce object: `Master_Framework__c`). We retrieve every Framework with the following criteria:

```
WHERE Don_t_publish_on_website__c = FALSE
```

We store each of these Framework's locally in a database table. We then check whether this Framework has been imported before or not. If not, we create a new post in Wordpress.

#### Lots

Once we have saved the Framework above, we fetch the Lots attached to that Framework. (Salesforce object: `Master_Framework_Lot__c`). All Lots are retrieved that match the following conditions:

```
Master_Framework_Lot_Number__c > '0'
AND
Hide_this_Lot_from_Website__c = FALSE
```

We store each Lot locally in a database table.

We create a Wordpress post for each Lot which hasn't been imported before.

#### Suppliers

For each Lot we then retrieve suppliers if the following property is *not* set on the Lot `Hide_Lot_Suppliers_from_Website__c`.

Providing this not set, we fetch all Suppliers attached to the Lot. (Salesforce object: `Account`). Suppliers are retrieved when they match the following condition:

```
Status__c = 'Live'
```

Providing the above is met, we save all suppliers in a database table.

We then create a link between this supplier and the lot (as suppliers can be on multiple lots). From this point forward, we will refer to this link as the _Lot Supplier joining table_

##### Further supplier details

We perform further queries using the inital data we gathered at the start of the import process, and the data we have already gathered to ascertain whether there are any specific contact or informational details for this lot / supplier relationship.

Once we cross reference the data, details for the following fields are input, where found:

- trading name
- contact name
- contact email

The above details are stored on the _Lot Supplier joining table_ if they exist.

### Checking Supplier Frameworks

Once the above has run through for every Framework, we check each supplier, one by one, to determine if they belong to any _live_ frameworks. If they do, we mark this column in the Supplier database. This is important for certain features of the website to ensure performance is high.

### Completing the import

We have now completed the import. A summary of the number of imported items, errors and issues are logged to the relevant log file.

## Further technical information

### Scheduled Cron Jobs

A full (all) import is run 4 times a day via the [Linux cron](https://github.com/Crown-Commercial-Service/ccs-wordpress/blob/master/deploy/import/files/wp_import) at: 02:00, 10:00, 12:00, 15:00.

### Single import

1. Start lock
1. Generate Salesforce token to authenticate 
1. Get single framework over GET request
1. Map returned JSON object to a PHP object (Framework)
1. Get existing frameworks and lots from WordPress **(may be a redundant step, review and poss remove)**
1. Save single framework (see [importSingleFramework](#importSingleFramework))
1. Update search index for single framework
1. Check all suppliers and update live status if they are on at least one live framework (see [checkSupplierLiveFrameworks](#checkSupplierLiveFrameworks)) - please note this runs for all suppliers, not just those on this framework
1. Update search index for all suppliers, if supplier no longer attached to live framework remove supplier from search index
1. Updates all framework titles in WordPress to include the RM Number
1. Updates all lot titles in WordPress to include the RM Number and the lot number
1. Release lock (this happens automatically on fatal error)

### Import all records

1. Start lock
1. Generate Salesforce token to authenticate 
1. Get all Salesforce contacts and save to temp tables (`temp_contact`, `temp_master_framework_lot_contact`), this helps future processing 
1. Get all frameworks over GET request
1. Map returned JSON objects to a PHP object (Framework), return as an array of frameworks
1. Get existing frameworks and lots from WordPress **(may be a redundant step, review and poss remove)**
1. Loop through all frameworks and:
    1. Save single framework (see [importSingleFramework](#importSingleFramework))
1. Check all suppliers and update live status if they are on at least one live framework (see [checkSupplierLiveFrameworks](#checkSupplierLiveFrameworks))
1. Update search index for all frameworks 
1. Update search index for all suppliers, if supplier no longer attached to live framework remove supplier from search index
1. Updates all framework titles in WordPress to include the RM Number
1. Updates all lot titles in WordPress to include the RM Number and the lot number
1. Release lock (this happens automatically on fatal error)

### importSingleFramework

The following process is run to save a single framework record.

1. Save framework to DB (create or update to `ccs_frameworks` table)
1. Verify framework saved to DB
1. Create framework in WordPress if is does not exist
1. Gets lots for the framework from Salesforce
1. For each lot:
    1. Get WordPress ID for existing lot from DB if it exists (`ccs_lots`)
    1. Save lot to DB  (create or update to `ccs_lots` table)
    1. Verify lot saved to DB
    1. Create lot in WordPress if it does not exist, updates wordpress_id in `ccs_lots` record
    1. Delete all lot suppliers for this lot (this removes the relationship from `ccs_lot_supplier` table, not the supplier record itself)
    1. If this lot has `hideSuppliers` set to true, skip the rest of the process and do not add any suppliers to this lot
    1. Get lot suppliers from Salesforce for this Lot
    1. For each lot supplier:
        1. Save supplier to DB (create or update to `ccs_suppliers` table, does not set the `on_live_frameworks` field)
        1. Create new LotSupplier object
        1. Get trading name for a supplier from Salesforce, and set to LotSupplier object
        1. Get contact details from temp contact tables (see previous step _Get all Salesforce contacts and save to temp tables_)
        1. Add contact details to LotSupplier object
        1. Save LotSupplier object to DB (`ccs_lot_supplier` table)

### checkSupplierLiveFrameworks

This check runs to ensure all suppliers are attached to a live framework. At present CCS do not wish to display suppliers
who are not attached to a live framework.

1. Select all suppliers (from `ccs_suppliers` table)
1. Check how many live frameworks each supplier is on. This is defined as: 
    * `status = 'Live' OR status = 'Expired - Data Still Received' AND published_status = 'publish'`
1. Set Supplier object property `onLiveFrameworks` to true or false
1. Save supplier data (to `ccs_suppliers` table)
