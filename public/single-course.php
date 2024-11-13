<?php

if( wp_is_block_theme() ) {
    block_template_part('header');
} else {
    // Load the theme's header
    get_header();    
}

// if (have_posts()) : while (have_posts()) : the_post();
?>
    <div class="property-content">
        <h1><?php the_title(); ?></h1>
        
        <?php if (has_post_thumbnail()) : ?>
            <div class="property-thumbnail">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php endif; ?>

        <div class="property-meta">
            <p><strong>Type:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'type', true)); ?></p>
            <p><strong>Lecturer:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'lecturer', true)); ?></p>
            <p><strong>Semester:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'semester', true)); ?></p>
            <p><strong>Credits:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'credits', true)); ?></p>
            <p><strong>Hours:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), 'hours', true)); ?></p>
        </div>

        <div class="property-description">
            <?php the_content(); ?>
        </div>
    </div>

<?php 
// endwhile; endif;

if( wp_is_block_theme() ) {
    block_template_part('footer');
} else {
    // Load the theme's footer
    get_footer(); // Load the theme's footer
}

