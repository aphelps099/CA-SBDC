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


if ( ! class_exists( 'Crown_Site_Settings_Site_Announcement' ) ) {
	class Crown_Site_Settings_Site_Announcement {

		public static $init = false;

		public static $site_announcement_admin_page = null;


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'after_setup_theme', array( __CLASS__, 'register_admin_pages' ) );
			add_filter( 'crown_site_announcement', array( __CLASS__, 'filter_site_announcement' ) );

		}


		public static function register_admin_pages() {

			self::$site_announcement_admin_page = new AdminPage( array(
				'key' => 'site-announcement',
				'parent' => 'dashboard',
				'title' => 'Site Announcement',
				'menuTitle' => 'Site Announcement',
				'fields' => array(
					new FieldGroupSet( array(
						'fieldGroups' => array(

							new FieldGroup( array(
								'label' => 'General',
								'fields' => array(
									new Field( array(
										'input' => new CheckboxInput( array( 'name' => 'site_announcement_enabled', 'label' => 'Enable Site Announcement' ) )
									) ),
									new FieldGroup(array(
										'class' => 'no-border',
										'uIRules' => array( new UIRule( array( 'property' => 'input', 'options' => array( 'inputName' => 'site_announcement_enabled' ), 'value' => 1 ) ) ),
										'fields' => array(
											new Field( array(
												'label' => 'Announcement Message',
												'input' => new TextInput( array( 'name' => 'site_announcement_message', 'class' => 'input-large' ) )
											) ),
											new FieldGroup( array(
												'label' => 'Announcement Link',
												'fields' => array(
													new FieldGroup(array(
														'class' => 'no-border two-column large-left',
														'fields' => array(
															new Field( array(
																'label' => 'Link URL',
																'input' => new TextInput( array( 'name' => 'site_announcement_link_url', 'placeholder' => 'https://' ) )
															) ),
															new Field( array(
																'label' => 'Link Label',
																'input' => new TextInput( array( 'name' => 'site_announcement_link_label', 'placeholder' => 'Learn More' ) )
															) )
														)
													) ),
													new Field( array(
														'input' => new CheckboxSet( array( 'name' => 'site_announcement_link_options', 'options' => array(
															array( 'value' => 'open-new-window', 'label' => 'Open in new window' )
														) ) )
													) )
												)
											) )
										)
									) )
								)
							) ),

							new FieldGroup( array(
								'label' => 'Configuration Options',
								'fields' => array(
									new Field( array(
										'label' => 'Display on',
										'input' => new RadioSet( array( 'name' => 'site_announcement_display_on', 'defaultValue' => 'all-pages', 'class' => 'toggle', 'options' => array(
											array( 'value' => 'all-pages', 'label' => 'All Pages' ),
											array( 'value' => 'homepage', 'label' => 'Homepage' )
										) ) )
									) )
								)
							) )

						)
					) )
				)
			) );

		}


		public static function filter_site_announcement( $announcement = null ) {

			if ( ! boolval( get_option( 'site_announcement_enabled' ) ) ) return $announcement;

			$display_on = get_option( 'site_announcement_display_on' );
			if ( $display_on == 'homepage' && ! is_front_page() ) return $announcement;

			$announcement = (object) array(
				'message' => get_option( 'site_announcement_message' ),
				'link' => (object) array(
					'url' => get_option( 'site_announcement_link_url' ),
					'label' => get_option( 'site_announcement_link_label' ),
					'options' => get_option( 'site_announcement_link_options', array() )
				)
			);

			return $announcement;

		}


	}
}