# Data import process

We import framework and supplier data from Salesforce into WordPress. Data is read in from the Salesforce database and 
saved to:
* Custom database table to store core framework, supplier & lots data
* WordPress table to store content
* Search index (Elasticsearch) for Frameworks and Suppliers

## Scheduled Cron Jobs

A full (all) import is run 4 times a day via the [Linux cron](https://github.com/Crown-Commercial-Service/ccs-wordpress/blob/master/deploy/import/files/wp_import) at: 02:00, 10:00, 12:00, 15:00.

## Single import

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

## Import all records

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

## importSingleFramework

The following process is run to save a single framework record.

1. Save framework to DB (create or update to `ccs_frameworks` table)
1. Verify framework saved to DB
1. Create framework in WordPress if is does not exist
1. Update search index with single framework
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

## checkSupplierLiveFrameworks

This check runs to ensure all suppliers are attached to a live framework. At present CCS do not wish to display suppliers
who are not attached to a live framework.

1. Select all suppliers (from `ccs_suppliers` table)
1. Check how many live frameworks each supplier is on. This is defined as: 
    * `status = 'Live' OR status = 'Expired - Data Still Received' AND published_status = 'publish'`
1. Set Supplier object property `onLiveFrameworks` to true or false
1. Save supplier data (to `ccs_suppliers` table)
