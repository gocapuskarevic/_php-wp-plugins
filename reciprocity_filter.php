add_action('wp_ajax_resources_ajax', 'resources_ajax');
add_action('wp_ajax_nopriv_resources_ajax', 'resources_ajax');
function resources_ajax() {
   $industries = $_POST['industry'];
   $use_cases = $_POST['cases'];
   $types = $_POST['content_types'];
   $text = $_POST['string'];

   // Articles id
   $article = get_term_by( 'slug', 'articles', 'content-type');
   $article_id = $article->term_id;
   $include_blog = false;
   $are_blogs = false;

   $args = array(
    'post_type'        => 'resource',
    'posts_per_page'   => -1,
    'post_status'      => 'publish',
    'orderby'         => 'date',
    'order'            => 'DESC',
  );

   // include blog posts - start

   $args_b = array(
    'post_type'        => 'post',
    'posts_per_page'   => -1,
    'post_status'      => 'publish',
    'orderby'         => 'date',
    'order'            => 'DESC',
  );

   // include blog posts - end


  $tax_terms = array();

  //Include types if it is selected - start

  $type_terms = array();
    if( sizeof($types) > 1 ) {
      foreach($types as $type) {
        if( $type == $article_id ) $include_blog = true;
          array_push($type_terms, array(
            'taxonomy' => 'content-type',
            'field' => 'id',
            'terms' => $type,
          ) );
      }
      $arr_fin_types = array_merge( array('relation' => 'OR'), $type_terms );
    } elseif( sizeof($types) == 1 ) {
      if( $types[0] == $article_id ) $include_blog = true;
        $arr_fin_types = array(
          'taxonomy' => 'content-type',
          'field' => 'id',
          'terms' => $types[0],
        );
    }
  //Include types if it is selected - end

  if( $industries == '' && $use_cases == '' && $text == '' ){
    $initial_terms = get_terms( 'topics', array(
      'hide_empty' => true,
    ) );

    foreach( $initial_terms as $term ){
      array_push($tax_terms, array(
        'taxonomy' => 'topics',
        'field' => 'id',
        'terms' => $term,
      ));
      
      if( isset($arr_fin_types) ){
        array_push( $tax_terms, $arr_fin_types );
        $arr_with_ct = array_merge( array('relation' => 'AND'), $tax_terms );
        $args['tax_query'] = $arr_with_ct;
      }else $args['tax_query'] = $tax_terms;
      
      // include blog posts - start
      if( $include_blog ) {
        $blogs = array();
        $args_b['tax_query'] = array(
          'relation' => 'OR',
          array(
            'taxonomy' => 'post_tag',
            'field' => 'slug',
            'terms' => $term->slug,
          ),
          array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => $term->slug
          ),
        );
        $post_b = new WP_Query( $args_b );
        $args_b['tax_query'] = array();

        if ( $post_b->have_posts() ) {
          while ( $post_b->have_posts() ) {
            $post_b->the_post(); 
            array_push( $blogs, get_the_ID() );
          }
          wp_reset_postdata();
        }

        $post_r = new WP_Query( $args );
        $tax_terms = array();
        $args['tax_query'] = $tax_terms;

        if ( $post_r->have_posts() ) {
          while ( $post_r->have_posts() ) {
            $post_r->the_post();
            array_push( $blogs, get_the_ID() );
          }
          wp_reset_postdata();
        }
        if( $blogs ){
          $posts = new WP_Query( array( 'post_type' => array('resource', 'post' ), 'post__in' => array_slice( $blogs, 0, 4 ), 'order' => 'DESC' ) );
        }
        
      }else{
        $posts = new WP_Query( $args );
        $tax_terms = array();
        $args['tax_query'] = $tax_terms;
      }
      
      $term_name = $term->name;

      if( $posts->have_posts()) : ?>
        <section class="cluster-section">
          <div class="container-fluid">
            <div class="row">
              <?php show_cluster_thumbnail($term_name, $term->term_id, 'topics' ); ?>
              <div class="col-lg-7">
                <div class="cluster-card-group">
                  <?php while( $posts->have_posts() ) : $posts->the_post(); $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id; $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name; ?>
                    <?php show_posts_info( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
                  <?php endwhile; ?>
                </div>
              </div>
            </div>
          </div>
        </section>
      <?php wp_reset_postdata(); ?>
      <?php else: ?>
      <?php endif;
    }
  }

  if( $text != '' ){
    $string = esc_sql( $text );
    //Check if exist term with that name
    $term_finded = array();

    $topic_terms = get_terms( 'industry', array(
      'hide_empty' => true
    ) );

    foreach( $topic_terms as $term ){
      if( strtolower( $term->name ) == $string )
        $term_finded[$term->term_id] = $term->taxonomy;
    }

    $use_cases_terms = get_terms( 'topics', array(
      'hide_empty' => true
    ) );
    foreach( $use_cases_terms as $term ){
      if( strtolower( $term->name ) == $string )
        $term_finded[$term->term_id] = $term->taxonomy;
    }
    
    $check = true;

    if( !empty( $term_finded ) ){
      foreach($term_finded as $key => $value ) {
        array_push($tax_terms, array(
          'taxonomy' => $value,
          'field' => 'id',
          'terms' => $key,
        ) );
        $term_name = get_term_by( 'id', $key, $value )->name;
        $term_id = get_term_by( 'id', $key, $value )->term_id;
      }

      $args['tax_query'] = $tax_terms;

      $posts = new WP_Query( $args );
      $tax_terms = array();

      if( $posts->have_posts()) : $check = false;?>
        <section class="cluster-section">
          <div class="container-fluid">
            <div class="row">
              <?php show_cluster_thumbnail($term_name, $term_id, $value ); ?>
              <div class="col-lg-7">
                <div class="cluster-card-group">
                  <?php while( $posts->have_posts() ) : $posts->the_post(); $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id; $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name;  ?>
                    <?php show_posts_info( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
                  <?php endwhile; ?>
                </div>
              </div>
            </div>
          </div>
        </section>
      <?php wp_reset_postdata(); ?>
      <?php else: ?>
      <?php endif;
    }
    $mixi = array();
    $args['s'] = $string;
    $args['post_type'] = array( 'resource', 'post' );

    $args_b['tax_query'] = array(
      'relation' => 'OR',
      array(
        'taxonomy' => 'post_tag',
        'field' => 'slug',
        'terms' => $string,
      ),
      array(
        'taxonomy' => 'category',
        'field' => 'slug',
        'terms' => $string,
      ),
    );
    $post_b = new WP_Query( $args_b );
    $args_b['tax_query'] = array();

    if ( $post_b->have_posts() ) {
      while ( $post_b->have_posts() ) {
        $post_b->the_post(); 
        array_push( $mixi, get_the_ID() );
      }
      wp_reset_postdata();
    }
    $post_r = new WP_Query( $args );
    $args['post_type'] = 'resource';
    $tax_terms = array();

    if ( $post_r->have_posts() ) {
      while ( $post_r->have_posts() ) {
        $post_r->the_post(); 
        array_push( $mixi, get_the_ID() );
      }
      wp_reset_postdata();
    }
    if( !empty( $mixi ) )
      $posts = new WP_Query( array( 'post_type' => array('resource', 'post' ), 'post__in' => $mixi, 'order' => 'DESC', 'posts_per_page' => -1 ) );
    else $posts = new WP_Query( array( 'post_type' => array('false' ) ) );


    if( $posts->have_posts()) : ?>
      <section class="results-section">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="decorator-red-squares">
                <svg width="69" height="69">
                  <image href="<?php bloginfo('template_url');?>/images/icons/squares.svg" />
                </svg>
              </div>
              <h2>Search Results: <?php echo $posts->found_posts; ?></h2>
            </div>
            <?php while( $posts->have_posts() ) : $posts->the_post(); ?>
              <?php
                $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id;
                $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name; 
              ?>
              <?php short_post_preview( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
            <?php endwhile; ?>
          </div>
        </div>
      </section>
    <?php wp_reset_postdata(); ?>
    <?php else: if($check) show_empty_search_info( $text ); ?>
    <?php endif;
  }

  if(  $industries != '' && $use_cases == '' ){

    foreach($industries as $industry){
      if( $types != '' ) {
        if( $include_blog ){
          $blogs = array();
          $slug = get_term_by( 'id', $industry, 'industry' );
          $slug_name = $slug->slug;
          $blog = array();

          $args_b['tax_query'] = array(
            'relation' => 'OR',
            array(
              'taxonomy' => 'post_tag',
              'field' => 'slug',
              'terms' => $slug_name,
            ),
            array(
              'taxonomy' => 'category',
              'field' => 'slug',
              'terms' => $slug_name
            ),
          );
          $post_b = new WP_Query( $args_b );
          $args_b['tax_query'] = array();
  
          if ( $post_b->have_posts() ) {
            $are_blogs = true;
            while ( $post_b->have_posts() ) {
              $post_b->the_post(); 
              array_push( $blogs, get_the_ID() );
            }
            wp_reset_postdata();
          }

          array_push( $tax_terms, $arr_fin_types );

          array_push( $tax_terms, array(
            'taxonomy' => 'industry',
            'field' => 'id',
            'terms' => $industry,
          ) );

          $arr_finnal = array_merge (array('relation' => 'AND'), $tax_terms );
          $args['tax_query'] = $arr_finnal;

          $chech_for_btm_info = new WP_Query( $args );
          $only_empty = $types;
          if ( $chech_for_btm_info->have_posts() ) {
            while ( $chech_for_btm_info->have_posts() ) {
              $chech_for_btm_info->the_post();
              $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id;
              foreach( $only_empty as $type ){
                if( $type == $ct )
                  $only_empty = array_diff($only_empty, array($ct));
              }
            }
            wp_reset_postdata();
          }else {
            $only_empty = array_diff($only_empty, array(683));
          }

          $post_r = new WP_Query( $args );
          $tax_terms = array();

          if ( $post_r->have_posts() ) {
            while ( $post_r->have_posts() ) {
              $post_r->the_post();
              array_push( $blogs, get_the_ID() );
            }
            wp_reset_postdata();
          }

          if( $blogs ){
            $posts = new WP_Query( array( 'post_type' => array('resource', 'post' ), 'post__in' => array_slice( $blogs, 0, 4 ), 'order' => 'DESC' ) );
          } else {
            $posts = new WP_Query( array( 'post_type' => array( 'empty' ) ) );
          }


        } else{
          array_push( $tax_terms, $arr_fin_types );

          array_push( $tax_terms, array(
            'taxonomy' => 'industry',
            'field' => 'id',
            'terms' => $industry,
          ) );

          $arr_finnal = array_merge (array('relation' => 'AND'), $tax_terms );
          $args['tax_query'] = $arr_finnal;

          $chech_for_btm_info = new WP_Query( $args );
          $only_empty = $types;
          if ( $chech_for_btm_info->have_posts() ) {
            while ( $chech_for_btm_info->have_posts() ) {
              $chech_for_btm_info->the_post();
              $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id;
              foreach( $only_empty as $type ){
                if( $type == $ct )
                  $only_empty = array_diff($only_empty, array($ct));
              }
            }
            wp_reset_postdata();
          }else {
            $only_empty = array_diff($only_empty, array(683));
          }

          $posts = new WP_Query( $args );
          $tax_terms = array();
        }

      } else {
        $tax_terms = array(
          array(
            'taxonomy' => 'industry',
            'field' => 'id',
            'terms' => $industry,
          )
        );

        $args['tax_query'] = $tax_terms;
        $posts = new WP_Query( $args );
        $tax_terms = array();
      }

      $term_name = get_term_by( 'id', $industry, 'industry' )->name;
  
      if( $posts->have_posts()) : ?>
        <section class="cluster-section">
          <div class="container-fluid">
            <div class="row">
              <?php show_cluster_thumbnail($term_name, $industry, 'industry' ); ?>
              <div class="col-lg-7">
              <div class="cluster-card-group">
                  <?php while( $posts->have_posts() ) : $posts->the_post(); $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id; $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name; ?> 
                    <?php show_posts_info( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
                  <?php endwhile; ?>
                </div>
              </div>
              <?php empty_ct_info( $only_empty, $are_blogs ); ?>
            </div>
          </div>
        </section>
      <?php wp_reset_postdata(); ?>
      <?php else: show_empty_cluster_info( $term_name, $industry, $types, 'industry' ); ?>
      <?php endif;
    }
  }

  if(  $industries == '' && $use_cases != '' ){
    
    foreach($use_cases as $case){

      if( $types != '' ) {
        if( $include_blog ){
          $blogs = array();
          $slug = get_term_by( 'id', $case, 'topics' );
          $slug_name = $slug->slug;
          $blog = array();

          $args_b['tax_query'] = array(
            'relation' => 'OR',
            array(
              'taxonomy' => 'post_tag',
              'field' => 'slug',
              'terms' => $slug_name,
            ),
            array(
              'taxonomy' => 'category',
              'field' => 'slug',
              'terms' => $slug_name
            ),
          );
          $post_b = new WP_Query( $args_b );
          $args_b['tax_query'] = array();
  
          if ( $post_b->have_posts() ) {
            $are_blogs = true;
            while ( $post_b->have_posts() ) {
              $post_b->the_post(); 
              array_push( $blogs, get_the_ID() );
            }
            wp_reset_postdata();
          }

          array_push( $tax_terms, $arr_fin_types );

          array_push( $tax_terms, array(
            'taxonomy' => 'topics',
            'field' => 'id',
            'terms' => $case,
          ) );

          $arr_finnal = array_merge (array('relation' => 'AND'), $tax_terms );
          $args['tax_query'] = $arr_finnal;

          $chech_for_btm_info = new WP_Query( $args );
          $only_empty = $types;
          if ( $chech_for_btm_info->have_posts() ) {
            while ( $chech_for_btm_info->have_posts() ) {
              $chech_for_btm_info->the_post();
              $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id;
              foreach( $only_empty as $type ){
                if( $type == $ct )
                  $only_empty = array_diff($only_empty, array($ct));
              }
            }
            wp_reset_postdata();
          }else {
            $only_empty = array_diff($only_empty, array(683));
          }

          $post_r = new WP_Query( $args );
          $tax_terms = array();

          if ( $post_r->have_posts() ) {
            while ( $post_r->have_posts() ) {
              $post_r->the_post();
              array_push( $blogs, get_the_ID() );
            }
            wp_reset_postdata();
          }

          if( $blogs ){
            $posts = new WP_Query( array( 'post_type' => array('resource', 'post' ), 'post__in' => array_slice( $blogs, 0, 4 ), 'order' => 'DESC' ) );
          } else {
            $posts = new WP_Query( array( 'post_type' => array( 'empty' ) ) );
          }

        }else{
          array_push( $tax_terms, $arr_fin_types );

          array_push( $tax_terms, array(
            'taxonomy' => 'topics',
            'field' => 'id',
            'terms' => $case,
          ) );
  
          $arr_finnal = array_merge (array('relation' => 'AND'), $tax_terms );
          $args['tax_query'] = $arr_finnal;

          $chech_for_btm_info = new WP_Query( $args );
          $only_empty = $types;
          if ( $chech_for_btm_info->have_posts() ) {
            while ( $chech_for_btm_info->have_posts() ) {
              $chech_for_btm_info->the_post();
              $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id;
              foreach( $only_empty as $type ){
                if( $type == $ct )
                  $only_empty = array_diff($only_empty, array($ct));
              }
            }
            wp_reset_postdata();
          }else {
            $only_empty = array_diff($only_empty, array(683));
          }

          $posts = new WP_Query( $args );
          $tax_terms = array();
        }
      } else {
          $tax_terms = array(
            array(
              'taxonomy' => 'topics',
              'field' => 'id',
              'terms' => $case,
            )
          );
  
          $args['tax_query'] = $tax_terms;
          $posts = new WP_Query( $args );
          $tax_terms = array();
        }

      $term_name = get_term_by( 'id', $case, 'topics' )->name;
  
      if( $posts->have_posts()) : ?>
        <section class="cluster-section">
          <div class="container-fluid">
            <div class="row">
              <?php show_cluster_thumbnail($term_name, $case, 'topics' ); ?>
              <div class="col-lg-7">
              <div class="cluster-card-group">
                  <?php while( $posts->have_posts() ) : $posts->the_post(); $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id; $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name; ?>
                    <?php show_posts_info( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
                  <?php endwhile; ?>
                </div>
              </div>
              <?php empty_ct_info( $only_empty, $are_blogs ); ?>
            </div>
          </div>
        </section>
      <?php wp_reset_postdata(); ?>
      <?php else: show_empty_cluster_info( $term_name, $case, $types, 'topics' ); ?>
      <?php endif; ?>
      <?php
    }
  }

  if(  $industries != '' && $use_cases != '' ){
    $all_posts_from_industries =array( );
    $all_posts_from_cases =array( );
    $blogs = array( );
    foreach($industries as $industry){

      if( $types != '' ) {
        if( $include_blog ){
          $slug = get_term_by( 'id', $industry, 'industry' );
          $slug_name = $slug->slug;

          $args_b['tax_query'] = array(
            'relation' => 'OR',
            array(
              'taxonomy' => 'post_tag',
              'field' => 'slug',
              'terms' => $slug_name,
            ),
            array(
              'taxonomy' => 'category',
              'field' => 'slug',
              'terms' => $slug_name
            ),
          );
          $post_b = new WP_Query( $args_b );
          $args_b['tax_query'] = array();
  
          if ( $post_b->have_posts() ) {
            while ( $post_b->have_posts() ) {
              $post_b->the_post(); 
              array_push( $blogs, get_the_ID() );
            }
            wp_reset_postdata();
          }
        }
        
        array_push( $tax_terms, $arr_fin_types );

        array_push( $tax_terms, array(
          'taxonomy' => 'industry',
          'field' => 'id',
          'terms' => $industry,
        ) );

        $arr_finnal = array_merge (array('relation' => 'AND'), $tax_terms );
        $args['tax_query'] = $arr_finnal;

        $posts_industry = new WP_Query( $args );
        if( $posts_industry->have_posts()) : ?>
          <?php while( $posts_industry->have_posts() ) : $posts_industry->the_post(); ?>
            <?php array_push( $all_posts_from_industries, get_the_ID() ); ?>
          <?php endwhile; ?>
        <?php endif;
        wp_reset_postdata();
        $tax_terms = array();

      } else {
        $tax_terms = array(
          array(
            'taxonomy' => 'industry',
            'field' => 'id',
            'terms' => $industry,
          )
        );

        $args['tax_query'] = $tax_terms;
        $posts = new WP_Query( $args );
        $tax_terms = array();

        //show here
        $term_name = get_term_by( 'id', $industry, 'industry' )->name;
  
        if( $posts->have_posts()) : ?>
          <section class="cluster-section">
            <div class="container-fluid">
              <div class="row">
                <?php show_cluster_thumbnail($term_name, $industry, 'industry' ); ?>
                <div class="col-lg-7">
                <div class="cluster-card-group">
                    <?php while( $posts->have_posts() ) : $posts->the_post(); $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id; $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name; ?>
                      <?php show_posts_info( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
                    <?php endwhile; ?>
                  </div>
                </div>
              </div>
            </div>
          </section>
        <?php wp_reset_postdata(); ?>
        <?php else: ?>
        <?php endif;
      }
    }

    foreach($use_cases as $case){
      if( $types != '' ) {
        if( $include_blog ){
          $slug = get_term_by( 'id', $case, 'topics' );
          $slug_name = $slug->slug;

          $args_b['tax_query'] = array(
            'relation' => 'OR',
            array(
              'taxonomy' => 'post_tag',
              'field' => 'slug',
              'terms' => $slug_name,
            ),
            array(
              'taxonomy' => 'category',
              'field' => 'slug',
              'terms' => $slug_name
            ),
          );
          $post_b = new WP_Query( $args_b );
          $args_b['tax_query'] = array();

          if ( $post_b->have_posts() ) {
            while ( $post_b->have_posts() ) {
              $post_b->the_post(); 
              array_push( $blogs, get_the_ID() );
            }
            wp_reset_postdata();
          }
        }
        array_push( $tax_terms, $arr_fin_types );

        array_push( $tax_terms, array(
          'taxonomy' => 'topics',
          'field' => 'id',
          'terms' => $case,
        ) );

        $arr_finnal = array_merge (array('relation' => 'AND'), $tax_terms );
        $args['tax_query'] = $arr_finnal;

        $posts_cases = new WP_Query( $args );
        if( $posts_cases->have_posts()) : ?>
          <?php while( $posts_cases->have_posts() ) : $posts_cases->the_post(); ?>
            <?php array_push( $all_posts_from_cases, get_the_ID() ); ?>
          <?php endwhile; ?>
        <?php endif;
        wp_reset_postdata();
        $tax_terms = array();

      } else {
        $tax_terms = array(
          array(
            'taxonomy' => 'topics',
            'field' => 'id',
            'terms' => $case,
          )
        );

        $args['tax_query'] = $tax_terms;
        $posts = new WP_Query( $args );
        $tax_terms = array();
        //show here
        $term_name = get_term_by( 'id', $case, 'topics' )->name;
  
        if( $posts->have_posts()) : ?>
          <section class="cluster-section">
            <div class="container-fluid">
              <div class="row">
                <?php show_cluster_thumbnail($term_name, $case, 'topics' ); ?>
                <div class="col-lg-7">
                <div class="cluster-card-group">
                    <?php while( $posts->have_posts() ) : $posts->the_post(); $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id; $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name; ?>
                      <?php show_posts_info( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
                    <?php endwhile; ?>
                  </div>
                </div>
              </div>
            </div>
          </section>
        <?php wp_reset_postdata(); ?>
        <?php else: ?>
        <?php endif;
      }
    }
    if( $types != '' ) {
      
      $all = array_unique( array_merge( $all_posts_from_industries, $all_posts_from_cases, $blogs ), SORT_NUMERIC );
      $all_posts = new WP_Query( array( 'posts_per_page' => -1, 'post_type' => ['any'], 'post__in' => $all, 'post_status' => 'publish' ) );
       
      if( $all_posts->have_posts()) : ?>
        <section class="results-section">
          <div class="container-fluid">
            <div class="row">
              <div class="col-12">
                <div class="decorator-red-squares">
                  <svg width="69" height="69">
                    <image href="<?php bloginfo('template_url');?>/images/icons/squares.svg" />
                  </svg>
                </div>
                <h2>Search Results : <?php echo $all_posts->post_count; ?></h2>
              </div>
              <?php while( $all_posts->have_posts() ) : $all_posts->the_post(); ?>
                <?php
                  $ct = get_the_terms(get_the_ID(), 'content-type')[0]->term_id;
                  $ct_name = get_the_terms(get_the_ID(), 'content-type')[0]->name; 
                ?>
                <?php short_post_preview( $ct, get_the_time('F j, Y'), get_the_permalink(), get_the_title(), get_bloginfo("template_url").'/images/icons/arrow-1.svg', $ct_name ); ?>
                
              <?php endwhile; ?>
            </div>
          </div>
        </section>
      <?php wp_reset_postdata(); ?>
      <?php else: show_empty_search_info( ( sizeof( $types ) > 1 ) ?  'Selected types: ' : 'Selected type: ', $types ); ?>
      <?php endif;
    }
  }
  die();
}

function show_posts_info( $id_for_svg, $time, $link, $title, $imglink, $ct_name ){
  //SVG for content types
  global $svg;
  if( $id_for_svg == null ) $id_for_svg = 683; // id of Articles content type, change if need
  $go = ( strtolower( $ct_name ) == 'webinars' ) ? 'Watch ' : 'Read ';
  if( $ct_name == null ) $ct_name = 'Article';
  $go .= str_replace( 'ie', 'y', rtrim( $ct_name, 's' ) );
  if( get_field( 'permalink_override' ) != '' ) $link = get_field( 'permalink_override' );
  echo '
    <div class="cluster-card-wrapper">
      <div class="cards cards-resource">
        <div class="card-content">
          <svg width="32" height="32">
            <image href="'.$svg[$id_for_svg].'" />
          </svg>
          <span>'.$time.'</span>
          <h4><a href="'.$link.'" rel="bookmark">'.$title.'</a></h4>
          <a href="'.$link.'" class="tertiary-button">'.$go.'   <object data="'.$imglink.'" type="image/svg+xml" width="15"></object></a>
        </div>
      </div>
    </div> ';
}

function show_cluster_thumbnail( $term_name, $term_id, $tax_slug ){
  global $frameworks_slug_redirect;
  $src = get_term_meta( $term_id, 'thumb_url' )[0];
  if( $src == null ){
    $src = get_option( 'def_thumb_url' );
  }
  $page_link = get_site_url();
  $tm = get_term_by( 'id', $term_id, $tax_slug );
  if( $tax_slug == 'topics' ){
    $frmwk_url = ( array_key_exists( $tm->slug, $frameworks_slug_redirect ) ) ? $frameworks_slug_redirect[$tm->slug] : $tm->slug;
  }
  if( $tax_slug == 'topics' ){
    if( $frmwk_url == 'compliance' ){
      $page_link .= '/'.$frmwk_url.'#content2';
    }else if( $frmwk_url == '/topics/security' || $frmwk_url == '/topics/covid-19' ){
      $page_link .= $frmwk_url;
    }else{
      $page_link .= '/frameworks/'.$frmwk_url.'#content2';
    }
  }
  if( $tax_slug == 'industry' ) $page_link .= '/industries/'.$tm->slug.'#content2';
  echo '
    <div class="col-lg-5">
      <div class="thumbnail-card">
        <div class="thumbnail-image img-hover-zoom">
          <img src="'.$src.'" alt="Risk Medium Image" class="img-fluid" />
        </div>
        <div class="thumbnail-content">
          <h3>'.$term_name.'</h3>
          <!--<p>'.term_description( $term_id ).'</p>-->
          <a href="'.$page_link.'" class="button button-primary">View All Resources</a>
        </div>
      </div>
    </div>
  ';
}

function show_empty_cluster_info( $term_name, $term_id, $types, $tax_slug ){
  $page_link = get_site_url();
  $tm = get_term_by( 'id', $term_id, $tax_slug );
  if( $tax_slug == 'topics' ){
    $frmwk_url = ( array_key_exists( $tm->slug, $frameworks_slug_redirect ) ) ? $frameworks_slug_redirect[$tm->slug] : $tm->slug;
  }
  if( $tax_slug == 'topics' ){
    if( $frmwk_url == 'compliance' ){
      $page_link .= '/'.$frmwk_url.'#content2';
    }else{
      $page_link .= '/frameworks/'.$frmwk_url.'#content2';
    }
  }
  if( $tax_slug == 'industry' ) $page_link .= '/industries/'.$tm->slug.'#content2';
  $src = get_term_meta( $term_id, 'thumb_url' )[0];
  if( $src == null ){
    $src = get_option( 'def_thumb_url' );
  }
  $noun = ( sizeof( $types ) > 1 ) ? 'types' : 'type';
  echo '
    <section class="cluster-section">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-5">
            <div class="thumbnail-card">
              <div class="thumbnail-image img-hover-zoom">
                <img src="'.$src.'" alt="Risk Medium Image" class="img-fluid" />
              </div>
              <div class="thumbnail-content">
                <h3>'.$term_name.'</h3>
                <!--<p>'.term_description( $term_id ).'</p>-->
                <a href="'.$page_link.'" class="button button-primary">View All Resources</a>
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="cluster-card-group">
              <h2>There are no posts for selected content '.$noun.'.</h2>
            </div>
          </div>
        </div>
      </div>
    </section>';
}

function show_empty_search_info( $text, $types = false ){
  if( $types ){
    $span = '';
    foreach( $types as $type ){
      $name = get_term_by( 'id', $type, 'content-type');
      $span .= '<span>'.$name->name.'</span>';
    }
  }

  echo '
    <section class="cluster-section">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-5">
            <div class="thumbnail-card">
              <div class="thumbnail-image img-hover-zoom">
                <img src="'.get_bloginfo("template_url").'/images/risk-medium.jpg" alt="Risk Medium Image" class="img-fluid" />
              </div>
              <div class="thumbnail-content">
                <h3>'.$text.'</h3>
                '.$span.'
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="cluster-card-group">
              <h2>There are no posts for the specified criteria.</h2>
            </div>
          </div>
        </div>
      </div>
    </section>';
}

function short_post_preview( $id_for_svg, $time, $link, $title, $imglink, $ct_name ){
  global $svg;
  if( $id_for_svg == null ) $id_for_svg = 683; // id of Articles content type, change if need
  $go = ( strtolower( $ct_name ) == 'webinars' ) ? 'Watch ' : 'Read ';
  if( $ct_name == null ) $ct_name = 'Article';
  $go .= str_replace( 'ie', 'y', rtrim( $ct_name, 's' ) );
  if( get_field( 'permalink_override' ) != '' ) $link = get_field( 'permalink_override' );
  echo '
      <div class="col-md-4 col-sm-6">
        <div class="cards cards-resource">
          <div class="card-content">
            <svg width="32" height="32">
              <image href="'.$svg[$id_for_svg].'" />
            </svg>
            <span>'.$time.'</span>
            <h4><a href="'.$link.'" rel="bookmark">'.$title.'</a></h4>
            <a href="'.$link.'" class="tertiary-button">'.$go.'   <object data="'.$imglink.'" type="image/svg+xml" width="15"></object></a>
          </div>
        </div>
      </div>
  ';
}

function empty_ct_info( $only_empty, $are_blogs ){
  if( !empty( $only_empty ) ) : ?>
    <div class="col-12">
      <div class="info">
        <?php foreach( $only_empty as $one ) : ?>
          <?php $name = get_term_by( 'id', $one, 'content-type'); ?>
            <?php if( $name->name == 'Articles' && $are_blogs ) : ?>
              <?php continue; ?>
            <?php else: ?>
              <p>There are no posts that have a content type: <?php echo $name->name; ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif;
}

$svg = array( 
  287 => get_bloginfo('template_url').'/images/icons/case-studies.svg',
  280 => get_bloginfo('template_url').'/images/icons/resource-center.svg',
  281 => get_bloginfo('template_url').'/images/icons/faq.svg',
  282 => get_bloginfo('template_url').'/images/icons/guide.svg',
  283 => get_bloginfo('template_url').'/images/icons/resource-center.svg',
  367 => get_bloginfo('template_url').'/images/icons/news-and-events.svg',
  284 => get_bloginfo('template_url').'/images/icons/webinar.svg',
  285 => get_bloginfo('template_url').'/images/icons/resource-center.svg',
  683 => get_bloginfo('template_url').'/images/icons/article.svg',
);

$frameworks_slug_redirect = array(
  'iso'           => 'iso-framework-and-iso-compliance',
  'pci'           => 'pci-framework-and-pci-compliance',
  'soc'           => 'soc-framework-and-soc-compliance',
  'coso'          => 'coso-framework-coso-compliance',
  'ssae'          => 'ssae-18',
  'ccpa'          => 'ccpa',
  'gdpr'          => 'gdpr-compliance',
  'hipaa'         => 'hipaa-compliance',
  'hitrust'       =>'hitrust',
  'nist'          => 'nist-framework-and-nist-compliance',
  'cmmc'          => 'cmmc-compliance',
  'fedramp'       => 'fedramp-compliance',
  'fisma'         => 'fisma-compliance',
  'sox'           => 'sox-framework-sox-compliance',
  'cobit'         => 'cobit',
  'gapp'          => 'gapp-compliance',
  'ferma'         => 'ferma-compliance',
  'case-studies'  => 'case-studies',
  'compliance'    => 'compliance',
  'covid-19'      => '/topics/covid-19',
  'risk'          => 'risk',
  'security'      => '/topics/security'
);

// remove_filter('the_content', 'wpautop');
// remove_filter('the_excerpt', 'wpautop');
remove_filter('term_description', 'wpautop');
