<?php
/**
 * The loop that displays posts.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 */
$featured_image = theme_get_option('blog', 'index_featured_image');
$featured_image_type = theme_get_option('blog', 'featured_image_type');
$display_full = theme_get_option('blog','display_full');
if(is_search()){
	$layout = theme_get_option('blog','search_layout');
	$display_full = theme_get_option('blog','search_display_full');
}
if(!isset($layout) || $layout=='default'){
	$layout = theme_get_option('blog','layout');
}
$columns = theme_get_option('blog','columns');
$frame = theme_get_option('blog','frame');

$columns = (int)$columns;
if($columns > 6){
	$columns = 6;
}elseif($columns < 1){
	$columns = 1;
}
if ($columns != 1) {
	if($layout == 'full'){
		$layout = floor((958-25*($columns-1))/$columns);
	}else{
		$layout = floor((628-25*($columns-1))/$columns);
	}
}


$class = array('','half','third','fourth','fifth','sixth');
$css = $class[$columns-1];
$i = 0;
if($frame){
	$frame_css = ' entry_frame';
}else{
	$frame_css = '';
}
if(is_search() && !have_posts() && $search_nothing_found = wpml_t(THEME_NAME, 'Search Nothing Found Text',theme_get_option('blog','search_nothing_found'))) { 
   echo '<div class="info">
<div class="message_box_content">'.$search_nothing_found.'</div>
<div class="clearboth"></div>
</div>'; 
}
if ( have_posts() ) while ( have_posts() ) : the_post(); 
$i++;
if ($columns != 1) {
	if ($i%$columns !== 0) {
		echo "<div class=\"one_{$css}\">";
	} else {
		echo "<div class=\"one_{$css} last\">";
	}
}


if ( get_post_type() == 'cww_event' ) {
	$event_id = get_the_ID();
	$event_is_over = cww_event_is_over( $event_id );
	$after_post_id = get_post_meta($event_id, 'cww_event_after_post_id', true);
	$orig_post = $post;
	$post = $after_post_id && $event_is_over ? get_post($after_post_id) : get_post($event_id);
}

?>
<article id="post-<?php the_ID(); ?>" class="entry entry_<?php echo $featured_image_type;?><?php echo $frame_css;?>"> 
<?php if($featured_image && $featured_image_type!=='below'){echo theme_generator('blog_featured_image',$featured_image_type,$layout,'',$frame);} ?>
	<div class="entry_info">
		<h2 class="entry_title"><a href="<?php echo get_permalink() ?>" rel="bookmark" title="<?php printf( __("Permanent Link to %s", 'striking_front'), get_the_title() ); ?>"><?php the_title(); ?></a></h2>
		<div class="entry_meta">
<?php echo theme_generator('blog_meta'); ?>
		</div>
	</div>
<?php if($featured_image && $featured_image_type=='below'){echo theme_generator('blog_featured_image',$featured_image_type,$layout);} ?>
		<div class="entry_content">
<?php 
	if($display_full):
		global $more;
		$more = 0;
		the_content(wpml_t(THEME_NAME, 'Blog Post Read More Button Text',stripslashes(theme_get_option('blog','read_more_text'))),false);
	else:
		the_excerpt();
		if(theme_get_option('blog','read_more_button')):?>
			<a class="read_more_link <?php echo apply_filters( 'theme_css_class', 'button' );?> small" href="<?php the_permalink(); ?>" rel="nofollow"><span><?php echo wpml_t(THEME_NAME, 'Blog Post Read More Button Text',stripslashes(theme_get_option('blog','read_more_text')));?></span></a>
	<?php else: ?>
			<a class="read_more_link" href="<?php the_permalink(); ?>" rel="nofollow"><?php echo wpml_t(THEME_NAME, 'Blog Post Read More Button Text',stripslashes(theme_get_option('blog','read_more_text')));?></a>
	<?php endif; 
	endif;
?>
		
	</div>
</article>
<?php

if ($columns != 1) {
	echo '</div>';
	if ($i%$columns === 0) {
		echo "<div class=\"clearboth\"></div>";
	}
}

$post = empty($orig_post) ? $post : $orig_post;

endwhile;
wp_reset_postdata();
?>
