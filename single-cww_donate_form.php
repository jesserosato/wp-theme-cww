<?php
// Check for successfuly submitted form or query string
global $df_clean;
global $df_errors;
global $df_content;
require_once('library/post_types/donate_form/query_process.php');
if (empty($df_content))
	require_once('library/post_types/donate_form/process.php');	

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
			if (!empty($df_content)) {
				echo $df_content;
			} else {
				the_content();
				get_template_part('template', 'donate_form');
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