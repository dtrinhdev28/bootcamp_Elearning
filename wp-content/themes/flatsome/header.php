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

		<!-- swiper -->
		<link rel="stylesheet" href="/wp-content/themes/flatsome-child/assets/css/swiper-bundle.min.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"
			integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg=="
			crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script src="/wp-content/themes/flatsome-child/assets/js/swiper-bundle.min.js"></script>


		<!-- slide start  -->
		<div class="container">
			<div class="d-flex px-5 __banner_wrap">
				<div class="col-6 col-lg-6 col-xl-6 col-sm-12 col-md-12">
					<!-- <div class="__text_content_wrap d-flex justify-content-center h-100 flex-column "> -->
					<?php if (have_rows('content_slide_images')):
						?>
						<?php if (get_field('subtitle')): ?>
							<p class="__text_sub_content"><?php echo get_field('subtitle') ?>
							</p>
						<?php endif; ?>

						<?php if (get_field('title')): ?>
							<h2 class="ls-2 __text_title_content">
								<?php echo get_field('title') ?>
							</h2>
						<?php endif; ?>

						<?php if (get_field('descripption')): ?>
							<p class="__text_description_content">
								<?php echo get_field('descripption') ?>
							</p>
						<?php endif; ?>

						<div class="__text_button_content wp-block-button hover-white">
							<a style="border-radius: 5px; color: #353758;"
								class="wp-block-button__link has-white-background-color has-text-color has-background wp-element-button"
								href="<?php echo (get_field('button_link')) ? get_field('button_link') : '' ?>"><?php echo (get_field('button_content')) ? get_field('button_content') : '' ?>
								&#8594; </a>
						</div>

					<?php endif; ?>

					<!-- </div> -->
				</div>

				<div class="swiper mySwiper col-6 col-lg-6 col-xl-6 col-sm-12 col-md-12">
					<?php if (have_rows('content_slide_images')):
						?>
						<!-- <div class=""> -->
						<div class="swiper-wrapper">
							<?php while (have_rows('content_slide_images')):
								the_row() ?>
								<!-- Slides -->
								<div class="swiper-slide" style="width: 585px;">
									<img class="__custom_image" src="<?php echo get_sub_field('image_slide_custom') ?>"
										alt="<?php echo get_sub_field('image_slide_custom') ?>" />
								</div>
							<?php endwhile; ?>
						</div>
						<div class="swiper-pagination"></div>
						<!-- </div> -->
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