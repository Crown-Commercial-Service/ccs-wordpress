<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
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

        $supplierRepository = new SupplierRepository();
        $supplierCount = $supplierRepository->countAll('true');
        $suppliers = $supplierRepository->findAll(true, $limit, $page);

        $frameworkRepository = new FrameworkRepository();

        $suppliersData = [];

        if ($suppliers !== false) {
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
        }

        $meta = [
            'total_results' => $supplierCount,
            'limit' => $limit,
            'results' => count($suppliers),
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
    function get_individual_supplier(WP_REST_Request $request) {

        if (!isset($request['id'])) {
            return new WP_Error( 'bad_request', 'request is invalid', array('status' => 400) );

        }
        $supplierId = $request['id'];

        $supplierRepository = new SupplierRepository();

        //Retrieve the framework data
        $supplier = $supplierRepository->findById($supplierId);

        if ($supplier === false) {
            return new WP_Error('rest_invalid_param', 'supplier not found', array('status' => 404));
        }

        $lotRepository = new LotRepository();
        $frameworkRepository = new FrameworkRepository();


        // Find all lots for the retrieved supplier
        $lots = $lotRepository->find();
        $lotsData = [];

        if ($lots === false) {
            $lotsData = [];

        } else {
            $uniqueSuppliers = [];

            foreach ($lots as $lot) {
                $lotData = $lot->toArray();
                $suppliersData = [];

                // Find all suppliers for the retrieved lots
                $suppliers = $supplierRepository->findAllWhere('salesforce_id IN (SELECT supplier_id FROM ccs_lot_supplier where lot_id=\'' . $lot->getSalesforceId() . '\')', false);

                if ($suppliers !== false) {
                    foreach ($suppliers as $supplier) {
                        $suppliersData[] = $supplier->toArray();
                        $uniqueSuppliers[] = $supplier->getId();
                    }
                }

                $lotData['suppliers'] = $suppliersData;
                $lotsData[$lot->getLotNumber()] = $lotData;
            }
        }

        // Natural sort lots array
        $lotNumbers = array_keys($lotsData); // @todo remove this reliance so we can duplidate
        natsort($lotNumbers);
        $lotsDataCopy = $lotsData;
        $lotsData = [];
        foreach ($lotNumbers as $number) {
            $lotsData[] = $lotsDataCopy[$number];
        }

        // Get unique count of lot suppliers for a framework
        $uniqueSuppliers = count(array_unique($uniqueSuppliers));

        //Populate the framework array with data
        $frameworkData = $framework->toArray();
        $frameworkData['lots'] = $lotsData;
        $frameworkData['total_suppliers'] = $uniqueSuppliers;

        header('Content-Type: application/json');
        return rest_ensure_response($frameworkData);
    }
}
