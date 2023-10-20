for this plugin to work, you need to create some fields using ACF Advanced Custom Field plugin.

1- a Number field for attendance minimum, with the field name 'attendance_minimum'
this is used to specify the minimum number of attendance to pass the course.
this field location rule is at post type = Course

2- Number field for attendance total, with the field name 'attendance_total'
this is used to specify the total number of attendance in a course, it should have a min value of 1 to prevent dividing by 0.
this field location rule is at post type = Course

3- Number field for attendance weight, with the field name 'attendance_weight'
this is used to specify the weight of attendance points in the course final grade.
this field location rule is at post type = Course

4- true/false field for marking a course complete, with the name 'course_complete'
this is used to mark a course complete for all users, and display the final grades for all of them at once.
this field location rule is at post type = Course

5- radio button field to specify the audiance of the quiz, with the name 'quiz_for'
this field is used to specify weather this quiz is for speaker or writer, it needs to have choices of (writer, speaker), with the default set to writer.
this field location rule is at post type = Quiz

6- radio button field to specify the output of the user, with the name 'user_output'
this field is used to specify weather this user is speaker or writer, it needs to have choices of (writer, speaker), with the default set to writer.
this field location rule is at user role = all

7- radio button field for users who answered audio assignments, with the name 'old_assignments'
this field is used to know weather this user have old audio assignmets, it needs to have choices of (yes, no), with the default set to no.
this field location rule is at user role = all

to use the plugin use the shortcode [gradebook]
