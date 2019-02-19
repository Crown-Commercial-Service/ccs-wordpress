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

            $framework = $frameworkRepository->findById($framework->getSalesforceId(), 'salesforce_id');

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
                $lot = $lotRepository->findById($lot->getSalesforceId(), 'salesforce_id');


                $importCount['lots']++;

                $this->createLotInWordpress($lot);


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
                    $lotSupplierRepository->create($lotSuppler);
                }

            }
        }

        return $response = [
          'importCount' => $importCount,
          'errorCount'  => $errorCount
        ];
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
            $this->updatePostTitle($framework, 'framework');
            return;
        }

        $wordpressId = $this->createFrameworkPostInWordpress($framework);

        //Update the Framework model with the new Wordpress ID
        $framework->setWordpressId($wordpressId);

        // Save the Framework back into the custom database.
        $frameworkRepository = new FrameworkRepository();
        $frameworkRepository->update('salesforce_id', $framework->getSalesforceId(), $framework);
    }

    /**
     * Determine if we need to create a new 'Lot' post in Wordpress, then (if we do) - create one.
     *
     * @param $lot
     */
    protected function createLotInWordpress($lot)
    {
        if (!empty($lot->getWordpressId()))
        {
            // This lot already has a Wordpress ID assigned, so we need to update the Title.
            $this->updatePostTitle($lot, 'lot');
            return;
        }

        $wordpressId = $this->createLotPostInWordpress($lot);

        //Update the Lot model with the new Wordpress ID
        $lot->setWordpressId($wordpressId);

        // Save the Lot back into the custom database.
        $lotRepository = new LotRepository();
        $lotRepository->update('salesforce_id', $lot->getSalesforceId(), $lot);
    }


    public function updatePostTitle($model, $type)
    {
       wp_update_post(array(
            'ID' => $model->getWordpressId(),
            'post_title' => $model->getTitle(),
            'post_type' => $type
        ));

    }

    public function createFrameworkPostInWordpress($framework)
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

    public function createLotPostInWordpress($lot)
    {
        // Create a new post
        $wordpressId = wp_insert_post(array(
            'post_title' => $lot->getTitle(),
            'post_type' => 'lot'
        ));

        return $wordpressId;
    }

    public function test_endpoint() {
        return 'Hello';
    }
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'ccs-salesforce-import/v1', '/framework', array(
        'methods' => 'GET',
        'callback' => 'test_endpoint',
    ) );
} );
