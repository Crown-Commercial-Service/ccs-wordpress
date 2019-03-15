<?php

/**
 * Class CustomTrainingApi
 */
class CustomTrainingApi
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
     * Get esourcing dates from Wordpress
     *
     * @return array
     */
    public function get_esourcing_dates()
    {
        $buyerDates = [];
        if (have_rows('buyer_dates', 'option')):
            while (have_rows('buyer_dates', 'option')): the_row();
                $date = date_create_from_format('d/m/Y g:i a' , get_sub_field('buyer_dates__date'))->format('Y-m-d H:i:s');
                $buyerDates[] = ['date' => $date];
            endwhile;
        endif;

        $supplierDates = [];
        if (have_rows('supplier_dates', 'option')):
            while (have_rows('supplier_dates', 'option')): the_row();
                $date = date_create_from_format('d/m/Y g:i a', get_sub_field('supplier_dates_date'))->format('Y-m-d H:i:s');
                $supplierDates[] = ['date' => $date];
            endwhile;
        endif;

        return [
          'buyer_dates'    => $buyerDates,
          'supplier_dates' => $supplierDates
        ];
    }

}
