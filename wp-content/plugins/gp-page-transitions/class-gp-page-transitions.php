<?php

if ( ! class_exists( 'GP_Plugin' ) ) {
	return;
}

class GP_Page_Transitions extends GP_Plugin {

	private static $instance = null;

	protected $_version     = GP_PAGE_TRANSITIONS_VERSION;
	protected $_path        = 'gp-page-transitions/gp-page-transitions.php';
	protected $_full_path   = __FILE__;
	protected $_slug        = 'gp-page-transitions';
	protected $_title       = 'Gravity Forms Page Transitions';
	protected $_short_title = 'Page Transitions';

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function minimum_requirements() {
		return array(
			'gravityforms' => array(
				'version' => '2.4',
			),
			'wordpress'    => array(
				'version' => '4.8',
			),
			'plugins'      => array(
				'gravityperks/gravityperks.php' => array(
					'name'    => 'Gravity Perks',
					'version' => '2.2.3',
				),
			),
		);
	}

	public function init() {

		parent::init();

		add_action( 'gform_form_args', array( $this, 'force_ajax_mode' ) );

		if ( class_exists( 'GFCommon' ) && GFCommon::is_preview() ) {
			add_action( 'wp', array( $this, 'handle_core_preview_ajax' ), 9 );
		}

		add_filter( 'gform_pre_render', array( $this, 'pre_render' ), 11 ); // allows form to be more easily modified by users before init script is added
		add_filter( 'gform_validation_message', array( $this, 'add_submission_result_script_block' ), 99, 2 );
		add_filter( 'gform_confirmation_anchor', array( $this, 'suppress_form_anchor' ), 10, 2 );
		add_filter( 'gform_progress_bar', array( $this, 'modify_progress_bar' ), 10, 3 );
		add_filter( 'gform_input_mask_script', array( $this, 'enable_auto_progress_for_input_masks' ), 10, 4 );
		add_action( 'gform_register_init_scripts', array( $this, 'enable_auto_progress_for_phone_input_mask' ) );
		add_filter( 'gppt_script_args', array( $this, 'disable_soft_validation_if_cc_field_present' ), 10, 2 );

	}

	public function init_admin() {
		GWPerks::enqueue_field_settings();

		add_action( 'gform_editor_js', array( $this, 'form_editor_settings' ) );
		add_action( 'gws_field_settings', array( $this, 'field_settings_ui' ) );
	}

	public function tooltips( $tooltips ) {
		$tooltips['gppt_enable_page_transitions'] = sprintf( '<h6>%s</h6> %s', __( 'Page Transitions', 'gp-page-transitions' ), __( 'Enable animated transitions when navigating from page to page.', 'gp-page-transitions' ) );
		$tooltips['gppt_enable_auto_progress']    = sprintf( '<h6>%s</h6> %s', __( 'Auto-progression', 'gp-page-transitions' ), __( 'Automatically progress to the next page when the last field of the current page has been completed. Some field types do not support auto-progression.', 'gp-page-transitions' ) );
		$tooltips['gppt_enable_soft_validation']  = sprintf( '<h6>%s</h6> %s', __( 'Soft Validation', 'gp-page-transitions' ), __( 'Provides the smoothest experience. Pages are not submitted as you progress through the form. Required fields are validated on the frontend to catch obvious mistakes. Full validation for all pages is processed on the final page submission.', 'gp-page-transitions' ) );
		$tooltips['gppt_transition_style']        = sprintf( '<h6>%s</h6> %s', __( 'Transition Style', 'gp-page-transitions' ), __( 'Select the desired transition style. <b>Slide</b> will scroll the form pages horizontally. <b>Fade</b> will fade the current page out before fading the next page in.', 'gp-page-transitions' ) );
		$tooltips['gppt_hide_buttons']            = sprintf( '<h6>%s</h6> %s', __( 'Hide Next/Prev Buttons', 'gp-page-transitions' ), __( 'Check the corresponding checkbox to hide the next and previous buttons when auto-progression is enabled.', 'gp-page-transitions' ) );

		return $tooltips;
	}

