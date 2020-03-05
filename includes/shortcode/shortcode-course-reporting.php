<?php 

/* 
===========================================================================
COURSE REPORTING SHORTCODE 
===========================================================================
*/ 

defined( 'ABSPATH' ) || exit;

// shortcode to print course report view
function course_reporting_shortcode_fun() {
	ob_start();
	
	// if user is not logged in return	
	if( ! is_user_logged_in() ) return;

	// current user object
	$user = wp_get_current_user();

	// check if current user is instructor
	if ( !in_array( 'instructor', (array) $user->roles ) ) return;
	

	// if url has parameters then route to single course details & students
	// else load courses
	if ( isset($_GET['course_id'] ) && isset($_GET['view'] ) ) {

		$course_id 	= $_GET['course_id'];
		$view 		= $_GET['view'];

		load_single_course_view($course_id, $view); 		// 	load course details
		load_students_course_details($course_id, $view);	//	load students details in single course

	} else {

		load_courses_by_instructor($user);		// load courses by instructor
	}


	$output = ob_get_contents();   
	ob_end_clean();   

	return $output;


}
add_shortcode( 'course_reporting_shortcode', 'course_reporting_shortcode_fun' );

// load all course for the instructor
function load_courses_by_instructor($user) {

	global $post;

	$current_page_link = get_permalink( $post );

	// get llms instructor class object
	$llms_instructor_courses = new LLMS_Instructor();
	
	// set arguments with author / instructor
	$args = array ('author' => $instructor->ID);

	// get courses by instructor
	$course = $llms_instructor_courses->get_courses($args);
 
?>
	<div class="container_cr">
		<h4>Courses</h4>	
   		<div class="table-container">
   		<table class="table">
   			<thead>
   				<tr>
   					<th>ID</th>
   					<th>Title</th>
   					<th>Instructors</th>
   					<th>Students</th>
   					<th>Average Progress</th>
   					<th>Average Grade</th>
   				</tr>
   			</thead>
   			<tbody>
			<?php 
				foreach ($course as $key => $value) {
					
					$id 				= $value->id;
   					$title 				= $value->title;
					$instructor_name 	= get_user_meta( $value->author, 'first_name', true) .' '. get_user_meta( $value->author, 'last_name', true);
   					$capacity 			= count(llms_get_enrolled_students($value->id, 'enrolled', 50, 0));	 
   					$avg_progress       = get_post_meta($value->id,'_llms_average_progress',true);
   					$avg_grade       	= get_post_meta($value->id,'_llms_average_grade',true);

   					if ($avg_progress == '') $avg_progress = 0;

   					if ($avg_grade == '') $avg_grade = 0;
   					
   					// route link to single course details
					$reporting_var = add_query_arg(array(
						'course_id' => $value->id,
						'view' =>	'report'
					), $current_page_link );
					?>
					<tr>
						<td><?php echo  $id; ?></td>
						<td>
							<a href="<?php echo $reporting_var; ?>"> <?php echo  $title; ?></a>
						</td>
						<td><?php echo  $instructor_name; ?></td>
						<td><?php echo $capacity; ?></td>
						<td class="progress">
						<div class="llms-table-progress">
							<span class="llms-table-progress-text"><?php echo $avg_progress . '%';?></span>
							<div class="llms-table-progress-inner" style="<?php echo 'width:'. $avg_progress.'%'; ?>"></div>
						</div>
						</td>
						<td><?php echo $avg_grade . '%'; ?></td>
					</tr>
   					<?php
   					}
			?>
   			</tbody>
   			<tfoot>
   				<!-- export button & pagination -->
   			</tfoot>
   		</table>
   	</div>
	</div>

<?php
}

