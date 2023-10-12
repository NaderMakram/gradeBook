<?php
// /////////////////////////////////
// get attendance data
// /////////////////////////////////
function get_attendance($courseID)
{

    $user_id = get_current_user_id();
    $key = 'course_attendance_' . $courseID;
    return get_user_meta($user_id, $key, true);
}




// /////////////////////////////////
// get courses data
// /////////////////////////////////
function get_course_data($courseID)
{

    global $wpdb;

    // Define your table names
    $meta_table = $wpdb->prefix . 'postmeta';
    $posts_table = $wpdb->prefix . 'posts';

    // Your SQL query
    $sql = $wpdb->prepare(
        "SELECT p.ID AS id, p.post_title
    FROM $meta_table AS m
    JOIN $posts_table AS p ON (p.ID = m.post_id)
    WHERE m.meta_key = 'course_id'
    AND m.meta_value = %d
    AND p.post_type = 'sfwd-quiz'",
        $courseID
    );

    // Execute the query
    $results = $wpdb->get_results($sql);
    return $results;
}


// testing functions
// helper testing function for printing arrays 
function customPrintR($arr)
{
    ob_start(); // Start output buffering
    echo '<pre style="direction: ltr;">';
    print_r($arr);
    echo '</pre>';
    $output = ob_get_clean(); // Capture and clean the output buffer
    return $output;
}
