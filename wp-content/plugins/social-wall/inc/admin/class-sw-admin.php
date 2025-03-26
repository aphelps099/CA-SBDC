<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use SB\SocialWall\Utility\Helpers;

class SW_Admin {

	protected $vars;

	protected $base_path;

	protected $slug;

	protected $plugin_name;

	protected $capability;

	protected $tabs;

	protected $active_tab;

	protected $settings_sections;

	protected $display_your_feed_sections;

	protected $option_name;

	protected $types;

	protected $layouts;

	protected $false_fields;

	protected $textarea_fields;

	protected $settings;

	protected $icon;

	protected $position;

	public function __construct( $vars, $base_path, $slug, $plugin_name, $capability, $icon, $position, $tabs, $settings, $active_tab = false, $option_name = 'sbsw_settings' ) {
		$this->vars = $vars;
		$this->base_path = $base_path;
		$this->slug = $slug;
		$this->plugin_name = $plugin_name;
		$this->capability = $capability;
		$this->icon = $icon;
		$this->position = $position;

		$this->tabs = $tabs;

		if ( $active_tab ) {
			$this->set_active_tab( $active_tab );
		} else {
			$this->set_active_tab( $tabs[0]['slug'] );
		}
		$this->settings = $settings;
		$this->option_name = $option_name;
		$this->false_fields = array();
		$this->textarea_fields = array();
		$this->display_your_feed_sections = array();

		$this->settings_sections = array();
	}

	public function get_vars() {
		return $this->vars;
	}

	public function get_option_name() {
		return $this->option_name;
	}

	public function verify_post( $post ) {
		return wp_verify_nonce( $post[ $this->option_name . '_validate' ], $this->option_name . '_validate' );
	}

	public function hidden_fields_for_tab( $tab ) {
		wp_nonce_field( $this->get_option_name() . '_validate', $this->get_option_name() . '_validate', true, true );
		?>
        <input type="hidden" name="<?php echo $this->get_option_name() . '_tab_marker'; ?>" value="<?php echo esc_attr( $tab ); ?>"/>
		<?php
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'create_menus' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_init', array( $this, 'additional_settings_init' ) );
		add_action( 'admin_head', array( $this, 'sbsw_hide_yt_menu' ) );
		add_action( 'admin_footer', array( $this, 'sbsw_add_admin_js' ) );
	}

