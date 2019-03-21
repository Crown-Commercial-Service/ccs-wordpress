<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\SupplierRepository;

/**
 * Class CustomFrameworkApi
 */
class CustomFrameworkApi
{
    /**
     * Return error
     *
     * @todo Log this error?
     *
     * @param string $message Error message
     * @param int $statusCode HTTP response status code
     */
    public function error(string $message = '', int $statusCode = 500)
    {
        $data = json_encode([
            'message' => $message
        ]);

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo $data;
        exit;
    }

    /**
     * Endpoint that returns a paginated list of frameworks in a json format
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    function get_frameworks(WP_REST_Request $request)
    {
        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
        }
        $limit = $limit ?? 20;

        if (isset($request['page'])) {
            $page = (int)$request['page'];
        }
        $page = $page ?? 0;

        $category = false;

        $pillar = false;

        $searchKeyword = false;

        if (isset($request['category'])) {
            $category = $request['category'];
        }

        if (isset($request['pillar'])) {
            $pillar = $request['pillar'];
        }

        if (isset($request['keyword'])) {
            $searchKeyword = $request['keyword'];
        }

        //List all frameworks by the search keyword
        if($searchKeyword) {
          return $this->get_frameworks_by_search($searchKeyword, $limit, $page);
        }

        $queryCondition = 'published_status = \'publish\' AND (status = \'Live\' OR status = \'Expired - Data Still Received\') ORDER BY title';

        //If the category search parameter is defined, add it in the SQL query
        if($category){
            $queryCondition = 'category = \'' . $category . '\' AND ' . $queryCondition;
        }

        //If the pillar search parameter is defined, add it in the SQL query
        if($pillar){
            $queryCondition = 'pillar = \'' . $pillar . '\' AND ' . $queryCondition;
        }

        $frameworkRepository = new FrameworkRepository();
        $frameworkCount = $frameworkRepository->countAll($queryCondition);
        $frameworks = $frameworkRepository->findAllWhere($queryCondition, true, $limit, $page);

        if ($frameworks === false) {
            $frameworks = [];

        } else {
            foreach ($frameworks as $index => $framework) {

                $frameworks[$index] = $framework->toArray();
                //Delete the last 3 elements from the frameworks array
                unset($frameworks[$index]['document_updates'], $frameworks[$index]['lots'], $frameworks[$index]['documents']);

            }
        }

        $meta = [
            'total_results' => $frameworkCount,
            'limit'         => $limit,
            'results'       => $frameworks ? count($frameworks) : 0,
            'page'          => $page == 0 ? 1 : $page
        ];


        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta, 'results' => $frameworks]);
    }

    /**
     * Endpoint that returns an individual framework and the corresponding lots in a json format based on the RM number
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response | WP_Error
     */
    function get_individual_framework(WP_REST_Request $request) {

        if (!isset($request['rm_number'])) {
            return new WP_Error( 'bad_request', 'request is invalid', array('status' => 400) );

        }
        $rmNumber = $request['rm_number'];

        $frameworkRepository = new FrameworkRepository();

        //Retrieve the framework data
        $framework = $frameworkRepository->findLiveOrUpcomingFramework($rmNumber);

        if ($framework === false) {
            return new WP_Error('rest_invalid_param', 'framework not found', array('status' => 404));
        }

        //Retrieve the framework documents array
        $frameworkDocuments = $this->get_documents_content($framework->getWordpressId());

        $lotRepository = new LotRepository();
        $supplierRepository = new SupplierRepository();

        // Find all lots for the retrieved framework
        $lots = $lotRepository->findAllById($framework->getSalesforceId(), 'framework_id');
        $lotsData = [];
        $uniqueSuppliers = [];

        if ($lots !== false) {
            foreach ($lots as $lot) {
                $singleLotData = $lot->toArray();
                $suppliersData = [];

                // Find all suppliers for the retrieved lots
                $suppliers = $supplierRepository->findAllWhere('salesforce_id IN (SELECT supplier_id FROM ccs_lot_supplier where lot_id=\'' . $lot->getSalesforceId() . '\')', false);

                if ($suppliers !== false) {
                    foreach ($suppliers as $supplier) {
                        $suppliersData[] = $supplier->toArray();
                        $uniqueSuppliers[] = $supplier->getId();
                    }
                }

                $singleLotData['suppliers'] = $suppliersData;
                $lotsData[$lot->getLotNumber()] = $singleLotData;
            }
        }

        //Sort the lots data
        $sortedLotsData = $this->natural_sort_array($lotsData);

        // Get unique count of lot suppliers for a framework
        $uniqueSuppliers = count(array_unique($uniqueSuppliers));

        //Populate the framework array with data
        $frameworkData = $framework->toArray();

        $frameworkData['lots'] = $sortedLotsData;
        $frameworkData['documents'] = $frameworkDocuments;
        $frameworkData['total_suppliers'] = $uniqueSuppliers;

        header('Content-Type: application/json');
        return rest_ensure_response($frameworkData);
    }

