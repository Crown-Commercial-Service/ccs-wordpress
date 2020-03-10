# Data Import

## CLI Commands

### wp salesforce import single

You can import a single Framework, and any attached lots, and suppliers by running the following command:

`wp salesforce import single a04b000000XUJxmAAH` where `a04b000000XUJxmAAH` is the Salesforce Framework ID.

### wp salesforce import all

The import can be run with the command `wp salesforce import all` when in the `/public` folder.

## Further technical information

Location of function which this command runs: ***public function all*** `public/wp-content/plugins/ccs-salesforce/includes/cli-commands.php`
