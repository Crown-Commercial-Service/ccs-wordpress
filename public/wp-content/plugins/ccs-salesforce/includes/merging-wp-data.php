<?php

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;


/**
 * Method that saves the submitted Wordpress post acf data into the custom database,
 * Only for frameworks and lots
 *
 * @param $post_id
 */
function save_post_acf($post_id) {

    $post_type = get_post_type($post_id);

    if ($post_type == 'framework' ) {
        save_framework_data($post_id);
    }

    if ($post_type == 'lot' ) {
        save_lot_data($post_id);
    }
}

/**
 * Saving user input framework data into the custom database
 *
 * @param $post_id
 */
function save_framework_data ($post_id) {

    $frameworkRepository = new FrameworkRepository();

    if (!$frameworkRepository->findById($post_id, 'wordpress_id')) {
        //add error
        return;
    }

    $framework = $frameworkRepository->findById($post_id, 'wordpress_id');

    if(!empty(get_field('framework_summary')))
    {
        $framework->setSummary(sanitize_text_field(get_field('framework_summary')));
    }

    if(!empty(get_field('framework_description')))
    {
        $framework->setDescription(sanitize_text_field(get_field('framework_description')));
    }

    if(!empty(get_field('framework_benefits')))
    {
        $framework->setBenefits(sanitize_text_field(get_field('framework_benefits')));
    }

    if(!empty(get_field('framework_how_to_buy')))
    {
        $framework->setHowToBuy(sanitize_text_field(get_field('framework_how_to_buy')));
    }

    if(!empty(get_field('framework_documents_updates')))
    {
        $framework->setDocumentUpdates(sanitize_text_field(get_field('framework_documents_updates')));
    }

    if(!empty(get_field('framework_keywords')))
    {
        $framework->setKeywords(sanitize_text_field(get_field('framework_keywords')));
    }

    $framework->setPublishedStatus(sanitize_text_field(get_post_status($post_id)));

    //Save the Wordpress data back into the custom database
    $frameworkRepository->update('wordpress_id', $framework->getWordpressId(), $framework);

}

/**
 *
 * Saving user input lot data into the custom database
 *
 * @param $post_id
 */
function save_lot_data ($post_id) {

    $lotRepository = new LotRepository();

    if (!$lotRepository->findById($post_id, 'wordpress_id')) {
        //add error
        return;
    }

    $lot = $lotRepository->findById($post_id, 'wordpress_id');

    if (!empty(get_post_field('post_content', $post_id))){

        $lot->setDescription(sanitize_text_field(get_post_field('post_content', $post_id)));

    }
    //Save the Wordpress data back into the custom database
    $lotRepository->update('wordpress_id', $lot->getWordpressId(), $lot);
}