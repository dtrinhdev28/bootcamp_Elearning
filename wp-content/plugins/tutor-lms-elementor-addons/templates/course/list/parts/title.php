<!-- custom price -->
<h3 class="tutor-course-name tutor-fs-5 tutor-fw-medium tutor-mb-12" title="<?php the_title(); ?>">
    <a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
</h3>

<p>
<?php
echo '<i class="fa fa-users"></i>'. '<span class="__enroll-user">' .tutor_utils()->count_enrolled_users_by_course(). '<span>';
?>
</p>