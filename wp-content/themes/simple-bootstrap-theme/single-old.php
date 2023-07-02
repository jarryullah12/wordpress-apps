

<?php get_header();?>
       
      
       <!-- Page content-->
       <div class="container">
           <div class="row">
               <!-- Blog entries-->
               <div class="col-lg-8">
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
                               </div>
                           </div>

                           <?php


                       }
                   }
               ?>
                
               </div>
               <!-- Side widgets-->
               <div class="col-lg-4">
                   <!-- Search widget-->
                   <div class="card mb-4">
                       <div class="card-header">Search</div>
                       <div class="card-body">
                           <div class="input-group">
                               <input class="form-control" type="text" placeholder="Enter search term..." aria-label="Enter search term..." aria-describedby="button-search" />
                               <button class="btn btn-primary" id="button-search" type="button">Go!</button>
                           </div>
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
    