	//If using an individual plugin then open the SW menu
	public function sbsw_add_admin_js(){

		//Show menu tooltip once only
		$sbsw_seen_menu_tooltip = get_option('sbsw_seen_menu_tooltip');
		$nonce = wp_create_nonce('sbsw_admin_settings');

		echo "<script type='text/javascript'>
        jQuery(document).ready( function($) {
        	//Open SW menu when on individual plugins
        	var on_cff_settings = jQuery('#cff-builder-app').length || jQuery('#cff-settings').length || jQuery('#cff-extensions').length || jQuery('#cff-oembeds').length || jQuery('#cff-about').length || jQuery('#cff-support').length,
        		on_sbi_settings = jQuery('#sbi_admin.wrap').length || jQuery('#sbi-builder-app').length || jQuery('#sbi-settings').length || jQuery('#sbi-extensions').length || jQuery('#sbi-oembeds').length || jQuery('#sbi-about').length || jQuery('#sbi-support').length,
        		on_ctf_settings = jQuery('#ctf-admin.wrap').length,
        		on_yt_settings = jQuery('#sbspf_admin.wrap h1:contains(\'Feeds for YouTube\')').length,
        		on_tt_settings = jQuery('#sb-app').length;
			var nonce = '" . $nonce . "';

			function get_sw_plugin_icon(plugin) {
				// check if the plugin contains such string 
				if(plugin === 'Facebook') {
					return '<svg width=\"16\" height=\"16\" viewBox=\"0 0 20 20\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M8 1.35999C4.33334 1.35999 1.33334 4.35332 1.33334 8.03999C1.33334 11.3733 3.77334 14.14 6.96 14.64V9.97332H5.26667V8.03999H6.96V6.56665C6.96 4.89332 7.95334 3.97332 9.48 3.97332C10.2067 3.97332 10.9667 4.09999 10.9667 4.09999V5.74665H10.1267C9.3 5.74665 9.04 6.25999 9.04 6.78665V8.03999H10.8933L10.5933 9.97332H9.04V14.64C10.611 14.3919 12.0415 13.5903 13.0733 12.38C14.1051 11.1697 14.6702 9.63041 14.6667 8.03999C14.6667 4.35332 11.6667 1.35999 8 1.35999Z\" fill=\"#141B38\"></svg>';
				}
				if(plugin === 'Instagram') {
					return '<svg width=\"16\" height=\"16\" viewBox=\"0 0 20 20\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M10 5.50781C7.5 5.50781 5.50781 7.53906 5.50781 10C5.50781 12.5 7.5 14.4922 10 14.4922C12.4609 14.4922 14.4922 12.5 14.4922 10C14.4922 7.53906 12.4609 5.50781 10 5.50781ZM10 12.9297C8.39844 12.9297 7.07031 11.6406 7.07031 10C7.07031 8.39844 8.35938 7.10938 10 7.10938C11.6016 7.10938 12.8906 8.39844 12.8906 10C12.8906 11.6406 11.6016 12.9297 10 12.9297ZM15.7031 5.35156C15.7031 4.76562 15.2344 4.29688 14.6484 4.29688C14.0625 4.29688 13.5938 4.76562 13.5938 5.35156C13.5938 5.9375 14.0625 6.40625 14.6484 6.40625C15.2344 6.40625 15.7031 5.9375 15.7031 5.35156ZM18.6719 6.40625C18.5938 5 18.2812 3.75 17.2656 2.73438C16.25 1.71875 15 1.40625 13.5938 1.32812C12.1484 1.25 7.8125 1.25 6.36719 1.32812C4.96094 1.40625 3.75 1.71875 2.69531 2.73438C1.67969 3.75 1.36719 5 1.28906 6.40625C1.21094 7.85156 1.21094 12.1875 1.28906 13.6328C1.36719 15.0391 1.67969 16.25 2.69531 17.3047C3.75 18.3203 4.96094 18.6328 6.36719 18.7109C7.8125 18.7891 12.1484 18.7891 13.5938 18.7109C15 18.6328 16.25 18.3203 17.2656 17.3047C18.2812 16.25 18.5938 15.0391 18.6719 13.6328C18.75 12.1875 18.75 7.85156 18.6719 6.40625ZM16.7969 15.1562C16.5234 15.9375 15.8984 16.5234 15.1562 16.8359C13.9844 17.3047 11.25 17.1875 10 17.1875C8.71094 17.1875 5.97656 17.3047 4.84375 16.8359C4.0625 16.5234 3.47656 15.9375 3.16406 15.1562C2.69531 14.0234 2.8125 11.2891 2.8125 10C2.8125 8.75 2.69531 6.01562 3.16406 4.84375C3.47656 4.10156 4.0625 3.51562 4.84375 3.20312C5.97656 2.73438 8.71094 2.85156 10 2.85156C11.25 2.85156 13.9844 2.73438 15.1562 3.20312C15.8984 3.47656 16.4844 4.10156 16.7969 4.84375C17.2656 6.01562 17.1484 8.75 17.1484 10C17.1484 11.2891 17.2656 14.0234 16.7969 15.1562Z\" fill=\"#141B38\"/></svg>';
				}
				if(plugin === 'Twitter') {
					return '<svg width=\"16\" height=\"16\" viewBox=\"0 0 16 16\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M14.9733 4.00002C14.46 4.23335 13.9067 4.38669 13.3333 4.46002C13.92 4.10669 14.3733 3.54669 14.5867 2.87335C14.0333 3.20669 13.42 3.44002 12.7733 3.57335C12.2467 3.00002 11.5067 2.66669 10.6667 2.66669C9.09999 2.66669 7.81999 3.94669 7.81999 5.52669C7.81999 5.75335 7.84665 5.97335 7.89332 6.18002C5.51999 6.06002 3.40665 4.92002 1.99999 3.19335C1.75332 3.61335 1.61332 4.10669 1.61332 4.62669C1.61332 5.62002 2.11332 6.50002 2.88665 7.00002C2.41332 7.00002 1.97332 6.86669 1.58665 6.66669V6.68669C1.58665 8.07335 2.57332 9.23335 3.87999 9.49335C3.46047 9.60816 3.02005 9.62413 2.59332 9.54002C2.77439 10.1083 3.12901 10.6056 3.60733 10.962C4.08565 11.3183 4.66362 11.5158 5.25999 11.5267C4.24907 12.327 2.99598 12.7596 1.70665 12.7534C1.47999 12.7534 1.25332 12.74 1.02665 12.7134C2.29332 13.5267 3.79999 14 5.41332 14C10.6667 14 13.5533 9.64002 13.5533 5.86002C13.5533 5.73335 13.5533 5.61335 13.5467 5.48669C14.1067 5.08669 14.5867 4.58002 14.9733 4.00002Z\" fill=\"#141B38\"/></svg>';
				}
				if(plugin === 'YouTube') {
					return '<svg width=\"16\" height=\"16\" viewBox=\"0 0 20 20\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M6.66667 9.99998L10.1267 7.99998L6.66667 5.99998V9.99998ZM14.3733 4.77998C14.46 5.09331 14.52 5.51331 14.56 6.04665C14.6067 6.57998 14.6267 7.03998 14.6267 7.43998L14.6667 7.99998C14.6667 9.45998 14.56 10.5333 14.3733 11.22C14.2067 11.82 13.82 12.2066 13.22 12.3733C12.9067 12.46 12.3333 12.52 11.4533 12.56C10.5867 12.6066 9.79333 12.6266 9.06 12.6266L8 12.6666C5.20667 12.6666 3.46667 12.56 2.78 12.3733C2.18 12.2066 1.79333 11.82 1.62667 11.22C1.54 10.9066 1.48 10.4866 1.44 9.95331C1.39333 9.41998 1.37333 8.95998 1.37333 8.55998L1.33333 7.99998C1.33333 6.53998 1.44 5.46665 1.62667 4.77998C1.79333 4.17998 2.18 3.79331 2.78 3.62665C3.09333 3.53998 3.66667 3.47998 4.54667 3.43998C5.41333 3.39331 6.20667 3.37331 6.94 3.37331L8 3.33331C10.7933 3.33331 12.5333 3.43998 13.22 3.62665C13.82 3.79331 14.2067 4.17998 14.3733 4.77998Z\" fill=\"#141B38\"/></svg>';
				}
				if(plugin === 'TikTok') {
					return '<svg width=\"16\" height=\"16\" xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"geometricPrecision\" text-rendering=\"geometricPrecision\" image-rendering=\"optimizeQuality\" fill-rule=\"evenodd\" clip-rule=\"evenodd\" viewBox=\"0 0 449.45 515.38\"><path fill=\"#444444\" fill-rule=\"nonzero\" d=\"M382.31 103.3c-27.76-18.1-47.79-47.07-54.04-80.82-1.35-7.29-2.1-14.8-2.1-22.48h-88.6l-.15 355.09c-1.48 39.77-34.21 71.68-74.33 71.68-12.47 0-24.21-3.11-34.55-8.56-23.71-12.47-39.94-37.32-39.94-65.91 0-41.07 33.42-74.49 74.48-74.49 7.67 0 15.02 1.27 21.97 3.44V190.8c-7.2-.99-14.51-1.59-21.97-1.59C73.16 189.21 0 262.36 0 352.3c0 55.17 27.56 104 69.63 133.52 26.48 18.61 58.71 29.56 93.46 29.56 89.93 0 163.08-73.16 163.08-163.08V172.23c34.75 24.94 77.33 39.64 123.28 39.64v-88.61c-24.75 0-47.8-7.35-67.14-19.96z\"></path></svg>';
				}
				
			}

        	if( on_cff_settings || on_sbi_settings || on_ctf_settings || on_yt_settings || on_tt_settings ){
        		jQuery('#toplevel_page_sbsw, #toplevel_page_sbsw > a.wp-has-submenu').addClass('wp-has-current-submenu wp-menu-open');

        		var sbsw_menu_sel = '';
        		if( on_cff_settings ){
	        		sbsw_menu_sel = '.sbsw_cff_menu';
	        	} else if( on_sbi_settings ){
	        		sbsw_menu_sel = '.sbsw_sbi_menu';
	        	} else if( on_ctf_settings ){
	        		sbsw_menu_sel = '.sbsw_ctf_menu';
	        	} else if( on_yt_settings ){
	        		sbsw_menu_sel = '.sbsw_yt_menu';
	        	} else if( on_tt_settings ){
	        		sbsw_menu_sel = '.sbsw_tt_menu';
	        	}
	        	jQuery('#toplevel_page_sbsw '+sbsw_menu_sel).closest('li').addClass('current');
        	}

        	//SW plugin missing modal
        	jQuery('.toplevel_page_sbsw .sbsw_plugin_missing').parent().on('click', function(e){
        		e.preventDefault();
        		jQuery('.sbsw_missing_plugin_modal').remove();

        		var pluginName = jQuery(this).text(),
        			platformName = pluginName.split(' ')[0]
					pluginLogo = get_sw_plugin_icon(platformName);
        		var sbsw_missing_html = '<div class=\"sbsw_missing_plugin_modal\">';
        		sbsw_missing_html += '<div class=\"sbsw_missing_inner\">';
				sbsw_missing_html += '<span class=\"plugin-logo\">'+ pluginLogo +'</span>';
        		sbsw_missing_html += '<h3>Add '+platformName+' Posts</h3>';
				
        		sbsw_missing_html += '<p>The '+pluginName+' plugin is not installed. To add the '+pluginName+' to your Social Wall, click the button below to get it.</p>';
        		sbsw_missing_html += '<p class=\"modal-buttons\"><button class=\"button button-default sw-close-missng-modal\">Close</button> <a href=\"'+jQuery(this).attr('href')+'\" target=\"_blank\" class=\"button button-primary\">Get '+pluginName+' Pro</a></p>';
        		sbsw_missing_html += '</div>';
        		sbsw_missing_html += '</div>';

				jQuery('body').append( sbsw_missing_html + '<style>.sbsw_missing_plugin_modal{position:fixed;z-index:999;width:calc(100% - 160px);top:0;left:160px;bottom:0;background:rgba(146, 149, 166, 0.5);display:flex;justify-content:center;align-items:center;}.sbsw_missing_inner, .sbsw-plugin-modal-inner{width:460px;;padding:1.25rem 1.5rem  1.5rem  3.5rem;position:relative;background:#fff;-webkit-box-shadow:0 1px 10px rgb(0 0 0 / 10%);box-shadow:0 1px 10px rgb(0 0 0 / 10%);-moz-border-radius:0.5rem;-webkit-border-radius:0.5rem;border-radius:0.5rem;box-sizing:border-box;}.sbsw_missing_inner h3{margin: 0 0px 10px;font-size:16px;font-weight:600}.sbsw_missing_inner .plugin-logo{position: absolute;left: 20px;top: 22px}.sbsw_missing_inner p{font-size: 0.875rem;line-height:1.25rem;margin-top: -4px;padding-right:30px;color:#272727b8}.sbsw_missing_inner .modal-buttons{margin-top:30px; display: flex;justify-content: flex-end;margin-bottom: 0px; padding-right: 0px;gap: 8px}.sbsw_missing_inner .modal-buttons .button {padding: 0.5rem 0.75rem;font-weight: 600;line-height: 1.6;box-shadow:1px 2px 3px rgba(0,0,0,.2);border-radius: 4px} .sbsw_missing_inner .modal-buttons .button-primary{background-color:#0068a0!important;} .sbsw_missing_inner .modal-buttons .button-default{border-color:#dedede!important;color: #000}</style>' );
        	});
        	//Close the modal if clicking anywhere outside it
        	jQuery('body').on('click', '.sbsw_missing_plugin_modal', function(e){
        		if (e.target !== this) return;
        		jQuery('.sbsw_missing_plugin_modal').remove();
        	});
			jQuery('body').on('click', '.sw-close-missng-modal', function(e){
        		jQuery('.sbsw_missing_plugin_modal').remove();
        	});
			
        	//SW plugin inactive modal
        	jQuery('.toplevel_page_sbsw .sbsw_plugin_inactive').parent().on('click', function(e){
        		e.preventDefault();
        		jQuery('.sbsw_missing_plugin_modal').remove();

        		var pluginName = jQuery(this).text(),
        			platformName = pluginName.split(' ')[0],
					pluginLogo = get_sw_plugin_icon(platformName);

        		var sbsw_missing_html = '<div class=\"sbsw_missing_plugin_modal\">';
        		sbsw_missing_html += '<div class=\"sbsw_missing_inner\">';
				sbsw_missing_html += '<span class=\"plugin-logo\">'+ pluginLogo +'</span>';
        		sbsw_missing_html += '<h3>Activate '+platformName+' Feed</h3>';

        		sbsw_missing_html += '<p>To add '+pluginName+' posts to the wall, you need to activate the plugin first.</p>';
        		sbsw_missing_html += '<p class=\"modal-buttons\"><button class=\"button button-default sw-close-missng-modal\">Close</button> <a href=\"'+jQuery(this).attr('href')+'\" target=\"_blank\" class=\"button button-primary sw-activate-plugin\" data-plugin-name=\"'+pluginName+'\" >'+ loader +' Activate Plugin</a></p>';
        		sbsw_missing_html += '</div>';
        		sbsw_missing_html += '</div>';

				jQuery('body').append( sbsw_missing_html + '<style>.sbsw_missing_plugin_modal{position:fixed;z-index:999;width:calc(100% - 160px);top:0;left:160px;bottom:0;background:rgba(146, 149, 166, 0.5);display:flex;justify-content:center;align-items:center;}.sbsw_missing_inner, .sbsw-plugin-modal-inner{width:460px;;padding:1.25rem 1.5rem  1.5rem  3.5rem;position:relative;background:#fff;-webkit-box-shadow:0 1px 10px rgb(0 0 0 / 10%);box-shadow:0 1px 10px rgb(0 0 0 / 10%);-moz-border-radius:0.5rem;-webkit-border-radius:0.5rem;border-radius:0.5rem;box-sizing:border-box;}.sbsw_missing_inner h3{margin: 0 0px 10px;font-size:16px;font-weight:600}.sbsw_missing_inner .plugin-logo{position: absolute;left: 20px; top: 22px}.sbsw_missing_inner p{font-size: 0.875rem;line-height:1.25rem;margin-top: -4px;padding-right:30px;color:#272727b8}.sbsw_missing_inner .modal-buttons{margin-top:30px; display: flex;justify-content: flex-end;margin-bottom: 0px; padding-right: 0px;gap: 8px}.sbsw_missing_inner .modal-buttons .button {padding: 0.5rem 0.75rem;font-weight: 600;line-height: 1.6;box-shadow:1px 2px 3px rgba(0,0,0,.2);border-radius: 4px} .sbsw_missing_inner .modal-buttons .button-primary{background-color:#0068a0!important;display:flex;gap: 5px;}.sbsw_missing_inner .modal-buttons .button-primary svg{display:none} .sbsw_missing_inner .modal-buttons .button-default{border-color:#dedede!important;color: #000}</style>' );
        	});
        	//Close the modal if clicking anywhere outside it
        	jQuery('body').on('click', '.sbsw_missing_plugin_modal', function(e){
        		if (e.target !== this) return;
        		jQuery('.sbsw_missing_plugin_modal').remove();
        	});
			jQuery('body').on('click', '.sw-close-missng-modal', function(e){
        		jQuery('.sbsw_missing_plugin_modal').remove();
        	});
			jQuery('body').on('click', '.sw-activate-plugin', function(e){
				e.preventDefault();
				var self = jQuery(this);
				var pluginName = self.data('plugin-name');
				self.find('svg').show();

				jQuery.ajax({
					type : \"post\",
					dataType : \"json\",
					url : ajaxurl,
					data : {action: \"sw_activate_plugin\", plugin : pluginName, nonce: nonce},
					success: function(response) {
						self.find('svg').hide();
						location.reload();
					}
				 });
			});
			
			";

        if( !$sbsw_seen_menu_tooltip ){
        	//Add an initial direction tooltip for menu
        	echo "jQuery('a.toplevel_page_sbsw').prepend('<div class=\"sbsw-installed-pointer\">Your Smash Balloon Social Feeds are here<svg aria-hidden=\"true\" focusable=\"false\" data-prefix=\"fas\" data-icon=\"caret-left\" role=\"img\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 192 512\" class=\"svg-inline--fa fa-caret-left fa-w-6 fa-2x\"><path fill=\"currentColor\" d=\"M192 127.338v257.324c0 17.818-21.543 26.741-34.142 14.142L29.196 270.142c-7.81-7.81-7.81-20.474 0-28.284l128.662-128.662c12.599-12.6 34.142-3.676 34.142 14.142z\"></path></svg></div><style>.sbsw-installed-pointer{position:absolute;z-index:9;left:90%;padding:10px;background:#ca4a1f;color:#fff;width:100%;top:-10px;text-align:center;border-radius:3px;cursor:default;}.wp-menu-open .sbsw-installed-pointer{z-index:99999;} .sbsw-installed-pointer svg{ position: absolute; z-index: 99999; width: 12px; left: -10px; top: 50%; margin-top: -16px;}.sbsw-installed-pointer path{ fill: #ca4a1f;}</style>');";
        	echo "jQuery('body').on('mouseover', '.sbsw-installed-pointer', function(e){
        		e.preventDefault();
        		if (e.target !== this) return;
        		jQuery('.sbsw-installed-pointer').fadeOut();
        	});";
        }

        echo "});
        </script>";

        update_option('sbsw_seen_menu_tooltip', true);
	}

	public function settings_init() {
		$text_domain = $this->vars->text_domain();
		/**
		 * Configure Tab
		 */
		$args = array(
			'id' => 'sbspf_types',
			'tab' => 'configure',
			'save_after' => false
		);
		$this->add_settings_section( $args );

		/**
		 * Customize Tab
		 */

		$args = array(
			'title' => __( 'Posts', $text_domain ),
			'id' => 'sbspf_posts',
			'tab' => 'customize',
			'save_after' => 'true'
		);
		$this->add_settings_section( $args );

		$args = array(
			'title' => __( 'Display', $text_domain ),
			'id' => 'sbspf_layout',
			'tab' => 'customize',
			'save_after' => 'true'
		);
		$this->add_settings_section( $args );

		$args = array(
			'name' => 'layout',
			'section' => 'sbspf_layout',
			'callback' => 'layout',
			'title' => __( 'Layout Type', $text_domain ),
			'layouts' => $this->layouts,
			'shortcode' => array(
				'key' => 'layout',
				'example' => 'list',
				'description' => __( 'How your posts are displayed visually.', $text_domain ),
				'display_section' => 'layout'
			)
		);
		$this->add_settings_field( $args );

		$this->add_false_field( 'carouselarrows', 'customize');
		$this->add_false_field( 'carouselpag', 'customize');
		$this->add_false_field( 'carouselautoplay', 'customize');
		$this->add_false_field( 'masonryshowfilter', 'customize');
		$this->add_false_field( 'showbutton', 'customize');
		$this->add_false_field( 'preserve_settings', 'customize');
		$this->add_false_field( 'ajaxtheme', 'customize');
		$this->add_false_field( 'customtemplates', 'customize');
	}

	public function additional_settings_init() {
		$text_domain = SBSW_TEXT_DOMAIN;

		$defaults = sbsw_settings_defaults();

		$args = array(
			'name' => 'num',
			'default' => $defaults['num'],
			'section' => 'sbspf_posts',
			'callback' => 'text',
			'min' => 1,
			'max' => 50,
			'size' => 4,
			'title' => __( 'Number of Posts', $text_domain ),
			'additional' => '<span class="sbsw_note">' . __( 'Number of posts to show initially.', $text_domain ) . '</span>',
			'shortcode' => array(
				'key' => 'num',
				'example' => 5,
				'description' => __( 'The number of posts in the feed', $text_domain ),
				'display_section' => 'layout'
			)
		);
		$this->add_settings_field( $args );

		$select_options = array(
			array(
				'label' => __( 'Light', $text_domain ),
				'value' => 'light'
			),
			array(
				'label' => __( 'Dark', $text_domain ),
				'value' => 'dark'
			),
		);
		$args = array(
			'name' => 'theme',
			'default' => 'light',
			'section' => 'sbspf_layout',
			'callback' => 'select',
			'title' => __( 'Color Scheme', $text_domain ),
			'shortcode' => array(
				'key' => 'theme',
				'example' => 'dark',
				'description' => __( 'Light or dark color scheme for the feed.', $text_domain ) . ' light, dark',
				'display_section' => 'layout'
			),
			'options' => $select_options,
		);
		$this->add_settings_field( $args );

		$select_options = array(
			array(
				'label' => 'px',
				'value' => 'px'
			),
			array(
				'label' => '%',
				'value' => '%'
			)
		);
		$args = array(
			'name' => 'itemspacing',
			'default' => $defaults['itemspacing'],
			'section' => 'sbspf_layout',
			'callback' => 'text',
			'min' => 0,
			'size' => 4,
			'title' => __( 'Spacing Between Posts', $text_domain ),
			'shortcode' => array(
				'key' => 'itemspacing',
				'example' => '5px',
				'description' => __( 'The spacing/padding around the posts in the feed. Any number with a unit like "px" or "em".', $text_domain ),
				'display_section' => 'layout'
			),
			'select_name' => 'itemspacingunit',
			'select_options' => $select_options,
		);
		$this->add_settings_field( $args );

		$args = array(
			'name' => 'background',
			'default' => '',
			'section' => 'sbspf_layout',
			'callback' => 'color',
			'title' => __( 'Post Background Color', $text_domain ),
			'shortcode' => array(
				'key' => 'background',
				'example' => '#f00',
				'description' => __( 'Background color for the feed. Any hex color code.', $text_domain ),
				'display_section' => 'layout'
			),
		);
		$this->add_settings_field( $args );

		/* Cache */
		$args = array(
			'name' => 'cache',
			'section' => 'sbspf_posts',
			'callback' => 'cache',
			'title' => __( 'Check for new posts', $text_domain )
		);
		$this->add_settings_field( $args );

		$args = array(
			'name' => 'textlength',
			'default' => $defaults['textlength'],
			'section' => 'sbspf_text_date',
			'callback' => 'text',
			'min' => 5,
			'max' => 1500,
			'size' => 4,
			'title' => __( 'Text Length', $text_domain ),
			'shortcode' => array(
				'key' => 'textlength',
				'example' => 300,
				'description' => __( 'Maximum length of the text in the post', $text_domain ),
				'display_section' => 'text_date'
			)
		);
		$this->add_settings_field( $args );

		$select_options = array(
			array(
				'label' => __( 'inherit', $text_domain ),
				'value' => 'inherit'
			),
			array(
				'label' => __( '20px', $text_domain ),
				'value' => '20px'
			),
			array(
				'label' => __( '18px', $text_domain ),
				'value' => '18px'
			),
			array(
				'label' => __( '16px', $text_domain ),
				'value' => '16px'
			),
			array(
				'label' => __( '15px', $text_domain ),
				'value' => '15px'
			),
			array(
				'label' => __( '14px', $text_domain ),
				'value' => '14px'
			),
			array(
				'label' => __( '13px', $text_domain ),
				'value' => '13px'
			),
			array(
				'label' => __( '12px', $text_domain ),
				'value' => '12px'
			),
		);
		$args = array(
			'name' => 'contenttextsize',
			'default' => '16px',
			'section' => 'sbspf_text_date',
			'callback' => 'select',
			'title' => __( 'Content Text Size', $text_domain ),
			'shortcode' => array(
				'key' => 'contenttextsize',
				'example' => 'inherit',
				'description' => __( 'Size of content/caption text, size of other text will be relative to this size.', $text_domain ) . ' 13px, 14px, inherit',
				'display_section' => 'text_date'
			),
			'tooltip_info' => __( 'Size of content/caption  text, size of other text in the info display will be relative to this size.', $text_domain ),
			'options' => $select_options,
		);
		$this->add_settings_field( $args );
		$date_format_options = array(
			array(
				'label' => __( 'Relative', $text_domain ),
				'value' => 'relative'
			),
			array(
				'label' => __( 'Custom (Enter Below)', $text_domain ),
				'value' => 'custom'
			)
		);
		$date_text_options = array(
			array(
				'label' => __( 'm', $text_domain ),
				'key' => 'minutetext',
				'default' => 'm'
			),
			array(
				'label' => __( 'h', $text_domain ),
				'key' => 'hourtext',
				'default' => 'h'
			),
			array(
				'label' => __( 'd', $text_domain ),
				'key' => 'daytext',
				'default' => 'd'
			),
			array(
				'label' => __( 'w', $text_domain ),
				'key' => 'weektext',
				'default' => 'w'
			),
			array(
				'label' => __( 'mo', $text_domain ),
				'key' => 'monthtext',
				'default' => 'mo'
			),
			array(
				'label' => __( 'y', $text_domain ),
				'key' => 'yeartext',
				'default' => 'y'
			),
		);
		$args = array(
			'name' => 'dateformat',
			'default' => '',
			'section' => 'sbspf_text_date',
			'date_formats' => $date_format_options,
			'text_settings' => $date_text_options,
			'callback' => 'date_format',
			'title' => __( 'Date Format', $text_domain ),
			'shortcode' => array(
				'key' => 'dateformat',
				'example' => 'false',
				'description' => __( 'Include a "Load More" button at the bottom of the feed to load more posts.', $text_domain ),
				'display_section' => 'text_date'
			)
		);
		$this->add_settings_field( $args );

		$args = array(
			'title' => __( 'Text and Date', $text_domain ),
			'id' => 'sbspf_text_date',
			'tab' => 'customize',
		);
		$this->add_settings_section( $args );

		$args = array(
			'title' => __( '"Load More" Button', $text_domain ),
			'id' => 'sbspf_loadmore',
			'tab' => 'customize',
			'save_after' => 'true'
		);
		$this->add_settings_section( $args );

		$args = array(
			'name' => 'showbutton',
			'section' => 'sbspf_loadmore',
			'callback' => 'checkbox',
			'title' => __( 'Show "Load More" Button', $text_domain ),
			'default' => true,
			'shortcode' => array(
				'key' => 'showbutton',
				'example' => 'false',
				'description' => __( 'Include a "Load More" button at the bottom of the feed to load more posts.', $text_domain ),
				'display_section' => 'button'
			)
		);
		$this->add_settings_field( $args );

		$args = array(
			'name' => 'buttontext',
			'default' => __( 'Load More...', $text_domain ),
			'section' => 'sbspf_loadmore',
			'callback' => 'text',
			'title' => __( 'Button Text', $text_domain ),
			'shortcode' => array(
				'key' => 'buttontext',
				'example' => '"More Posts"',
				'description' => __( 'The text that appears on the "Load More" button.', $text_domain ),
				'display_section' => 'button'
			)
		);
		$this->add_settings_field( $args );

		$args = array(
			'title' => __( 'Moderation', $text_domain ),
			'id' => 'sbspf_moderation',
			'tab' => 'customize',
			'save_after' => 'true'
		);
		$this->add_settings_section( $args );

		$args = array(
			'name' => 'includewords',
			'default' => '',
			'section' => 'sbspf_moderation',
			'callback' => 'text',
			'class' => 'large-text',
			'title' => __( 'Show posts containing these words or hashtags', $text_domain ),
			'shortcode' => array(
				'key' => 'includewords',
				'example' => '#filter',
				'description' => __( 'Show posts that have specific text in the content/caption.', $text_domain ),
				'display_section' => 'customize'
			),
			'additional' => __( '"includewords" separate multiple words with commas, include "#" for hashtags', $text_domain )
		);
		$this->add_settings_field( $args );

		$args = array(
			'name' => 'excludewords',
			'default' => '',
			'section' => 'sbspf_moderation',
			'callback' => 'text',
			'class' => 'large-text',
			'title' => __( 'Remove posts containing these words or hashtags', $text_domain ),
			'shortcode' => array(
				'key' => 'excludewords',
				'example' => '#filter',
				'description' => __( 'Remove posts that have specific text in the title or description.', $text_domain ),
				'display_section' => 'customize'
			),
			'additional' => __( '"excludewords" separate multiple words with commas, include "#" for hashtags', $text_domain )
		);
		$this->add_settings_field( $args );

		$args = array(
			'title' => __( 'Custom Code Snippets', $text_domain ),
			'id' => 'sbspf_custom_snippets',
			'tab' => 'customize'
		);
		$this->add_settings_section( $args );

		$args = array(
			'name' => 'custom_css',
			'default' => '',
			'section' => 'sbspf_custom_snippets',
			'callback' => 'textarea',
			'title' => __( 'Custom CSS', $text_domain ),
			'options' => $select_options,
			'tooltip_info' => __( 'Enter your own custom CSS in the box below', $text_domain )
		);
		$this->add_settings_field( $args );

		$args = array(
			'name' => 'custom_js',
			'default' => '',
			'section' => 'sbspf_custom_snippets',
			'callback' => 'textarea',
			'title' => __( 'Custom JavaScript', $text_domain ),
			'options' => $select_options,
			'tooltip_info' => __( 'Enter your own custom JavaScript/jQuery in the box below', $text_domain ),
			'note' => __( 'Note: Custom JavaScript reruns every time more posts are loaded into the feed', $text_domain )
		);
		$this->add_settings_field( $args );

		$args = array(
			'title' => __( 'Advanced', $text_domain ),
			'id' => 'sbspf_advanced',
			'tab' => 'customize',
			'save_after' => 'true'
		);
		$this->add_settings_section( $args );

		$args = array(
			'name' => 'preserve_settings',
			'section' => 'sbspf_advanced',
			'callback' => 'checkbox',
			'title' => __( 'Preserve settings when plugin is removed', $text_domain ),
			'default' => false,
			'tooltip_info' => __( 'When removing the plugin your settings are automatically erased. Checking this box will prevent any settings from being deleted. This means that you can uninstall and reinstall the plugin without losing your settings.', $text_domain )
		);
		$this->add_settings_field( $args );

		$args = array(
			'name' => 'ajaxtheme',
			'section' => 'sbspf_advanced',
			'callback' => 'checkbox',
			'title' => __( 'Are you using an AJAX theme?', $text_domain ),
			'default' => false,
			'tooltip_info' => __( 'When navigating your site, if your theme uses Ajax to load content into your pages (meaning your page doesn\'t refresh) then check this setting. If you\'re not sure then it\'s best to leave this setting unchecked while checking with your theme author, otherwise checking it may cause a problem.', $text_domain )
		);
		$this->add_settings_field( $args );

		$args = array(
			'name' => 'ajax_post_load',
			'section' => 'sbspf_advanced',
			'callback' => 'checkbox',
			'title' => __( 'Load initial posts with AJAX', $text_domain ),
			'default' => false,
			'tooltip_info' => __( 'Initial posts will be loaded using AJAX instead of added to the page directly. If you use page caching, this will allow the feed to update according to the "Check for new posts every" setting on the "Configure" tab.', $text_domain )
		);
		//$this->add_settings_field( $args );

		$args = array(
			'name' => 'customtemplates',
			'section' => 'sbspf_advanced',
			'callback' => 'checkbox',
			'title' => __( 'Enable Custom Templates', $text_domain ),
			'default' => false,
			'tooltip_info' => __( 'The default HTML for the feed can be replaced with custom templates added to your theme\'s folder. Enable this setting to use these templates. See <a href="https://smashballoon.com/social-wall-custom-templates/" target="_blank">this guide</a>', $text_domain )
		);
		$this->add_settings_field( $args );
	}

	public function add_false_field( $name, $tab ) {
		$this->false_fields[ $tab ][] = $name;
	}

	public function get_false_fields( $tab ) {
		if ( isset( $this->false_fields[ $tab ] ) ) {
			return $this->false_fields[ $tab ];
		}

		return array();
	}

	public function add_textarea_field( $name, $tab ) {
		$this->textarea_fields[ $tab ][] = $name;
	}

	public function get_textarea_fields( $tab ) {
		if ( isset( $this->textarea_fields[ $tab ] ) ) {
			return $this->textarea_fields[ $tab ];
		}

		return array();
	}

	public function blank() {

	}

	public function instructions( $args ) {
		?>
        <div class="sbspf_instructions_wrap">
			<?php echo $args['instructions']?>
        </div>
		<?php
	}

	public function add_settings_section( $args ) {
		$title = isset( $args['title'] ) ? $args['title'] : '';
		$callback = isset( $args['callback'] ) ? $args['callback'] : array( $this, 'blank' );
		$id = $this->slug . '_' . $args['id'];
		add_settings_section(
			$id,
			$title,
			$callback,
			$id
		);

		$save_after = isset( $args['save_after'] ) ? $args['save_after'] : false;
		$this->settings_sections[ $args['tab'] ][] = array(
			'id' => $id,
			'save_after' => $save_after
		);
	}

	public function add_settings_field( $args ) {
		$title_after = '';
		$shortcode = false;
		if ( isset( $args['shortcode'] ) ) {
			$title_after = isset( $args['shortcode']['after'] ) ? $args['shortcode']['after'] : '';
			$shortcode = $args['shortcode'];
		}

		if ( $shortcode ) {
			$this->display_your_feed_sections[ $shortcode['display_section'] ]['settings'][] = $shortcode;
		}

		$title = $this->format_title( $args['title'], $args['name'], $shortcode, $title_after );

		if ( $args['callback'] === 'checkbox' || (isset( $args['falsefield'] ) && $args['falsefield'] === true) ) {
			$tab = 'none';
			foreach ( $this->settings_sections as $key => $settings_sections ) {
				foreach ( $settings_sections as $this_tab_sections ) {
					if ( $this_tab_sections['id'] === $args['section'] ) {
						$tab = $key;
					}
				}

			}
			$this->add_false_field( $args['name'], $tab );
		}

		if ( $args['callback'] === 'layout' || $args['callback'] === 'sub_option' ) {
			$tab = 'none';
			foreach ( $this->settings_sections as $key => $settings_sections ) {
				foreach ( $settings_sections as $this_tab_sections ) {
					if ( $this_tab_sections['id'] === $args['section'] ) {
						$tab = $key;
					}
				}

			}
			$sub_options = isset( $args['layouts'] ) ? $args['layouts'] : $args['sub_options'];
			foreach ( $sub_options as $sub_option ) {
				if ( isset( $sub_option['options'] ) ) {
					foreach( $sub_option['options'] as $sub_sub_option ) {
						if ( ! empty( $sub_sub_option['shortcode'] ) ) {
							$key = ! empty( $sub_sub_option['shortcode']['key'] ) ? $sub_sub_option['shortcode']['key'] : $sub_option['slug'] . $sub_sub_option['name'];
							$example = ! empty( $sub_sub_option['shortcode']['example'] ) ? $sub_sub_option['shortcode']['example'] : '';
							$description = ! empty( $sub_sub_option['shortcode']['description'] ) ? $sub_sub_option['shortcode']['description'] : '';
							$display_section = ! empty( $sub_sub_option['shortcode']['display_section'] ) ? $sub_sub_option['shortcode']['display_section'] : str_replace( 'sbspf_', '', $args['section'] );
							$sub_shortcode = array(
								'key' => $key,
								'example' => $example,
								'description' => $description,
								'display_section' => $display_section
							);
							if ( isset( $this->display_your_feed_sections[ $display_section ] ) ) {
								$this->display_your_feed_sections[ $display_section ]['settings'][] = $sub_shortcode;
							}
						}
						if ( $sub_sub_option['callback'] === 'checkbox' ) {
							$this->add_false_field( $sub_option['slug'] . $sub_sub_option['name'], $tab );
						}
					}
				}
			}
		}

		if ( $args['callback'] === 'textarea' ) {
			$tab = 'none';
			foreach ( $this->settings_sections as $key => $settings_sections ) {
				foreach ( $settings_sections as $this_tab_sections ) {
					if ( $this_tab_sections['id'] === $args['section'] ) {
						$tab = $key;
					}
				}

			}
			$this->add_textarea_field( $args['name'], $tab );
		}
		$section = $this->slug . '_' . $args['section'];

		add_settings_field(
			$args['name'],
			$title,
			array( $this, $args['callback'] ),
			$section,
			$section,
			$args
		);

		if ( isset( $args['hidden'] ) ) {
			if ( $args['hidden']['callback'] === 'checkbox' ) {
				$tab = 'none';
				foreach ( $this->settings_sections as $key => $settings_sections ) {
					foreach ( $settings_sections as $this_tab_sections ) {
						if ( $this_tab_sections['id'] === $args['section'] ) {
							$tab = $key;
						}
					}

				}
				$this->add_false_field( $args['hidden']['name'], $tab );
			}
		}
	}

	public function set_feed_types( $types ) {
		$this->types = $types;
	}

	public function set_feed_layouts( $layouts ) {
		$this->layouts = $layouts;
	}

	public function set_display_table_sections( $headings ) {
		foreach ( $headings as $heading ) {
			$this->display_your_feed_sections[ $heading['slug'] ] = array(
				'label' => $heading['label'],
				'settings' => array()
			);
		}
	}

	public function checkbox( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : false;
		$selected = isset( $this->settings[ $args['name'] ] ) ? $this->settings[ $args['name'] ] : $default;
		$label = isset( $args['label'] ) ? $args['label'] : __( 'Yes' );
		$tooltip_text = isset( $args['tooltip_text'] ) ? $args['label'] : $this->default_tooltip_text();
		$has_shortcode = isset( $args['has_shortcode'] ) && $args['has_shortcode'] ? '1' : '';
		?>
        <input name="<?php echo $this->option_name .'['.esc_attr( $args['name'] ).']'; ?>" id="<?php echo $this->option_name . '_' . $args['name']; ?>" class="sbspf_single_checkbox" type="checkbox"<?php if ( $selected ) echo ' checked'; ?>/>
        <label for="<?php echo $this->option_name . '_' . $args['name'] . $has_shortcode; ?>"><?php echo esc_html( $label ); ?></label><?php if ( $has_shortcode === '1' ) : ?><code class="sbspf_shortcode"> <?php echo $args['name'] . "\n"; ?>
            Eg: <?php echo $args['name']; ?>=<?php echo $args['shortcode_example']; ?></code><br><?php endif; ?>
		<?php if ( isset( $args['tooltip_info'] ) ) : ?>
            <a class="sbspf_tooltip_link" href="JavaScript:void(0);"><?php echo $tooltip_text; ?></a>
            <p class="sbspf_tooltip sbspf_more_info"><?php echo $args['tooltip_info']; ?></p>
		<?php
		endif;
	}

	public function multi_checkbox( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : false;
		$selection_array = isset( $this->settings[ $args['name'] ] ) ? (array)$this->settings[ $args['name'] ] : (array)$default;
		$tooltip_text = isset( $args['tooltip_text'] ) ? $args['label'] : $this->default_tooltip_text();
		$index = 0;
		?>
		<?php foreach ( $args['select_options'] as $select_option ) :
			$selected = in_array( $select_option['value'], $selection_array, true );
			$pro_only = (isset( $select_option['pro'] ) && $select_option['pro']) ? ' sbspf_pro_only' : '';
			$class = ! empty( $select_option['class'] ) ? ' ' . $select_option['class'] : '';
			?>
            <div class="sbspf_multi_checkbox_option<?php echo $pro_only . $class; ?>">
                <input name="<?php echo $this->option_name .'['.esc_attr( $args['name'] ).'][]'; ?>" id="<?php echo $this->option_name . '_' . $args['name']. '_' . $index; ?>" value="<?php echo esc_attr( $select_option['value'] ); ?>" type="checkbox"<?php if ( $selected ) echo ' checked'; ?>/>
                <label for="<?php echo $this->option_name . '_' . $args['name'] . '_' . $index; ?>"><?php echo esc_html( $select_option['label'] ); ?></label>
            </div>
			<?php
			$index++;
		endforeach; ?>

		<?php if ( isset( $args['tooltip_info'] ) ) : ?>
            <a class="sbspf_tooltip_link" href="JavaScript:void(0);"><?php echo $tooltip_text; ?></a>
            <p class="sbspf_tooltip sbspf_more_info"><?php echo $args['tooltip_info']; ?></p>
		<?php
		endif;
	}

	public function text( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value = isset( $this->settings[ $args['name'] ] ) ? $this->settings[ $args['name'] ] : $default;
		$size = ( isset( $args['size'] ) ) ? ' size="'. $args['size'].'"' : '';
		$class = isset( $args['class'] ) ? ' class="'. esc_attr( $args['class'] ) . '"' : '';

		$tooltip_text = isset( $args['tooltip_text'] ) ? $args['label'] : $this->default_tooltip_text();

		if ( isset( $args['min'] ) ) :
			$min = ( isset( $args['min'] ) ) ? ' min="'. $args['min'].'"' : '';
			$max = ( isset( $args['max'] ) ) ? ' max="'. $args['max'].'"' : '';
			$step = ( isset( $args['step'] ) ) ? ' step="'. $args['step'].'"' : '';
			$class = isset( $args['class'] ) ? ' class="sbspf_number_field sbspf_size_' . $args['size'] . ' '. esc_attr( $args['class'] ) . '"' : ' class="sbspf_number_field sbspf_size_' . $args['size'] . '"';
			?>
            <input name="<?php echo $this->option_name.'['.$args['name'].']'; ?>" id="<?php echo $this->option_name . '_' . $args['name']; ?>"<?php echo $class; ?> type="number"<?php echo $size; ?><?php echo $min; ?><?php echo $max; ?><?php echo $step; ?> value="<?php echo esc_attr( $value ); ?>" />
		<?php elseif ( isset( $args['color'] ) ) : ?>
            <input name="<?php echo $this->option_name.'['.$args['name'].']'; ?>" id="<?php echo $this->option_name . '_' . $args['name']; ?>" class="sbspf_colorpicker" type="text" value="#<?php echo esc_attr( str_replace('#', '', $value ) ); ?>" />
		<?php else: ?>
            <input name="<?php echo $this->option_name.'['.$args['name'].']'; ?>" id="<?php echo $this->option_name . '_' . $args['name']; ?>"<?php echo $class; ?> type="text" value="<?php echo esc_attr( stripslashes( $value ) ); ?>" />
		<?php endif; ?>

		<?php if ( isset( $args['select_options'] ) ) :
			$value = isset( $this->settings[ $args['select_name'] ] ) ? $this->settings[ $args['select_name'] ] : $args['select_options'][0]['value'];
			?>
            <select name="<?php echo $this->option_name.'['.$args['select_name'].']'; ?>" id="<?php echo $this->option_name . '_' . $args['select_name']; ?>">
				<?php foreach ( $args['select_options'] as $select_option ) : ?>
                    <option value="<?php echo esc_attr( $select_option['value'] ); ?>"<?php if ( (string)$select_option['value'] === (string)$value ) echo ' selected'; ?>><?php echo esc_html( $select_option['label'] ); ?></option>
				<?php endforeach; ?>
            </select>
		<?php endif; ?>

		<?php if ( isset( $args['hidden'] ) ) : ?>

			<?php
			if ( is_callable( array( $this, $args['hidden']['callback'] ) ) ){
				echo $args['hidden']['before'];
				call_user_func_array(
					array( $this, $args['hidden']['callback'] ),
					array( $args['hidden'] )
				);
				echo $args['hidden']['after'];
			}
			?>
		<?php endif; ?>

		<?php if ( isset( $args['additional'] ) ) : ?>
			<?php echo $args['additional']; ?>
		<?php endif; ?>

		<?php if ( isset( $args['tooltip_info'] ) ) : ?>
            <a class="sbspf_tooltip_link" href="JavaScript:void(0);"><?php echo $tooltip_text; ?></a>
            <p class="sbspf_tooltip sbspf_more_info"><?php echo $args['tooltip_info']; ?></p>
		<?php
		endif;

		if ( false/*$args['name'] === 'num'*/ ) :
        ?>
        <div style="margin: 8px 0 0 1px; font-size: 12px;" class="cff-load-more-setting">
            <input type="checkbox" name="cff_show_num_mobile" id="cff_show_num_mobile">&nbsp;<label for="cff_show_num_mobile">Show different number for mobile</label>
            <div class="cff-mobile-col-settings" style="">
                <div class="cff-row">
                    <label title="Click for shortcode option">Mobile Number:</label><code class="cff_shortcode"> nummobile
                        Eg: nummobile=4</code>
                    <input type="text" name="cff_num_mobile" id="cff_num_mobile" size="4" value="">
                    <i style="color: #666; font-size: 11px;">Leave blank for default</i>
                </div>
            </div>
        </div>
            <?php
        endif;
	}

	public function select( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : $args['options'][0]['value'];
		$value = isset( $this->settings[ $args['name'] ] ) ? $this->settings[ $args['name'] ] : $default;

		if ( isset( $args['min'] ) && isset( $args['max'] ) && ((int)$args['min'] < (int)$args['max']) && empty( $args['options'] ) ) {
			$args['options'] = array();
			$i = (int)$args['min'];

			while ( $i <= (int)$args['max'] ) {
				$args['options'][] = array(
					'label' => $i,
					'value' => $i
				);
				$i++;
			}
		}

		$tooltip_text = isset( $args['tooltip_text'] ) ? $args['label'] : $this->default_tooltip_text();
		?>
        <select name="<?php echo $this->option_name.'['.$args['name'].']'; ?>" id="<?php echo $this->option_name . '_' . $args['name']; ?>">
			<?php foreach ( $args['options'] as $select_option ) : ?>
                <option value="<?php echo esc_attr( $select_option['value'] ); ?>"<?php if ( (string)$select_option['value'] === (string)$value ) echo ' selected'; ?>><?php echo esc_html( $select_option['label'] ); ?></option>
			<?php endforeach; ?>
        </select>

		<?php if ( isset( $args['additional'] ) ) : ?>
			<?php echo $args['additional']; ?>
		<?php endif; ?>

		<?php if ( isset( $args['tooltip_info'] ) ) : ?>
            <a class="sbspf_tooltip_link" href="JavaScript:void(0);"><?php echo $tooltip_text; ?></a>
            <p class="sbspf_tooltip sbspf_more_info"><?php echo $args['tooltip_info']; ?></p>
		<?php endif;
	}

	public function textarea( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value = isset( $this->settings[ $args['name'] ] ) ? stripslashes( $this->settings[ $args['name'] ] ) : $default;

		if ( isset( $args['tooltip_info'] ) ) : ?>
            <span><?php echo $args['tooltip_info']; ?></span><br>
		<?php endif; ?>

        <textarea name="<?php echo $this->option_name.'['.$args['name'].']'; ?>" id="<?php echo $this->option_name . '_' . $args['name']; ?>"rows="7"><?php echo $value; ?></textarea>

		<?php if ( isset( $args['note'] ) ) : ?>
            <br><span class="sbspf_note"><?php echo $args['note']; ?></span>
		<?php endif;
	}

	public function color( $args ) {
		$args['color'] = true;
		$this->text( $args );
	}

	public function cache( $args ) {
		$social_network = 'Social Wall';
		$caching_time = isset( $this->settings['caching_time'] ) ? $this->settings['caching_time'] : 1;
		$cache_time_unit_selected = isset( $this->settings['cache_time_unit'] ) ? $this->settings['cache_time_unit'] : 'hours';
		$cache_cron_interval_selected = isset( $this->settings['cache_cron_interval'] ) ? $this->settings['cache_cron_interval'] : '';
		$cache_cron_time = isset( $this->settings['cache_cron_time'] ) ? $this->settings['cache_cron_time'] : '';
		$cache_cron_am_pm = isset( $this->settings['cache_cron_am_pm'] ) ? $this->settings['cache_cron_am_pm'] : '';
		?>
        <div class="sbspf_cache_settings_wrap">

            <div class="sbspf_row sbspf-caching-cron-options" style="display: block;">

                <select name="<?php echo $this->option_name.'[cache_cron_interval]'; ?>" id="sbspf_cache_cron_interval">
                    <option value="30mins"<?php if ( $cache_cron_interval_selected === '30mins' ) echo ' selected'?>><?php _e ( 'Every 30 minutes', $this->vars->text_domain() ); ?></option>
                    <option value="1hour"<?php if ( $cache_cron_interval_selected === '1hour' ) echo ' selected'?>><?php _e ( 'Every hour', $this->vars->text_domain() ); ?></option>
                    <option value="12hours"<?php if ( $cache_cron_interval_selected === '12hours' ) echo ' selected'?>><?php _e ( 'Every 12 hours', $this->vars->text_domain() ); ?></option>
                    <option value="24hours"<?php if ( $cache_cron_interval_selected === '24hours' ) echo ' selected'?>><?php _e ( 'Every 24 hours', $this->vars->text_domain() ); ?></option>
                </select>

                <div id="sbspf-caching-time-settings" style="">
					<?php _e ( 'at', $this->vars->text_domain() ); ?>
                    <select name="<?php echo $this->option_name.'[cache_cron_time]'; ?>" style="width: 80px">
                        <option value="1"<?php if ( (int)$cache_cron_time === 1 ) echo ' selected'?>>1:00</option>
                        <option value="2"<?php if ( (int)$cache_cron_time === 2 ) echo ' selected'?>>2:00</option>
                        <option value="3"<?php if ( (int)$cache_cron_time === 3 ) echo ' selected'?>>3:00</option>
                        <option value="4"<?php if ( (int)$cache_cron_time === 4 ) echo ' selected'?>>4:00</option>
                        <option value="5"<?php if ( (int)$cache_cron_time === 5 ) echo ' selected'?>>5:00</option>
                        <option value="6"<?php if ( (int)$cache_cron_time === 6 ) echo ' selected'?>>6:00</option>
                        <option value="7"<?php if ( (int)$cache_cron_time === 7 ) echo ' selected'?>>7:00</option>
                        <option value="8"<?php if ( (int)$cache_cron_time === 8 ) echo ' selected'?>>8:00</option>
                        <option value="9"<?php if ( (int)$cache_cron_time === 9 ) echo ' selected'?>>9:00</option>
                        <option value="10"<?php if ( (int)$cache_cron_time === 10 ) echo ' selected'?>>10:00</option>
                        <option value="11"<?php if ( (int)$cache_cron_time === 11 ) echo ' selected'?>>11:00</option>
                        <option value="0"<?php if ( (int)$cache_cron_time === 0 ) echo ' selected'?>>12:00</option>
                    </select>

                    <select name="<?php echo $this->option_name.'[cache_cron_am_pm]'; ?>" style="width: 60px">
                        <option value="am"<?php if ( $cache_cron_am_pm === 'am' ) echo ' selected'?>><?php _e ( 'AM', $this->vars->text_domain() ); ?></option>
                        <option value="pm"<?php if ( $cache_cron_am_pm === 'pm' ) echo ' selected'?>><?php _e ( 'PM', $this->vars->text_domain() ); ?></option>
                    </select>
                </div>

				<?php
				if ( wp_next_scheduled( 'sbsw_feed_update' ) ) {
					$time_format = get_option( 'time_format' );
					if ( ! $time_format ) {
						$time_format = 'g:i a';
					}
					//
					$schedule = wp_get_schedule( 'sbsw_feed_update' );
					if ( $schedule == '30mins' ) $schedule = __( 'every 30 minutes', $this->vars->text_domain() );
					if ( $schedule == 'twicedaily' ) $schedule = __( 'every 12 hours', $this->vars->text_domain() );
					$sbspf_next_cron_event = wp_next_scheduled( 'sbsw_feed_update' );
					echo '<p class="sbspf-caching-sched-notice"><span><b>' . __( 'Next check', $this->vars->text_domain() ) . ': ' . date( $time_format, $sbspf_next_cron_event + sbsw_get_utc_offset() ) . ' (' . str_replace( 'sw', '', $schedule ) . ')</b> - ' . __( 'Note: Saving the settings on this page will clear the cache and reset this schedule', $this->vars->text_domain() ) . '</span></p>';
				} else {
					echo '<p style="font-size: 11px; color: #666;">' . __( 'Nothing currently scheduled', $this->vars->text_domain() ) . '</p>';
				}
				?>
            </div>
        </div>
		<?php
	}

	public function layout( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : $args['layouts'][0]['slug'];
		$value = isset( $this->settings[ $args['name'] ] ) ? $this->settings[ $args['name'] ] : $default;
		?>
        <div class="sbspf_layouts">
			<?php foreach ( $args['layouts'] as $layout ) : ?>
                <div class="sbspf_layout_cell">
                    <input class="sbspf_layout_type" id="sbspf_layout_type_<?php echo esc_attr( $layout['slug'] ); ?>" name="<?php echo $this->option_name.'['.$args['name'].']'; ?>" type="radio" value="<?php echo esc_attr( $layout['slug'] ); ?>"<?php if ( $layout['slug'] === $value ) echo ' checked'?>><label for="sbspf_layout_type_<?php echo esc_attr( $layout['slug'] ); ?>"><span class="sbspf_label"><?php echo $layout['label']; ?></span><img src="<?php echo esc_url( $this->vars->plugin_url() . $layout['image'] ); ?>"></label>
                </div>
			<?php endforeach; ?>

            <div class="sbspf_layout_options_wrap">
				<?php foreach ( $args['layouts'] as $layout ) : ?>
                    <div class="sbspf_layout_settings sbspf_layout_type_<?php echo esc_attr( $layout['slug'] ); ?>">

                        <div class="sbspf_layout_setting">
							<?php echo sbsw_admin_icon( 'info-circle' ); ?>&nbsp;&nbsp;&nbsp;<span class="sbspf_note" style="margin-left: 0;"><?php echo $layout['note']; ?></span>
                        </div>
						<?php if ( ! empty( $layout['options'] ) ) : ?>
							<?php foreach ( $layout['options'] as $option ) :
								$option['name'] = $layout['slug'].$option['name'];
								?>
                                <div class="sbspf_layout_setting">
									<?php if ( $option['callback'] !== 'checkbox' ) : ?>
                                        <label title="<?php echo __( 'Click for shortcode option', $this->vars->text_domain() ); ?>"><?php echo $option['label']; ?></label><code class="sbspf_shortcode"> <?php echo $option['name'] . "\n"; ?>
                                            Eg: <?php echo $option['name']; ?>=<?php echo $option['shortcode']['example']; ?></code><br>
									<?php else:
										$option['shortcode_example'] = $option['shortcode']['example'];
										$option['has_shortcode'] = true;
									endif; ?>
									<?php call_user_func_array( array( $this, $option['callback'] ), array( $option ) ); ?>

                                </div>

							<?php endforeach; ?>
						<?php endif; ?>

                    </div>

				<?php endforeach; ?>
            </div>
        </div>
		<?php
	}

	public function format_title( $label, $name, $shortcode_args = false, $after = '' ) {
		$formatted_label = '<label for="' . $this->option_name . '_' . $name . '">' . $label .'</label>';
		if ( $shortcode_args ) {
			$formatted_label .= '<code class="sbspf_shortcode"> ' . $shortcode_args['key'] . "\n";
			$formatted_label .= 'Eg: ' . $shortcode_args['key'] . '=' . $shortcode_args['example'] . '</code><br>';
		}
		$formatted_label .= $after;

		return $formatted_label;
	}

	public function validate_options( $input, $tab ) {
		$updated_options = get_option( $this->option_name, array() );
		$false_if_empty_keys = $this->get_false_fields( $tab );
		$textarea_keys = $this->get_textarea_fields( $tab );

		foreach ( $false_if_empty_keys as $false_key ) {
			$updated_options[ $false_key ] = false;
		}

		foreach ( $input as $key => $val ) {
			if ( in_array( $key, $false_if_empty_keys ) ) {
				$updated_options[ $key ] = ($val === 'on');
			} elseif ( in_array( $key, $textarea_keys ) ) {
				$updated_options[ $key ] = sanitize_textarea_field( $val );
			} elseif ( is_array( $val ) ) {
				$updated_options[ $key ] = array();
				foreach ( $val as $key2 => $val2 ) {
					$updated_options[ $key ][ $key2 ] = sanitize_text_field( $val2 );
				}
			} else {
				$updated_options[ $key ] = sanitize_text_field( $val );
			}
		}

		if ( $tab === 'configure' ) {
			do_action( $this->option_name . '_after_configure_save', $updated_options );
		} elseif ( $tab === 'customize' ) {
			do_action( $this->option_name . '_after_customize_save', $updated_options );
		}

		return $updated_options;
	}


	public function update_options( $new_settings ) {
		update_option( $this->get_option_name(), $new_settings );
		$this->settings = $new_settings;
	}

	public function get_sections( $tab ) {
		if ( isset( $this->settings_sections[ $tab ] ) ) {
			return $this->settings_sections[ $tab ];
		}
		return array();
	}

	public function create_menus() {

		add_menu_page(
			'Social Wall',
			'Social Wall',
			$this->capability,
			$this->slug,
			array( $this, 'create_options_page' ),
			$this->icon,
			$this->position
		);

		$installed_plugins = Helpers::get_installed_plugins_for_sw_menu();
		$active_plugins = Helpers::get_active_plugins_for_sw_menu();

		$capability = current_user_can( 'manage_social_wall_options' ) ? 'manage_social_wall_options' : 'manage_options';

		//Change the menu links based on whether the plugin is installed or not
		$sbsw_sbi_menu_text = '<span class="sbsw_sbi_menu">Instagram Feed</span>';
		$sbsw_sbi_menu_link = 'sb-instagram-feed';
		if ( ! isset( $installed_plugins['is_instagram_installed'] ) ) {
			$sbsw_sbi_menu_text = '<span class="sbsw_plugin_missing">Instagram Feed</span>';
			$sbsw_sbi_menu_link = 'https://smashballoon.com/instagram-feed/';
		} else if ( ! isset( $active_plugins['is_instagram_active'] ) ) {
			$sbsw_sbi_menu_text = '<span class="sbsw_plugin_inactive">Instagram Feed</span>';
			$sbsw_sbi_menu_link = 'https://smashballoon.com/instagram-feed/';
		} else {
		    if ( version_compare( SBIVER, '6.0', '>=' ) ) {
			    $sbsw_sbi_menu_link = admin_url( 'admin.php?page=sbi-feed-builder' );
            }
        }

		$sbsw_cff_menu_text = '<span class="sbsw_cff_menu">Facebook Feed</span>';
		$sbsw_cff_menu_link = defined( 'CFFVER' ) && version_compare(CFFVER, 4.0, '<' ) ? admin_url( 'admin.php?page=cff-top' ) : admin_url( 'admin.php?page=cff-feed-builder' );
		if ( ! isset( $installed_plugins['is_facebook_installed'] ) ) {
			$sbsw_cff_menu_text = '<span class="sbsw_plugin_missing">Facebook Feed</span>';
			$sbsw_cff_menu_link = 'https://smashballoon.com/custom-facebook-feed/';
		} else if ( ! isset( $active_plugins['is_facebook_active'] ) ) {
			$sbsw_cff_menu_text = '<span class="sbsw_plugin_inactive">Facebook Feed</span>';
			$sbsw_cff_menu_link = 'https://smashballoon.com/custom-facebook-feed/';
		}

		$sbsw_ctf_menu_text = '<span class="sbsw_ctf_menu">Twitter Feed</span>';
		$sbsw_ctf_menu_link = 'custom-twitter-feeds';
		if ( ! isset( $installed_plugins['is_twitter_installed'] ) ) {
			$sbsw_ctf_menu_text = '<span class="sbsw_plugin_missing">Twitter Feed</span>';
			$sbsw_ctf_menu_link = 'https://smashballoon.com/custom-twitter-feeds/';
		} else if ( ! isset( $active_plugins['is_twitter_active'] ) ) {
			$sbsw_ctf_menu_text = '<span class="sbsw_plugin_inactive">Twitter Feed</span>';
			$sbsw_ctf_menu_link = 'https://smashballoon.com/custom-twitter-feeds/';
		} else{
			if ( version_compare( CTF_VERSION, '2.0', '>=' ) ) {
			    $sbsw_ctf_menu_link = admin_url( 'admin.php?page=ctf-feed-builder' );
            }
		}

		$sbsw_yt_menu_text = '<span class="sbsw_yt_menu">YouTube Feed</span>';
		$sbsw_yt_menu_link = 'youtube-feed';
		if ( ! isset( $installed_plugins['is_youtube_installed'] ) ) {
			$sbsw_yt_menu_text = '<span class="sbsw_plugin_missing">YouTube Feed</span>';
			$sbsw_yt_menu_link = 'https://smashballoon.com/youtube-feed/';
		} else if ( ! isset( $active_plugins['is_youtube_active'] ) ) {
			$sbsw_yt_menu_text = '<span class="sbsw_plugin_inactive">YouTube Feed</span>';
			$sbsw_yt_menu_link = 'https://smashballoon.com/youtube-feed/';
		} else {
			if ( version_compare( SBYVER, '2.0', '>=' ) ) {
				$sbsw_yt_menu_link = admin_url( 'admin.php?page=sby-feed-builder' );
			}
		}

		$sbsw_tt_menu_text = '<span class="sbsw_tt_menu">TikTok Feeds</span>';
		$sbsw_tt_menu_link = 'tiktok-feeds';
		if (!isset($installed_plugins['is_tiktok_installed'])) {
			$sbsw_tt_menu_text = '<span class="sbsw_plugin_missing">TikTok Feeds</span>';
			$sbsw_tt_menu_link = 'https://smashballoon.com/tiktok-feeds/';
		} elseif (!isset($active_plugins['is_tiktok_active'])) {
			$sbsw_tt_menu_text = '<span class="sbsw_plugin_inactive">TikTok Feeds</span>';
			$sbsw_tt_menu_link = 'https://smashballoon.com/tiktok-feeds/';
		} else {
			if (sbsw_tiktok_feeds_is_minimum_version()) {
				$sbsw_tt_menu_link = admin_url('admin.php?page=sbtt');
			}
		}

		add_submenu_page(
			'sbsw',
			'Social Wall',
			'Social Wall',
			$capability,
			$this->slug
		);
		add_submenu_page(
	        'sbsw',
	        'Instagram Feed',
	        $sbsw_sbi_menu_text,
	        $capability,
	        $sbsw_sbi_menu_link
	    );
		add_submenu_page(
	        'sbsw',
	        'Facebook Feed',
	        $sbsw_cff_menu_text,
	        $capability,
	        $sbsw_cff_menu_link
	    );
		add_submenu_page(
	        'sbsw',
	        'Twitter Feed',
	        $sbsw_ctf_menu_text,
	        $capability,
	        $sbsw_ctf_menu_link
	    );
		add_submenu_page(
	        'sbsw',
	        'YouTube Feed',
	        $sbsw_yt_menu_text,
	        $capability,
	        $sbsw_yt_menu_link
	    );
		add_submenu_page(
			'sbsw',
			'TikTok Feed',
			$sbsw_tt_menu_text,
			$capability,
			$sbsw_tt_menu_link
		);

		// //Hide the other plugin menus
		remove_menu_page('sb-instagram-feed');
		remove_menu_page('cff-top');
		remove_menu_page('custom-twitter-feeds');
		remove_menu_page('sbtt');
	    //YouTube menu hidden with CSS: sbsw_hide_yt_menu()

		$this->after_create_menues();
	}

	//Hide the YouTube plugin menu
	public function sbsw_hide_yt_menu(){
		echo '<style>';
		echo '#adminmenu li.menu-top.toplevel_page_youtube-feed{ display: none !important; }';
		echo '#adminmenu li.menu-top.toplevel_page_sby-feed-builder{ display: none !important; }';
		echo 'ul#adminmenu .toplevel_page_sbsw a.wp-has-current-submenu:after, .toplevel_page_sbsw ul#adminmenu>li.current>a.current:after{ z-index: 10001; }';
		echo '</style>';
	}

	public function after_create_menues() {

	}

	public function set_active_tab( $active_tab ) {
		foreach ( $this->tabs as $tab ) {
			if ( $tab['slug'] === $active_tab ) {
				$this->active_tab = $tab['slug'];
			}
		}
	}

	public function get_tabs() {
		return $this->tabs;
	}

	public function get_active_tab() {
		return $this->active_tab;
	}

	public function get_slug() {
		return $this->slug;
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_path( $view ) {
		return trailingslashit( $this->base_path ) . $view . '.php';
	}

	public function create_options_page() {
		wp_enqueue_script( 'sbsw-main-script' );
		wp_enqueue_style( 'sbsw-main-style' );
		echo '<div id="sbsw-app"></div>';
	}

	public function next_step() {
		$return = array();
		$i = 0;
		foreach ( $this->tabs as $tab ) {
			if ( $this->active_tab === $tab['slug'] && isset( $tab['next_step_instructions'] ) ) {
				$next_tab_slug = isset( $this->tabs[ $i + 1 ]['slug'] ) ? $this->tabs[ $i + 1 ]['slug'] : $tab['slug'];
				$return = array(
					'instructions' => $tab['next_step_instructions'],
					'next_tab' => $next_tab_slug
				);
			}
			$i++;
		}
		return $return;
	}

	public function sub_option( $args ) {
		$value = isset( $this->settings[ $args['name'] ] ) ? $this->settings[ $args['name'] ] : 'related';

		$cta_options = $args['sub_options'];
		?>
		<?php if ( ! empty( $args['before'] ) ) {
			echo $args['before'];
		}?>

        <div class="sbspf_sub_options">
			<?php foreach ( $cta_options as $sub_option ) : ?>
                <div class="sbspf_sub_option_cell">
                    <input class="sbspf_sub_option_type" id="sbspf_sub_option_type_<?php echo esc_attr( $sub_option['slug'] ); ?>" name="<?php echo $this->option_name.'['.$args['name'].']'; ?>" type="radio" value="<?php echo esc_attr( $sub_option['slug'] ); ?>"<?php if ( $sub_option['slug'] === $value ) echo ' checked'?>><label for="sbspf_sub_option_type_<?php echo esc_attr( $sub_option['slug'] ); ?>"><span class="sbspf_label"><?php echo $sub_option['label']; ?></span></label>
                </div>
			<?php endforeach; ?>

            <div class="sbspf_box_setting">
				<?php if ( isset( $cta_options ) ) : foreach ( $cta_options as $sub_option ) : ?>
                    <div class="sbspf_sub_option_settings sbspf_sub_option_type_<?php echo esc_attr( $sub_option['slug'] ); ?>">

                        <div class="sbspf_sub_option_setting">
							<?php echo sbsw_admin_icon( 'info-circle', 'sbspf_small_svg' ); ?>&nbsp;&nbsp;&nbsp;<span class="sbspf_note" style="margin-left: 0;"><?php echo $sub_option['note']; ?></span>
                        </div>
						<?php if ( ! empty( $sub_option['options'] ) ) : ?>
							<?php foreach ( $sub_option['options'] as $option ) :
								$option['name'] = $sub_option['slug'].$option['name'];
								?>
                                <div class="sbspf_sub_option_setting">
									<?php if ( $option['callback'] !== 'checkbox' ) :
										if ( isset( $option['shortcode'] ) ) : ?>
                                            <label title="<?php echo __( 'Click for shortcode option', $this->vars->text_domain() ); ?>"><?php echo $option['label']; ?></label><code class="sbspf_shortcode"> <?php echo $option['name'] . "\n"; ?>
                                                Eg: <?php echo $option['name']; ?>=<?php echo $option['shortcode']['example']; ?></code><br>
										<?php else: ?>
                                            <label><?php echo $option['label']; ?></label><br>
										<?php endif; ?>
									<?php else:
										$option['shortcode_example'] = $option['shortcode']['example'];
										$option['has_shortcode'] = true;
									endif; ?>
									<?php call_user_func_array( array( $this, $option['callback'] ), array( $option ) ); ?>

                                </div>

							<?php endforeach; ?>
						<?php endif; ?>

                    </div>

				<?php endforeach; endif; ?>
            </div>
        </div>
		<?php
	}

	public function date_format( $args ) {

		?>
        <div class="sbspf_setting_wrap">

		<?php
			$args['options'] = $args['date_formats'];
			$this->select( $args );
			$custom_value = isset( $this->settings['customdate'] ) ? stripslashes( $this->settings['customdate'] ) : '';
			?>
        </div>
        <div class="sbspf_box_settings">
            <div class="sbspf_box_setting sbsw_relativetext_wrap">
                <?php foreach ( $args['text_settings'] as $text_setting ) :
                    $value = isset( $this->settings[ $text_setting['key'] ] ) ? stripslashes( $this->settings[ $text_setting['key'] ] ) : $text_setting['default'];

                    ?>
                <div class="sbsw-date-text-setting-wrap">
                    <label><?php echo $text_setting['label']; ?></label>
                    <input name="sbsw_settings[<?php echo $text_setting['key']; ?>]" id="sbsw_settings_<?php echo $text_setting['key']; ?>" type="text" placeholder="<?php echo $text_setting['default']; ?>" value="<?php echo esc_attr( $value ); ?>">
                </div>
                <?php endforeach; ?>

            </div>
            <div class="sbspf_box_setting sbsw_customdate_wrap">
                <label><?php _e( 'Custom Format', SBSW_TEXT_DOMAIN ); ?></label><br>
                <input name="sbsw_settings[customdate]" id="sbsw_settings_customdate" type="text" placeholder="F j, Y g:i a" value="<?php echo esc_attr( $custom_value ); ?>"><a href="https://smashballoon.com/social-wall/docs/date/" class="sbspf-external-link sbspf_note" target="_blank"><?php _e( 'Examples', SBSW_TEXT_DOMAIN ); ?></a>
            </div>
        </div>
		<?php
	}

	public function default_tooltip_text() {
		return '<span class="screen-reader-text">' . __( 'What does this mean?', $this->vars->text_domain() ) . '</span>' . sbsw_admin_icon( 'question-circle' );
	}

}
