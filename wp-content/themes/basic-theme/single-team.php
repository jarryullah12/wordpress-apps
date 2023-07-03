<?php 


$locations = get_field("locations");




get_header();
?>

<section class="page">
    <div class="container">



                <h1><?php the_title();?></h1>

                  <?php var_dump($locations);?>
                

    </div>
</section>

<?php get_footer();?>