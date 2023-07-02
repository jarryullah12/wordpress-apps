
<?php get_header();?>
       
        <!-- Page content-->
        <div class="container">
            <div class="row">
                <!-- Blog entries-->
                <div class="col-lg-8">

                            <?php
                    if(is_home()){
                        echo "<h1>This is Home Page</h1>";
                    }
                    if(is_front_page()){
                        echo "<h1>This is front Page</h1>";
                    }
                    if(is_404()){
                        echo "<h1>This is 404 Page</h1>";
                    }
                    if(is_page()){
                        echo "<h1>This is page template</h1>";
                    }
                    if(is_single()){
                        echo "<h1>This is single template</h1>";
                    }
                ?>


                    <?php
                    if(have_posts()){
                        while(have_posts()){
                            
                            the_post();
                            // $url = wp_get_attachment_url(get_post_thumbnail_id($post->ID),"thumbnail");
                            $url = wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()),"thumbnail");

                            ?>
  

            <!-- Featured blog post-->
            <div class="card mb-4">
                                <a href="#!"><img class="card-img-top" src="<?php echo $url?>" alt="..." /></a>
                                <div class="card-body">
                                    <!-- <div class="small text-muted">January 1, 2022</div> -->
                                    <h2 class="card-title"><?php the_title();?></h2>
                                    <p class="card-text"><?php the_content(); ?></p>
                                    <a class="btn btn-primary" href="<?php the_permalink();?>">Read more →</a>
                                </div>
                            </div>

                            <?php

                                }
                            }
                            ?>
                            <!-- Products new arrivals and popularity -->
                        <div class="products-new-arrivals">
                            <h3>New Arrivals</h3>
                            <?php 
                                $new_arrival_limit = get_theme_mod('set_new_arrival_limit');
                                $new_arrival_column = get_theme_mod('set_new_arrival_column');
                            ?>
                            <!-- no of limit /columns -->
                            <?php echo do_shortcode( '[products limit= "'.$new_arrival_limit.'" columns="'.$new_arrival_column.'" orderby="date" class="new-arrival-custom-class" ] ' ); ?>
                        </div>


                        <div class="products-popularity">
                           <!-- Popularity means how many times the product sale -->
                            <h3>Popularity</h3>
                            <?php 
                                $popularity_limit = get_theme_mod('set_popular_limit');
                                $popularity_column = get_theme_mod('set_popular_column');
                            ?>
                            <?php echo do_shortcode( '[products limit= "'.$popularity_limit.'" columns="'.$popularity_column.'" orderby="popularity"]' ); ?>
                        </div>
            </div>
                <!-- Side widgets-->
                <div class="col-lg-4">
                    <!-- Search widget-->
                    <div class="card mb-4">
                        
                        <div class="card-header">Search</div>
                        <div class="card-body">
                        <?php  get_search_form(); ?>
                           
                        </div>
                    </div>
                    <!-- Categories widget-->
                    <div class="card mb-4">
                        <div class="card-header">Categories</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><a href="#!">Web Design</a></li>
                                        <li><a href="#!">HTML</a></li>
                                        <li><a href="#!">Freebies</a></li>
                                    </ul>
                                </div>
                                <div class="col-sm-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><a href="#!">JavaScript</a></li>
                                        <li><a href="#!">CSS</a></li>
                                        <li><a href="#!">Tutorials</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Side widget-->
                    <div class="card mb-4">
                        <div class="card-header">Side Widget</div>
                        <div class="card-body">You can put anything you want inside of these side widgets. They are easy to use, and feature the Bootstrap 5 card component!</div>
                    </div>
                </div>
            </div>
        </div>
<?php get_footer();?>
     