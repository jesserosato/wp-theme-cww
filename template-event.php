<?php
/*
 * Event post type template
 *
 * BE VERY CAREFUL WHEN EDITING THIS FILE:
 * - Edit only the indicated portion.
 * - Remember that any changes you make here will affect the display of ALL events,
 *   including those displayed using the event shortcodes.
 */
 
// DON'T EDIT THIS TOP PART!!!!
global $cww_event_type;
$cww_event_post = $post;
$cww_event_post_id		= $cww_event_post->ID;
$event_is_over	= cww_event_is_over( $cww_event_post_id );
$after_post_id	= get_post_meta($cww_event_post_id, 'cww_event_after_post_id', true);
if ( $after_post_id &&  $event_is_over )
	$the_post = get_post($after_post_id);

if ($cww_event_type == 'single' || $cww_event_type == 'multi-full') {
	$content = apply_filters('the_content', $cww_event_post->post_content);
} else {
	$content = apply_filters('the_excerpt', $cww_event_post->post_excerpt);
	if ( empty( $content ) )
		$content = apply_filters('the_content', $cww_event_post->post_content);
}

$location	= do_shortcode('[eventlocation eventid="' . $cww_event_post_id . '"]');
$start_date = do_shortcode('[eventstartdate eventid="' . $cww_event_post_id . '"]');
$start_time = do_shortcode('[eventstarttime eventid="' . $cww_event_post_id . '"]');
$end_date	= do_shortcode('[eventenddate eventid="' . $cww_event_post_id . '"]');
$end_time	= do_shortcode('[eventendtime eventid="' . $cww_event_post_id . '"]');
$details	= do_shortcode('[eventinfo eventid="' . $cww_event_post_id . '"]');
$reg_btn	= do_shortcode('[eventregbtn eventid="' . $cww_event_post_id . '"]Register[/eventregbtn]');

if ( has_post_thumbnail( $cww_event_post->ID ) ) {
	$images = wp_get_attachment_image_src( get_post_thumbnail_id( $cww_event_post->ID ), 'single-post-thumbnail' );
	$image = empty($images[0]) ? false : $images[0];
} else {
	$image = false;
}
// BEGIN EDITING ONLY AFTER THIS LINE! ?>

<div class="cww-event <?php echo $cww_event_type; ?>">
  <div class="cww-event-title">
    <h3><a href="<?php echo get_permalink($cww_event_post_id); ?>"><?php echo $cww_event_post->post_title; ?></a></h3>
  </div>
  <div class="cww-event-description">
    <?php echo $content; ?>
  </div>
  <div class="cww-event-details">
    <?php if( $image ) : ?><img class="cww-event-thumbnail" src="<?php echo $image; ?>" /><?php endif; ?>
	<?php if( !( $event_is_over && $after_post_id ) ) : ?>
		<p><strong>Location</strong><br /><?php echo $location; ?></p>
		<p><strong>Starts</strong><br /><?php echo $start_date;?> at <?php echo $start_time; ?></p>
		<p><strong>Ends</strong><br /><?php echo $end_date; ?> at <?php echo $end_time; ?></p>
		<?php if ( $details ) : ?><p><strong>Details</strong><br /><?php echo $details; ?></p><?php endif; ?>
		<?php if ( $reg_btn ) : echo $reg_btn; endif; ?>
		<?php if ( $cww_event_type == 'multi' || $cww_event_type == 'multi-full') :
			if ( $reg_btn ) : ?>&nbsp; &nbsp;<?php endif; ?>
			<a href="<?php echo get_permalink($cww_event_post_id); ?>">Learn more...</a>
		<?php endif; ?>
	<?php endif; ?>
  </div>
</div>