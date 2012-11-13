<?php
if (isset($_GET['df'])) {
	$df_passed_data = get_transient($_GET['df']);
	// Check for expired transient.
	if ($df_passed_data) {
		$df_passed_data = unserialize($df_passed_data);
		$post = get_post($df_passed_data['conf_post_id']);
		$df_content = $df_passed_data['conf_content'];
	}
}

if (!isset($df_content)) {
	require_once('library/post_types/donate_form/df_process.php');
	require_once('library/post_types/donate_form/text/countries.inc');
	require_once('library/post_types/donate_form/text/error.inc');
}

$layout = get_post_meta($post->ID, '_layout', true);
if (empty($layout) || $layout == 'default'){
	$layout=theme_get_option('general','layout');
}

?>
<?php get_header(); ?>
<?php theme_generator('introduce',$post->ID);?>
<div id="page">
	<div class="inner <?php if($layout=='right'):?>right_sidebar<?php endif;?><?php if($layout=='left'):?>left_sidebar<?php endif;?>">
		<div id="main">
			<?php theme_generator('breadcrumbs',$post->ID);?>
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<div class="content">
			<?php
			if (isset($df_content)) {
				echo $df_content;
			} else {
				the_content();
				require_once('library/post_types/donate_form/df_form.php');
			}
			?>
			 <div class="clearboth"></div>
			</div><!-- end #content !-->
<?php endwhile; ?>
			<div class="clearboth"></div>
		</div><!-- end #main !-->
		<?php if($layout != 'full') {
			get_sidebar();
		}?>
		<div class="clearboth"></div>
	</div><!-- end #.inner etc !-->
	<div id="page_bottom"></div>
</div><!-- end #page !-->
<?php get_footer(); ?>