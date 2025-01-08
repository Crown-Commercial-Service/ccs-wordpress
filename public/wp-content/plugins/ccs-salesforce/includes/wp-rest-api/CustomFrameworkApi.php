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

        // Set to true if at least one supplier on the framework has CRP url
        $crpCompliant = null;

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
                        if ($supplier->getCrpUrl()) {
                            $crpCompliant = true;
                        }
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

        $frameworkData['crp_compliant'] = $framework->getTerms() != 'Standard' ? null : $crpCompliant;

        if ($framework->getType() == 'CAS framework'){
            $frameworkData['cas_updates']              = $this->getAndSortCasUpdates($framework->getWordpressId());

            $frameworkData['customer_guide']           = get_field('framework_customer_guide', $framework->getWordpressId());
            $frameworkData['core_terms_conditions']    = get_field('framework_core_terms_conditions', $framework->getWordpressId());
            $frameworkData['call_off_order_form']      = get_field('framework_call_off_order_form', $framework->getWordpressId());

            $frameworkData['joint_schedules']          = $this->preparing_cas_documents_content( get_field('framework_cas_joint_schedules_joint_schedule', $framework->getWordpressId()), 'joint_schedule');
            $frameworkData['call_off_schedules']       = $this->preparing_cas_documents_content( get_field('framework_cas_call_off_schedules_call_off_schedule', $framework->getWordpressId()), 'call_off_schedule');
            $frameworkData['framework_schedules']      = $this->preparing_cas_documents_content( get_field('framework_cas_framework_schedules_framework_schedule', $framework->getWordpressId()), 'framework_schedule');
            $frameworkData['templates']                = get_field('framework_templates', $framework->getWordpressId());
        }
        
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

        if ($framework === false) {
            return new WP_Error('rest_invalid_param', 'framework not found', array('status' => 404));
        }

        $frameworkData = $framework->toArray();

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
        $suppliers = $supplierRepository->findLotSuppliers($lotIds, true, $limit, $page, true);

        $suppliersData = [];

        if ($suppliers !== false) {
            foreach ($suppliers as $index => $supplier) {

                $frameworks = $frameworkRepository->findSupplierLiveFrameworks($supplier->getSalesforceId());
                $liveFrameworks = [];

                if ($frameworks !== false) {
                    foreach ($frameworks as $counter => $framework) {
                        $childFrameworkData = $framework->toArray();
                        $liveFrameworks[$counter] =
                            ['title'     => $childFrameworkData['title'],
                             'rm_number' => $childFrameworkData['rm_number'],
                             'status'    => $childFrameworkData['status'],
                             'end_date'  => $childFrameworkData['end_date']
                            ];
                    }
                }

                $suppliersData[$index] =
                    [
                        'supplier_name' => $supplier->getName(),
                        'supplier_id' => $supplier->getId(),
                        'supplier_crp_url' => $supplier->getCrpUrl(),
                        'live_frameworks' => $liveFrameworks
                    ];

                //If the trading name exists, show this as the supplier name
                if(!empty($supplier->getTradingName())) {
                    $suppliersData[$index]['supplier_name'] = $supplier->getTradingName();
                }

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
     * Endpoint that returns an individual lot based on the RM number and lot number
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response | WP_Error
     */
    function get_individual_lot(WP_REST_Request $request) {

        if (!isset($request['rm_number']) || !isset($request['lot_number'])) {
            return new WP_Error( 'bad_request', 'request is invalid', array('status' => 400) );
        }

        $rmNumber = $request['rm_number'];
        $frameworkRepository = new FrameworkRepository();
        $framework = $frameworkRepository->findLiveOrUpcomingFramework($rmNumber);

        if ($framework === false) {
            return new WP_Error('rest_invalid_param', 'framework not found', array('status' => 404));
        }

        $lotRepository = new LotRepository();

        // Find all lots for the retrieved framework
        $lots = $lotRepository->findAllById($framework->getSalesforceId(), 'framework_id');
        $lotData = [];

        if ($lots !== false) {
            foreach ($lots as $lot) {
                if ($lot->getLotNumber() == $request['lot_number']){
                    $lotData = $lot->toArray();
                    break; 
                }
            }
        }
        if ($lotData == []) {
            return new WP_Error('rest_invalid_param', 'lot not found', array('status' => 404));
        }

        header('Content-Type: application/json');
        return rest_ensure_response($lotData);
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
                    'url' => $attachment["url"],
                    'file_size' => $attachment["filesize"]
                ];

            endwhile;
        }

        return $frameworkDocuments;
    }

    /**
     * preparing CAS document content for frontend to display
     *
     * @param $wordpressId
     * @return array
     */
    public function preparing_cas_documents_content($documentsArray, $typeOfSchedules)
    {
        if($documentsArray != false){
            foreach ((array)$documentsArray as $key => $eachEntry) {

                $mediaId = $eachEntry[$typeOfSchedules . '_document'];
                $attachment = acf_get_attachment($mediaId);
                if ($attachment != null) {
                    $documentsArray[$key][$typeOfSchedules . '_document'] = $attachment['url'];
                }

                if($typeOfSchedules != "framework_schedule"){
                    switch ($eachEntry[$typeOfSchedules . '_document_type']) {
                        case 'essential':
                            $documentsArray[$key][$typeOfSchedules . '_document_type'] = 'Essential document';
                            break;
                        case 'optional':
                            $documentsArray[$key][$typeOfSchedules . '_document_type'] = 'Optional document';
                            break;
                    }
                }

                switch ($eachEntry[$typeOfSchedules . '_document_usage']) {
                    case 'read_only':
                        $documentsArray[$key][$typeOfSchedules . '_document_usage'] = 'Read only';
                        break;
                    case 'enter_detail':
                        $documentsArray[$key][$typeOfSchedules . '_document_usage'] = 'You will need to enter details in this document';
                        break;
                    case 'enter_detail_optional':
                        $documentsArray[$key][$typeOfSchedules . '_document_usage'] = 'If you use this schedule, you will need to enter details in this document';
                        break;
                }
                $documentsArray[$key][$typeOfSchedules . '_file_size'] = $attachment["filesize"];
            }
        }

        return $documentsArray;
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
     * Get CAS framework updates content from acf and sorting it with the latest first
     *
     * @param $wordpressId
     * @return array
     */
    public function getAndSortCasUpdates($wordpressId)
    {
        $casUpdates = get_field('framework_cas_updates', $wordpressId);

        if ($casUpdates == false){
            return null;
        }

        $casUpdates = (array) $casUpdates;

        usort($casUpdates, function ($x, $y) {
            return strtotime($x['framework_cas_updates_date']) < strtotime($y['framework_cas_updates_date']);
        });

        return $casUpdates;
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
