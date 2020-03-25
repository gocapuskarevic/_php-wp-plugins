<?php
wp_head();

$wiki_data=get_queried_object();
var_dump($wiki_data);

$posts_args=array(
    'post_type'     => 'axiomqwiki',
    'post_status'   => 'publish',
    'order'         => 'DESC',
    'orderby'       => 'date',
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
                </div>
            </div>
        <?php endif; ?>
        <?php $wiki_wiki->wiki_sidebar(); ?>
    </div>
</div>
<?php wp_reset_query(); ?>


<?php $wiki_wiki->wiki_footer(); ?>