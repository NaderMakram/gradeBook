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
function make_accordion_item($accordion_head, $accordion_body, $key)
{
    // $accordion_item_id = get_accordion_item_id($id);
    $accordion_item_id = get_accordion_item_id($key);
    $accordion_item = "
    <div class='accordion-item'>
    <h2 class='accordion-header'>
      <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse$key' aria-expanded='true' aria-controls='collapse$key'>
        $accordion_head
      </button>
    </h2>
    <div id='collapse$key' class='accordion-collapse collapse' data-bs-parent='#quizzesAccordion'>
      <div class='accordion-body p-0'>
      $accordion_body
      </div>
    </div>
  </div>";

    return $accordion_item;
}


function get_accordion_item_id($id)
{
    $accordion_item_id = 'collapse';
    switch ($id) {
        case "0":
            $accordion_item_id .= 'One';
        case "1":
            $accordion_item_id .= 'Two';
        case "2":
            $accordion_item_id .= 'Three';
        case "3":
            $accordion_item_id .= 'Four';
        case "4":
            $accordion_item_id .= 'Five';
        case "5":
            $accordion_item_id .= 'Six';
        case "6":
            $accordion_item_id .= 'Seven';
        case "7":
            $accordion_item_id .= 'Eight';
        case "8":
            $accordion_item_id .= 'Nine';
    }
    return $accordion_item_id;
}


function make_comments_number($comments_number, $question_name)
{
    $return_value = '';
    if ($comments_number != 0) {
        $return_value .= "<a class='comments-number' href='$question_name'>$comments_number</a>";
    } else {
        $return_value .= $comments_number;
    }

    return $return_value;
}


function make_status($status)
{
    switch ($status) {
        case "graded":
            return "تم التصحيح";
        case "not_graded":
            return "لم يتم التصحيح بعد";
        default:
            return $status;
    }
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
