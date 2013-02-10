<?php
/************************************************************************************ 
/* A class for generating Wordpress custom Post Types
/* By Jesse Rosato 2012
/************************************************************************************/
class CwwPostTypeEngine {

	protected $_post_type;
	protected $_meta_boxes;
	protected $_post;
	
	/************************************************************************************ 
	/* Default constructor
	/************************************************************************************/
	public function __construct($post_type = array(), $meta_boxes = array()) {
		if (!$this->set_post_type($post_type))
			throw new Exception('CwwPostTypeEngine constructor requires an array describing the post type.');
		if(!$this->set_meta_boxes($meta_boxes))
			throw new Exception('CwwPostTypeEngine constructor failed to process parameter 2, $meta_boxes.');
	} // end __construct()
	
	/************************************************************************************ 
	/* Set post type array
	/************************************************************************************/
	public function set_post_type($post_type = false) {
		if (!$post_type || !is_array($post_type) || empty($post_type))
			return false;
		if (!isset($post_type['handle']) || !$post_type['handle'])
			return false;
		$args = isset($post_type['args']) ? $post_type['args'] : false;
		if (!$args || !is_array($args) || empty($args))
			return false;
		$labels = isset($args['labels']) ? $args['labels'] : false;
		if (!$labels || !is_array($labels) || !isset($labels['name']) || !$labels['name'])
			return false;
		$this->_post_type = $post_type;
		return true;
	} // end set_post_type()
	
	/************************************************************************************ 
	/* Get post type array
	/************************************************************************************/
	public function get_post_type() {
		return $this->_post_type;
	} // end get_post_type()
	
	/************************************************************************************ 
	/* Set meta boxes array
	/************************************************************************************/
	public function set_meta_boxes($meta_boxes = false) {
		if (!$meta_boxes || !is_array($meta_boxes) || empty($meta_boxes)) {
			$this->_meta_boxes = array();
			return true;
		}
		foreach ($meta_boxes as $meta_box_group) {
			foreach ($meta_box_group as $meta_box) {
				if (!isset($meta_box['title']) || !$meta_box['title'])
					return false;
			}
		}
		$this->_meta_boxes = $meta_boxes;
		return true;
	} // end set_meta_boxes()
	
	/************************************************************************************ 
	/* Get meta boxes array
	/************************************************************************************/
	public function get_meta_boxes() {
		return $this->_meta_boxes;
	} // end get_meta_boxes()
	
	/************************************************************************************ 
	/* Register post type
	/************************************************************************************/
	public function create_post_type() {
		register_post_type($this->_post_type['handle'], $this->_post_type['args']);
		flush_rewrite_rules();
	} // end create_post_type()
	
	/************************************************************************************ 
	/* Add meta boxes
	/************************************************************************************/
	public function add_meta_boxes() {
		foreach ($this->_post_type['meta_box_groups'] as $handle => $meta_box_group) {
			$post_type = $this->_post_type['handle'];
			$title = $meta_box_group['title'];
			$callback = empty($meta_box_group['callback']) ? array(&$this, 'meta_box_callback') : $meta_box_group['callback'];
			$context = empty($meta_box_group['context']) ? 'advanced' : $meta_box_group['context'];
			$priority = empty($meta_box_group['priority']) ? 'default' : $meta_box_group['priority'];
			$desc = empty($meta_box_group['desc']) ? false : $meta_box_group['desc'];
			add_meta_box($handle, $title, $callback, $post_type, $context, $priority, array('desc' => $desc));
		}
	} // end add_meta_boxes()
	
	/************************************************************************************ 
	/* Print meta box group
	/************************************************************************************/
	public function meta_box_callback( $post, $meta_box_group ) {
		// Use nonce for verification
		wp_nonce_field( 'cww_nonce_field_' . $this->_post_type['handle'], $this->_post_type['handle'] . '_nonce' );
		echo '<p class="description">' . $meta_box_group['args']['desc'] . '</p>';
		foreach ($this->_meta_boxes[$meta_box_group['id']] as $meta_box) {
			$this->render_meta_box($post, $meta_box);
		}
	} // end meta_box_callback()
	
	/************************************************************************************ 
	/* Print meta box
	/************************************************************************************/
	protected function render_meta_box($post, $meta_box) {
		$desc = isset($meta_box['args']['desc']) ? $meta_box['args']['desc'] : '';
		if ($meta_box['args']['type'] != 'group_start' && $meta_box['args']['type'] != 'group_end') :
		?>
		<div class="meta-box-item">
		 <div class="meta-box-item-title">
		  <h4><?php echo $meta_box['title']; ?></h4>
		  <?php if ($desc) echo '<a class="switch" href="">[+] more info</a></div><p class="description">' . $desc . '</p>'; else echo '</div>'; ?>
		 <div class="meta-box-item-content">
		  <?php $this->render_meta_box_input($post, $meta_box); ?>
		 </div>
		</div>
		<?php
		else :
		$this->render_meta_box_input($post, $meta_box);
		endif;
	} // end render_meta_box();
	
