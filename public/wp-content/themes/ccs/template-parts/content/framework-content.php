<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */


$framework_id = get_the_ID();

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php
        if ( is_sticky() && is_home() && ! is_paged() ) {
            printf( '<span class="sticky-post">%s</span>', _x( 'Featured', 'post', 'twentynineteen' ) );
        }
        if ( is_singular() ) :
            echo '<h1 class="entry-title">'. ccs_get_framework_title($framework_id) . '</h1>';
        else :
            the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
        endif;
        ?>
    </header><!-- .entry-header -->

    <?php twentynineteen_post_thumbnail(); ?>

    <div class="entry-content">
        <?php
        the_content(
            sprintf(
                wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
                    __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'twentynineteen' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                ccs_get_framework_title($framework_id)
            )
        );
        ?>

        <?php //echo ccs_get_framework_description($framework_id); ?>

        <?php if(!empty(get_field('framework_summary'))): ?>
            <div class="framework-summary" id="framework-summary">
                <?= get_field('framework_summary'); ?>
            </div>
        <?php endif; ?>


        <?php if(!empty(get_field('framework_updates'))): ?>
            <div class="accordion-title" data-associated-accordion="framework-updates">
                <h2>Updates</h2>
            </div>
            <div class="accordion-content" id="framework-updates">
                <?= get_field('framework_updates'); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty(get_field('framework_description'))): ?>
            <div class="accordion-title" data-associated-accordion="framework-description">
                <h2>Description</h2>
            </div>
            <div class="accordion-content" id="framework-description">
                <?= get_field('framework_description'); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty(get_field('framework_benefits'))): ?>
            <div class="accordion-title" data-associated-accordion="framework-benefits">
                <h2>Benefits</h2>
            </div>
            <div class="accordion-content" id="framework-benefits">
                <?= get_field('framework_benefits'); ?>
            </div>
        <?php endif; ?>

        <div class="accordion-title" data-associated-accordion="products-suppliers">
            <h2>Products and suppliers</h2>
        </div>
        <div class="accordion-content" id="products-suppliers">
            <p>Please read the 'How to buy' tab below for detailed instructions on how to find out which suppliers are on this framework.</p>
        </div>

        <?php if(!empty(get_field('framework_how_to_buy'))): ?>
            <div class="accordion-title" data-associated-accordion="framework-how-to-buy">
                <h2>How to buy</h2>
            </div>
            <div class="accordion-content" id="framework-how-to-buy">
                <?= get_field('framework_how_to_buy'); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty(get_field('framework_availability'))): ?>
            <div class="accordion-title" data-associated-accordion="framework-availability">
                <h2>Framework Availability</h2>
            </div>
            <div class="accordion-content" id="framework-availability">
                <?= get_field('framework_availability'); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty(get_field('framework_cannot_use'))): ?>
            <div class="accordion-title" data-associated-accordion="framework-cannot-use">
                <h2>Framework Cannot Use</h2>
            </div>
            <div class="accordion-content" id="framework-cannot-use">
                <?= get_field('framework_cannot_use'); ?>
            </div>
        <?php endif; ?>

        <?php if(have_rows('framework_documents') || !empty(get_field('framework_documents_updates')) ): ?>
            <div class="accordion-title" data-associated-accordion="framework-documents">
                <h2>Documents</h2>
            </div>
        <?php endif; ?>

        <div class="accordion-content" id="framework-documents">
            <?php if(!empty(get_field('framework_documents_updates'))): ?>
                <?= get_field('framework_documents_updates'); ?>
            <?php endif; ?>

            <?php
            // check if the repeater field has rows of data
            if( have_rows('framework_documents') ) {
                // loop through the rows of data
                echo '<ol>';
                while ( have_rows('framework_documents') ) {
                    the_row();

                    $attachmentId = get_sub_field('framework_documents_framework_documents_document');

                    $documentTitle = get_the_title($attachmentId);
                    $documentUrl = get_the_permalink($attachmentId);
                    echo '<li><a href="', $documentUrl ,'">', $documentTitle ,'</a></li>';
                }
                echo '</ol>';
            } else {
                // no rows found
            }
            ?>
        </div>


        <style>
            .accordion-title {
                cursor: pointer;
                position: relative;
            }

            .accordion-title:after {
                content: "+";
                font-size: 2rem;
                line-height: 1;
                position: absolute;
                right: 0;
                top: 0;
            }

            .accordion-title--expanded:after {
                content: "-";
            }
            
            .accordion-title h2 {
                font-size: 2rem;
            }
            
            .accordion-title h2:before {
                display: none;
            }

            .accordion-content {
                display: none;
            }
            
            .accordion-content--visible {
                display: block;
            }
        </style>
        
        <script>

            function toggleAccordionContent(event) {
                event.preventDefault();

                var accordionContentId = this.getAttribute('data-associated-accordion');
                var accordionItemContent = document.getElementById(accordionContentId);
                this.classList.toggle('accordion-title--expanded');
                accordionItemContent.classList.toggle('accordion-content--visible');
            }

            var accordionTitles = document.getElementsByClassName('accordion-title');

            for(var i = 0; i < accordionTitles.length; i++) {
                accordionTitles[i].addEventListener('click', toggleAccordionContent);
            }

        </script>



    </div><!-- .entry-content -->

    <footer class="entry-footer">
        <?php twentynineteen_entry_footer(); ?>
    </footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
