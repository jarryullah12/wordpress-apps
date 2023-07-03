<?php 
/* Template Name: Options */

// $flavours = get_field("choose_a_flavour");
// $where = get_field("where_do_you_want_to_go");
$question = get_field("are_you_learning_anything");




get_header();
?>

<section class="page">
    <div class="container">



                <h1><?php the_title();?></h1>

              <?php if($question):?>
                  <?php echo ($question);?>
                
              <?php endif;?>      

    </div>
</section>

<?php get_footer();?>