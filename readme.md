for this plugin to work, you need to create three Number fields, and one true/false field with the Advanced Custom Field plugin.

1- Number field for attendance minimum, with the field name 'attendance_minimum'
this is used to specify the minimum number of attendance to pass the course.

2- Number field for attendance total, with the field name 'attendance_total'

3- Number field for attendance weight, with the field name 'attendance_weight'
this is used to specify the weight of attendance points in the course final grade.

4- true/false field for marking a course complete, with the name 'course_complete'
this is used to mark a course complete for all users, and display the final grades for all of them at once.

all these fields' location rule is in post type equal to 'course'

to use the plugin use the shortcode [gradebook]