	public function scripts() {
		$asset_file_core        = include( plugin_dir_path( __FILE__ ) . 'js/built/gp-page-transitions-swiper.asset.php' );
		$asset_file_swiper      = include( plugin_dir_path( __FILE__ ) . 'js/built/gp-page-transitions-swiper.asset.php' );
		$asset_file_swiper_full = include( plugin_dir_path( __FILE__ ) . 'js/built/gp-page-transitions-swiper-full.asset.php' );

		$scripts = array(
			array(
				'handle'  => 'gp-page-transitions-swiper',
				'src'     => $this->get_base_url() . '/js/built/gp-page-transitions-swiper.js',
				'deps'    => $asset_file_swiper['dependencies'],
				'version' => $asset_file_swiper['version'],
				'enqueue' => array(
					array( $this, 'should_enqueue_swiper' ),
				),
			),
			array(
				'handle'  => 'gp-page-transitions-swiper-full',
				'src'     => $this->get_base_url() . '/js/built/gp-page-transitions-swiper-full.js',
				'deps'    => $asset_file_swiper_full['dependencies'],
				'version' => $asset_file_swiper_full['version'],
				'enqueue' => array(
					array( $this, 'should_enqueue_swiper_full' ),
				),
			),
			array(
				'handle'  => 'gp-page-transitions',
				'src'     => $this->get_base_url() . '/js/built/gp-page-transitions.js',
				'deps'    => $asset_file_core['dependencies'],
				'version' => $asset_file_core['version'],
				'enqueue' => array(
					array( $this, 'is_any_feature_enabled' ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gp-page-transitions',
				'src'     => $this->get_base_url() . '/styles/gp-page-transitions.css',
				'enqueue' => array(
					array( $this, 'is_any_feature_enabled' ),
				),
			),
			array(
				'handle'  => 'gp-page-transitions-swiper-full',
				'src'     => $this->get_base_url() . '/styles/gp-page-transitions-swiper-full.css',
				'enqueue' => array(
					array( $this, 'should_enqueue_swiper_full' ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * @param array $form
	 *
	 * @return boolean
	 */
	public function should_enqueue_swiper( $form ) {
		if ( ! $this->is_any_feature_enabled( $form ) ) {
			return false;
		}

		/**
		 * Filter whether all of Swiper should be loaded instead of a smaller subset of features.
		 *
		 * @param bool $enable_extras Whether to load the full version of Swiper. Default: `false`.
		 * @param array $form The current form.
		 */
		return ! gf_apply_filters( array( 'gppt_enable_extras', $form['id'] ), false, $form );
	}

	/**
	 * @param array $form
	 *
	 * @return boolean
	 */
	public function should_enqueue_swiper_full( $form ) {
		if ( ! $this->is_any_feature_enabled( $form ) ) {
			return false;
		}

		// documented in GP_Page_Transitions::should_enqueue_frontend()
		return gf_apply_filters( array( 'gppt_enable_extras', $form['id'] ), false, $form );
	}

	public function pre_render( $form ) {

		// Class exists check is [mostly] for cron events such as GPNS.
		if ( ! $this->is_any_feature_enabled( $form ) || ! class_exists( 'GFFormDisplay' ) ) {
			return $form;
		}

		if ( $this->is_auto_progress_enabled( $form ) ) {
			$this->add_auto_progress_field_class( $form );
			$form = $this->hide_next_prev_buttons( $form );
		}

		$transition_style = rgar( $form, $this->perk->key( 'transitionStyle' ), 'scrollHorz' );

		/**
		 * Filter all of the properties that will be used to initialize the GP Page Transitions JS functionality.
		 *
		 * @since 1.0
		 *
		 * @param array $args {
		 *     An array of properties that will be used to initialize the GP Page Transitions JS functionality.
		 *
		 *     @type int  $formId                Current form ID.
		 *     @type bool $enablePageTransitions Flag indicating whether page transitions are enabled.
		 *     @type bool $enableAutoProgress    Flag indicating whether auto-progression is enabled.
		 *     @type bool $hideNextButton        Flag indicating whether next button should be hidden.
		 *     @type bool $hidePrevButton        Flag indicating whether previous button should be hidden.
		 *     @type bool $enableSoftValidation  Flag indicating whether soft validation is enabled.
		 *     @type array $validationSelectors {
		 *         An array of validation selector objects which control which inputs are validated and how.
		 *
		 *         @type int    $id                Field ID of the selector.
		 *         @type array  $selectors         An array of selector strings (i.e. '#input_1_2').
		 *         @type string $relation          Specifies how the validation should be applied. Should 'all' selectors have a value or does validation pass if 'any' selector has a value?
		 *         @type string $validationMessage Message to be displayed if field fails validation.
		 *     }
		 *     @type string $validationClass            Class(es) to be applied to the field container when a field fails validation.
		 *     @type string $validationMessageContainer Markup that will wrap the validation message. Must include "{0}" wherever the message should be included in the markup.
		 *     @type array  $submission {
		 *         The result of the submission. Used to reset the state of the GPPageTransitions JS object after a submission.
		 *
		 *         @type bool $hasError   Flag indicating whether the submission has an error.
		 *         @type int  $sourcePage Page number from which the form was submitted.
		 *         @type int  $errorPage  Page number on which the first field with an error resides.
		 *     }
		 *     @type array pagination {
		 *         An array of properties specific to how the form's pagination is configured.
		 *
		 *         @type string $type               The pagination type of the current form.
		 *         @type bool   $startAtZero        Flag indicating whether or not the progress bar should start at zero and only show 100% on the confirmation page.
		 *         @type int    $pageCount          The total number of pages on the current form.
		 *         @type array  $progressIndicators An array of the progress indicator markup (progress bar or steps) for each page of the form. This is only used if 'gppt_is_custom_pagination' filter is configured to return true.
		 *         @type array  $pages              An array of page names specified for the current form.
		 *         @type bool   $isCustom           Flag indicating whether or not the progress indicators are custom or standard.
		 *         @type array  $labels             An array of labels used to recreate Gravity Forms' standard page verbiage.
		 *     }
		 *     @type array $transitionSettings An array of parameters specific to Swiper. See https://swiperjs.com/swiper-api#parameters for more information.
		 * }
		 * @param array $form Current form object.
		 *
		 * @see http://gravitywiz.com/documentation/gppt_script_args
		 */
		$args = gf_apply_filters(
			array( 'gppt_script_args', $form['id'] ),
			array(
				'formId'                     => $form['id'],
				'hasConditionalLogic'        => GFFormDisplay::has_conditional_logic( $form ),
				'enablePageTransitions'      => $this->is_page_transitions_enabled( $form ),
				'enableAutoProgress'         => $this->is_auto_progress_enabled( $form ),
				'hideNextButton'             => rgar( $form, $this->perk->key( 'hideNextButton' ) ),
				'hidePrevButton'             => rgar( $form, $this->perk->key( 'hidePrevButton' ) ),
				'enableSoftValidation'       => $this->is_soft_validation_enabled( $form ),
				'validationSelectors'        => $this->get_validation_selectors( $form ),
				'validationClassForm'        => 'gform_validation_error',
				'validationClass'            => 'gfield_error gfield_contains_required',
				'validationMessageContainer' => "<div class='gfield_description validation_message gfield_validation_message'>{0}</div>",
				'submission'                 => $this->get_submission_result( $form ),
				'pagination'                 => $this->get_pagination_script_args( $form ),
				'progressBarStartAtZero'     => apply_filters( 'gform_progressbar_start_at_zero', rgars( $form, 'pagination/display_progressbar_on_confirmation' ), $form ),
				'transitionSettings'         => array(
					'effect'               => $transition_style,
					'speed'                => $transition_style == 'fade' ? 400 : 800,
					'direction'            => 'horizontal',
					'spaceBetween'         => 20,
					'observer'             => true,
					'observeSlideChildren' => true,
					'autoHeight'           => true, // disable to use tallest page as height for all slides
					'loop'                 => false,
					'allowTouchMove'       => false,
				),
				'skipSoftValidation'         => false,
			),
			$form
		);
		// @deprecated
		$args = apply_filters( 'gppt_scripts_args_' . $form['id'], $args, $form );

		$script = 'if( ! window["GPPageTransitions_' . $form['id'] . '"] ) { window["GPPageTransitions_' . $form['id'] . '"] = new GPPageTransitions( ' . json_encode( $args ) . ' ); } window["GPPageTransitions_' . $form['id'] . '"].init( currentPage );';
		GFFormDisplay::add_init_script( $form['id'], $this->perk->key( 'init' ), GFFormDisplay::ON_PAGE_RENDER, $script );

		if ( $this->is_page_transitions_enabled( $form ) ) {
			$form['cssClass'] = $this->perk->add_css_class( rgar( $form, 'cssClass' ), 'gppt-has-page-transitions gform-theme__no-reset--el' );
		}

		return $form;
	}

	public function get_pagination_script_args( $form ) {

		/**
		 * Filter the custom pagination flag to enable custom pagination support.
		 *
		 * @since 1.0
		 *
		 * @param bool  $is_custom Set to true to enable custom pagination support.
		 * @param array $form      Current form object.
		 *
		 * @see http://gravitywiz.com/documentation/gppt_is_custom_pagination/
		 */
		$is_custom = apply_filters( 'gppt_is_custom_pagination', false, $form );
		$is_custom = apply_filters( "gppt_is_custom_pagination_{$form['id']}", $is_custom, $form );

		$args = array(
			'type'               => rgars( $form, 'pagination/type' ),
			'startAtZero'        => apply_filters( 'gform_progressbar_start_at_zero', rgars( $form, 'pagination/display_progressbar_on_confirmation' ), $form ),
			'pageCount'          => $this->get_page_count( $form ),
			'progressIndicators' => $is_custom ? $this->get_all_progress_indicators( $form ) : array(),
			'pages'              => rgars( $form, 'pagination/pages' ),
			'isCustom'           => $is_custom,
			'labels'             => array(
				'step' => esc_html__( 'Step', 'gravityforms' ),
				'of'   => esc_html__( 'of', 'gravityforms' ),
			),
		);

		return $args;
	}

	/**
	 * Append a script block containing the submission result to the form validation message.
	 *
	 * The form validation message will only be output if there is an error once the form has actually been submitted.
	 * Since we're forcing AJAX submissions for all page-transition-enabled, we can be confident that this is the only
	 * scenario in which we will need an updated JS submission object.
	 *
	 * @param string $markup Default GF validation markup.
	 * @param array  $form   Current form object.
	 *
	 * @return string
	 */
	public function add_submission_result_script_block( $markup, $form ) {

		if ( ! $this->is_soft_validation_enabled( $form ) ) {
			return $markup;
		}

		$result = $this->get_submission_result( $form );
		if ( $result['hasError'] ) {
			$script  = sprintf( '<script type="text/javascript"> if( window["GPPageTransitions_%1$d"] ) { window["GPPageTransitions_%1$d"].submission = %2$s; } </script>', $form['id'], json_encode( $result ) );
			$markup .= $script;
		}

		return $markup;
	}

	public function get_validation_selectors( $form ) {

		$selectors = array();

		// selectors will be grouped by page; set empty array as base for each page index
		for ( $i = 1; $i <= $this->get_page_count( $form ); $i++ ) {
			$selectors[ $i ] = array();
		}

		foreach ( $form['fields'] as $field ) {

			if ( ! $field->isRequired ) {
				continue;
			}

			// intentionally uses 'gravityforms' domain so translations are automatically picked up here as well
			$validation_message = empty( $field->errorMessage ) ? __( 'This field is required.', 'gravityforms' ) : $field->errorMessage;
			$default_selector   = sprintf( '#input_%d_%d', $form['id'], $field->id );

			$selector = array(
				'id'                => $field->id,
				'selectors'         => array( $default_selector ),
				'relation'          => 'any',
				'validationMessage' => $validation_message,
			);

			switch ( $field->get_input_type() ) {
				case 'checkbox':
				case 'radio':
					$selector['selectors'] = array( sprintf( '%s input[type="%s"]', $default_selector, $field->get_input_type() ) );
					break;
				case 'likert':
					$selector['selectors'] = array( sprintf( '%s input[type="radio"]', $default_selector ) );
					break;
				case 'consent':
					$selector['selectors'] = array( sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 1 ) );
					break;
				case 'name':
					// GF only requires first and last name regardless of other enabled inputs (e.g. middle name)
					$selector['relation']  = 'all';
					$selector['selectors'] = array(
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 3 ), // first name
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 6 ), // last name
					);
					break;
				case 'address':
					$selector['relation']  = 'all';
					$selector['selectors'] = array(
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 1 ), // street address
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 3 ), // city
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 4 ), // state
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 5 ), // zip
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 6 ), // country
					);
					break;
				case 'date':
					if ( in_array( $field->dateType, array( 'datefield', 'datedropdown' ) ) ) {
						$selector['relation']  = 'all';
						$selector['selectors'] = array(
							sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 1 ), // month
							sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 3 ), // day
							sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 4 ), // year
						);
					}
					break;
				case 'time':
					$selector['relation']  = 'all';
					$selector['selectors'] = array(
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 1 ), // hour
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 3 ), // minute
						sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 4 ), // am/pm
					);
					break;
				case 'fileupload':
					if ( $field->multipleFiles ) {
						$selector['selectors'] = array(
							sprintf( '#field_%d_%d input[type="file"]', $form['id'], $field->id ),
						);
					}
					break;
				case 'email':
					if ( $field->emailConfirmEnabled ) {
						$selector['relation']    = 'all';
						$selector['selectors'][] = sprintf( '#input_%d_%d_%d', $form['id'], $field->id, 2 ); // confirm email
					}
					break;
				case 'list':
					$selector['relation']    = 'all';
					$selector['selectors']   = array( sprintf( '#field_%1$d_%2$d [name="input_%2$d[]"]', $form['id'], $field->id ) );
					$selector['bypassCache'] = true;
					break;
				case 'singleproduct':
					// Skip validation for a single product field without quantity.
					if ( $field->disableQuantity == true ) {
						continue 2;
					}
					$selector['selectors'] = array( sprintf( '#ginput_quantity_%d_%d', $form['id'], $field->id ) );
					break;
				case 'chainedselect':
					$selector['relation']  = 'all';
					$selector['selectors'] = array();
					foreach ( $field->inputs as $input ) {
						list( $field_id, $input_id ) = explode( '.', $input['id'] );
						$selector['selectors'][]     = sprintf( '#input_%d_%d_%d', $form['id'], $field_id, $input_id );
					}
					break;
			}

			$selectors[ $field->pageNumber ][] = $selector;

		}

		return $selectors;
	}

	public function modify_progress_bar( $markup, $form, $confirmation ) {

		if ( ! $this->is_soft_validation_enabled( $form ) ) {
			return $markup;
		}

		$result = $this->get_submission_result( $form );
		if ( $result['hasError'] == false ) {
			return $markup;
		}

		/* Prevent recursion */
		remove_filter( 'gform_progress_bar', array( $this, 'modify_progress_bar' ), 10, 3 );
		$markup = GFFormDisplay::get_progress_bar( $form, $result['sourcePage'] );
		add_filter( 'gform_progress_bar', array( $this, 'modify_progress_bar' ), 10, 3 );

		return $markup;
	}

	public function get_submission_result( $form ) {

		$submission = rgar( GFFormDisplay::$submission, $form['id'], array( 'is_valid' => true ) );
		$result     = array( 'hasError' => false );

		if ( rgar( $submission, 'is_valid' ) === false ) {

			$result['hasError']   = true;
			$result['sourcePage'] = intval( $submission['source_page_number'] );
			$result['errorPage']  = intval( GFFormDisplay::get_first_page_with_error( $form ) );

		}

		return $result;
	}

	public function add_auto_progress_field_class( $form ) {
		foreach ( $form['fields'] as &$field ) {
			if ( $this->supports_auto_progress( $field ) ) {
				$field['cssClass'] = $this->perk->add_css_class( 'gppt-auto-progress-field', rgar( $field, 'cssClass' ) );
			}
		}
		return $form;
	}

	public function suppress_form_anchor( $anchor, $form ) {
		if ( $this->is_page_transitions_enabled( $form ) ) {
			$anchor = false;
		}
		return $anchor;
	}

	public function force_ajax_mode( $form_args ) {

		$form = GFAPI::get_form( $form_args['form_id'] );

		if ( $this->is_page_transitions_enabled( $form ) ) {
			$form_args['ajax'] = true;
		}

		return $form_args;
	}

	/**
	 * Page Transitions relies on AJAX for validation. However, AJAX form display is not supported by default on the
	 * form preview.
	 */
	public function handle_core_preview_ajax() {
		$form_id = rgpost( 'gform_submit' ) ? rgpost( 'gform_submit' ) : rgget( 'id' );

		if ( $this->is_any_feature_enabled( GFAPI::get_form( $form_id ) ) &&
			( class_exists( 'GFFormDisplay' ) && ! empty( GFFormDisplay::$submission ) )
			&& ( class_exists( 'GFCommon' ) && GFCommon::is_preview() )
			&& isset( $_REQUEST['gform_ajax'] )
		) {
			echo GFForms::get_form( $form_id, true, true, true );
			exit;
		}
	}

	public function field_settings_ui() {
		?>
		<style type="text/css">
			#gws_pagination_tab > ul > li {
				margin: 10px 0;
			}
			#gws_pagination_tab label {
				margin-right: 10px;
			}
			#gws_pagination_tab input[type="checkbox"] + label {
				vertical-align: top;
			}
			#gppt-enable-auto-progress-child-settings label {
				vertical-align: top;
			}
			.gppl-child-settings label {
				display: block;
			}
			.gppt-error {
				background-color: #FFEBE8;
				border-color: #CC0000;
				border-width: 1px;
				border-style: solid;
				padding: 10px;
				margin: 10px 15px 10px 0;
				-moz-border-radius: 3px;
				-khtml-border-radius: 3px;
				-webkit-border-radius: 3px;
				border-radius: 3px;
				max-width: 480px !important;
			}
			.gppt-error p {
				margin: 0.5em 0;
				line-height: 1;
				padding: 2px;
			}
			#gppt-hide-next-button-warning {
				margin-top: 10px;
			}
		</style>

		<li id="gppt-page-transitions" class="gp-field-setting gp-page-transitions-setting field_setting" style="display: none">

			<div>
				<input type="checkbox" id="gppt-enable-page-transitions" value="1" onclick="GPPageTransitions.toggleSettings( '<?php echo $this->perk->key( 'enablePageTransitions' ); ?>', this.checked, jQuery( '#gppt-enable-page-transitions-child-settings' ) );" />
				<label for="gppt-enable-page-transitions">
					<?php _e( 'Enable Page Transitions', 'gp-page-transitions' ); ?>
					<?php gform_tooltip( 'gppt_enable_page_transitions' ); ?>
				</label>
			</div>

			<div id="gppt-enable-page-transitions-child-settings" class="gp-child-settings gppl-child-settings" style="display:none;">

				<div class="gp-row">
					<label for="gppt-transition-style">
						<?php _e( 'Transition Style', 'gp-page-transitions' ); ?>
						<?php gform_tooltip( 'gppt_transition_style' ); ?>
					</label>
					<select type="checkbox" id="gppt-transition-style" onchange="gperk.setFormProperty( '<?php echo $this->perk->key( 'transitionStyle' ); ?>', this.value );">
						<?php foreach ( $this->get_transition_styles() as $value => $label ) : ?>
							<option value="<?php echo $value; ?>"><?php echo $label; ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="gp-row">
					<input type="checkbox" id="gppt-enable-soft-validation" value="1" onclick="gperk.setFormProperty( '<?php echo $this->perk->key( 'enableSoftValidation' ); ?>', this.checked );" />
					<label for="gppt-enable-soft-validation">
						<?php _e( 'Enable Soft Validation', 'gp-page-transitions' ); ?>
						<?php gform_tooltip( 'gppt_enable_soft_validation' ); ?>
					</label>
				</div>

			</div>

		</li>

		<li class="gp-page-transitions-setting field_setting" style="display: none">

			<div>
				<input type="checkbox" id="gppt-enable-auto-progress" value="1" onclick="GPPageTransitions.toggleSettings( '<?php echo $this->perk->key( 'enableAutoProgress' ); ?>', this.checked, jQuery( '#gppt-enable-auto-progress-child-settings' ) );" />
				<label for="gppt-enable-auto-progress">
					<?php _e( 'Enable Auto-progression', 'gp-page-transitions' ); ?>
					<?php gform_tooltip( 'gppt_enable_auto_progress' ); ?>
				</label>
			</div>

			<div id="gppt-enable-auto-progress-child-settings" class="gp-child-settings gppl-child-settings" style="display:none;">

				<label for="gppt-hide-next-button">
					<?php _e( 'Hide:', 'gp-page-transitions' ); ?>
					<?php gform_tooltip( 'gppt_hide_buttons' ); ?>
				</label>
				<input type="checkbox" id="gppt-hide-next-button" value="1" onchange="GPPageTransitions.toggleHideNextButton( this.checked );" />
				<label for="gppt-hide-next-button">
					<?php _e( 'Next Button', 'gp-page-transitions' ); ?>
				</label>
				<input type="checkbox" id="gppt-hide-prev-button" value="1" onclick="gperk.setFormProperty( '<?php echo $this->perk->key( 'hidePrevButton' ); ?>', this.checked );" />
				<label for="gppt-hide-prev-button">
					<?php _e( 'Previous Button', 'gp-page-transitions' ); ?>
				</label>

				<div id="gppt-hide-next-button-warning" class="gp-notice" style="display:none;">
					<i class="fa fa-warning"></i>
					<?php _e( 'You have opted to hide the Next button. Make sure that each form page ends with a field that supports auto-progression to ensure the form can be completed.', 'gp-page-transitions' ); ?>
				</div>

			</div>

		</li>
		<?php
	}

	public function form_editor_settings() {
		?>

		<script type="text/javascript">

			jQuery( document ).ready( function( $ ) {

				window.GPPageTransitions = function() {

					var self = this;

					self.toggleOnInit      = [ 'gppt-enable-page-transitions', 'gppt-enable-auto-progress', 'gppt-enable-soft-validation' ];
					self.options           = {
						'gppt-enable-page-transitions': key( 'enablePageTransitions' ),
						'gppt-enable-auto-progress':    key( 'enableAutoProgress' ),
						'gppt-enable-soft-validation':  key( 'enableSoftValidation' ),
						'gppt-transition-style':        key( 'transitionStyle' ),
						'gppt-hide-next-button':        key( 'hideNextButton' ),
						'gppt-hide-prev-button':        key( 'hidePrevButton' )
					};

					GPPageTransitions.toggleSettings = function( prop, isChecked, $childSettings ) {

						if( prop == key( 'enablePageTransitions' ) && isChecked && ! form[ prop ] ) {
							$( '#gppt-enable-soft-validation' ).prop( 'checked', true );
							form[ key( 'enableSoftValidation' ) ] = true;
						}

						form[ prop ] = isChecked;

						if( isChecked ) {
							$childSettings.slideDown();
						} else {
							$childSettings.slideUp( function() {
								// reset child settings
								$childSettings.find( 'select, input' ).each( function() {
									var $input = $( this ),
										value  = null;
									if( $input.is( ':checkbox' ) ) {
										$input.prop( 'checked', false );
									} else if( $input.is( 'select' ) ) {
										$input.find( 'option:first-child' ).prop( 'selected', true );
										value = $input.val();
									}
									form[ self.options[ $input.attr( 'id' ) ] ] = value;
								} );
							} );

						}

					};

					GPPageTransitions.maybeDisableSoftValidation = function() {
						jQuery('#gppt-soft-validation-notice').remove();

						var $softValidationCheckbox = jQuery('#gppt-enable-soft-validation');

						if (GPPageTransitions.formHasCreditCardField()) {
							const $notice = jQuery(
								'<div id="gppt-soft-validation-notice" class="gform-accessibility-warning field_setting gform-alert gform-alert--accessibility gform-alert--inline">'
								+ '<span class="gform-icon gform-icon--password gform-alert__icon"></span>'
								+ '<div class="gform-alert__message-wrap">'
								+ '<p class="gform-alert__message" style="margin: 0;padding-top:2px;">'
								+ '<?php echo esc_js( esc_html__( 'Soft Validation is disabled due to a Credit Card field existing in this form.', 'gp-page-transitions' ) ); ?>'
								+ '</p>'
								+ '</div>'
								+ '</div>');

							$softValidationCheckbox.prop('disabled', true);
							$softValidationCheckbox.prop('checked', false);
							$notice.insertAfter($softValidationCheckbox.parent());
						} else {
							$softValidationCheckbox.prop('disabled', false);
						}
					};

					GPPageTransitions.formHasCreditCardField = function() {
						if (!window.form || typeof window.form.fields === 'undefined') {
							return false;
						}

						for (var i = 0; i < form.fields.length; i++) {
							if (form.fields[i].type.indexOf('creditcard') !== -1) {
								return true;
							}
						}

						return false;
					};

					GPPageTransitions.toggleHideNextButton = function ( isChecked ) {

						form[ key( 'hideNextButton' ) ] = isChecked;

						var $hideNextButtonWarning = $( '#gppt-hide-next-button-warning' );

						if( isChecked ) {
							$hideNextButtonWarning.slideDown();
						} else {
							$hideNextButtonWarning.slideUp()
						}

					};

					self.overrideInitPaginationOptionsFunction = function() {

						var initPaginationOptions = window.InitPaginationOptions;

						window.InitPaginationOptions = function() {

							initPaginationOptions();

							jQuery('.gp-page-transitions-setting').show();

							self.toggleSettings();

						};

					};

					self.toggleSettings = function() {
						for( var id in self.options ) {
							if( self.options.hasOwnProperty( id ) && form[ self.options[ id ] ] ) {

								var $input = $( '#' + id );

								if( $input.is( 'input[type="checkbox"]' ) ) {
									$input.prop( 'checked', form[ self.options[ id ] ] == true ).change();
								} else {
									$input.val( form[ self.options[ id ] ] )
								}

								if( $.inArray( id, self.toggleOnInit ) != -1 ) {
									GPPageTransitions.toggleSettings( self.options[ id ], $input.is( ':checked' ), $( '#' + id + '-child-settings' ) );
								}

							}
						}

						GPPageTransitions.maybeDisableSoftValidation();
					};

					self.initUI = function() {

						if (!document.body.classList.contains('gf-legacy-ui')) {
							/**
							 * Hide Page Transitions any time a normal field has its settings loaded
							 */
							$(document).on('gform_load_field_settings', function() {
								jQuery('.gp-page-transitions-setting').hide();
							});
						} else {
							var $legacyPageSettingsElem = $( '#pagination_settings' );
							var legacyOptionsHtml       = $('<div id="gws_pagination_tab"><ul class="gforms_form_settings" /></div>');

							legacyOptionsHtml
								.find('.gforms_form_settings')
								.append($('li.gp-page-transitions-setting'));

							$legacyPageSettingsElem.append( legacyOptionsHtml );

							gperk.addTab( $legacyPageSettingsElem, '#gws_pagination_tab', '<?php _e( 'Perks', 'gp-page-transitions' ); ?>' );

							gform_initialize_tooltips();
						}

						self.overrideInitPaginationOptionsFunction();
						self.toggleSettings();

					};

					self.initUI();

				};

				function key( key ) {
					return '<?php echo $this->perk->key( '' ); ?>' + key;
				}

				var gpptAdmin = new GPPageTransitions();

			} );

		</script>

		<?php
	}

	public function hide_next_prev_buttons( $form ) {

		$hide_next_button      = rgar( $form, $this->perk->key( 'hideNextButton' ) );
		$hide_prev_button      = rgar( $form, $this->perk->key( 'hidePrevButton' ) );
		$save_continue_enabled = rgars( $form, 'save/enabled' ) == true;

		/**
		 * Filter the visibility of the form footer.
		 *
		 * If "Hide Next Button" and "Hide Previous Button" are enabled and GF's Save & Continue feature is disabled, the
		 * footer will automatically be hidden.
		 *
		 * @since 1.0
		 *
		 * @param bool  $hide_footer Set to false to disable hiding the footer. Defaults to true.
		 * @param array $form        Current form object.
		 *
		 * @see http://gravitywiz.com/documentation/gppt_hide_footer/
		 */
		$hide_footer = apply_filters( 'gppt_hide_footer', $hide_next_button && $hide_prev_button && ! $save_continue_enabled, $form );
		$hide_footer = apply_filters( "gppt_hide_footer_{$form['id']}", $hide_footer, $form );

		if ( $hide_footer ) {
			$form['cssClass'] = $this->perk->add_css_class( rgar( $form, 'cssClass' ), 'gppt-no-buttons' );
		}

		add_filter( 'gform_next_button', array( $this, 'hide_next_button' ), 10, 2 );
		add_filter( 'gform_previous_button', array( $this, 'hide_prev_button' ), 10, 2 );

		return $form;
	}

	public function hide_next_button( $button, $form ) {
		if ( rgar( $form, $this->perk->key( 'hideNextButton' ) ) ) {
			$button = $this->hide_button( $button );
		}
		return $button;
	}

	public function hide_prev_button( $button, $form ) {
		if ( rgar( $form, $this->perk->key( 'hidePrevButton' ) ) ) {
			$button = $this->hide_button( $button );
		}
		return $button;
	}

	public function hide_button( $button ) {
		return sprintf( '<div class="gppt-hide">%s</div>', $button );
	}

	/**
	 * Enable auto-progress for input masks by modifing the initialization code for the input mask to tirgger a custom
	 * event when the input mask is complete.
	 *
	 * @param $script
	 * @param $form_id
	 * @param $field_id
	 * @param $mask
	 *
	 * @return mixed
	 */
	public function enable_auto_progress_for_input_masks( $script, $form_id, $field_id, $mask ) {

		$form  = GFAPI::get_form( $form_id );
		$field = GFFormsModel::get_field( $form, $field_id );

		if ( $this->is_auto_progress_enabled( $form ) && $this->supports_auto_progress( $field ) ) {
			$search  = "'{$mask}'";
			$replace = sprintf( '%s, %s', $search, "{ completed: function() { jQuery( this ).trigger( 'gpptAutoProgress' ); } }" );
			$script  = str_replace( $search, $replace, $script );
		}

		return $script;
	}

	/**
	 * Register init script to allow Phone input mask to auto-progress form.
	 *
	 * Phone field registers it's own input mask. We use the same logic to register a new version which triggers the
	 * gpptAutoProgress event when the input mask is complete.
	 *
	 * @param $form
	 */
	public function enable_auto_progress_for_phone_input_mask( $form ) {

		if ( ! $this->is_auto_progress_enabled( $form ) ) {
			return;
		}

		foreach ( $form['fields'] as $field ) {

			if ( $field->get_input_type() == 'phone' && $this->supports_auto_progress( $field ) ) {

				$phone_format = $field->get_phone_format();
				$mask         = rgar( $phone_format, 'mask' );

				$search  = "'{$mask}'";
				$replace = sprintf( '%s, %s', $search, "{ completed: function() { jQuery( this ).trigger( 'gpptAutoProgress' ); } }" );
				$script  = str_replace( $search, $replace, $field->get_form_inline_script_on_page_render( $form ) );

				GFFormDisplay::add_init_script( $form['id'], $field->type . '_' . $field->id . ' _alt', GFFormDisplay::ON_PAGE_RENDER, $script );

			}
		}

	}

	/**
	 * Disable Soft Validation if there is a Credit Card field present in the form.
	 *
	 * @param array $args Page Transition JS init args.
	 * @param array $form The current form.
	 *
	 * @return array
	 */
	public function disable_soft_validation_if_cc_field_present( $args, $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( strpos( $field->get_input_type(), 'creditcard' ) !== false ) {
				$args['enableSoftValidation'] = false;
				break;
			}
		}

		return $args;
	}



	// HELPERS //

	public function get_transition_styles() {
		/**
		 * Filter available transition styles (will appear in the Transition Style setting).
		 *
		 * @since 1.0
		 *
		 * @param array $styles An array of transion styles. Array key is the Cycle.js name for the transition effect. Value is the label for the transition effect.
		 *
		 * @see http://gravitywiz.com/documentation/gppt_transition_styles/
		 */
		return apply_filters(
			'gppt_transition_styles',
			array(
				'scrollHorz' => __( 'Slide', 'gp-page-transitions' ),
				'fade'       => __( 'Fade', 'gp-page-transitions' ),
			)
		);
	}

	public function get_all_progress_indicators( $form ) {

		$page_count = $this->get_page_count( $form );
		$type       = $form['pagination']['type'];
		$indicators = array();

		if ( ! in_array( $type, array( 'steps', 'percentage' ) ) ) {
			return $indicators;
		}

		for ( $i = 1; $i <= $page_count; $i++ ) {
			if ( $type == 'steps' ) {
				$indicators[] = GFFormDisplay::get_progress_steps( $form, $i );
			} else {
				$indicators[] = GFFormDisplay::get_progress_bar( $form, $i );
			}
		}

		return $indicators;
	}

	public function get_page_count( $form ) {
		return GFFormDisplay::get_max_page_number( $form );
	}

	public function is_any_feature_enabled( $form ) {
		return $this->is_page_transitions_enabled( $form ) || $this->is_soft_validation_enabled( $form ) || $this->is_auto_progress_enabled( $form );
	}

	public function is_page_transitions_enabled( $form ) {
		/*
		 * Do not enable GPPT on Gravity Forms Entry Edit, Gravity Flow Inbox pages, or GravityView Edit screens as they
		 * control how the form is displayed and forcing things like AJAX can cause some bizarre issues upon submission.
		 *
		 * Gravity Flow unsets 'page' during form render so interestingly, gravity_flow()->is_workflow_detail_page() is
		 * not a reliable check.
		 */
		if (
				GFCommon::is_entry_detail_edit()
				|| ( rgar( $_REQUEST, 'page' ) === 'gravityflow-inbox' && rgget( 'view' ) === 'entry' )
				|| ( class_exists( 'GravityView_frontend' ) && GravityView_frontend::getInstance()->getEntry() )
		) {
			return false;
		}

		/**
		 * Filter whether page transitions is enabled for a form or not. Useful for other plugins to disable page transitions
		 * if AJAX forms are not supported.
		 *
		 * @param boolean $is_enabled Whether page transitions is enabled.
		 * @param array $form The current form.
		 *
		 * @since 1.0-beta-2.7
		 */
		return gf_apply_filters( array( 'gppt_is_enabled', $form['id'] ), rgar( $form, $this->perk->key( 'enablePageTransitions' ) ) == true, $form );
	}

	/**
	 * Soft Validation refers to the process of suppressing all form errors (in fact, each page will not be submitted at all),
	 * until the final page is submitted. Then the user will be directed to the first page with errors.
	 *
	 * @param $form
	 *
	 * @return bool
	 */
	public function is_soft_validation_enabled( $form ) {
		return $this->is_page_transitions_enabled( $form ) && rgar( $form, $this->perk->key( 'enableSoftValidation' ) ) == true;
	}

	public function is_auto_progress_enabled( $form ) {
		return rgar( $form, $this->perk->key( 'enableAutoProgress' ) ) == true;
	}

	public function supports_auto_progress( $field ) {

		$conditions = $this->get_auto_progress_support_conditions();

		foreach ( $conditions as $condition ) {

			if ( isset( $condition['type'] ) && $field->get_input_type() != $condition['type'] ) {
				continue;
			}

			if ( isset( $condition['callback'] ) && is_callable( $condition['callback'] ) && ! call_user_func( $condition['callback'], $field ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	public function get_auto_progress_support_conditions() {

		/**
		 * Filter the conditions that dictate whether or not a field supports auto-progression.
		 *
		 * Each condition supports a 'type' and 'callback' property. If the field type supports auto-progression
		 * unconditionally, you only need to specific the 'type' property. If the field type only supports auto-progression
		 * if certain requirements are met, specify a callback function which will determine if the field passed to it
		 * meets the requirements for the condition's field type.
		 *
		 * @since 1.0
		 *
		 * @param array $conditions An array of auto-progression condition objects.
		 *
		 * @see http://gravitywiz.com/documentation/gppt_auto_progress_support_conditions/
		 */
		$conditions = apply_filters(
			'gppt_auto_progress_support_conditions',
			array(
				array(
					'type'     => 'text',
					'callback' => array( $this, 'auto_progress_condition_text' ),
				),
				array(
					'type' => 'radio',
				),
				array(
					'type' => 'select',
				),
				array(
					'type'     => 'date',
					'callback' => array( $this, 'auto_progress_condition_date' ),
				),
				array(
					'type'     => 'fileupload',
					'callback' => array( $this, 'auto_progress_condition_fileupload' ),
				),
				array(
					'type'     => 'phone',
					'callback' => array( $this, 'auto_progress_condition_phone' ),
				),
				array(
					'type'     => 'checkbox',
					'callback' => array( $this, 'auto_progress_condition_checkbox' ),
				),
				array(
					'type' => 'likert',
				),
				array(
					'type' => 'rating',
				),
				array(
					'type' => 'consent',
				),
			)
		);

		return $conditions;
	}

	public function auto_progress_condition_text( $field ) {
		return $field['inputMask'] == true;
	}

	public function auto_progress_condition_date( $field ) {
		return in_array( $field['dateType'], array( 'datepicker', 'datedropdown' ) );
	}

	public function auto_progress_condition_fileupload( $field ) {
		return ! $field['multipleFiles'];
	}

	public function auto_progress_condition_phone( $field ) {
		// Either international format numbers of GP Advanced Phone Field Numbers can be used for Auto Progress
		return ( $field->phoneFormat != 'international' || rgar( $field, 'gpapfEnable' ) );
	}

	public function auto_progress_condition_checkbox( $field ) {
		$choices = rgar( $field, 'choices' );

		return is_array( $choices ) && count( $choices ) === 1;
	}


}

GFAddOn::register( 'GP_Page_Transitions' );
