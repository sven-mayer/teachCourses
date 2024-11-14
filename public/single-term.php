<?php


if( wp_is_block_theme() ) {
    block_template_part('header');
} else {
    // Load the theme's header
    get_header();    
}

$term = get_query_var('term');
$pagename = get_query_var('pagename');

// var_dump($courses);
$terms = TC_Terms::get_terms(array('slug' => $term, 'visibility' => 1));

if (isset($terms) && count($terms) > 1) {
    echo '<div id="loop-container" class="loop-container">';
    echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title warning">' . __('Wanring Multiple Entries', 'teachcourses') . '</h1></div>';
	echo '<div class="post-content"></article></div>';
    echo '</div>';
} 

if (empty($terms)) {
    // echo course not found in div container 
    echo '<div id="loop-container" class="loop-container">';
    echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title">' . __('Term not found', 'teachcourses') . '</h1></div>';
	echo '<div class="post-content"></article></div>';
    echo '</div>';
} else {


    $course_types = get_tc_options('course_type', '`value` ASC');    

    foreach ($terms as $term) {

        echo '<div id="loop-container" class="loop-container">';
        echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title">' . $term->name. the_title() . '</h1></div>';
        echo '<div class="post-content">';

        foreach($course_types as $type){

            $courses_list = TC_Courses::get_courses(array('term_id' => $term->term_id, 'type' => $type->value, 'visibility' => 1));
            
            if(count($courses_list) == 0) {
            
            } else {
                echo '<h2>' . $type->value . '</h2>';
                echo '<ul>';
                foreach($courses_list as $course) {
                    echo '<li><a href="' . get_site_url() . '/teaching/' . $course->term_slug . '/' .$course->slug . '">' . $course->name . '</a></<li>';
                }
                echo '</ul>';
            }
        }
        echo '<div>';
        echo '</article></div>';
        echo '</div>';
    }
}


if( wp_is_block_theme() ) {
    block_template_part('footer');
} else {
    // Load the theme's footer
    get_footer(); // Load the theme's footer
}



?>