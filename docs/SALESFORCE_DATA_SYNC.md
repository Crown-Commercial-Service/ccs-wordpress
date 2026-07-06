# Salesforce to Website data sync process

Salesforce is the master data for many objects used on the website. The core content of the website runs off data stored on Salesforce. However, we are using a wrapper service called MDM for fetching data.

Data imported from MDM is saved to:
* Custom database table to store core framework, supplier & lots data
* WordPress table to store content
* Search index (OpenSearch) for Frameworks and Suppliers


## The data we are interested in

Predominantly we are interested in the following three business entities:

1. Frameworks
2. Lots
3. Suppliers

To support supplier content we also fetch supplier contact details, however we will go into that in more detail later.

## The import process

### Importing data

#### Frameworks

We retrieve every Framework with the following criteria:
```
WHERE Create_draft_web_page__c = TRUE and status in ('Live', 'Expired - Data Still Received', 'Future (Pipeline)', 'Planned (Pipeline)', 'Underway (Pipeline)', 'Awarded (Pipeline)')
```

### importSingleFramework
Check out [Data import](IMPORT_CLI_COMMANDS.md) for instruction on how to run 

The following process is run to save a single framework record.

1. Save framework to DB (create or update to `ccs_frameworks` table)
1. Verify framework saved to DB
1. Create framework in WordPress object if is does not exist
1. Gets lots for the framework from MDM
1. Check if there are any lot that has been deleted or `HideThisLotFromWebsite` has been checked
    1. delete the lot  
    1. delete the lot Wordpress object
1. For lots returned:
    1. Get WordPress ID for existing lot from DB if it exists (`ccs_lots`)
    1. Save lot to DB  (create or update to `ccs_lots` table)
    1. Verify lot saved to DB
    1. Create lot in WordPress object if it does not exist, updates wordpress_id in `ccs_lots` record
    1. If this lot has `hideSuppliers` set to true, skip the rest of the process and do not add any suppliers to this lot
    1. Get lot suppliers from MDM for this Lot
    1. Check if there are any suppliers that has been deleted
        1. delete the suppliers
    1. For each lot supplier returned:
        1. Save supplier to DB (create or update to `ccs_suppliers` table, does not set the `on_live_frameworks` field)
        1. Create or update a LotSupplier object in `ccs_lot_supplier` table

### Checking Supplier Frameworks

Once the above has run through for every Framework, we check each supplier, one by one, to determine if they belong to any _live_ frameworks. If they do, we mark this column in the Supplier database. This is important for certain features of the website to ensure performance is high.

1. Select all suppliers (from `ccs_suppliers` table)
1. Check how many live frameworks each supplier is on. This is defined as: 
    * `status = 'Live' OR status = 'Expired - Data Still Received' AND published_status = 'publish'`
1. Set Supplier object property `onLiveFrameworks` to true or false
1. Save supplier data (to `ccs_suppliers` table)

### Completing the import

We have now completed the import. A summary of the number of imported items, errors and issues are logged to the relevant log file.

## Further technical information

### Scheduled Cron Jobs

A full (all) import is run 4 times a day via the [Linux cron](https://github.com/Crown-Commercial-Service/ccs-wordpress/blob/master/deploy/import/files/wp_import) at: **02:00, 10:00, 12:00, 17:00**.

### Failure Notifications (Dead Man's Snitch and OpsGenie)

In order to ensure staff get notified in the event of a failed import, each import task is piped through Dead Man's Snitch and opsgenie, which sends an alert if no completion hook is triggered when the script finishes. We also get alert on opsgenie when there is a Salesforce connection error which is caused by incorrect API or Salesforce account getting locked up.

### Lock files

`**/home/ec2-user/wp_import_sh.pid**`

This file is created at the start of the import and then deleted on completion of the import to prevents multiple imports from running simultaneously. 
