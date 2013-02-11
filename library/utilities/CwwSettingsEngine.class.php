<?php
/************************************************************************************ 
/* A class for generating Wordpress settings pages
/* By Jesse Rosato, 2012.
/************************************************************************************/
class CwwSettingsEngine {

	protected $_settings;
	protected $_scripts;
	
	public function __construct($settings_page_info = array()) {
		if (!$this->set_settings($settings_page_info))
			throw new Exception('CwwSettingsEngine constructor requires settings page information.');
	}
	
	/************************************************************************************ 
	/* Set settings page
	/************************************************************************************/
	public function set_settings($settings_page_info = false) {
		if (!$settings_page_info || !is_array($settings_page_info) || empty($settings_page_info))
			return false;
		$req_str = array('page-title', 'menu-title', 'slug', 'capability');
		$req_arr = array( 'sections', 'fields');
		foreach ($req_str as $key) {
			if (!isset($settings_page_info[$key]) || !$settings_page_info[$key])
				return false;
		}
		foreach ($req_arr as $key) {;
			if (!isset($settings_page_info[$key]) || !is_array($settings_page_info[$key]) || empty($settings_page_info[$key]))
				return false;
		}
		$this->_settings = $settings_page_info;
		return true;
	}
	
	/************************************************************************************ 
	/* Get settings. 
	/* 
	/* @return array 
	/************************************************************************************/  
	public function get_settings($key = false) {
		if ($key)
			return isset($this->_settings[$key]) ? $this->_settings[$key] : false;
	    return $this->_settings;
	} // get_settings
	
	
	/************************************************************************************
	/* Add settings page and action for admin css and js
	/************************************************************************************/
	public function add_settings_page() {
		// May want to require a more advanced 'capability' to make these changes.
		$settings_page = add_options_page($this->_settings['page-title'], $this->_settings['menu-title'], $this->_settings['capability'], $this->_settings['slug'], array( &$this, 'options_page_callback' ) );
	}
	
	/************************************************************************************ 
	/* Create custom settings page HTML.
	/************************************************************************************/
	public function options_page_callback() {	
	    ?>  
	    <div class="wrap">  
	        <div class="icon32" id="icon-options-general"></div>  
	        <h2><?php echo $this->_settings['page-title']; ?></h2>  
	          
	        <form action="options.php" method="post">  
	            <?php   
	            // http://codex.wordpress.org/Function_Reference/settings_fields  
	            settings_fields($this->_settings['slug']);   
	              
	            // http://codex.wordpress.org/Function_Reference/do_settings_sections  
	            do_settings_sections(__FILE__);   
	            ?>  
	            <p class="submit">  
	                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes','cww'); ?>" />  
	            </p>  
	              
	        </form>  
	    </div><!-- wrap -->
	<?php
	} // options_page_callback
	
	/************************************************************************************ 
	/* Register settings
	/************************************************************************************/
	public function register_settings( $validation_callback = false ) {
		$validation_callback = $validation_callback ? $validation_callback : array( &$this, 'validation_callback' );
		$option_name = $this->_settings['slug']; 
		// Sections  
	    // add_settings_section( $id, $title, $callback, $page );  
	    if(!empty($this->_settings['sections'])){  
	        // call the "add_settings_section" for each!  
	        foreach ( $this->_settings['sections'] as $id => $title ) {  
	            add_settings_section( $id, $title, array(&$this, 'section_callback'), __FILE__);  
	        }  
	    }
	    //fields  
	    if(!empty($this->_settings['fields'])){  
	        // call the "add_settings_field" for each!  
	        foreach ($this->_settings['fields'] as $option) {  
	            $this->create_settings_field($option);  
	        }  
	    }
	    // register_setting( $option_group, $option_name, $sanitize_callback );  
		register_setting($option_name, $option_name, $validation_callback);
	}
	
	/************************************************************************************
	/* Section HTML, displayed before the first option 
	/*
	/* @return echoes output 
	/************************************************************************************/  
	function  section_callback($desc) {  
		// __('Settings for this section','cww')
	    echo "<p>" . $desc['title'] . " " . __('settings', 'cww') . ".</p>";
	}

