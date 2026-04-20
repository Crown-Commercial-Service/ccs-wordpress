# Data Import - CLI Commands

Note: The following import commands must be executed from within the /public folder.

## Importing single agreement

You can import a single agreement—along with any attached lots and suppliers—by running the following command:

`wp mdm-import importSingle RM6200`

In this example, `RM6200` represents the RM number for the agreement. By default, this command will also re-index your database for all agreements and suppliers. To bypass the indexing process, include the `--skip` flag.

Example: `wp mdm-import importSingle RM526 --skip`


## Importing all agreements
`wp mdm-import importAll` 

This command imports all agreements and their associated lots and suppliers, providing the latest data available.


## Further technical information

The logic for these commands is located here:
`public/wp-content/plugins/ccs-mdm/includes/cli-commands.php`