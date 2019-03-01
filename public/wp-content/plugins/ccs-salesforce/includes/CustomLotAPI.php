<?php

class CustomLotAPI
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
var_dump('hello');
//        header('Content-Type: application/json');
//        return rest_ensure_response(['meta' => $meta, 'frameworks' => $finalData, 'suppliers' => $suppliersData]);
    }
}
