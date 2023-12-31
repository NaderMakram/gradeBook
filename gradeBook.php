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
    // $user_id = 60;
    // $meta_key = '_sfwd-quizzes';

    // $quizzesData = get_user_meta($user_id, $meta_key, true);

    // Check if data exists
    // if (empty($quizzesData)) {
    //     return "No data found for user ID $user_id and meta key '$meta_key'";
    // }

    // store the data for each course

    // Loop through the quizzes of each user
    // foreach ($quizzesData as $quiz) {
    //     // Check if the "has_graded" key is set to true
    //     if ($quiz['has_graded']) {
    //         $courseID = $quiz['course'];
    //         $quizID = $quiz['quiz'];
    //         $pointsToAdd = $quiz['scored_points'];
    //         $total_points = $quiz['total_points'];

    //         // Initialize course-specific variables if not already initialized
    //         if (!isset($user_courses_data[$courseID])) {
    //             $user_courses_data[$courseID] = [];
    //         }
    //         // if quiz alreade exist, the points to add is the max between current and previous
    //         if (isset($user_courses_data[$courseID][$quizID])) {
    //             $pointsToAdd = max($user_courses_data[$courseID][$quizID]['scored_points'], $pointsToAdd);
    //         }
    //         $user_courses_data[$courseID][$quizID] = ['scored_points' => $pointsToAdd, 'total_points' => $total_points];
    //     }
    // }


    $user_courses_data = get_user_courses_data($user_id);

    // /////////////////////////////////
    // /////////////////////////////////

    function build_output($user_courses_data)
    {
        // output is for total output
        $output = '<div class="table-container">';
        // $curr_user = wp_get_current_user();
        // $output .= '<H2 class="bold center">مرحبا: ' . $curr_user->display_name . '</H2>';

        // $answer_data_serialized = (get_answer_data_by_id(224));
        // $output .= $answer_data_serialized;
        // $answer_data_unserialized = unserialize($answer_data_serialized);
        // $output .= customPrintR($answer_data_unserialized);
        // $output .= 'copied data after unserialize';
        // $user_answers = ["1", "0"];

        // displayUserAnswers($answer_data_unserialized, $user_answers);



        // $output .= 'user_course_data';
        // $output .= customPrintR(get_user_courses_data(60));

        // global $user_courses_data;
        foreach ($user_courses_data as $courseID => $userCourse) {


            $user_id = get_current_user_id();

            // $output .= 'course data';
            // $output .= customPrintR(get_user_meta($user_id, '_sfwd-quizzes', true));





            // if course is not mark complete, skip
            $post_title = get_the_title($courseID);
            $course_marked_complete = get_post_meta($courseID, 'course_complete')[0];

            $course_data = get_course_data($courseID);
            $output .= '<H2 class="bold center">درجات مادة' . '<br>' . $post_title . '</H2>';
            // $output .= 'عدد كويزات الكورس' . count($course_data);
            // $output .= 'عدد كويزات اليوزر' . count($user_courses_data[$courseID]);
            $attendanceWeight = get_post_meta($courseID, 'attendance_weight')[0] / 100;
            $user_attendance = get_attendance($courseID);
            $course_attendance_min = get_post_meta($courseID, 'attendance_minimum')[0];
            $course_attendance_total = get_post_meta($courseID, 'attendance_total')[0];
            $pass_attendance = $user_attendance >= $course_attendance_min;
            // filter the course data array from inappropriate quizes for the current user output (speaker or writer)



            // filter course data and user course data based on user output is speaker or writer
            $filtered_course_data = array();
            $filtered_user_course_data = array();
            $user_output = get_user_meta(get_current_user_id(), 'user_output')[0];



            foreach ($course_data as $quiz) {
                $quizID = $quiz->id;
                $quiz_for = get_post_meta($quizID, 'quiz_for', true);
                // $output .= 'quiz for ==> ' . $quiz_for . '<br>';
                // $output .= 'user output ==> ' . $user_output . '<br>';
                if ($user_output == $quiz_for) {
                    $filtered_course_data[] = $quiz;
                }
            }
            foreach ($user_courses_data[$courseID] as $quizID => $quiz) {
                $quiz_for = get_post_meta($quizID, 'quiz_for', true);
                // console_log($quiz_for);
                // console_log($user_output);
                if ($user_output == $quiz_for) {
                    $filtered_user_course_data[$quizID] = $quiz;
                }
            }


            // console_log($course_data);
            // console_log($filtered_course_data);
            // console_log($filtered_user_course_data);


            // test arrays output
            // $output .= customPrintR($filtered_course_data);
            // $output .= customPrintR($user_courses_data[$courseID]);

            // require_once '/home/nader/vhosts/newtest/wordpress/wp-content/plugins/sfwd-lms/includes/lib/wp-pro-quiz/lib/model/WpProQuiz_Model_AnswerTypes.php';

            // $output .= 'course complete status: ';
            // $output .= $courseComplete;
            // $output .= '<br>';

            // $output .= 'filtered_course_data ==> ' . count($filtered_course_data) . '<br>';
            // $output .= 'user_courses_data[$courseID] ==> ' . count($filtered_user_course_data) . '<br>';

            // user arrays
            // $output .= '$user_courses_data[$courseID]';
            // $output .= customPrintR($user_courses_data[$courseID]);

            // course arrays
            // $output .= 'course_data';
            // $output .= customPrintR($course_data);
            // $output .= 'filtered course data';
            // $output .= customPrintR($filtered_course_data);


            // quizzes_table is for quizzes html table output, to display the final result first, then the quizzes table 

            $quizzes_table = '';


            $quizzes_table .= "
                <div class='row align-items-center py-2 px-5 row-header'>
                    <div class='col-8 text-end'>
                        الاختبار
                    </div>
                    <div class='col-4'>
                        النسبة
                    </div>
                </div>";

            $quizzes_table .= "<div class='accordion' id='quizzesAccordion-" . $courseID . "'>";
            // add row for each quiz
            $scores_total = 0;
            $from_total = 0;
            $courseTotalPercentage = '-';
            foreach ($filtered_course_data as $key => $quiz) {
                $pattern = '/\bاسئلة\b/ui';
                $quiz_accordion_head = "<div class='container p-0'>";
                $quiz_accordion_body = "<div class='container'>";
                $quiz_id = $quiz->id;
                $questions_table = '';
                $quiz_questions = $filtered_user_course_data[$quiz_id]['quiz_questions'];
                $stat_ref_id = $filtered_user_course_data[$quiz_id]['statistic_ref_id'];
                $chooice_user_answers = getStatisticsByRefId($stat_ref_id);
                // console_log(getStatisticsByRefId($stat_ref_id));

                $user_answer_data = $filtered_user_course_data[$quiz_id];
                $score = $user_answer_data['scored_points'];
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
                };


                foreach ($quiz_questions as $question) {
                    if (in_array('not_graded', $question, true)) {
                        $quizPercentage = '⏳';
                    }
                };

                $quiz_accordion_head .= "
                <div class='row align-items-center fw-bold text-end'>
                <div class='col-8 text-end'>
                $title
                </div>";

                // <div class='col'>
                // $score_to_display
                // </div>
                // <div class='col'>
                // $from
                // </div>
                $quiz_accordion_head .= "
                <div class='col-4 pe-4'>
                $quizPercentage
                </div>
                </div>
                ";
                $quiz_accordion_head .= '</div>';

                $quiz_accordion_body .= "
                <div class='row align-items-center p-2 row-header'>
                    <div class='col'>
                        السؤال
                    </div>
                    <div class='col'>التعليقات
                    </div>
                    <div class='col'>الدرجة
                    </div>
                </div>";


                // add row for each choose question within a quiz
                if ($stat_ref_id) {
                    foreach ($chooice_user_answers as $answer) {
                        // display only questions that have answer_type = single or multiple
                        $answer_type = get_answer_type_by_question_id($answer->question_id);
                        // console_log("answer_type");
                        // console_log($answer_type);
                        if ($answer_type === 'single' || $answer_type === 'multiple') {

                            console_log($answer);
                            $question_id = $answer->question_id;
                            $question_info = getQuestionById($question_id);
                            // $output .= customPrintR($question_info);
                            $answer_json = json_encode($answer);
                            $question_json = json_encode($question_info);

                            $model_answer_serialized = (get_answer_data_by_id($question_id));
                            $model_answer = unserialize($model_answer_serialized);

                            $answersArray = array();

                            foreach ($model_answer as $single_answer) {
                                $theanswer = $single_answer->getAnswer();
                                $correct = $single_answer->isCorrect();
                                $answersArray[$theanswer] = $correct ? '1' : '0';
                            };

                            // $output .= customPrintR($answersArray);




                            // $output .= customPrintR($model_answer);
                            $model_answer_json = json_encode($answersArray);


                            // $question_title = $question_object->post_title;
                            // $question_name = $question_object->post_name;
                            // $comments_number = get_comments_number($question_id);
                            // $status = $question['status'];
                            $points_awarded = $answer->points;
                            $quiz_accordion_body .= "
                        <div class='row align-items-center'>
                        <div class='col open-pop'>
                        <a data-bs-toggle='modal' data-bs-target='#exampleModal' class='text-decoration-underline' onclick='changeContent($answer_json, $question_json, $model_answer_json)'>
                        $question_info->title
                      </a>
                        
                        </div>
                        <div class='col'>" .
                                (($points_awarded > 0) ? '✅' : '❌')
                                . "</div>                      
                        <div class='col'>$points_awarded</div>
                        </div>";
                        }
                    }
                }
                // add row for each essay question within a quiz
                foreach ($quiz_questions as $question) {
                    $pattern = '/\bالسؤال\b/ui';
                    $question_id = $question['post_id'];
                    $question_object = get_post($question_id);
                    // $output .= customPrintR($question_object);
                    $question_title = $question_object->post_title;
                    $title = preg_replace($pattern, '', $question_title);
                    $question_name = $question_object->post_name;
                    $comments_number = get_comments_number($question_id);
                    $status = $question['status'];
                    $points_awarded = $question['points_awarded'];
                    $quiz_accordion_body .= "
                    <div class='row align-items-center'>
                        <div class='col'>
                            <a href='/$question_id'>$title</a>
                        </div>
                        <div class='col'>"
                        . make_comments_number($comments_number, $question_name) . "
                        </div>                      
                        <div class='col'>" .
                        (($status == 'graded') ? $points_awarded : '⏳')
                        . "</div>
                    </div>";
                }
                $quiz_accordion_body .= '</div>';
                $quizzes_table .= make_accordion_item($quiz_accordion_head, $quiz_accordion_body, $courseID);
            };

            // close the accordion div
            $quizzes_table .= '</div>';




            // mark course complete if the user answered all questions
            $courseComplete = (count($filtered_course_data) == count($filtered_user_course_data)) && count($filtered_user_course_data) != 0;


            // $output .= 'filtered course data';
            // $output .= customPrintR($course_data);
            // $output .= 'filtered user course data';
            // $output .= customPrintR($user_courses_data);



            $someQuestionsPending = false;
            foreach ($filtered_user_course_data as $quiz) {
                $questions = $quiz['quiz_questions'];
                foreach ($questions as $question) {

                    if (in_array('not_graded', $question, true)) {
                        // $output .= customPrintR($question);
                        $someQuestionsPending = true;
                    }
                }
            }



            if (!$courseComplete) {
                $scores_total = '-';
                $from_total = '-';
            }
            if ($courseComplete) {
                $courseTotalPercentage = '%' . floor(($scores_total / $from_total) * 100);
                // $courseTotalPercentage = 'courseTotalPercentage scores total' . $scores_total . 'from total' . $from_total;
            }
            // add row for total
            // $quizzes_table .= "
            // <div class='container'>
            //     <div class='row  p-2 mt-5 border rounded row-header'>
            //     <div class='col-4'>
            //         المجموع
            //     </div>
            //     <div class='col'>
            //     $scores_total
            //     </div>
            //     <div class='col'>
            //     $from_total
            //     </div>
            //     <div class='col'>
            //     $courseTotalPercentage
            //     </div>
            //     </div>
            //     </div>
            //     ";
            // $quizzes_table .= '</tbody></table>';





            // output final score
            $output .= '<div class="final-score center">';
            if ($attendanceWeight == 0) {
                $pass_attendance = true;
            }
            if ($pass_attendance && $courseComplete && !$someQuestionsPending && $course_marked_complete) {
                $quizPercentage = ($scores_total / $from_total) * 100;
                // $quizPercentage = 'scores total' . $scores_total . 'from total' . $from_total;
                $attendancePercentage = ($user_attendance / $course_attendance_total) * 100;
                $quizWeight = 1 - $attendanceWeight;
                $percentage = ($attendancePercentage * $attendanceWeight) + ($quizPercentage * $quizWeight);

                // $output .= '<br>quiz percentage' . $quizPercentage;
                // $output .= '<br>attendancePercentage' . $attendancePercentage;
                // $output .= '<br>quizWeight<br>' . $quizWeight;
                // $output .= '<br>attendanceWeight<br>' . $attendanceWeight;
                // $output .= '<br>';

                $output .= 'النسبة المئوية التي حصلت عليها: ' . floor($percentage) . '%';
            } elseif (!$course_marked_complete) {
                $output .= 'لم يتم الانتهاء من حساب الدرجة الكلية ⏳';
            } elseif (!$pass_attendance && !$courseComplete) {
                $output .= 'عذرا، لا يوجد درجة نهائية لعد اتمام كل الاختبارات';
                $output .= '<br>';
                $output .= 'وعدم اتمام الحد الادنى لمرات حضور الزوم';
            } elseif (!$pass_attendance) {
                $output .= 'عذرا، لا يوجد درجة نهائية لعد اتمام الحد الأدنى لمرات حضور الزوم';
            } elseif ($someQuestionsPending) {
                $output .= 'لم يتم الانتهاء من تصحيح كل الاسئلة ⏳';
            } elseif (!$courseComplete) {
                $output .= 'عذرا، لا يوجد درجة نهائية لعد اتمام كل الاختبارات';
            }
            $output .= '</div>';

            // output quizzes table
            $output .= $quizzes_table;


            // attendance table is for user's attendance details
            $attendanceTable = "<div class='container mt-5 border rounded'>";
            $attendanceTable .= "<div class='row row-header'>
                                    <div class='col'>عدد مرات حضور الزوم</div>
                                    <div class='col'>من</div>
                                    <div class='col'>الحد الأدنى للنجاح</div>
                                    <div class='col'>النسبة</div>
                                </div>";


            // user attendance info
            $attendanceTable .= "
                            <div class='row bold'>
                                <div class='col'>$user_attendance</div>
                                <div class='col'>$course_attendance_total</div>
                                <div class='col'>$course_attendance_min</div>
                                <div class='col'>$courseTotalPercentage</div>
                            </div>";
            $attendanceTable .= '</div>';



            if ($course_marked_complete && $attendanceWeight != 0) {
                $output .= '<h6>عدد مرات حضور الزوم ' . $user_attendance . ' من أصل ' . $course_attendance_total . ' ، والحد الأدنى للنجاح هو عدد  ' . $course_attendance_min . ' مرات حضور.</h6> ';
                // $output .= $attendanceTable;
                $output .= '<h6>**تم احتساب نسبة درجات الحضور في هذا الكورس بنسبة  ' . $attendanceWeight * 100 . '% من الدرجة الكلية للكورس.</h6>';
            }
            $output .= '<hr>';
        };

        $output .= '</div>';

        // ////////////////
        // end of quizzes output
        // ////////////////



        // ////////////////
        // start modal
        // ////////////////
        $output .= '
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
              </div>
              <div class="modal-body">
                ...
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">غلق</button>
              </div>
            </div>
          </div>
        </div>';


        // ////////////////
        // end modal
        // ////////////////



        // if suer has old qudio assignments
        $user_old_assignments = get_user_meta(get_current_user_id(), 'old_assignments')[0];
        $audio_output = '';
        if ($user_old_assignments == 'yes') {

            $audio_output .= '<div>';

            $user_audio_assignments = get_audio_assignment_user_course_data();
            // $audio_output .= customPrintR(get_audio_assignment_user_course_data());

            foreach ($user_audio_assignments as $course_id => $user_course) {

                $audio_course_data = get_audio_course_data($course_id);

                if (count($audio_course_data) > 0) {

                    $audio_questions_table = "<div class='container'>";

                    // $audio_questions_table = customPrintR($audio_course_data);
                    $audio_questions_table .= "
                    <div class='row p-2 row-header'>
                    <div class='col'>السؤال</div>
                    <div class='col'>التعليقات</div>
                    <div class='col'>الدرجة</div>
                    </div>";

                    $total_from = count($audio_course_data) * 10;
                    $total_course_points = 0;
                    foreach ($user_course as $question) {
                        // $total_course_points = $total_course_points + $question['scored_points'];
                        // $audio_questions_table .= $question['lesson_title'] . $question['scored_points'] . '<br>';
                        $question_id = $question['post_id'];
                        $question_title = $question['lesson_title'];
                        $scored_points = $question['scored_points'];
                        $file_name = $question['file_name'];
                        $comments_number = get_comments_number($question_id);
                        if (($scored_points > 0 && $scored_points <= 10)) {
                            $points_to_display  = $scored_points;
                            $total_course_points += $scored_points;
                        } elseif ($scored_points == 'pending') {
                            $points_to_display  = '⏳';
                        } else {
                            $total_course_points += $scored_points;
                            $points_to_display  = 0;
                        }



                        $audio_questions_table .= "
                    <div class='row align-items-center'>
                    <div class='col'> $question_title </div>
                    <div class='col'>" . make_comments_number($comments_number, $file_name) . " </div>                      
                    <div class='col'>" . $points_to_display . "</div>
                    </div>";
                    };
                    $audio_questions_result = '<div class="final-score center">';
                    $audio_questions_result .= '<H2 class="bold center">درجات مادة' . '<br>' . get_the_title($course_id) . '</H2>';

                    $audio_questions_result .= 'النسبة المئوية التي حصلت عليها: ' . floor(($total_course_points / $total_from) * 100) . '%';
                    // $audio_questions_result .= customPrintR(get_user_meta(104, 'old_assignments'));




                    $audio_questions_result .= '</div>';



                    $audio_output .= $audio_questions_result . $audio_questions_table;
                    $audio_output .= '</div>';
                }
            }






            $audio_output .= '</div>';
        }
        return  $audio_output . $output;
    }





    return build_output($user_courses_data);
    // return customPrintR($user_courses_data);
}

// Register the shortcode
add_shortcode('gradebook', 'display_grade_book');


// bootstrap css
function enqueue_bootstrap_css()
{
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
}

add_action('wp_enqueue_scripts', 'enqueue_bootstrap_css');


// bootstrap js
function enqueue_bootstrap_js()
{
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '', true);
}

add_action('wp_enqueue_scripts', 'enqueue_bootstrap_js');

// my CSS
function enqueue_custom_css()
{
    // Get the URL of your plugin directory
    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue the custom CSS file
    wp_enqueue_style('custom-style', $plugin_url . 'custom-style.css');
}
// Hook the function to a WordPress action
add_action('wp_enqueue_scripts', 'enqueue_custom_css');

// my js
// Your main plugin PHP file

function enqueue_custom_script()
{
    // Enqueue your script
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-gradebook-script', plugin_dir_url(__FILE__) . 'custom-gradebook-script.js', array('jquery'), '1.0.0', true);
}

// Hook the enqueue function to the appropriate action
add_action('wp_enqueue_scripts', 'enqueue_custom_script');
