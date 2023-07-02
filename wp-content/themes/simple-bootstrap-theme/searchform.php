<form role="search" <?php echo $twentytwentyone_aria_label; ?> method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <div class="input-group">
        <input type="search" id="<?php echo esc_attr( $twentytwentyone_unique_id ); ?>" class="form-control" placeholder="Enter search term..."  value="<?php echo get_search_query(); ?>" name="s" aria-label="Enter search term..." aria-describedby="button-search" />
        <button type="submit" class="btn btn-primary" id="button-search"> Go</button>
        
        <!-- <input type="hidden" value="product" name="post_type" id="post_type"> -->
    </div>
</form>