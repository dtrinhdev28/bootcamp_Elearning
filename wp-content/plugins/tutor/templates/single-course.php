<?php
/**
 * Template for displaying single course
 *
 * @package Tutor\Templates
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 1.0.0
 */

$course_id     = get_the_ID();
$course_rating = tutor_utils()->get_course_rating( $course_id );
$is_enrolled   = tutor_utils()->is_enrolled( $course_id, get_current_user_id() );

// Prepare the nav items.
$course_nav_item = apply_filters( 'tutor_course/single/nav_items', tutor_utils()->course_nav_items(), $course_id );
$is_public       = \TUTOR\Course_List::is_public( $course_id );
$is_mobile       = wp_is_mobile();

$enrollment_box_position = tutor_utils()->get_option( 'enrollment_box_position_in_mobile', 'bottom' );
if ( '-1' === $enrollment_box_position ) {
	$enrollment_box_position = 'bottom';
}
$student_must_login_to_view_course = tutor_utils()->get_option( 'student_must_login_to_view_course' );

tutor_utils()->tutor_custom_header();

if ( ! is_user_logged_in() && ! $is_public && $student_must_login_to_view_course ) {
	tutor_load_template( 'login' );
	tutor_utils()->tutor_custom_footer();
	return;
}
?>
<!-- course -->
<?php do_action( 'tutor_course/single/before/wrap' ); ?>
<div <?php tutor_post_class( 'tutor-full-width-course-top tutor-course-top-info tutor-page-wrap tutor-wrap-parent' ); ?>>
	<div class="tutor-course-details-page tutor-container">
		<?php ( isset( $is_enrolled ) && $is_enrolled ) ? tutor_course_enrolled_lead_info() : tutor_course_lead_info(); ?>
		<div class="tutor-row tutor-gx-xl-5">
			<main class="tutor-col-xl-8">
				<?php do_action( 'tutor_course/single/before/inner-wrap' ); ?>

				<?php if ( $is_mobile && 'top' === $enrollment_box_position ) : ?>
					<div class="tutor-mt-32">
						<?php tutor_load_template( 'single.course.course-entry-box' ); ?>
					</div>
				<?php endif; ?>

				<div class="tutor-course-details-tab">
					<!-- <?php if ( is_array( $course_nav_item ) && count( $course_nav_item ) > 1 ) : ?>
						<div class="tutor-is-sticky">
							<?php // tutor_load_template( 'single.course.enrolled.nav', array( 'course_nav_item' => $course_nav_item ) ); ?>
						</div>
					<?php endif; ?> -->
						<?php foreach ( $course_nav_item as $key => $subpage ) : ?>
							<div id="tutor-course-details-tab-<?php echo esc_attr( $key ); ?>" class="tutor-tab-item<?php echo 'info' == $key ? ' is-active' : ''; ?>">
								<?php
									do_action( 'tutor_course/single/tab/' . $key . '/before' );

									$method = $subpage['method'];
								if ( is_string( $method ) ) {
									$method();
								} else {
									$_object = $method[0];
									$_method = $method[1];
									$_object->$_method( get_the_ID() );
								}

									do_action( 'tutor_course/single/tab/' . $key . '/after' );
								?>
							</div>
						<?php endforeach; ?>
				</div>
				<?php do_action( 'tutor_course/single/after/inner-wrap' ); ?>
			</main>

			<aside class="tutor-col-xl-4">

			<!-- course -->
			<?php tutor_utils()->has_video_in_single() ? tutor_course_video() : get_tutor_course_thumbnail(); ?>

				<?php $sidebar_attr = apply_filters( 'tutor_course_details_sidebar_attr', '' ); ?>
				<div class="tutor-single-course-sidebar tutor-mt-40 tutor-mt-xl-0" <?php echo esc_attr( $sidebar_attr ); ?> >
					<?php do_action( 'tutor_course/single/before/sidebar' ); ?>

					<?php if ( ( $is_mobile && 'bottom' === $enrollment_box_position ) || ! $is_mobile ) : ?>
						<?php tutor_load_template( 'single.course.course-entry-box' ); ?>
					<?php endif ?>

					<div class="tutor-single-course-sidebar-more tutor-mt-24">
						<?php tutor_course_instructors_html(); ?>
						<?php tutor_course_requirements_html(); ?>
						<?php tutor_course_tags_html(); ?>
						<?php tutor_course_target_audience_html(); ?>
					</div>

					<div class="tutor-course-details-actions tutor-mt-12 tutor-mt-sm-0">
						<a href="#" class="tutor-btn tutor-btn-ghost tutor-course-wishlist-btn tutor-mr-16" data-course-id="<?php echo get_the_ID(); ?>">
							<i class="<?php echo $is_wish_listed ? 'tutor-icon-bookmark-bold' : 'tutor-icon-bookmark-line'; ?> tutor-mr-8"></i> <?php esc_html_e( 'Wishlist', 'tutor' ); ?>
						</a>

						<?php
						if ( tutor_utils()->get_option( 'enable_course_share', false, true, true ) ) {
							tutor_load_template_from_custom_path( tutor()->path . '/views/course-share.php', array(), false );
						}
						?>
					</div>

					<?php do_action( 'tutor_course/single/after/sidebar' ); ?>
				</div>
			</aside>
		</div>
	</div>
</div>

<?php do_action( 'tutor_course/single/after/wrap' ); ?>

<?php
tutor_utils()->tutor_custom_footer();
