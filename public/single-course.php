<?php

if( wp_is_block_theme() ) {
    block_template_part('header');
} else {
    // Load the theme's header
    get_header();    
}

$term = get_query_var('term');
$course = get_query_var('course');
$pagename = get_query_var('pagename');

$courses = TC_Courses::get_courses(array('term' => $term, 'slug' => $course));

if (count($courses) > 1) {
    echo '<div id="loop-container" class="loop-container">';
    echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title warning">' . __('Wanring Multiple Entries', 'teachcourses') . '</h1></div>';
	echo '<div class="post-content"></article></div>';
    echo '</div>';

} 

if (empty($courses)) {
    // echo course not found in div container 
    echo '<div id="loop-container" class="loop-container">';
    echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title">' . __('Course not found', 'teachcourses') . '</h1></div>';
	echo '<div class="post-content"></article></div>';
    echo '</div>';
} else {

    $ret_coures_list = "";
    $courses_list = TC_Courses::get_courses(array('slug' => $course));
    if (count($courses_list) > 1) {
        $ret_coures_list .= '<div id="teachcourses_cours_term_overview"><span>'.__('Course in other Semester', 'teachcourses').':</span> ';
        foreach($courses_list as $course) {
            $ret_coures_list .= '<a href="' . get_site_url() . '/teaching/' . $course->term_slug . '/' .$course->slug . '">' . strtoupper($course->term_slug) . '</a> ';
        }
        $ret_coures_list .= '</div>';
    }

    foreach ($courses as $course) {

        echo '<div id="loop-container" class="loop-container">';
        echo '<div class="post-2 page type-page status-publish hentry entry"><article><div class="post-header"><h1 class="post-title">' . $course->name. the_title() . '</h1></div>';
        echo '<div class="post-content">';

        echo $ret_coures_list;
        if (has_post_thumbnail()){
            echo '<div class="property-thumbnail">' . the_post_thumbnail('large') .'</div>';
        }
        echo '</div>';
        if (!empty($course->action)) {
            echo '<p><strong>' . __('Action', 'teachcourses') . ':</strong> ' . esc_html($course->action) . '</p>';
        }
        
        if (!empty($course->name)) {
            echo '<p><strong>' . __('Name', 'teachcourses') . ':</strong> ' . esc_html($course->name) . '</p>';
        }
        
        if (!empty($course->type)) {
            echo '<p><strong>' . __('Type', 'teachcourses') . ':</strong> ' . esc_html($course->type) . '</p>';
        }
        
        if (!empty($course->lecturer)) {
            echo '<p><strong>' . __('Lecturer', 'teachcourses') . ':</strong> ' . esc_html($course->lecturer) . '</p>';
        }
        
        if (!empty($course->assistant)) {
            echo '<p><strong>' . __('Assistant', 'teachcourses') . ':</strong> ' . esc_html($course->assistant) . '</p>';
        }
        
        if (!empty($course->credits)) {
            echo '<p><strong>' . __('Credits', 'teachcourses') . ':</strong> ' . esc_html($course->credits) . '</p>';
        }
        
        if (!empty($course->hours)) {
            echo '<p><strong>' . __('Hours', 'teachcourses') . ':</strong> ' . esc_html($course->hours) . '</p>';
        }
        
        if (!empty($course->module)) {
            echo '<p><strong>' . __('Module', 'teachcourses') . ':</strong> ' . esc_html($course->module) . '</p>';
        }
        
        if (!empty($course->language)) {
            echo '<p><strong>' . __('Language', 'teachcourses') . ':</strong> ' . esc_html($course->language) . '</p>';
        }
        
        if (!empty($course->links)) {
            echo '<p><strong>' . __('Links', 'teachcourses') . ':</strong> ' . esc_html($course->links) . '</p>';
        }
        
        if (!empty($course->image_url)) {
            echo '<p><strong>' . __('Image URL', 'teachcourses') . ':</strong> ' . esc_html($course->image_url) . '</p>';
        }

        if (!empty($course->description)) {
            echo html_entity_decode($course->description);
        }


        // if (!empty($course->term_id)) {
        //     echo '<p><strong>' . __('Term ID', 'teachcourses') . ':</strong> ' . esc_html($course->term_id) . '</p>';
        // }
        // if (isset($course->visible) && $course->visible !== '') {
        //     echo '<p><strong>' . __('Visible', 'teachcourses') . ':</strong> ' . esc_html($course->visible) . '</p>';
        // }
        // if (!empty($course->slug)) {
        //     echo '<p><strong>' . __('Slug', 'teachcourses') . ':</strong> ' . esc_html($course->slug) . '</p>';
        // }
        
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