// load dynamic data of courses
function load_single_course_view( $id, $view ) {
	
	$course_data = new LLMS_Course_Data($id);	// course data class from lifterlms 
	$course_data->set_period('today');			// set period/datetime
	
	$new_enrollments	= $course_data->get_enrollments('current');			// new enrollments
	$unenrollments 		= $course_data->get_unenrollments('current');		// currently unenrollments
	$course_completion 	= $course_data->get_completions('current');			// course completion
	$lesson_completion 	= $course_data->get_lesson_completions('current');	// lesson completion
	$new_orders 		= $course_data->get_orders('current');				// orders
	$sales 				= $course_data->get_revenue('current');				// sales / revenue

	$no_of_enrolled_students = count(llms_get_enrolled_students($id, 'enrolled', 50, 0));	// no of students enrolled
	$current_avg_progress	 = get_post_meta($id, '_llms_average_progress' , true);			// current average progress
	$current_avg_grade 		 = get_post_meta($id, '_llms_average_grade', true);				// current average grade

	$certificate_earned = $course_data->get_engagements('certificate', 'current');		// certificate earned
	$achievement_earned = $course_data->get_engagements('achievement', 'current');		// achievement earned
	$emails_sent 		= $course_data->get_engagements('email', 'current');			// emails sent
?>
	<div class="container_cr">
		<h4>Course Overview</h4>
		<section class="llms-reporting-tab-main llms-reporting-widgets">
			
			<div class="d-1of3">
			<div class="llms-reporting-widget llms-reporting-course-total-enrollments" id="llms-reporting-course-total-enrollments">
			<i class="fa fa-users" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $no_of_enrolled_students; ?></strong>
			</div>
			<small>Currently enrolled students</small>
			</div>
			</div>

			<div class="d-1of3">
			<div class="llms-reporting-widget llms-reporting-course-avg-progress" id="llms-reporting-course-avg-progress">
			<i class="fa fa-line-chart" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong> <?php echo $current_avg_progress; ?> <sup>%</sup></strong>
			</div>
			<small>Current average progress</small>
			</div>
			</div>

			<div class="d-1of3">
			<div class="llms-reporting-widget llms-reporting-course-avg-grade" id="llms-reporting-course-avg-grade">
			<i class="fa fa-graduation-cap" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong> <?php echo $current_avg_grade;?> <sup>%</sup></strong>
			</div>
			<small>Current average grade</small>
			</div>
			</div>

			<div class="d-1of2">
			<div class="llms-reporting-widget llms-reporting-course-orders" id="llms-reporting-course-orders">
			<i class="fa fa-shopping-cart" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $new_orders; ?></strong>
			</div>
			<small>New orders today</small>
			</div>
			</div>

			<div class="d-1of2">
			<div class="llms-reporting-widget llms-reporting-course-revenue" id="llms-reporting-course-revenue">
			<i class="fa fa-money" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><span class="lifterlms-price"><span class="llms-price-currency-symbol">₨</span><?php echo $sales; ?></span></strong>
			</div>
			<small>Total sales today</small>
			</div>
			</div>

			<div class="d-1of2">
			<div class="llms-reporting-widget llms-reporting-course-enrollments" id="llms-reporting-course-enrollments">
			<i class="fa fa-smile-o" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $new_enrollments; ?></strong>
			</div>
			<small>New enrollments today</small>
			</div>
			</div>

			<div class="d-1of2">
			<div class="llms-reporting-widget llms-reporting-course-unenrollments" id="llms-reporting-course-unenrollments">
			<i class="fa fa-frown-o" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $unenrollments; ?></strong>
			</div>
			<small>Unenrollments today</small>
			</div>
			</div>

			<div class="d-1of2">
			<div class="llms-reporting-widget llms-reporting-course-lessons-completed" id="llms-reporting-course-lessons-completed">
			<i class="fa fa-check-circle" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $lesson_completion; ?></strong>
			</div>
			<small>Lessons completed today</small>
			</div>
			</div>

			<div class="d-1of2">
			<div class="llms-reporting-widget llms-reporting-course-course-completions" id="llms-reporting-course-course-completions">
			<i class="fa fa-flag-checkered" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $course_completion; ?></strong>
			</div>
			<small>Course completions today</small>
			</div>
			</div>

			<div class="d-1of3">
			<div class="llms-reporting-widget llms-reporting-course-achievements" id="llms-reporting-course-achievements">
			<i class="fa fa-trophy" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $achievement_earned;?></strong>
			</div>
			<small>Achievements earned today</small>
			</div>
			</div>

			<div class="d-1of3">
			<div class="llms-reporting-widget llms-reporting-course-certificates" id="llms-reporting-course-certificates">
			<i class="fa fa-certificate" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $certificate_earned ;?></strong>
			</div>
			<small>Certificates earned today</small>
			</div>
			</div>

			<div class="d-1of3">
			<div class="llms-reporting-widget llms-reporting-course-email" id="llms-reporting-course-email">
			<i class="fa fa-envelope" aria-hidden="true"></i>
			<div class="llms-reporting-widget-data">
			<strong><?php echo $emails_sent; ?></strong>
			</div>
			<small>Emails sent today</small>
			</div>
			</div>

		</section>
	</div>


<?php 
}

