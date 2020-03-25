<?php
/* Template Name: Search Result */
echo __FILE__;
if(isset($_POST['wp_nonce']) && !wp_verify_nonce($_POST['wp_nonce'],'form-submit')) wp_safe_redirect(site_url());

wp_head();



if(isset($_POST['search'])){
    $search=esc_sql($_POST['search']);
}

if($search){
    $keywords=explode(',',$search);

    $post__in=array();
    
    $terms=array();
    

    //all data for query
    foreach($keywords as $keyword){
        $keyword=esc_sql($keyword);
        $results=$wpdb->get_results("SELECT ID FROM wp_posts WHERE (post_title LIKE '%{$keyword}%' OR post_content LIKE'%{$keyword}%') AND post_status='publish' AND post_type='axiomqwiki'");
        
        //search through title and content
        if($results){
            foreach($results as $result){
                if(!in_array($result->ID,$post__in)) array_push($post__in,(int)$result->ID);
            }
        }

        //add taxonomy terms if exist
        if(get_term_by('name',$keyword,'post_tag')){
            array_push($terms,array(
                'taxonomy'  => 'post_tag',
                'field'     => 'name',
                'terms'     => $keyword
            ));
        }

        //add taxonomy terms if exist
        if(get_term_by('name',$keyword,'wiki_categories')){
            array_push($terms,array(
                'taxonomy'  => 'wiki_categories',
                'field'     => 'name',
                'terms'     => $keyword
            ));
        }

    }

}
//main query args
$posts_args=array(
    'post_type'     =>'axiomqwiki',
    'post_status'   =>'publish',
    'order'         => 'DESC',
    'orderby'       => 'date'
    );

if(!empty($post__in)){
    $posts_args['post__in'] = $post__in;

    $wiki_first= new WP_Query($posts_args);
    unset($posts_args['post__in']);

}

if($terms){
    $count_meta_queries = count($terms);
    if ($count_meta_queries) {
        $posts_args['tax_query'] = ($count_meta_queries > 1) ? array_merge(array('relation' => 'OR'), $terms) : $terms;
    }

    $wiki_second= new WP_Query($posts_args);
    unset($posts_args['tax_query']);
}




$wiki_posts = new WP_Query();
wp_reset_query();
if( $wiki_first && $wiki_second ){

    $count_posts=array_values(array_unique(array_merge( $wiki_first->posts,$wiki_second->posts),SORT_REGULAR));

    $wiki_posts->posts = $count_posts;

    $wiki_posts->post_count = count($count_posts);

}elseif ( $wiki_first ) $wiki_posts=$wiki_first;

elseif ( $wiki_second ) $wiki_posts=$wiki_second;

?>

<?php $wiki_wiki->wiki_header(); ?>
<?php $wiki_wiki->wiki_main_help(); ?>

<div class="container-fluid">
    <div class="row">

        <?php if($wiki_posts->have_posts()) : ?>
            <div class="col-12 col-md-8">
                <div class="row">
                    <h2>Search result</h2>
                    <?php while($wiki_posts->have_posts()) : $wiki_posts->the_post(); ?>
                        <div class="col-12">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        </div>
                    <?php endwhile;  wp_reset_postdata();?>
                </div>
            </div>

        <?php else : ?>
        <div class="col-12 col-md-8">
            <h3>No results found...Try with different keywords.</h3>
        </div>

        <?php endif; ?>
        <?php $wiki_wiki->wiki_sidebar(); ?>

        <?php $wiki_wiki->wiki_footer(); ?>
    </div>
</div>

