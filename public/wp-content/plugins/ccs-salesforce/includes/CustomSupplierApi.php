<?php

use App\Repository\FrameworkRepository;
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
}
