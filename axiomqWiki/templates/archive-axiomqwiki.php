<?php

echo __FILE__;
wp_head();

//fetch axiomqwiki categories
$terms = get_terms( 'wiki_categories', array(
    'hide_empty' => true,
) );


?>

<?php $wiki_wiki->wiki_header(); ?>
<?php $wiki_wiki->wiki_main_help(); ?>



<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-8">
            <h1>Topics</h1>
            <div class="row">
                <?php foreach($terms as $term) : ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <i class="fas fa-archive"></i>
                        <h3><a href="<?php echo get_term_link($term->term_id,'wiki_categories'); ?>"><?php echo $term->name; ?></a></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


<?php $wiki_wiki->wiki_sidebar(); ?>

<?php $wiki_wiki->wiki_footer(); ?>
