<?php if ( 'yes' === $settings['course_list_difficulty_settings'] && get_tutor_course_level() ) : ?>
	<span class="tutor-course-difficulty-level">
        <?php // echo get_tutor_course_level(); ?>
        <img class="level_course_icon" src="https://fullstack.edu.vn/static/media/crown_icon.3e4800f7485935ab6ea312a7080a85fe.svg" alt="">
    </span>
<?php endif; ?>