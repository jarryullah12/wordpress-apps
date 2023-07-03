<?php 

/*Template Name: Gallery*/

$images = get_field('gallery'); 



get_header();
?>


<section class="page">
    <div class="container">



                <h1><?php the_title();?></h1>
            
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <?php the_content(); ?>

                <?php endwhile; else: endif; ?>

          <?php if($image):?>
               
                     <img src="<?php echo $pic;?>" class="img-fluid" alt="<?php echo $alt;?>" title="<?php echo $title;?>">
           <?php endif;?>
           <?php if($file):?>
               
               <a href="<?php echo $fileurl;?>" download><?php echo $filename;?></a>
     <?php endif;?>

    <?php if($images):?>
        <div class="gallery">
        <div class="row">
            <?php foreach($images as $image):?>
                <div class="col-lg-3">


                <a href="<?php echo $image['sizes']['large'];?>" title="<?php echo $image['caption'];?>">
                     <img src="<?php echo $image['sizes']['thumbnail'];?>" class="img-fluid"> 
                </>      
                </div> 
            <?php endforeach;?>
        </div>
    </div>
     <?php endif;?>
    </div>
</section>

<?php get_footer();?>