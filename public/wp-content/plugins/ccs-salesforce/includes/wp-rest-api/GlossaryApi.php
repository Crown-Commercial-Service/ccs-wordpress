<?php

class GlossaryApi{

    public function getListOfGlossary(){

        $results  = [];
        if (have_rows('list_of_glossary', 'option')):
            while (have_rows('list_of_glossary', 'option')): the_row();
                $results[] = [
                                'term' => get_sub_field('term'),
                                'meaning' => get_sub_field('meaning')
                            ];
            endwhile;
        endif;

        $meta = [
            'total_results' => count($results),
        ];

        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta, 'glossaries' => $results]);

        }
}