	/************************************************************************************ 
	/* Print meta box input
	/************************************************************************************/
	protected function render_meta_box_input($post, $meta_box) {
		$meta_box_type	= isset($meta_box['args']['type']) ? $meta_box['args']['type'] : 'text';
		$meta_box_key	= $meta_box['handle'];
		$meta_box_title	= $meta_box['title'];
		$meta_box_class = isset($meta_box['args']['class']) ? $meta_box['args']['class'] : '';
		$meta_box_class = is_array($meta_box_class) ? implode(', ', $meta_box_class) : $meta_box_class;
		$meta_box_def	= isset($meta_box['args']['default']) ? $meta_box['args']['default'] : '';
		$meta_box_val 	= get_post_meta($post->ID, $meta_box_key);
		$meta_box_val 	= empty($meta_box_val) ? $meta_box_def : array_shift(array_values($meta_box_val));
		$label  = '<label for="' . $meta_box_key . '" class="' . $meta_box_class . '">' . $meta_box_title . '</label>';
		
		switch ($meta_box_type) {
			case 'info':
			break;
			case 'group_start':
				?>
				<div class="meta-box-group">
				<h3><?php echo $meta_box_title; ?></h3>
				<?php if (!empty($meta_box['args']['desc'])) : ?>
				<p class="description"><?php echo $meta_box['args']['desc']; ?></p>
				<?php endif;
			break;
			case 'group_end':
				echo '</div>';
			break;
			case 'select':
				$options = $meta_box['args']['options'];
				?>
				<select
					class="<?php echo $meta_box_class; ?>"
					id="<?php echo $meta_box_key; ?>"
					name="<?php echo $meta_box_key; ?>"
				>
				<?php
				foreach ( $options as $key => $value )
					echo '<option value="' . $key . '">' . $value . '</option>';
				?>
				</select>
				<?php
			case 'date':
			    // convert the date from timestamp to dash separated
			    if ( is_numeric( $meta_box_val ) ) {
			        if ( $meta_box_val > 99999999 ) {
			        	// Timestamp (new style)
			    		$meta_box_val = date('Y-m-d', $meta_box_val);
			    	} else {
			    		// Ymd (old style)
			    		$meta_box_val = substr_replace($meta_box_val, '-', 4, 0);
			    		$meta_box_val = substr_replace($meta_box_val, '-', 7, 0);
			    	}
			    }
				?>
				<input
					type="text"
					class="<?php echo $meta_box_class; ?>"
					id="<?php echo $meta_box_key; ?>-input"
					name="<?php echo $meta_box_key; ?>"
					value="<?php echo $meta_box_val; ?>"
				/>
				<?php
			break;
			case 'time':
				preg_match('/^(\d{1,2})[:](\d\d)(.*)$/', $meta_box_val, $time);
				$hours  = $time[1];
				$mins   = $time[2];
				$ampm   = preg_match('/p/i', $time[3]) ? 'p' : 'a';
				?>
				&nbsp;&nbsp;
				<select name="<?php echo $meta_box_key; ?>[1]" class="<?php echo $meta_box_class; ?>, hours" ?>[0]">
				<?php
				for ($i = 1; $i < 13; $i++) {
					echo '<option value="' . $i . '" ';
					echo ($hours == $i ? 'selected="selected"' : '') . '>';
					echo $i . '</option>';
				}
				?>
				</select>
				&nbsp;&nbsp;
				<select name="<?php echo $meta_box_key; ?>[2]" class="<?php echo $meta_box_class; ?>, minutes"> 
				<?php
				for ($i = 0; $i < 4; $i++) {
					$opt_mins = $i ? $i * 15 : '00';
					echo '<option value="' . $opt_mins . '" ';
					echo ($mins == $opt_mins ? 'selected="selected"' : '') . '>';
					echo $opt_mins . '</option>';
				}
				?>
				</select>
				&nbsp;&nbsp;
				<select name="<?php echo $meta_box_key; ?>[3]" class="<?php echo $meta_box_class; ?>, ampm">
					<option value="a" <?php echo $ampm == 'a' ? 'selected="selected"' : ''; ?> >AM</option>;
					<option value="p" <?php echo $ampm == 'p' ? 'selected="selected"' : ''; ?> >PM</option>;
				</select>
				&nbsp;
				<?php
			break;
			case 'checkbox':
				?>
				<input
					type="checkbox"
					class="<?php echo $meta_box_class; ?>" 
					id="<?php echo $meta_box_key; ?>"
					name="<?php echo $meta_box_key; ?>"
					<?php echo $meta_box_val ? 'checked="checked"' : null; ?>
					value="1"
				/> 
				<?php
				echo $label;
			break;
			case 'text':
				?>
				<input 
					type="text"
					class="<?php echo $meta_box_class; ?>"
					id="<?php echo $meta_box_key; ?>"
					name="<?php echo $meta_box_key; ?>"
					value="<?php echo $meta_box_val; ?>"
				/>
				<?php
			break;
			case 'textarea':
				?>
				<textarea
					class="<?php echo $meta_box_class; ?>"
					id="<?php echo $meta_box_key; ?>"
					name="<?php echo $meta_box_key; ?>"
				><?php echo $meta_box_val; ?></textarea>
				<?php
			break;
			default:
				throw new Exception('CwwPostTypeEngine meta_box_callback() failed handling meta box type "' . $meta_box_type . '".');
		} // end switch
	} // end render_meta_box()
} // end class