<?php
$custom_post_types = array('cww_event', 'cww_associate');
$post_type = get_post_type_object( $post->post_type );
$layout=theme_get_option('blog','layout');
get_header(); ?>
<?php theme_generator('introduce'); ?>
<div id="page">
	<div class="inner <?php if($layout=='right'):?>right_sidebar<?php endif;?><?php if($layout=='left'):?>left_sidebar<?php endif;?>">
		<div id="main">
			<?php theme_generator('breadcrumbs');?>
			<div class="content">
			<?php 
				$exclude_cats = theme_get_option('blog','exclude_categorys');
				foreach ($exclude_cats as $key => $value) {
					$exclude_cats[$key] = -$value;
				}
				if(stripos($query_string,'cat=') === false){
					query_posts($query_string."&cat=".implode(",",$exclude_cats));
				}else{
					query_posts($query_string.implode(",",$exclude_cats));
				}
				get_template_part('loop',$post_type->name);
			?>
				<div class="clearboth"></div>
			</div>
			<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } ?>
		</div>
		<?php if($layout != 'full') get_sidebar(); ?>
		<div class="clearboth"></div>
	</div>
	<div id="page_bottom"></div>
</div>
<?php get_footer(); ?>