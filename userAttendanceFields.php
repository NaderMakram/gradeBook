<?php

function add_custom_user_fields($user)
{
    if (user_has_role($user, 'subscriber')) {
        display_course_attendance_fields($user);
    }
}

function display_course_attendance_fields($user)
{
    $courses = get_sfwd_courses();

    foreach ($courses as $course) {
        $course_id = $course->ID;
        $course_title = $course->post_title;
        $field_key = 'course_attendance_' . $course_id;
        $attendance_value = get_user_meta($user->ID, $field_key, true);
        $attendance_value = ($attendance_value !== '') ? $attendance_value : 0;

?>
        <h3>Course Attendance for <?php echo $course_title; ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="<?php echo $field_key; ?>">Attendance</label></th>
                <td>
                    <input type="number" name="<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" value="<?php echo esc_attr($attendance_value); ?>" min="0" max="10" step="1" class="regular-text" />
                </td>
            </tr>
        </table>
<?php
    }
}

function save_custom_user_fields($user_id)
{
    if (current_user_can('edit_user', $user_id) && user_has_role(get_userdata($user_id), 'subscriber')) {
        $courses = get_sfwd_courses();

        foreach ($courses as $course) {
            $course_id = $course->ID;
            $field_key = 'course_attendance_' . $course_id;

            if (isset($_POST[$field_key])) {
                $attendance_value = sanitize_text_field($_POST[$field_key]);
                update_user_meta($user_id, $field_key, $attendance_value);
            }
        }
    }
}

function user_has_role($user, $role)
{
    return in_array($role, $user->roles);
}

function get_sfwd_courses()
{
    return get_posts(array(
        'post_type' => 'sfwd-courses',
        'posts_per_page' => -1,
    ));
}
