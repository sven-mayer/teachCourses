<?php


if( wp_is_block_theme() ) {
    block_template_part('header');
} else {
    // Load the theme's header
    get_header();    
}

$term_slug = get_query_var('term');
$pagename = get_query_var('pagename');
if (empty($term_slug)) {
    // get current trum from settings
    $term_id = get_tc_option('active_term');
    $term = TC_Terms::get_term($term_id);

} else {
    $terms = TC_Terms::get_terms(array('slug' => $term_slug, 'visibility' => 1));
    if (empty($terms)) {
        $term = null;
        $term_id = null;
    } else {
        $term = $terms[0];
        $term_id = $term->term_id;
    }
}

function get_tc_courses_lits($terms){
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

echo '<div id="loop-container" class="loop-container">';

if (empty($term)) {
    // echo course not found in div container 
    echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title">' . __('No Active Term Available', 'teachcourses') . '</h1></div>';
    echo '<div class="post-content"></article></div>';
    
} else {
    get_tc_courses_lits(array($term));
}

$terms = TC_Terms::get_terms(array('term_id' => $term_id, 'visibility' => 1));

if (empty($terms)) {
    // echo course not found in div container 
    echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title">' . __('No Terms Available', 'teachcourses') . '</h1></div>';
    echo '<div class="post-content"></article></div>';
    
} else {
    echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title">' . __('Other Semesters', 'teachcourses') . '</h1></div>';
    echo '<div class="post-content"></article></div>';
    echo '<ul>';
    foreach($terms as $t) {
        echo '<li><a href="' . get_site_url() . '/teaching/' . $t->slug .'">' . $t->name . '</a></<li>';
    }
    echo '</ul>';

}
echo '</div>'; 


if( wp_is_block_theme() ) {
    block_template_part('footer');
} else {
    // Load the theme's footer
    get_footer(); // Load the theme's footer
}



?>