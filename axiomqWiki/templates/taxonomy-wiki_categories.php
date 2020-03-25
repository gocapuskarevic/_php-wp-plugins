<?php
wp_head();

$wiki_data=get_queried_object();
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$posts_args=array(
    'post_type'     => 'axiomqwiki',
    'post_status'   => 'publish',
    'order'         => 'DESC',
    'orderby'       => 'date',
    'posts_per_page' => '4',
    'paged'         => $paged,
    'tax_query'     => array(
        array(
            'taxonomy'      => $wiki_data->taxonomy,
            'field'         => 'slug',
            'terms'         => $wiki_data->slug
        )
    )
);

$wiki_posts_tax = new WP_Query($posts_args);

?>

<?php $wiki_wiki->wiki_header(); ?>
<?php $wiki_wiki->wiki_main_help(); ?>



<div class="container-fluid">
    <div class="row">
        <?php if($wiki_posts_tax->have_posts()) : ?>
            <div class="col-12 col-md-8">
                <div class="row">
                    <h2>All from topic <strong><?php echo $wiki_data->name; ?></strong> : </h2>
                    <?php while($wiki_posts_tax->have_posts()) : $wiki_posts_tax->the_post(); ?>
                        <div class="col-12">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>

                    <div class="pagination">
                        <?php 
                            echo paginate_links( array(
                                'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                                'total'        => $wiki_posts_tax->max_num_pages,
                                'current'      => max( 1, get_query_var( 'paged' ) ),
                                'format'       => '?paged=%#%',
                                'show_all'     => false,
                                'type'         => 'plain',
                                'end_size'     => 2,
                                'mid_size'     => 1,
                                'prev_next'    => true,
                                'prev_text'    => sprintf( '<i></i> %1$s', __( 'Newer Posts', 'text-domain' ) ),
                                'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Posts', 'text-domain' ) ),
                                'add_args'     => false,
                                'add_fragment' => '',
                            ) );
                        ?>
                    </div>
                </div>
            </div>

            <!-- pagination -->

            
        <?php endif; ?>
        <?php $wiki_wiki->wiki_sidebar(); ?>
    </div>
</div>
<?php wp_reset_query(); ?>


<?php $wiki_wiki->wiki_footer(); ?>