	/************************************************************************************
	/* Helper function for registering our form field settings 
	/* 
	/* src: http://alisothegeek.com/2011/01/wordpress-settings-api-tutorial-1/ 
	/* @param (array) $args The array of arguments to be used in creating the field 
	/* @return function call 
	/************************************************************************************/  
	public function create_settings_field( $args = array() ) {  
	    // default array to overwrite when calling the function  
	    $defaults = array(  
	        'id'      => 'cww_options_field',				// the ID of the setting in our options array, and the ID of the HTML form element  
	        'title'   => 'Field',							// the label for the HTML form element  
	        'desc'    => 'This is a default description.',  // the description displayed under the HTML form element  
	        'std'     => '', 								// the default value for this setting  
	        'type'    => 'text',							// the HTML form element to use  
	        'section' => 'main_section',                    // the section this setting belongs to — must match the array key of a section in wptuts_options_page_sections()  
	        'choices' => array(),                           // (optional): the values in radio buttons or a drop-down menu  
	        'class'   => ''                                 // the HTML form element class. Also used for validation purposes!  
	    );  
	      
	    // "extract" to be able to use the array keys as variables in our function output below  
	    extract( wp_parse_args( $args, $defaults ) );  
	      
	    // additional arguments for use in form field output in the function wptuts_form_field_fn!  
	    $field_args = array(  
	        'type'      => $type,  
	        'id'        => $id,  
	        'desc'      => $desc,  
	        'std'       => $std,  
	        'choices'   => $choices,  
	        'label_for' => $id,  
	        'class'     => $class  
	    );  
	  
	    add_settings_field( $id, $title, array(&$this, 'form_field_callback'), __FILE__, $section, $field_args );
	}
	
