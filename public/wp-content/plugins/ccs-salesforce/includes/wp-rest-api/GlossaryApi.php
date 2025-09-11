<?php

class GlossaryApi{

    public function getListOfGlossary(){

        $results  = [];
        if (have_rows('list_of_glossary', 'option')):
            while (have_rows('list_of_glossary', 'option')): the_row();
                $results[] = [
                                'term' => get_sub_field('term'),
                                'meaning' => get_sub_field('meaning'),
                                'keyword' => get_sub_field('keyword')
                            ];
            endwhile;
        endif;

        $meta = [];

        if(have_rows('intro_text', 'option')):
            while(have_rows('intro_text', 'option')): the_row();
                $meta[] = [
                    'total_results' => count($results),
                    'intro_text' => get_sub_field('intro_text')

                ];
            endwhile;
        endif;

        header('Content-Type: application/json');

        return rest_ensure_response(['meta' => $meta, 'glossaries' => $results]);

        }
}