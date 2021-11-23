<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !class_exists('JVM_acf_field_jvm_rich_text_icons') ) {

class JVM_acf_field_jvm_rich_text_icons extends acf_field {
    
    
    /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type    function
    *  @date    5/03/2014
    *  @since   5.0.0
    *
    *  @param   n/a
    *  @return  n/a
    */
    
    public function __construct( $settings ) { 
        /*
        *  name (string) Single word, no spaces. Underscores allowed
        */
        $this->name = 'jvm_rich_text_icon';
        
        
        /*
        *  label (string) Multiple words, can include spaces, visible when selecting a field type
        */
        $this->label = __('JVM Icon', 'jvm-richtext-insert-icons');
        
        
        /*
        *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
        */
        $this->category = 'content';
        
        
        /*
        *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
        */        
        $this->defaults = array(
                'allow_null'        =>  0,
                'default_value'     => '',
                'output_format'     => 'element'
            );

        /*
        *  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
        */
        $this->settings = $settings;
        
        // do not delete!
        parent::__construct();        
    }
    
    
    /*
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */
    
    public function render_field_settings( $field ) {
        acf_render_field_setting( $field, array(
            'label'         => __('Default icon','jvm-richtext-insert-icons'),
            'type'          => 'select',
            'name'          => 'default_value',
            'class'         => 'select2-jvm-rich-text-icon jvm-rich-text-icon-create',
            'choices'       => isset( $field['default_value'] ) ? [  $field['default_value'] => html_entity_decode( $field['default_value'] ) ] : [],
            'value'         => isset($field['default_value']) ? $field['default_value'] : '',
            'placeholder'   => __('Choose a default icon (optional)','jvm-richtext-insert-icons'),
            'ui'            => 1,
            'allow_null'    => 1,
            'ajax'          => 1,
            'ajax_action'   => 'acf/fields/jvm-richtext-insert-icons/query'
        ));

        acf_render_field_setting( $field, array(
            'label'         => __( 'Return Value', 'jvm-richtext-insert-icons' ),
            'instructions'  => __( 'Specify the returned value on front end', 'jvm-richtext-insert-icons' ),
            'type'          => 'radio',
            'name'          => 'output_format',
            'choices'   =>  array(
                'element'   =>  __( 'Icon Element', 'jvm-richtext-insert-icons' ),
                'class'     =>  __( 'Icon Class', 'jvm-richtext-insert-icons' )
            )
        ));

        acf_render_field_setting( $field, array(
            'label'         => __( 'Allow Null?', 'jvm-richtext-insert-icons' ),
            'instructions'  => '',
            'type'          => 'radio',
            'name'          => 'allow_null',
            'choices'   =>  array(
                1   =>  __( 'Yes', 'jvm-richtext-insert-icons' ),
                0   =>  __( 'No', 'jvm-richtext-insert-icons' )
            )
        ));
    }
    
    
    
    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param   $field (array) the $field being rendered
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */
    
    function render_field( $field ) {  
        if ( $field['allow_null'] ) {
            $select_value = $field['value'];
        } else {
            $select_value = ( 'null' != $field['value'] ) ? $field['value'] : $field['default_value'];
        }

        $field['type'] = 'select';
        $field['ui'] = 1;
        $field['ajax'] = 1;
        $field['ajax_action'] = 'acf/fields/jvm-richtext-insert-icons/query';
        $field['choices'] = [];
        $field['multiple'] = false;
        $field['class'] = ' select2-jvm-rich-text-icon select2-jvm-rich-text-icon-edit';

        $icons = JVM_Richtext_icons::get_icons();
        if ( $select_value && in_array($select_value, $icons) ) {
            $field['choices'][ $select_value ] = htmlentities( $select_value );
        }

        acf_render_field( $field );
    }
    
        
    /*
    *  input_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
    *  Use this action to add CSS + JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*
    
    function input_admin_enqueue_scripts() {
        
        // vars
        $url = $this->settings['url'];
        $version = $this->settings['version'];
        
        
        // register & include JS
        wp_register_script('jvm-richtext-insert-icons', "{$url}assets/js/input.js", array('acf-input'), $version);
        wp_enqueue_script('jvm-richtext-insert-icons');
        
        
        // register & include CSS
        wp_register_style('jvm-richtext-insert-icons', "{$url}assets/css/input.css", array('acf-input'), $version);
        wp_enqueue_style('jvm-richtext-insert-icons');
        
    }
    
    */
    
    
    /*
    *  input_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*
        
    function input_admin_head() {
    
        
        
    }
    
    */
    
    
    /*
    *  input_form_data()
    *
    *  This function is called once on the 'input' page between the head and footer
    *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and 
    *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
    *  seen on comments / user edit forms on the front end. This function will always be called, and includes
    *  $args that related to the current screen such as $args['post_id']
    *
    *  @type    function
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $args (array)
    *  @return  n/a
    */
    
    /*
    
    function input_form_data( $args ) {
        
        
    
    }
    
    */
    
    
    /*
    *  input_admin_footer()
    *
    *  This action is called in the admin_footer action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_footer)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*
        
    function input_admin_footer() {
    
        
        
    }
    
    */
    
    
    /*
    *  field_group_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
    *  Use this action to add CSS + JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*
    
    function field_group_admin_enqueue_scripts() {
        
    }
    
    */

    
    /*
    *  field_group_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is edited.
    *  Use this action to add CSS and JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*
    
    function field_group_admin_head() {
    
    }
    
    */


    /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */
    
    /*
    
    function load_value( $value, $post_id, $field ) {
        
        return $value;
        
    }
    
    */
    
    
    /*
    *  update_value()
    *
    *  This filter is applied to the $value before it is saved in the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */
    
    /*
    
    function update_value( $value, $post_id, $field ) {
        
        return $value;
        
    }
    
    */
    
    
    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value which was loaded from the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *
    *  @return  $value (mixed) the modified value
    */    
    public function format_value( $value, $post_id, $field ) {
        if ( 'null' == $value ) {
            return false;
        }

        if ( empty( $value ) ) {
            return $value;
        }
        
        $icons = JVM_Richtext_icons::get_icons();
        $css_class = JVM_Richtext_icons::get_class_prefix();
        $icon = in_array($value, $icons);

        if ($icon) {
            switch ( $field['output_format'] ) {
                case 'element':
                return '<i class="' . $css_class . ' ' . $value . '" aria-hidden="true"></i>';
                break;
            }
        }
        
        // return
        return $value;
    }
    
    
    /*
    *  validate_value()
    *
    *  This filter is used to perform validation on the value prior to saving.
    *  All values are validated regardless of the field's required setting. This allows you to validate and return
    *  messages to the user if the value is not correct
    *
    *  @type    filter
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $valid (boolean) validation status based on the value and the field's required setting
    *  @param   $value (mixed) the $_POST value
    *  @param   $field (array) the field array holding all the field options
    *  @param   $input (string) the corresponding input name for $_POST value
    *  @return  $valid
    */
    
    /*
    
    function validate_value( $valid, $value, $field, $input ){
        
        // Basic usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = false;
        }
        
        
        // Advanced usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = __('The value is too little!','jvm-richtext-insert-icons'),
        }
        
        
        // return
        return $valid;
        
    }
    
    */
    
    
    /*
    *  delete_value()
    *
    *  This action is fired after a value has been deleted from the db.
    *  Please note that saving a blank value is treated as an update, not a delete
    *
    *  @type    action
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $post_id (mixed) the $post_id from which the value was deleted
    *  @param   $key (string) the $meta_key which the value was deleted
    *  @return  n/a
    */
    
    /*
    
    function delete_value( $post_id, $key ) {
        
        
        
    }
    
    */
    
    
    /*
    *  load_field()
    *
    *  This filter is applied to the $field after it is loaded from the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0   
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */
    
    /*
    
    function load_field( $field ) {
        
        return $field;
        
    }   
    
    */
    
    
    /*
    *  update_field()
    *
    *  This filter is applied to the $field before it is saved to the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */
    
    /*
    
    function update_field( $field ) {
        
        return $field;
        
    }   
    
    */
    
    
    /*
    *  delete_field()
    *
    *  This action is fired after a field is deleted from the database
    *
    *  @type    action
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  n/a
    */
    
    /*
    
    function delete_field( $field ) {
        
        
        
    }   
    
    */
    
    
}


// initialize
new JVM_acf_field_jvm_rich_text_icons( $this->settings );


// class_exists check
}