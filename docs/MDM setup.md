# Master Data Model

Salesforce is the master data for many objects used on the website. However, there is a wrapper service called MDM that is serving the data between Salesforce and the website. Below are some important notes regarding the integration.

## Installation

### Settings / Environment variables

Obtain the following credentials from whoever manages MDM, and then update them in your `.env` file in the root of the project:

* MDM_TOKEN_ENDPOINT
* MDM_CLIENT_ID
* MDM_CLIENT_SECRET
* MDM_API_URL
* MDM_SCOPE

Visit `src/App/Services/MDM/MdmApi.php` for more information on how it works.

## Running the import

Find out more on the technical details of the [data import](IMPORT_CLI_COMMANDS.md).
