<?php
$event_id = $post->ID;
$event_is_over = cww_event_is_over( $event_id );
$after_post_id = get_post_meta($post->ID, 'cww_event_after_post_id', true);
if ( $after_post_id &&  $event_is_over )
	$post = get_post($after_post_id);

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
<?php
if ( $after_post_id &&  $event_is_over )
	query_posts( "p=$after_post_id" );
if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
			<div class="content">
			<?php
			cww_event_content( $event_id );
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