// load dynamic data of students registered in particular course
function load_students_course_details( $id, $view ) {
?>
<div class="container_cr_student">
<div class="llms-table-wrap">
	<h4>Students</h3>											
	<table class="llms-table llms-gb-table llms-gb-table-course-students zebra" id="llms-gb-table-course-students">
	
	<thead>
		<tr>
		<th class="id">ID</th>
		<th class="id">Name</th>
		<th class="id">Status</th>
		<th class="id">Enrollment Updated</th>
		<th class="id">Completed</th>
		<th class="id">Progress</th>
		<th class="id">Grade</th>
		<th class="id">Last Lesson</th>
		</tr>
	</thead>
	<?php 
		// $enrolled_students = new LLMS_Analytics();							// analytics class from lifterlms
		// $enrolled_students = $enrolled_students->get_enrolled_users($id);	// get enrolled students
		$llms_course = new LLMS_Course($id);								// course class from lifterlms
		
		$students = $llms_course->get_students(array(''));

		// check if enrolled_students is not empty
		if ( ! empty($students) ) {
		for ( $i = 0; $i < count($students) ; $i++) {

			$user = get_userdata( $students[$i] );
			$full_name = $user->first_name . ' '. $user->last_name; 
			
			$llms_student = new LLMS_Student($students[$i]);									// student class from lifterlms
			$grade = $llms_student->get_grade( $id, true );										// student grade

			$last_lesson = $llms_student->get_last_completed_lesson($id);
			
			if (empty($last_lesson)) {
				$last_lesson = '-';						
			} else {
				$last_lesson = get_the_title( $last_lesson );
			}


			$status = llms_get_enrollment_status_name($llms_student->get_enrollment_status($id));
			$updated = $llms_student->get_enrollment_date($id, 'updated', null);		// course updated date
			$progress = $llms_course->get_percent_complete($students[$i]);				// individual student progress

			// check if course is completed or not
			if (  $llms_student->is_complete( $id, 'course' ) ) 
				$is_course_completed = 'Yes';
			else 
				$is_course_completed = '-';

			?>
			<tbody>
			<tr class="llms-table-tr">
			<td class="id"><a href="#"><?php echo $students[$i]; ?></a></td>
			<td class="name"><a href="#"><?php echo $full_name; ?></a></td>
			<td class="status"><?php echo $status; ?></td>
			<td class="enrolled"><?php echo $updated; ?></td>
			<td class="completed"><?php echo $is_course_completed;?></td>
			<td class="progress">
				<div class="llms-table-progress">
					<span class="llms-table-progress-text"><?php echo $progress; ?></span>
					<div class="llms-table-progress-inner" style="<?php echo 'width:'. $progress . '%';?>"></div>
				</div>
			</td>
			<td class="grade"><?php echo $grade; ?></td>
			<td class="last_lesson"><?php echo $last_lesson;  ?></td>
			</tr>
			</tbody>

			<?php
		}
	} else {
		?>
		<tr><td colspan="8" style="text-align:center"> No results were found.</td></tr>
	<?php
	}
	?>


	</table>
</div>

</div>

<?php	
}

