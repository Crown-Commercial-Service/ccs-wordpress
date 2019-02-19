<?php
/**
 * Salesforce importer
 *
 * @see https://make.wordpress.org/cli/handbook/commands-cookbook/
 *
 */

namespace CCS\SFI;

use \WP_CLI;

use App\Model\LotSupplier;
use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\LotSupplierRepository;
use App\Repository\SupplierRepository;
use App\Services\Salesforce\SalesforceApi;

WP_CLI::add_command('salesforce import', 'CCS\SFI\Import');

class Import
{

    /**
     * Imports Salesforce objects into Wordpress database
     *
     * ## EXAMPLES
     *
     *     wp salesforce import frameworks
     *
     * @when after_wp_load
     */

    public function all()
    {
        WP_CLI::success('Starting Import');

        $importCount = [
          'frameworks' => 0,
          'lots'       => 0,
          'suppliers'  => 0
        ];

        $errorCount = [
          'frameworks' => 0,
          'lots'       => 0,
          'suppliers'  => 0
        ];

        $salesforceApi = new SalesforceApi();

        $frameworks = $salesforceApi->getAllFrameworks();
        $frameworkRepository = new FrameworkRepository();
        $lotRepository = new LotRepository();

        foreach ($frameworks as $index => $framework) {
            if (!$frameworkRepository->createOrUpdateExcludingWordpressId('salesforce_id',
              $framework->getSalesforceId(), $framework)) {
                WP_CLI::error('Framework ' . $index . ' not imported.');
                $errorCount['frameworks']++;
                continue;
            }

            $framework = $frameworkRepository->findById($framework->getId());

            WP_CLI::success('Framework ' . $index . ' imported.');
            $importCount['frameworks']++;

            $this->createFrameworkInWordpress($framework);

            $lots = $salesforceApi->getFrameworkLots($framework->getSalesforceId());

            foreach ($lots as $lot) {
                if (!$lotRepository->createOrUpdateExcludingWordpressId('salesforce_id',
                  $lot->getSalesforceId(), $lot)) {
                    $errorCount['lots']++;
                    continue;
                }

                $importCount['lots']++;

                $suppliers = $salesforceApi->getLotSuppliers($lot->getSalesforceId());

                $supplierRepository = new SupplierRepository();
                $lotSupplierRepository = new LotSupplierRepository();

                // Remove all the current relationships to this lot, and create fresh ones.
                $lotSupplierRepository->deleteById($lot->getSalesforceId(),
                  'lot_id');

                foreach ($suppliers as $supplier) {
                    if (!$supplierRepository->createOrUpdateExcludingWordpressId('salesforce_id',
                      $supplier->getSalesforceId(), $supplier)) {
                        $errorCount['suppliers']++;
                        continue;
                    }


                    $importCount['suppliers']++;
                    $lotSuppler = new LotSupplier([
                      'lot_id' => $lot->getSalesforceId(),
                      'supplier_id' => $supplier->getSalesforceId()
                    ]);

                    $contactDetails = $salesforceApi->getContact($lotSuppler->getLotId(), $lotSuppler->getSupplierId());

                    if (!empty($contactDetails))
                    {
                        $lotSupplier = $this->addContactDetailsToLotSupplier($lotSuppler, $contactDetails);
                    }

                    $lotSupplierRepository->create($lotSuppler);
                }

            }
        }

        return $response = [
          'importCount' => $importCount,
          'errorCount'  => $errorCount
        ];
    }


    protected function addContactDetailsToLotSupplier(LotSupplier $lotSuppler, $contactDetails) {

        if (isset($contactDetails->Contact_Name__c)) {
            $lotSuppler->setContactName($contactDetails->Contact_Name__c);
        }

        if (isset($contactDetails->Email__c)) {
            $lotSuppler->setContactEmail($contactDetails->Email__c);
        }

        if (isset($contactDetails->Website_Contact__c)) {
            $lotSuppler->setWebsiteContact($contactDetails->Website_Contact__c);
        }

        return $lotSuppler;
    }


    /**
     * Determine if we need to create a new 'Framework' post in Wordpress, then (if we do) - create one.
     *
     * @param $framework
     */
    protected function createFrameworkInWordpress($framework)
    {
        if (!empty($framework->getWordpressId()))
        {
            // This framework already has a Wordpress ID assigned, so we need to update the Title.
            $this->updateFrameworkTitle($framework);
            return;
        }

        $wordpressId = $this->createPostInWordpress($framework);

        //Update the Framework model with the new Wordpress ID
        $framework->setWordpressId($wordpressId);

        // Save the Framework back into the custom database.
        $frameworkRepository = new FrameworkRepository();
        $frameworkRepository->update('salesforce_id', $framework->getSalesforceId(), $framework);
    }


    public function updateFrameworkTitle($framework)
    {
       $post_id = wp_update_post(array(
            'ID' => $framework->getWordpressId(),
            'post_title' => 'Title 1',
            'post_type' => 'framework'
        ), true);

        if (is_wp_error($post_id)) {
            $errors = $post_id->get_error_messages();
            foreach ($errors as $error) {
                echo $error;
            }
        }

//        $frameworkTitle = get_post_field('post_title', $framework->getWordpressId());
//
//        update_field('post_title', 'Title1', $framework->getWordpressId());
    }

    public function createPostInWordpress($framework)
    {
        // Create a new post
        $wordpressId = wp_insert_post(array(
            'post_title' => $framework->getTitle(),
            'post_type' => 'framework'
        ));

        //Save the salesforce id in Wordpress
        update_field('framework_id', $framework->getSalesforceId(), $wordpressId);

        return $wordpressId;
    }
}
