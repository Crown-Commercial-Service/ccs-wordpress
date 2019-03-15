<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\SupplierRepository;

class CustomLotApi
{

    /**
     * Return all suppliers on a lot, based on their lot number
     *
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response | WP_Error
     */
    public function get_lot_suppliers(WP_REST_Request $request)
    {
        if (isset($request['limit'])) {
            $limit = (int)$request['limit'];
        }
        $limit = $limit ?? 4;

        if (isset($request['page'])) {
            $page = (int)$request['page'];
        }
        $page = $page ?? 0;


        if (!isset($request['rm_number'])) {
            return new WP_Error('bad_request', 'request is invalid', array('status' => 400));
        }

        $rmNumber = $request['rm_number'];

        $frameworkRepository = new FrameworkRepository();

        // Retrieve the live framework data
        $framework = $frameworkRepository->findLiveFramework($rmNumber);
        $frameworkData = $framework->toArray();

        if ($framework === false) {
            return new WP_Error('rest_invalid_param', 'framework not found', array('status' => 404));
        }

        if (!isset($request['lot_number'])) {
            return new WP_Error('bad_request', 'request is invalid', array('status' => 400));
        }

        $lotNumber = $request['lot_number'];

        $lotRepository = new LotRepository();
        //Retrieve the lot for a corresponding framework, based on the rm number and lot number
        $lot = $lotRepository->findSingleFrameworkLot($rmNumber, $lotNumber);
        $lotData = $lot->toArray();

        if ($lot === false) {
            return new WP_Error('rest_invalid_param', 'lot not found', array('status' => 404));
        }

        $supplierRepository = new SupplierRepository();
        $suppliersCount = $supplierRepository->countSuppliersForLot($lot->getSalesforceId());
        //Retrieve all suppliers for a specific lot, based on the salesforce id
        $suppliers = $supplierRepository->findLotSuppliers($lot->getSalesforceId(), true, $limit, $page);

        $suppliersData = [];

        if ($suppliers !== false) {
            foreach ($suppliers as $index => $supplier) {

                $frameworks = $frameworkRepository->findSupplierLiveFrameworks($supplier->getSalesforceId());
                $liveFrameworks = [];

                foreach ($frameworks as $counter => $framework) {
                    $liveFrameworks[$counter] =
                        ['title' => $framework->getTitle(),
                            'rm_number' => $framework->getRmNumber()
                        ];
                }

                $suppliersData[$index] =
                    [
                        'supplier_name' => $supplier->getName(),
                        'supplier_id' => $supplier->getId(),
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
            'limit' => $limit,
            'results' => $suppliers ? count($suppliers) : 0,
            'page' => $page == 0 ? 1 : $page,
            'framework_title' => $frameworkData['title'],
            'framework_rm_number' => $frameworkData['rm_number'],
            'lot_title' => $lotData['title'],
            'lot_number' => $lotData['lot_number'],
        ];

        header('Content-Type: application/json');
        return rest_ensure_response(['meta' => $meta, 'results' => $suppliersData]);
    }
}
