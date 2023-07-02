

<?php get_header();?>
       
       <div class="container">
           <div class="row">
               <div class="col-lg-12">
                   <?php
                       if(have_posts()){
                           while(have_posts()){
                               the_post();
                               ?>
                                   <article>
                                       <h2 ><?php the_title();?></h2>
                                       <p ><?php the_content(); ?></p>
                                   </article>
                               </div>

                               <?php

                           }
                       }
                   ?>
              
           </div>
       </div>
<?php get_footer();?>
    