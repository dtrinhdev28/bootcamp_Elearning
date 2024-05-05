<?php
/**
 * Header template.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>">

<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php do_action('flatsome_after_body_open'); ?>
	<?php wp_body_open(); ?>

	<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e('Skip to content', 'flatsome'); ?></a>

	<div id="wrapper">

		<?php do_action('flatsome_before_header'); ?>

		<header id="header" class="header <?php flatsome_header_classes(); ?>">
			<div class="header-wrapper">
				<?php get_template_part('template-parts/header/header', 'wrapper'); ?>
			</div>
		</header>

		<?php do_action('flatsome_after_header'); ?>

		<!-- bootstrap & swiper -->
		<link rel="stylesheet"
			href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap-grid.min.css"
			integrity="sha512-zDDxSlYrbKTTfup/YyljmstpX+1jwjeg15AKS/fl26gRxfpD+HMr6dfuJQzCcFtoIEjf93SuCffose5gDQOZtg=="
			crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
		<!-- bootstrap & swiper -->
		<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"
			integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg=="
			crossorigin="anonymous" referrerpolicy="no-referrer"></script>

		<!-- slide start  -->
		<div class="container">
			<div class="row __banner_wrap">
				<div class="col-xxl-6 col-lg-6 col-xl-6 col-sm-12 col-md-12">
					<!-- <div class="__text_content_wrap d-flex justify-content-center h-100 flex-column ">
										<?php if (get_sub_field('slide_sub_title')): ?>
											<p class="__text_sub_content"><?php echo get_sub_field('slide_sub_title') ?>
											</p>
										<?php endif; ?>

										<?php if (get_sub_field('slide_title')): ?>
											<h2 class="ls-2 __text_title_content">
												<?php echo get_sub_field('slide_title') ?>
											</h2>
										<?php endif; ?>

										<?php if (get_sub_field('slide_description')): ?>
											<p class="__text_description_content">
												<?php echo get_sub_field('slide_description') ?>
											</p>
										<?php endif; ?>

										<?php if (get_sub_field('slide_button_title')): ?>
											<div class="__text_button_content wp-block-button hover-white">
												<a style="border-radius: 5px; color: #353758;"
													class="wp-block-button__link has-white-background-color has-text-color has-background wp-element-button"
													href="<?php echo (get_sub_field('slide_button_link')) ? get_sub_field('slide_button_link') : '' ?>"><?php echo (get_sub_field('slide_button_title')) ? get_sub_field('slide_button_title') : '' ?>
													&#8594; </a>
											</div>
										<?php endif; ?>
									</div> -->
				</div>

				<div class="col-xxl-6 col-lg-6 col-xl-6 col-sm-12 col-md-12">
					<?php if (have_rows('content_slide_images')):
						?>
						<div class="swiper mySwiper">
							<div class="swiper-wrapper">
								<?php while (have_rows('content_slide_images')):
									the_row() ?>
									<!-- Slides -->
									<div class="swiper-slide">
										<img class="w-100 __custom_image"
											src="<?php echo get_sub_field('image_slide_custom') ?>"
											alt="<?php echo get_sub_field('image_slide_custom') ?>" />
									</div>
								<?php endwhile; ?>
							</div>
							<div class="swiper-pagination"></div>
						</div>
					<?php endif; ?>

				</div>
			</div>
		</div>

		<script>
			var swiper = new Swiper(".mySwiper", {
				spaceBetween: 0,
				centeredSlides: true,
				autoplay: {
					delay: 2500,
					disableOnInteraction: false,
				},
				pagination: {
					el: ".swiper-pagination",
					clickable: true,
				},
			});
		</script>
		<!-- slide end  -->

		<main id="main" class="<?php flatsome_main_classes(); ?>">