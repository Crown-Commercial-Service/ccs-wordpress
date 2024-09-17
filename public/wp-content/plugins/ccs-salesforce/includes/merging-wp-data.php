<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\SupplierRepository;

/**
 * Method that updates the submitted Wordpress post data into the custom database,
 * Only for frameworks and lots
 *
 * @param $post_id
 */
function updated_post_details($post_id, $post_after = null, $post_before = null) {
    if($post_after == null) {
        $post_after = get_post($post_id);
    }

    $post_type = get_post_type($post_id);

    if ($post_type == 'framework' ) {
        save_framework_nonacf_data($post_id, $post_after);
    }

    if ($post_type == 'lot' ) {
        save_lot_nonacf_data($post_id, $post_after);
    }

}

/**
 * Method that updates the submitted Wordpress ACF data into the custom database,
 * Only for frameworks and lots
 *
 * @param $post_id
 */
function updated_post_meta($post_id) {

    $post_type = get_post_type($post_id);

    if ($post_type == 'framework' ) {
        save_framework_acf_data($post_id);
    }

}

/**
 * Saving user input framework (default WP ) data into the custom database
 *
 * @param $post_id
 */
function save_framework_nonacf_data ($post_id, $post_data) {

    $frameworkRepository = new FrameworkRepository();

    if (!$frameworkRepository->findById($post_id, 'wordpress_id')) {
        //add error
        return;
    }

    $framework = $frameworkRepository->findById($post_id, 'wordpress_id');

    //Get the framework taxonomies and save them in the db
    $term_ids = $_POST['radio_tax_input']['framework_type'];

    if (!empty($term_ids)) {
        foreach ($term_ids as $term_id) {
            $term = get_term_by( 'id' , $term_id, 'framework_type');
            $framework->setType($term->name);
        }
    }

    $framework->setPublishedStatus($post_data->post_status);

    //Save the Wordpress data back into the custom database
    $frameworkRepository->update('wordpress_id', $framework->getWordpressId(), $framework);

    $supplierRepository = new SupplierRepository();

    if(get_post_status($post_id) === 'publish') {

        $suppliers = $supplierRepository->fetchSuppliersOnLiveFrameworksViaFrameworkId($framework->getSalesforceId());

        if (!empty($suppliers)) {
            foreach ($suppliers as $supplier)
            {
                //Update the Supplier model with the flag true for live frameworksOK
                $supplier->setOnLiveFrameworks(true);

                // Save the Supplier back into the custom database.
                $supplierRepository->update('salesforce_id', $supplier->getSalesforceId(), $supplier);
            }
        }
    }
}

/**
 * Saving user input framework ACF data into the custom database
 *
 * @param $post_id
 */
function save_framework_acf_data ($post_id) {

    $frameworkRepository = new FrameworkRepository();

    if (!$frameworkRepository->findById($post_id, 'wordpress_id')) {
        //add error
        return;
    }

    $framework = $frameworkRepository->findById($post_id, 'wordpress_id');

    if(get_field('framework_summary') !== null)
    {
        $framework->setSummary(get_field('framework_summary'));
    }

    if(get_field('framework_description') !== null)
    {
        $framework->setDescription(get_field('framework_description'));
    }

    if(get_field('framework_updates') !== null)
    {
        $framework->setUpdates(get_field('framework_updates'));
    }

    if(get_field('framework_benefits') !== null)
    {
        $framework->setBenefits(get_field('framework_benefits'));
    }

    if(get_field('framework_how_to_buy') !== null)
    {
        $framework->setHowToBuy(get_field('framework_how_to_buy'));
    }

    if(get_field('framework_documents_updates') !== null)
    {
        $framework->setDocumentUpdates(get_field('framework_documents_updates'));
    }

    if(get_field('framework_keywords') !== null)
    {
        $framework->setKeywords(get_field('framework_keywords'));
    }

    if(get_field('framework_upcoming_deal_details') !== null)
    {
        $framework->setUpcomingDealDetails(get_field('framework_upcoming_deal_details'));
    }

    if (get_field('framework_upcoming_deal_summary') !== null) {
        $framework->setUpcomingDealSummary(get_field('framework_upcoming_deal_summary'));
    }

    if(get_field('framework_availability') !== null)
    {
        $framework->setAvailability(get_field('framework_availability'));
    }

    if(get_field('framework_cannot_use') !== null)
    {
        $framework->setCannotUse(get_field('framework_cannot_use'));
    }

    if (get_field('framework_regulation') !== null) {
        $framework->setRegulation(get_field('framework_regulation'));
    }


    //Save the Wordpress data back into the custom database
    $frameworkRepository->update('wordpress_id', $framework->getWordpressId(), $framework);

    $supplierRepository = new SupplierRepository();

    if(get_post_status($post_id) === 'publish') {

        $suppliers = $supplierRepository->fetchSuppliersOnLiveFrameworksViaFrameworkId($framework->getSalesforceId());

        if (!empty($suppliers)) {
            foreach ($suppliers as $supplier)
            {
                //Update the Supplier model with the flag true for live frameworksOK
                $supplier->setOnLiveFrameworks(true);

                // Save the Supplier back into the custom database.
                $supplierRepository->update('salesforce_id', $supplier->getSalesforceId(), $supplier);
            }
        }
    }
}

/**
 *
 * Saving user input lot data into the custom database
 *
 * @param $post_id
 */
function save_lot_nonacf_data ($post_id, $post_data) {

    $lotRepository = new LotRepository();

    if (!$lotRepository->findById($post_id, 'wordpress_id')) {
        //add error
        return;
    }

    $lot = $lotRepository->findById($post_id, 'wordpress_id');

    if ($post_data->post_content !== null){

        $lot->setDescription($post_data->post_content);

    }
    //Save the Wordpress data back into the custom database
    $lotRepository->update('wordpress_id', $lot->getWordpressId(), $lot);
}
