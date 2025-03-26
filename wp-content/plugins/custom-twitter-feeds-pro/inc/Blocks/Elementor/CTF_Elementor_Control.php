<?php

namespace TwitterFeed\Blocks\Elementor;

use \Elementor\Base_Data_Control;
use \Elementor\Controls_Manager;

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class CTF_Elementor_Control extends Base_Data_Control {

	public function get_type(){
		return 'ctf_feed_control';
	}

	public function enqueue(){
		wp_enqueue_style(
			'sby-elementor-style',
			CTF_PLUGIN_URL . 'css/sb-elementor.css' ,
			null,
			CTF_VERSION
		);
	}

	protected function get_default_settings(){
		return [
			'label_block' => false
		];
	}

	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
			<div class="elementor-control-field">
				<# if ( data.label ) {#>
					<label for="<?php echo $control_uid; ?>" class="elementor-control-title">{{{ data.label }}}</label>
				<# } #>
				<div class="elementor-control-input-wrapper elementor-control-unit-5">
					<select id="<?php echo $control_uid; ?>" data-setting="{{ data.name }}" onchange="jQuery(this).parents('.elementor-control-field').find('.link-ctf-builder').attr('href', '<?php echo admin_url( 'admin.php?page=ctf-feed-builder' ) ?>&feed_id='+jQuery(this).val())">
					<#
						var printOptions = function( options ) {
							_.each( options, function( option_title, option_value ) { #>
									<option value="{{ option_value }}">{{{ option_title }}}</option>
							<# } );
						};

						if ( data.groups ) {
							for ( var groupIndex in data.groups ) {
								var groupArgs = data.groups[ groupIndex ];
									if ( groupArgs.options ) { #>
										<optgroup label="{{ groupArgs.label }}">
											<# printOptions( groupArgs.options ) #>
										</optgroup>
									<# } else if ( _.isString( groupArgs ) ) { #>
										<option value="{{ groupIndex }}">{{{ groupArgs }}}</option>
									<# }
							}
						} else {
							printOptions( data.options );
						}
					#>
					</select>
					<div style="font-weight: 700; color:#a73061; margin-top: 10px;">
						<# if( data.controlValue != undefined && data.controlValue != '' ) { #>
							<a class="link-ctf-builder" href="<?php echo admin_url( 'admin.php?page=ctf-feed-builder' ) ?>&feed_id={{data.controlValue}}" target="_blank"><?php echo __('Edit this Feed', 'custom-twitter-feeds'); ?></a>
							<span style="color:#aaa; display: inline-block; margin: 0 5px;">|</span>
						<# }  #>
						<a href="<?php echo admin_url( 'admin.php?page=ctf-feed-builder' ) ?>" target="_blank"><?php echo __('Create New Feed', 'custom-twitter-feeds'); ?></a>
					</div>
				</div>
			</div>
			<# if ( data.description ) { #>
				<div class="elementor-control-field-description">{{{ data.description }}}</div>
			<# } #>

		<?php
	}

}