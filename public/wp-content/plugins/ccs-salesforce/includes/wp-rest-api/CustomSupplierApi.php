<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\LotSupplierRepository;
use App\Repository\SupplierRepository;

class CustomSupplierApi
{
    /**
     * Endpoint that returns a paginated list of suppliers in a json format
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response | WP_Error
     */
    public function get_suppliers(WP_REST_Request $request)
    {
        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
        }
        $limit = $limit ?? 20;

        if (isset($request['page'])) {
            $page = (int)$request['page'];
        }
        $page = $page ?? 0;

        $searchKeyword = false;

        if (isset($request['keyword'])) {
            $searchKeyword = $request['keyword'];
        }

        //List all suppliers by the search keyword
        if($searchKeyword) {
            return $this->get_suppliers_by_search($searchKeyword, $limit, $page);
        }

        $supplierRepository = new SupplierRepository();

        $condition = 'on_live_frameworks = TRUE';
        $supplierCount = $supplierRepository->countAll($condition);
        $suppliers = $supplierRepository->findAllWhere($condition, true, $limit, $page);

        $frameworkRepository = new FrameworkRepository();

        $suppliersData = [];

        if ($suppliers !== false) {

            $suppliersData = $this->build_supplier_array($frameworkRepository, $suppliers);
        }

        $meta = [
            'total_results' => $supplierCount,
            'limit' => $limit,
            'results' => $suppliers ? count($suppliers) : 0,
            'page' => $page == 0 ? 1 : $page
        ];


        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta, 'results' => $suppliersData]);
    }

    /**
     * Endpoint that returns an individual supplier and the corresponding lots, frameworks, based on the db id
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response | WP_Error
     */
    function get_individual_supplier(WP_REST_Request $request)
    {
        if (!isset($request['id'])) {
            return new WP_Error('bad_request', 'request is invalid', array('status' => 400));
        }

        $supplierId = $request['id'];

        $supplierRepository = new SupplierRepository();

        //Retrieve the supplier data
        $supplier = $supplierRepository->findLiveSupplier($supplierId);

        if ($supplier === false) {
            return new WP_Error('rest_invalid_param', 'supplier not found', array('status' => 404));
        }

        $frameworkRepository = new FrameworkRepository();
        $lotRepository = new LotRepository();
        $lotSupplierRepository = new LotSupplierRepository();

        // Find all frameworks for the retrieved supplier
        $frameworks = $frameworkRepository->findSupplierLiveFrameworks($supplier->getSalesforceId());
        $frameworksData = [];

        if ($frameworks !== false) {
            foreach ($frameworks as $index => $framework) {
                $frameworksData[$index] = $framework->toArray();
                $lotsData = [];

                // Find all lots for the retrieved frameworks
                $lots = $lotRepository->findAllById($framework->getSalesforceId(), 'framework_id');

                if ($lots !== false) {
                    foreach ($lots as $lot) {
                        $currentLotData = $lot->toArray();
                        if ($lotSupplier = $lotSupplierRepository->findByLotIdAndSupplierId($lot->getSalesforceId(), $supplier->getSalesforceId()))
                        {
                            $currentLotData['supplier_contact_name'] = $lotSupplier->getContactName();
                            $currentLotData['supplier_contact_email'] = $lotSupplier->getContactEmail();
                            $currentLotData['supplier_trading_name'] = $lotSupplier->getTradingName();
                            $currentLotData['supplier_website_contact'] = $lotSupplier->isWebsiteContact();
                        }  else {
                            $currentLotData['supplier_contact_name'] = null;
                            $currentLotData['supplier_contact_email'] = null;
                            $currentLotData['supplier_trading_name'] = null;
                            $currentLotData['supplier_website_contact'] = null;
                        }

                        $lotsData[] = $currentLotData;
                    }
                }
                $frameworksData[$index]['lots'] = $lotsData;
            }
        }

        //Populate the framework array with data
        $supplierData = $supplier->toArray();
        $supplierData['live_frameworks'] = $frameworksData;

        header('Content-Type: application/json');
        return rest_ensure_response($supplierData);
    }

    /**
     * Keyword search functionality for all suppliers
     *
     * @param $keyword
     * @param $limit
     * @param $page
     * @return mixed|WP_REST_Response
     */
    public function get_suppliers_by_search($keyword, $limit, $page)
    {

        $supplierRepository = new SupplierRepository();
        $frameworkRepository = new FrameworkRepository();

        //Match the DUNS number of the supplier
        $singleSupplier = $supplierRepository->searchByDunsNumber($keyword);

        if ($singleSupplier !== false) {
            $supplierCount = 1;

            $frameworks = $frameworkRepository->findSupplierLiveFrameworks($singleSupplier->getSalesforceId());
            $liveFrameworks = [];

            if ($frameworks !== false) {
                foreach ($frameworks as $counter => $framework) {
                    $liveFrameworks[$counter] = $framework->toArray();
                }
            }

            $suppliersData = $singleSupplier->toArray();
            $suppliersData['live_frameworks'] = $liveFrameworks;
            $suppliersData = [$suppliersData];


        } else {
            // If it doesn't match, perform the rm number search
            $suppliersData = [];

            $supplierCount = $supplierRepository->countSearchByRmNumberResults($keyword);
            $suppliers = $supplierRepository->searchByRmNumber($keyword, $limit, $page);

            if ($supplierCount == 0)
            {
                // If nothing was found, lets try searching adding 'RM' to the start of the string
                // This solves the issue where a user may have searched with just the integer of the RM number
                $supplierCount = $supplierRepository->countSearchByRmNumberResults('RM'.$keyword);
                $suppliers = $supplierRepository->searchByRmNumber('RM'.$keyword, $limit, $page);
            }

            if ($suppliers !== false) {
                $suppliersData = $this->build_supplier_array($frameworkRepository, $suppliers);

            } else {
                // If the rm number doesn't match, perform the keyword search text
                $supplierCount = $supplierRepository->countSearchResults($keyword);
                $suppliers = $supplierRepository->performKeywordSearch($keyword, $limit, $page);

                if ($suppliers === false) {
                    $suppliers = [];
                } else {
                    $suppliersData = $this->build_supplier_array($frameworkRepository, $suppliers);
                }
            }
        }

        $meta = [
            'total_results' => $supplierCount,
            'limit' => $limit,
            'results' => $singleSupplier ? 1 : count($suppliers),
            'page' => $page == 0 ? 1 : $page
        ];

        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta, 'results' => $suppliersData]);
    }

    /**
     * Build the supplier data and its corresponding live frameworks array
     *
     * @param $frameworkRepository
     * @param $suppliers
     * @return array
     */
    public function build_supplier_array($frameworkRepository, $suppliers) {

        $suppliersData = [];

        foreach ($suppliers as $index => $supplier) {
            $frameworks = $frameworkRepository->findSupplierLiveFrameworks($supplier->getSalesforceId());
            $liveFrameworks = [];

            if ($frameworks !== false) {
                foreach ($frameworks as $counter => $framework) {
                    $liveFrameworks[$counter] = $framework->toArray();
                }
            }

            $suppliersData[$index] = $supplier->toArray();
            $suppliersData[$index]['live_frameworks'] = $liveFrameworks;
        }

        return $suppliersData;
    }
}
