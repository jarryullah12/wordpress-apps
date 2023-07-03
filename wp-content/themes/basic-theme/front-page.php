<?php get_header();

$title = get_field('page_title'); 
$description = get_field('text_field'); 
?>


<section class="page">
    <div class="container">



                <h1><?php the_title();?></h1>
            
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <?php the_content(); ?>

                <?php endwhile; else: endif; ?>

               
                
                     <p><?php echo $title;  ?></p>
               
                     <p><?php echo nl2br($description); ?></p>

    </div>
</section>

<?php get_footer();?>