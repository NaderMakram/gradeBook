<?php
/*
 * Plugin Name: Grade Book
 * Description: Calculate and display course grades.
 * Version: 1.0
 * Author: Nader Makram
 */

include_once(plugin_dir_path(__FILE__) . 'userAttendanceFields.php');
include_once(plugin_dir_path(__FILE__) . 'helperFunctions.php');

// display attendance fields for each user, for each course
add_action('show_user_profile', 'add_custom_user_fields');
add_action('edit_user_profile', 'add_custom_user_fields');
// add_action('personal_options_update', 'save_custom_user_fields');
add_action('edit_user_profile_update', 'save_custom_user_fields');





// Define a shortcode to display the grade book
function display_grade_book()
{
    // get and filter quiz data for current user
    $user_id = get_current_user_id();
    // $user_id = 56;
    $meta_key = '_sfwd-quizzes';

    $quizzesData = get_user_meta($user_id, $meta_key, true);

    // Check if data exists
    if (empty($quizzesData)) {
        return "No data found for user ID $user_id and meta key '$meta_key'";
    }

    // store the data for each course
    $courseTotals = [];

    // Loop through the quizzes of each user
    foreach ($quizzesData as $quiz) {
        // Check if the "has_graded" key is set to true
        if ($quiz['has_graded']) {
            $courseID = $quiz['course'];
            $quizID = $quiz['quiz'];
            $pointsToAdd = $quiz['points'];
            $total_points = $quiz['total_points'];

            // Initialize course-specific variables if not already initialized
            if (!isset($courseTotals[$courseID])) {
                $courseTotals[$courseID] = [];
            }
            // if quiz alreade exist, the points to add is the max between current and previous
            if (isset($courseTotals[$courseID][$quizID])) {
                $pointsToAdd = max($courseTotals[$courseID][$quizID]['points'], $pointsToAdd);
            }
            $courseTotals[$courseID][$quizID] = ['points' => $pointsToAdd, 'total_points' => $total_points];
        }
    }



    // /////////////////////////////////
    // /////////////////////////////////

    function build_output($courseTotals)
    {
        // output is for total output
        $output = '<div class="table-container">';
        $output = customPrintR(get_user_meta(85, '_sfwd-quizzes', true));
        // global $courseTotals;
        foreach ($courseTotals as $courseID => $userCourse) {
            // if course is not mark complete, skip
            $post_title = get_the_title($courseID);
            $course_marked_complete = get_post_meta($courseID, 'course_complete')[0];
            if (!$course_marked_complete) {
                $output .= '**لم يتم الانتهاء من حساب درجات مادة ' . $post_title . '<br>';
                continue;
            }
            $courseData = get_course_data($courseID);
            $output .= '<H2 class="bold center">درجات مادة ' . $post_title . '</H2>';
            // $output .= 'عدد كويزات الكورس' . count($courseData);
            // $output .= 'عدد كويزات اليوزر' . count($courseTotals[$courseID]);
            $attendanceWeight = get_post_meta($courseID, 'attendance_weight')[0] / 100;
            $usr_attendance = get_attendance($courseID);
            $course_attendance_min = get_post_meta($courseID, 'attendance_minimum')[0];
            $course_attendance_total = get_post_meta($courseID, 'attendance_total')[0];
            $passAttendance = $usr_attendance >= $course_attendance_min;
            // filter the course data array from inappropriate quizes for the current user output (speaker or writer)



            // filter course data and user course data based on user output is speaker or writer
            $filteredCourseData = array();
            $filteredUserCourseData = array();
            $user_output = get_user_meta(get_current_user_id(), 'user_output')[0];

            foreach ($courseData as $quiz) {
                $id = $quiz->id;
                $quiz_for = get_post_meta($id, 'quiz_for', true);
                if ($user_output == $quiz_for) {
                    $filteredCourseData[] = $quiz;
                }
            }
            foreach ($courseTotals[$courseID] as $quizID => $quiz) {
                $quiz_for = get_post_meta($quizID, 'quiz_for', true);
                if ($user_output == $quiz_for) {
                    $filteredUserCourseData[$quizID] = $quiz;
                }
            }






            // test arrays output

            // $output .= 'course complete status: ';
            // $output .= $courseComplete;
            // $output .= '<br>';

            // $output .= 'filteredCourseData ==> ' . count($filteredCourseData) . '<br>';
            // $output .= 'courseTotals[$courseID] ==> ' . count($filteredUserCourseData) . '<br>';

            // // user arrays
            // $output .= '$courseTotals[$courseID]';
            // $output .= customPrintR($courseTotals[$courseID]);
            // $output .= 'filteredUserCourseData';
            // $output .= customPrintR($filteredUserCourseData);
            // // course arrays
            // $output .= 'courseData';
            // $output .= customPrintR($courseData);
            // $output .= 'filtered course data';
            // $output .= customPrintR($filteredCourseData);


            // quizzesTable is for quizzes html table output, to display the final result first, then the quizzes table 
            $quizzesTable = '<table class="table"><tbody>';
            $quizzesTable .= '<tr class="tr">
                                <th>الاختبار</th>
                                <th>الدرجة</th>
                                <th>من</th>
                                <th>النسبة</th>
                            </tr>';
            // add row for each quiz
            $scores_total = 0;
            $from_total = 0;
            $courseTotalPercentage = '-';
            $pattern = '/\bاسئلة\b/ui';
            foreach ($filteredCourseData as $quiz) {
                $id = $quiz->id;

                $user_answer_data = $filteredUserCourseData[$id];
                $score = $user_answer_data['points'];
                $from = $user_answer_data['total_points'];
                // fix old courses that have question points from 1 instead of 10
                if ($from < 10) {
                    $from = $from * 10;
                }
                $title = preg_replace($pattern, '', $quiz->post_title);
                $scores_total = $scores_total + $score;
                $from_total = $from_total + $from;
                if (isset($score)) {
                    $score_to_display = $score;
                    $quizPercentage = '%' . floor(($score_to_display / $from) * 100);
                } else {
                    $score_to_display = '-';
                    $quizPercentage = '-';
                    $from = '-';
                }
                $quizzesTable .= "
                <tr class='tr'>
                <td class='td'>
                $title
                </td>
                <td class='td'>
                $score_to_display
                </td>
                <td class='td'>
                $from
                </td>
                <td class='td'>
                $quizPercentage
                </td>
                </tr>";
            };




            // mark course complete if the user answered all questions
            $courseComplete = (count($filteredCourseData) == count($filteredUserCourseData)) && $from_total;





            if (!$courseComplete) {
                $scores_total = '-';
                $from_total = '-';
            }
            if ($courseComplete) {
                $courseTotalPercentage = '%' . floor(($scores_total / $from_total) * 100);
            }
            // add row for total
            $quizzesTable .= "
            <tr class='tr bold'>
                <td class='td'>
                مجموع درجات المادة
                </td>
                <td class='td'>
                $scores_total
                </td>
                <td class='td'>
                $from_total
                </td>
                <td class='td'>
                $courseTotalPercentage
                </td>
            </tr>";
            $quizzesTable .= '</tbody></table>';

            // output final score
            $output .= '<div class="final-score center">';
            if ($attendanceWeight == 0) {
                $passAttendance = true;
            }
            if ($passAttendance && $courseComplete) {
                $quizPercentage = ($scores_total / $from_total) * 100;
                $attendancePercentage = ($usr_attendance / $course_attendance_total) * 100;
                $quizWeight = 1 - $attendanceWeight;
                $percentage = ($attendancePercentage * $attendanceWeight) + ($quizPercentage * $quizWeight);

                // $output .= '<br>quiz percentage' . $quizPercentage;
                // $output .= '<br>attendancePercentage' . $attendancePercentage;
                // $output .= '<br>quizWeight<br>' . $quizWeight;
                // $output .= '<br>attendanceWeight<br>' . $attendanceWeight;
                // $output .= '<br>';

                $output .= 'النسبة المئوية التي حصلت عليها: ' . floor($percentage) . '%';
            } elseif (!$passAttendance && !$courseComplete) {
                $output .= 'عذرا، لا يوجد درجة نهائية لعد اتمام كل الاختبارات وعدم اتمام الحد الادنى لمرات حضور الزوم';
            } elseif (!$passAttendance) {
                $output .= 'عذرا، لا يوجد درجة نهائية لعد اتمام الحد الأدنى لمرات حضور الزوم';
            } elseif (!$courseComplete) {
                $output .= 'عذرا، لا يوجد درجة نهائية لعد اتمام كل الاختبارات';
            }
            $output .= '</div>';

            // output quizzes table
            $output .= $quizzesTable;


            // attendance table is for user's attendance details
            $attendanceTable = '<table class="table"><tbody>';
            $attendanceTable .= '<tr class="tr">
                                <th>عدد مرات حضور الزوم</th>
                                <th>من</th>
                                <th>الحد الأدنى للنجاح</th>
                                <th>النسبة</th>
                            </tr>';



            $attendanceTable .= "
                            <tr class='tr bold'>
                            <td class='td'>
                            $usr_attendance
                            </td>
                            <td class='td'>
                            $course_attendance_total
                            </td>
                            <td class='td'>
                            $course_attendance_min
                            </td>
                            <td class='td'>
                            $courseTotalPercentage
                            </td>
            </tr>";
            $attendanceTable .= '</tbody></table>';

            // output user attendance data
            if ($attendanceWeight != 0) {
                $output .= $attendanceTable;
            }


            // $output .= '<h6>عدد مرات حضور الزوم ' . $usr_attendance . ' من أصل ' . $course_attendance_total . ' ، والحد الأدنى للنجاح هو عدد  ' . $course_attendance_min . ' مرات حضور.</h6> ';
            if ($passAttendance && $courseComplete && $attendanceWeight != 0) {
                $output .= '<h6>**تم احتساب نسبة درجات الحضور في هذا الكورس بنسبة  ' . $attendanceWeight * 100 . '% من الدرجة الكلية للكورس.</h6>';
            }
            $output .= '<p class="pt3 center">**هذه هي الدرجات النهائية فقط، وللتفاصيل عن كل سؤال برجاء الرجوع للبروفايل</p>';
            $output .= '<hr>';
        };

        $output .= '</div>';
        return $output;
    }





    return build_output($courseTotals);
    // return customPrintR($courseTotals);
}

// Register the shortcode
add_shortcode('gradebook', 'display_grade_book');
// Define a function to enqueue the CSS
function enqueue_custom_css()
{
    // Get the URL of your plugin directory
    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue the custom CSS file
    wp_enqueue_style('custom-style', $plugin_url . 'custom-style.css');
}

// Hook the function to a WordPress action
add_action('wp_enqueue_scripts', 'enqueue_custom_css');
