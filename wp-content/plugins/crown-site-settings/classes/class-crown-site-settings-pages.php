<?php

use Crown\AdminPage;
use Crown\Form\Field;
use Crown\Form\FieldGroup;
use Crown\Form\FieldGroupSet;
use Crown\Form\Input\CheckboxSet;
use Crown\Form\Input\Media as MediaInput;
use Crown\Form\Input\RadioSet;
use Crown\Form\Input\Select;
use Crown\Form\Input\Text as TextInput;
use Crown\Form\Input\Checkbox as CheckboxInput;
use Crown\Form\Input\Textarea;
use Crown\Post\MetaBox;
use Crown\Post\Type as PostType;
use Crown\UIRule;


if ( ! class_exists( 'Crown_Site_Settings_Pages' ) ) {
	class Crown_Site_Settings_Pages {

		public static $init = false;

		public static $post_types = array();


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'after_setup_theme', array( __CLASS__, 'register_page_fields' ) );

		}


		public static function register_page_fields() {

			self::$post_types['page'] = new PostType( array(
				'name' => 'page',
				'metaBoxes' => array(
					new MetaBox( array(
						'id' => 'page-header',
						'title' => 'Page Header',
						'context' => 'side',
						'fields' => array(
							new Field( array(
								'input' => new CheckboxSet( array( 'name' => 'page_header_options', 'options' => array(
									array( 'value' => 'disabled', 'label' => 'Disable header' )
								) ) )
							) )
						)
					) )
				)
			) );

		}


	}
}