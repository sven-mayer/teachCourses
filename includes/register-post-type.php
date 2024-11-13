<?php
/*
Plugin Name: TeachCourses Course Custom Post Type
Description: A custom post type for Courses with a custom page template at /teaching/.
Version: 1.0
Author: Your Name
*/

// Register Course custom post type with the slug 'teaching'
function teachcourses_course_post_type() {
    $labels = array(
        'name'                  => _x( 'Courses', 'Post type general name', 'teachcourses' ),
        'singular_name'         => _x( 'Course', 'Post type singular name', 'teachcourses' ),
        'menu_name'             => _x( 'Courses', 'Admin Menu text', 'teachcourses' ),
        'name_admin_bar'        => _x( 'Course', 'Add New on Toolbar', 'teachcourses' ),
        'add_new'               => __( 'Add New', 'teachcourses' ),
        'add_new_item'          => __( 'Add New Course', 'teachcourses' ),
        'edit_item'             => __( 'Edit Course', 'teachcourses' ),
        'view_item'             => __( 'View Course', 'teachcourses' ),
        'all_items'             => __( 'All Courses', 'teachcourses' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'teaching', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
        'description'        => __('A custom property post type', 'teachcourses'),
    );

    register_post_type( 'course', $args );
}

add_action( 'init', 'teachcourses_course_post_type' );


function teachcourses_register_meta_fields() {
    $post_type = 'course'; // Change to your custom post type name if needed

    // Register each custom field with register_post_meta
    register_post_meta($post_type, 'teachcourses_type', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Type of the property or teaching item',
    ));

    register_post_meta($post_type, 'teachcourses_term_id', array(
        'type' => 'integer',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Term ID',
    ));

    register_post_meta($post_type, 'teachcourses_lecturer', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Lecturer for the course or property',
    ));

    register_post_meta($post_type, 'teachcourses_assistant', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Assistant for the course or property',
    ));

    register_post_meta($post_type, 'teachcourses_semester', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Semester information',
    ));

    register_post_meta($post_type, 'teachcourses_credits', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Credits for the course or property',
    ));

    register_post_meta($post_type, 'teachcourses_hours', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Hours of instruction',
    ));

    register_post_meta($post_type, 'teachcourses_module', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Module information',
    ));

    register_post_meta($post_type, 'teachcourses_language', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Language of instruction or course',
    ));

    register_post_meta($post_type, 'teachcourses_links', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
        'description' => 'Relevant links or resources',
    ));
}
add_action('init', 'teachcourses_register_meta_fields');
