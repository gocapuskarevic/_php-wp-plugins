<?php
wp_head();
echo __FILE__;
$tags=get_the_tags($post->ID);
?>

<?php $wiki_wiki->wiki_header(); ?>
<?php $wiki_wiki->wiki_main_help(); ?>

<?php
//prepare tags
if($tags){
    $display_tags=[];
    foreach($tags as $tag){
        $display_tags[get_tag_link($tag)]=$tag->name;
    }
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-8 margin">
        <h1><?php echo $post->post_title; ?></h1>
        <p><?php echo $post->post_content; ?></p>
        <?php if(!empty($display_tags)) : ?>
            <div id="post_tags">
            <span>Tags :</span>
                <?php foreach($display_tags as $slug=>$name) : ?>
                    <a href="<?php echo site_url().'/axiomqwiki/?tag='.str_replace(' ','-',$name);?>"><?php echo $name; ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
        <?php $wiki_wiki->wiki_sidebar(); ?>
    </div>
</div>


<?php $wiki_wiki->wiki_footer(); ?>