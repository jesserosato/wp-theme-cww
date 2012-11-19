<?php
/*
 * Associate post type template
 *
 * BE VERY CAREFUL WHEN EDITING THIS FILE:
 * - Edit only the indicated poriton.
 * - Remember that any changes you make here will affect the display of ALL associates,
 *   including those displayed using the associate shortcodes.
 */
 
// DON'T EDIT THIS TOP PART!!!!
global $cww_associate_type;
$type = $cww_associate_type;

if ( $type == 'single' || $type == 'multi-full' ) {
    // Load body content for single and multi-full types
	$content = apply_filters('the_content', $post->post_content);
} else {
    // Attempt to lead excerpt content for multi type
	$content = apply_filters('the_excerpt', $post->post_excerpt);
	// Load body content if no excerpt content
	$content = $content ? $content : apply_filters('the_content', $post->post_content);
}
if ( has_post_thumbnail( $post->ID ) ) {
	$images = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
	$image = empty($images[0]) ? false : $images[0];
} else {
	$image = false;
}
$first_name 	= get_post_meta($post->ID, 'cww_associate_first_name', true);
$last_name		= get_post_meta($post->ID, 'cww_associate_last_name', true);
$organization	= get_post_meta($post->ID, 'cww_associate_organization', true);
$position		= get_post_meta($post->ID, 'cww_associate_position', true);
// BEGIN EDITING ONLY AFTER THIS LINE! ?>

<div class="cww-associate">
  <div class="cww-associate-title">
    <h3><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $first_name . ' ' . $last_name; ?></a></h3>
  </div>
  
  <div class="cww-associate-details">
    <?php if ( $image ) : ?>
    <img class="cww-associate-thumbnail thumbnail" src="<?php echo $image; ?>" />
    <?php endif; ?>
    <?php if ( $type == 'single' ) : ?>
    <p><?php echo $first_name . ' ' . $last_name; ?></p>
    <?php endif; ?>
    <?php if ( $organization ) : ?>
    <p><?php echo $organization; ?></p>
    <?php endif; ?>
    <?php if ( $position ) : ?>
    <p><?php echo $position; ?></p>
    <?php endif; ?>
  </div>
  <div class="cww-associate-description">
    <?php echo $content; ?>
    <?php if ( $type == 'multi' ) : ?>
    <a href="<?php echo get_permalink($post_id); ?>">Learn more...</a>
    <?php endif; ?>
  </div>
</div>