	/************************************************************************************ 
	/* Form Fields HTML 
	/* All form field types share the same function!! 
	/* @return echoes output 
	/************************************************************************************/  
	function form_field_callback($args = array()) {  
	      
	    extract( $args );    
	    
	    $option_name = $this->_settings['slug'];
	    $options = get_option($option_name);  
	      
	    // pass the standard value if the option is not yet set in the database  
	    if ( empty( $options[$id] ) && 'type' != 'checkbox' ) {  
	        $options[$id] = $std;  
	    }  
	      
	    // additional field class. output only if the class is defined in the create_setting arguments  
	    $field_class = !empty($class) ? $class : '';  
	      
	      
	    // switch html display based on the setting type.  
	    switch ( $type ) {  
	        case 'text':  
	            $options[$id] = stripslashes($options[$id]);  
	            $options[$id] = esc_attr( $options[$id]);  
	            echo "<input class='regular-text $field_class' type='text' id='$id' name='" . $option_name . "[$id]' value='$options[$id]' />";  
	            echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
	        break;
	        
	        case 'password':  
	            $options[$id] = esc_attr(stripslashes($options[$id]));  
	            echo "<input class='regular-text $field_class' type='password' id='$id' name='" . $option_name . "[$id]' value='$options[$id]' />";  
	            echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
	        break;    
	          
	        case "multi-text":  
	            foreach($choices as $item) {  
	                $item = explode("|",$item); // cat_name|cat_slug  
	                $item[0] = esc_html__($item[0], 'cww');  
	                if (!empty($options[$id])) {  
	                    foreach ($options[$id] as $option_key => $option_val){  
	                        if ($item[1] == $option_key) {  
	                            $value = $option_val;  
	                        }  
	                    }  
	                } else {  
	                    $value = '';  
	                }  
	                echo "<span>$item[0]:</span> <input class='$field_class' type='text' id='$id|$item[1]' name='" . $option_name . "[$id|$item[1]]' value='$value' /><br/>";  
	            }  
	            echo ($desc != '') ? "<span class='description'>$desc</span>" : "";  
	        break;  
	          
	        case 'textarea':  
	            $options[$id] = stripslashes($options[$id]);  
	            $options[$id] = esc_html( $options[$id]);  
	            echo "<textarea class='textarea$field_class' type='text' id='$id' name='" . $option_name . "[$id]' rows='5' cols='30'>$options[$id]</textarea>";  
	            echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";           
	        break;  
	          
	        case 'select':  
	            echo "<select id='$id' class='select $field_class' name='" . $option_name . "[$id]'>";  
	                foreach($choices as $item) {  
	                    $value  = esc_attr($item, 'cww');  
	                    $item   = esc_html($item, 'cww');  
	                      
	                    $selected = ($options[$id]==$value) ? 'selected="selected"' : '';  
	                    echo "<option value='$value' $selected>$item</option>";  
	                }  
	            echo "</select>";  
	            echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";   
	        break;  
	          
	        case 'select2':  
	            echo "<select id='$id' class='select $field_class' name='" . $option_name . "[$id]'>";  
	            foreach($choices as $item) {  
	                  
	                $item = explode("|",$item);  
	                $item[0] = esc_html($item[0], 'cww');  
	                  
	                $selected = ($options[$id]==$item[1]) ? 'selected="selected"' : '';  
	                echo "<option value='$item[1]' $selected>$item[0]</option>";  
	            }  
	            echo "</select>";  
	            echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
	        break;  
	          
	        case 'checkbox':  
	            echo "<input class='checkbox $field_class' type='checkbox' id='$id' name='" . $option_name . "[$id]' value='1' " . checked( $options[$id], 1, false ) . " />";  
	            echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
	        break;  
	          
	        case "multi-checkbox":  
	            foreach($choices as $item) {  
	                  
	                $item = explode("|",$item);  
	                $item[0] = esc_html($item[0], 'cww');  
	                  
	                $checked = '';  
	                  
	                if ( isset($options[$id][$item[1]]) ) {  
	                    if ( $options[$id][$item[1]] == 'true') {  
	                        $checked = 'checked="checked"';  
	                    }  
	                }  
	                  
	                echo "<input class='checkbox $field_class' type='checkbox' id='$id|$item[1]' name='" . $option_name . "[$id|$item[1]]' value='1' $checked /> $item[0] <br/>";  
	            }  
	            echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";  
	        break;  
	    }  
	} // form_field_callback()
	
	/************************************************************************************
	/* Validate input
	/*
	/* @return valid options
	/************************************************************************************/
	public function validation_callback( $input ) {
		// for enhanced security, create a new empty array  
	    $valid_input = array();  
	      
	    // collect only the values we expect and fill the new $valid_input array i.e. whitelist our option IDs  
	    $options = $this->_settings['fields'];
	      
	    // run a foreach and switch on option type  
	    foreach ($options as $option) {
	    	$key = $option['id'];
	    	$val = trim($input[$key]);
	    	
	    	// Check for empty/default fields values
	    	if ( empty($val) || $val == $option['std'] ) {
		    	if ( !empty( $option['req'] )) {
			    	$error_msg = __("The field") . " '" . $option['title'] . "' " . __('is required', 'cww') . '.';
			    	add_settings_error($key, $key . '_error', $error_msg, 'error');
		    	}
		    	continue;
	    	}
	        switch ( $option['type'] ) {  
	            default:  // text
	                //switch validation based on the class!  
	                $option['class'] = isset($option['class']) ? $option['class'] : false;
	                switch ( $option['class'] ) {
	                	case 'numeric':
	                        // If class is numeric and the input has a value,
	                        // check that input value is numeric.
	                        if($val && !is_numeric($val)) {
	                        	$error_msg = __('The field') . " '" . $option['title'] . "' " . __('must be a numeric value', 'cww') . '.';
	                            add_settings_error($key, $key . '_error', $error_msg, 'error');  
	                        } else {
		                        $valid_input[$key] = $val;
	                        }
	                    break;
	                    default:
	                    	$valid_input[$key] = $val;
	                }
	        }
	   }
	   return $valid_input;
	}
}