    /**
     * Endpoint that returns the suppliers corresponding to an individual framework in a json format
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response | WP_Error
     */
    function get_framework_suppliers(WP_REST_Request $request) {

        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
        }
        $limit = $limit ?? 4;

        if (isset($request['page'])) {
            $page = (int)$request['page'];
        }
        $page = $page ?? 0;


        if (!isset($request['rm_number'])) {
            return new WP_Error( 'bad_request', 'request is invalid', array('status' => 400) );
        }

        $rmNumber = $request['rm_number'];

        $frameworkRepository = new FrameworkRepository();

        // Retrieve the live framework data
        $framework = $frameworkRepository->findLiveFramework($rmNumber);
        $frameworkData = $framework->toArray();

        if ($framework === false) {
            return new WP_Error('rest_invalid_param', 'framework not found', array('status' => 404));
        }

        $lotRepository = new LotRepository();
        //Retrieve all lots for a corresponding framework, based on the rm number
        $lots = $lotRepository->findFrameworkLots($rmNumber);

        $lotSalesforceIds = [];

        if ($lots !== false) {
            foreach ($lots as $lot) {
                $lotSalesforceIds[] = $lot->getSalesforceId();
            }
        }

        $lotIds = implode ("', '", $lotSalesforceIds);

        $supplierRepository = new SupplierRepository();
        $suppliersCount = $supplierRepository->countAllSuppliers($lotIds);
        //Retrieve all suppliers for specific lots, based on their salesforce id
        $suppliers = $supplierRepository->findLotSuppliers($lotIds, true, $limit, $page);

        $suppliersData = [];

        if ($suppliers !== false) {
            foreach ($suppliers as $index => $supplier) {

                $frameworks = $frameworkRepository->findSupplierLiveFrameworks($supplier->getSalesforceId());
                $liveFrameworks = [];

                foreach ($frameworks as $counter => $framework) {
                    $liveFrameworks[$counter] =
                        ['title' => $framework->getTitle(),
                            'rm_number' => $framework->getRmNumber()
                        ] ;
                }

                $suppliersData[$index] =
                    [
                        'supplier_name' => $supplier->getName(),
                        'supplier_id' => $supplier->getId(),
                        'live_frameworks' => $liveFrameworks
                    ];
            }
        }

        $meta = [
            'total_results' => $suppliersCount,
            'limit'         => $limit,
            'results'       => $suppliers ? count($suppliers) : 0,
            'page'          => $page == 0 ? 1 : $page,
            'framework_title' => $frameworkData['title'],
            'framework_rm_number' => $frameworkData['rm_number'],
        ];

