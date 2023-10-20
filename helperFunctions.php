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


function get_audio_assignment_user_course_data()
{
    // Get the current user ID
    $current_user_id = get_current_user_id();
    // $current_user_id = 41;

    // Define post statuses to retrieve
    $post_statuses = array('published');

    // Query posts based on post status and current user ID
    $args = array(
        'author'         => $current_user_id,
        'post_status'    => $post_statuses,
        'post_type'      => 'sfwd-assignment',
        'posts_per_page' => -1,     // Retrieve all posts
    );

    $query = new WP_Query($args);

    $user_course_data = array();

    // Loop through the retrieved posts
    if ($query->have_posts()) {
        $posts = $query->posts;

        foreach ($posts as $post) {
            // Do your stuff, e.g.
            // echo $post->post_name;
            $post_id = $post->ID;
            // $post_name = $post->post_name;
            // $lesson = get_post($post->lesson_id);
            // $lesson_title = $lesson->post_title;

            $meta_data = get_post_meta($post_id);
            $user_course_data[] = array(
                'post_id'    => $post_id,
                // 'post_title' => $post->post_title,
                // 'post_content' => $post->post_content,
                'post_status' => $post->post_status,
                // 'post_name' => $post_name,
                'course_id' => $meta_data['course_id'][0],
                'lesson_title' => $meta_data['lesson_title'][0],
                'scored_points' => $meta_data['points'][0],
                'file_name' => $meta_data['file_name'][0],
                'meta_data'  => $meta_data,
            );
        }

        wp_reset_postdata();
    }




    $arranged_user_courses_data = [];

    // Loop through the quizzes of each user
    foreach ($user_course_data as $question) {
        // Check if the "has_graded" key is set to true
        $question_id = $question['post_id']; // int
        $course_id = $question['course_id']; // int
        $scored_points = $question['scored_points']; // int

        // Initialize course-specific variables if not already initialized
        if (!isset($arranged_user_courses_data[$course_id])) {
            $arranged_user_courses_data[$course_id] = [];
        }
        // if quiz alreade exist, find out the quiz with higher 'scored_points' to add
        if (isset($arranged_user_courses_data[$course_id][$question_id])) {
            // if the alreade existing quiz has lower score, add the new one instead
            if ($arranged_user_courses_data[$course_id][$question_id]['scored_points'] < $scored_points) {
                $arranged_user_courses_data[$course_id][$question_id] = $question;
            }
        } else {
            $arranged_user_courses_data[$course_id][$question_id] = $question;
        }
    }
    return $arranged_user_courses_data;
}

function get_audio_course_data($course_id)
{
    $args = array(
        'post_status'    => 'publish',
        'post_type'      => 'sfwd-lessons',
        'posts_per_page' => -1,     // Retrieve all posts
    );
    $course_lessons = get_posts($args);
    $filtered_lessons = array();
    foreach ($course_lessons as $lesson) {
        $lesson_id = $lesson->ID;
        $lesson_audio_enabled = get_post_meta($lesson_id, 'assignment_enabled_audio', true);
        $lesson_course_id = get_post_meta($lesson_id, 'course_id', true);
        if ($lesson_audio_enabled && $lesson_course_id == $course_id) {
            $filtered_lessons[] = $lesson;
        }
    }
    return $filtered_lessons;
}


function get_user_courses_data($user_id)
{

    $all_user_quizzes = get_user_meta($user_id, '_sfwd-quizzes', true);

    // Check if data exists
    if (empty($all_user_quizzes)) {
        return "No data found for user ID $user_id and meta key _sfwd-quizzes";
    }

    // store the data for each course in a different array
    // user_course_data should have different array for each course
    // each course array should have course_id (int), and quizzes (array)
    // each quizzes array should have quiz_id (int), and questions (array)
    // each questions array should have answers (array)
    // each answer array should have [post_id] [status] [points_awarded]
    $user_courses_data = [];

    // Loop through the quizzes of each user
    foreach ($all_user_quizzes as $quiz) {
        // Check if the "has_graded" key is set to true
        if ($quiz['has_graded']) {
            $courseID = $quiz['course']; // int
            $quizID = $quiz['quiz']; // int
            $scored_points = $quiz['points']; // int
            $total_points = $quiz['total_points']; // int
            $quiz_questions = $quiz['graded']; // arrray

            // Initialize course-specific variables if not already initialized
            if (!isset($user_courses_data[$courseID])) {
                $user_courses_data[$courseID] = [];
            }
            // if quiz alreade exist, find out the quiz with higher 'scored_points' to add
            if (isset($user_courses_data[$courseID][$quizID])) {
                // if the alreade existing quiz has lower score, add the new one instead
                if ($user_courses_data[$courseID][$quizID]['scored_points'] < $scored_points) {
                    $user_courses_data[$courseID][$quizID] = ['scored_points' => $scored_points, 'total_points' => $total_points, 'quiz_questions' => $quiz_questions];
                }
            } else {
                $user_courses_data[$courseID][$quizID] = ['scored_points' => $scored_points, 'total_points' => $total_points, 'quiz_questions' => $quiz_questions];
            }
        }
    }
    return $user_courses_data;
}


// styling functions
// make accordion item
function make_accordion_item($accordion_head, $accordion_body, $courseID)
{
    // $accordion_item_id = get_accordion_item_id($id);
    $accordion_item_id = get_random_key();
    $accordion_item = "
    <div class='accordion-item'>
    <h2 class='accordion-header'>
      <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse$accordion_item_id' aria-expanded='true' aria-controls='collapse$accordion_item_id'>
        $accordion_head
      </button>
    </h2>
    <div id='collapse$accordion_item_id' class='accordion-collapse collapse' data-bs-parent='#quizzesAccordion-$courseID'>
      <div class='accordion-body p-0'>
      $accordion_body
      </div>
    </div>
  </div>";

    return $accordion_item;
}


function get_random_key()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    $n = 5;
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}


// function get_accordion_item_id($id)
// {
//     $accordion_item_id = 'collapse';
//     switch ($id) {
//         case "0":
//             $accordion_item_id .= 'One';
//         case "1":
//             $accordion_item_id .= 'Two';
//         case "2":
//             $accordion_item_id .= 'Three';
//         case "3":
//             $accordion_item_id .= 'Four';
//         case "4":
//             $accordion_item_id .= 'Five';
//         case "5":
//             $accordion_item_id .= 'Six';
//         case "6":
//             $accordion_item_id .= 'Seven';
//         case "7":
//             $accordion_item_id .= 'Eight';
//         case "8":
//             $accordion_item_id .= 'Nine';
//     }
//     return $accordion_item_id;
// }


function make_comments_number($comments_number, $question_name, $middle_part)
{
    $return_value = '';
    if ($comments_number != 0) {

        $return_value .= "<a class='comments-number' href='/$middle_part/$question_name'>$comments_number</a>";
    } else {
        $return_value .= $comments_number;
    }

    return $return_value;
}


// function make_status($status)
// {
//     switch ($status) {
//         case "graded":
//             return "تم التصحيح";
//         case "not_graded":
//             // return "لم يتم التصحيح بعد";
//             return "⏳";
//         default:
//             return $status;
//     }
// }

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
