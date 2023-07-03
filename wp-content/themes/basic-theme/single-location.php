<?php 


$link = get_field('link'); 
$page_links = get_field('page_link'); 



get_header();
?>


<section class="page">
    <div class="container">



                <h1><?php the_title();?></h1>
            
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <?php the_content(); ?>

                <?php endwhile; else: endif; ?>

          
          <?php if ($link): ?>
                <a href="<?php echo $link['url'];?>" target="<?php echo $link['target'];?>"><?php echo $link['title'];?></a>
          <?php endif; ?>

          <?php if ($page_links): ?>
            <ul>
            <?php foreach($page_links as $link):?>
                <li> 
                    <a href="<?php echo ($link); ?>" target="_blank"><?php echo ($link); ?></a>
                </li>
            <?php endforeach; ?>
            <ul> 
          <?php endif; ?>
   
    </div>
</section>

<?php get_footer();?>