        header('Content-Type: application/json');
        return rest_ensure_response(['meta' => $meta, 'results' => $suppliersData]);
    }

    /**
     * Endpoint that returns a paginated list of upcoming deals in a json format
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    function get_upcoming_deals(WP_REST_Request $request)
    {

//    if(isset($request['limit']))
//    {
//        $limit = (int) $request['limit'];
//    }
//    $limit = $limit ?? 10;
//
//    if(isset($request['page']))
//    {
//        $page = (int) $request['page'];
//    }
//    $page = $page ?? 0;

        $frameworkRepository = new FrameworkRepository();
        $frameworks = $frameworkRepository->findUpcomingDeals();

        $futureFrameworks = [];
        $plannedFrameworks = [];
        $underwayFrameworks = [];
        $awardedFrameworks = [];
        $dynamicFrameworks = [];


        foreach ($frameworks as $framework)
        {
            if(!empty($framework->getStatus())) {

                if($framework->getStatus() === 'Future (Pipeline)') {
                    $futureFrameworks[] = $framework->toArray();
                }

                if($framework->getStatus() === 'Planned (Pipeline)') {
                    $plannedFrameworks[] = $framework->toArray();
                }

                if($framework->getStatus() === 'Underway (Pipeline)') {
                    $underwayFrameworks[] = $framework->toArray();
                }

                if($framework->getStatus() === 'Awarded (Pipeline)' || ($framework->getStatus() === 'Live' &&
                        $framework->getTerms() !== 'DPS')) {
                    $awardedFrameworks[] = $framework->toArray();
                }

                if($framework->getStatus() === 'Live' && $framework->getTerms() === 'DPS'){
                    $dynamicFrameworks[] = $framework->toArray();
                }
            }

        }

        $meta = [
            'awarded_pipeline_results'            => count($awardedFrameworks),
            'underway_pipeline_results'           => count($underwayFrameworks),
            'dynamic_purchasing_systems_results'  => count($dynamicFrameworks),
            'planned_pipeline_results'            => count($plannedFrameworks),
            'future_pipeline_results'             => count($futureFrameworks)
        ];

        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta,  'awarded_pipeline' => $awardedFrameworks,'underway_pipeline' => $underwayFrameworks,'dynamic_purchasing_systems' => $dynamicFrameworks, 'planned_pipeline' => $plannedFrameworks, 'future_pipeline' => $futureFrameworks]);
    }

    /**
     * Get framework documents content from Wordpress
     *
     * @param $wordpressId
     * @return array
     */
    public function get_documents_content($wordpressId) {

        $currentFramework = get_post($wordpressId);
        $currentFrameworkId = $currentFramework->ID;
        $frameworkDocuments = [];

        if(have_rows('framework_documents', $currentFrameworkId)) {
            while(have_rows('framework_documents', $currentFrameworkId)): the_row();

                $mediaId = get_sub_field('framework_documents_framework_documents_document');
                $attachment = acf_get_attachment($mediaId);
                $frameworkDocuments[] = [
                    'title' => $attachment["title"],
                    'url' => $attachment["url"]
                ];

            endwhile;
        }

        return $frameworkDocuments;
    }

    /**
     * Natural sort the data for the lots array based on the lot number
     *
     * @param $lots
     * @return array
     */
    public function natural_sort_array($lots) {

        $lotNumbers = array_keys($lots); // @todo remove this reliance so we can duplicate
        natsort($lotNumbers);
        $lotsDataCopy = $lots;
        $lotsData = [];
        foreach ($lotNumbers as $number) {
            $lotsData[] = $lotsDataCopy[$number];
        }

        return $lotsData;
    }


    /**
     * Keyword search functionality for all frameworks
     *
     * @param $keyword
     * @param $limit
     * @param $page
     * @return mixed|WP_REST_Response
     */
    public function get_frameworks_by_search($keyword, $limit, $page) {

        $frameworkRepository = new FrameworkRepository();

        //Match the rm number of the framework
        $singleFramework = $frameworkRepository->findLiveFramework($keyword);

        if ($singleFramework !== false) {
            $frameworks = [$singleFramework->toArray()];
            $frameworkCount = 1;

        } else {
            // If it doesn't match, perform the keyword search text
            $frameworkCount = $frameworkRepository->countSearchResults($keyword);

            $frameworks = $frameworkRepository->performKeywordSearch($keyword, $limit, $page);

            if ($frameworks === false) {
                $frameworks = [];

            } else {
                foreach ($frameworks as $index => $framework) {

                    $frameworks[$index] = $framework->toArray();
                    // Delete the last 3 elements from the frameworks array
                    unset($frameworks[$index]['document_updates'], $frameworks[$index]['lots'], $frameworks[$index]['documents']);

                }
            }
        }

        $meta = [
            'total_results' => $frameworkCount,
            'limit'         => $limit,
            'results'       => $singleFramework ?  1 : count($frameworks),
            'page'          => $page == 0 ? 1 : $page
        ];

        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta, 'results' => $frameworks]);
    }
}
