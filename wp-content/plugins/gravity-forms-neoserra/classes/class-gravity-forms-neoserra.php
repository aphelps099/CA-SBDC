<?php


if ( ! class_exists( 'Gravity_Forms_Neoserra' ) ) {
	class Gravity_Forms_Neoserra {

		public static $init = false;


		protected static $export_column_map = array(
			'date'                            => 'Date',
			'pc_field_map_first_name'         => 'Primary Contact First Name',
			'pc_field_map_last_name'          => 'Primary Contact Last Name',
			'pc_field_map_email'              => 'PC Email',
			'pc_field_map_phone'              => 'PC Phone number',
			'pc_field_map_street_address'     => 'Physical Street Address',
			'pc_field_map_city'               => 'PC Address City',
			'pc_field_map_state'              => 'PC Address State',
			'pc_field_map_zip'                => 'PC Address ZIP Code',
			'bd_field_map_initial_status'     => 'Company Initial Status',
			'bd_field_map_name'               => 'Company Name',
			'bd_field_map_date_established'   => 'Date Company Established',
			'bd_field_map_street_address'     => 'Physical Street Address',
			'bd_field_map_city'               => 'Physical Address City',
			'bd_field_map_state'              => 'Physical Address State',
			'bd_field_map_zip'                => 'Physical Address ZIP Code',
			'bd_field_map_pc_position'        => 'PC Position',
			'bd_field_map_description'        => 'Product or Service Description',
			'bd_field_map_idea'               => 'Product or Service Description',
			'other_field_map_referral'        => 'Referral From',
			'other_field_map_privacy_release' => 'Privacy Release',
			'other_field_map_consent'         => '641 Agreement (Consent)'
		);


		public static function init() {
			if( self::$init ) return;
			self::$init = true;

			add_action( 'gform_loaded', array( __CLASS__, 'register_add_ons' ), 5 );
			add_filter( 'gform_export_menu', array( __CLASS__, 'add_export_menu_items' ) );
			add_action( 'gform_export_page_neoserra_export', array( __CLASS__, 'neoserra_export_page' ) );

			add_action( 'wp_ajax_gf_process_export_neoserra', array( __CLASS__, 'ajax_process_export' ) );
			add_action( 'wp_ajax_gf_download_export_neoserra', array( __CLASS__, 'ajax_download_export' ) );

		}


		public static function register_add_ons() {
			if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) return;

			define( 'GRAVITY_FORMS_NEOSERRA_ADD_ON_VERSION', self::get_version() );
			define( 'GRAVITY_FORMS_NEOSERRA_ADD_ON_PATH_DIR', preg_replace( '/^' . preg_quote( WP_PLUGIN_DIR, '/' ) . '\/?/', '', dirname( __FILE__ ) ) );

			include_once( 'class-gravity-forms-neoserra-add-on.php' );
			GFAddOn::register( 'Gravity_Forms_Neoserra_Add_On' );

		}


		public static function get_version() {
			$plugin_file = preg_replace( '/\/classes$/', '', dirname( __FILE__ ) ) . '/plugin.php';
			$data = get_plugin_data( $plugin_file );
			return isset( $data['Version'] ) ? $data['Version'] : '';
		}


		public static function add_export_menu_items( $menu_items ) {
			$menu_items[] = array(
				'name' => 'neoserra_export',
				'label' => __( 'Export to Neoserra' )
			);
			return $menu_items;
		}


		public static function neoserra_export_page() {

			$scripts = array(
				'jquery-ui-datepicker',
				'gform_form_admin',
				'gform_field_filter',
				'sack',
			);
			foreach ( $scripts as $script ) {
				wp_enqueue_script( $script );
			}

			if ( ! GFCommon::current_user_can_any( 'gravityforms_export_entries' ) ) {
				wp_die( 'You do not have permission to access this page' );
			}

			$add_on = Gravity_Forms_Neoserra_Add_On::get_instance();
	
			GFExport::page_header( __( 'Export to Neoserra', 'gfneoserra' ) );

			?>

				<script type="text/javascript">

					var gfSpinner;

					<?php GFCommon::gf_global(); ?>
					<?php GFCommon::gf_vars(); ?>

					function SelectExportForm(formId) {

						if (!formId)
							return;

						gfSpinner = new gfAjaxSpinner(jQuery('select#export_form'), gf_vars.baseUrl + '/images/spinner.gif', 'position: relative; top: 2px; left: 5px;');

						var mysack = new sack("<?php echo admin_url( 'admin-ajax.php' )?>");
						mysack.execute = 1;
						mysack.method = 'POST';
						mysack.setVar("action", "rg_select_export_form");
						mysack.setVar("rg_select_export_form", "<?php echo wp_create_nonce( 'rg_select_export_form' ); ?>");
						mysack.setVar("form_id", formId);
						mysack.onError = function () {
							alert(<?php echo json_encode( __( 'Ajax error while selecting a form', 'gravityforms' ) ); ?>)
						};
						mysack.runAJAX();

						return true;
					}

					function EndSelectExportForm(aryFields, filterSettings) {

						gfSpinner.destroy();

						if (aryFields.length == 0) {
							jQuery("#export_field_container, #export_date_container, #export_submit_container").hide()
							return;
						}

						var fieldList = "<li><input id='select_all' type='checkbox' onclick=\"jQuery('.gform_export_field').prop('checked', this.checked); jQuery('#gform_export_check_all').html(this.checked ? '<strong><?php echo esc_js( __( 'Deselect All', 'gravityforms' ) ); ?></strong>' : '<strong><?php echo esc_js( __( 'Select All', 'gravityforms' ) ); ?></strong>'); \" onkeypress=\"jQuery('.gform_export_field').prop('checked', this.checked); jQuery('#gform_export_check_all').html(this.checked ? '<strong><?php echo esc_js( __( 'Deselect All', 'gravityforms' ) ); ?></strong>' : '<strong><?php echo esc_js( __( 'Select All', 'gravityforms' ) ); ?></strong>'); \"> <label id='gform_export_check_all' for='select_all'><strong><?php esc_html_e( 'Select All', 'gravityforms' ) ?></strong></label></li>";
						for (var i = 0; i < aryFields.length; i++) {
							fieldList += "<li><input type='checkbox' id='export_field_" + i + "' name='export_field[]' value='" + aryFields[i][0] + "' class='gform_export_field'> <label for='export_field_" + i + "'>" + aryFields[i][1] + "</label></li>";
						}
						jQuery("#export_field_list").html(fieldList);
						jQuery("#export_date_start, #export_date_end").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true});

						jQuery("#export_field_container, #export_filter_container, #export_date_container, #export_submit_container").hide().show();

						gf_vars.filterAndAny = <?php echo json_encode( esc_html__( 'Export entries if {0} of the following match:', 'gravityforms' ) ); ?>;
						jQuery("#export_filters").gfFilterUI(filterSettings);
					}

					( function( $, window, undefined ) {

						$(document).ready(function() {
							$("#submit_button").click(function () {
								// if ($(".gform_export_field:checked").length == 0) {
								// 	alert(<?php echo json_encode( __( 'Please select the fields to be exported', 'gravityforms' ) );  ?>);
								// 	return false;
								// }

								$(this).hide();
								$('#please_wait_container').show();
								process();

								return false;
							});
							
							$('#export_form').on('change', function() {
								SelectExportForm($(this).val());
							}).trigger('change');
						});

						function process( offset, exportId ) {

							if ( typeof offset == 'undefined' ) {
								offset = 0;
							}

							if ( typeof exportId == 'undefined' ) {
								exportId = 0;
							}

							var data = $('#gform_export').serialize();

							data += '&action=gf_process_export_neoserra';
							data += '&offset=' + offset;
							data += '&exportId='+ exportId;
							$.ajax({
								type: 'POST',
								url: ajaxurl,
								data: data,
								dataType: 'json'
							}).done(function( response ){
									if ( response.status == 'in_progress' ) {
										$('#progress_container').text( response.progress );
										process( response.offset, response.exportId );
									} else if ( response.status == 'complete' ) {
										$('#progress_container').text('0%');
										$('#please_wait_container').hide();
										var formId = parseInt( $('#export_form').val() );
										var url = ajaxurl + '?action=gf_download_export_neoserra&_wpnonce=<?php echo wp_create_nonce( 'gform_download_export_neoserra' ); ?>&export-id=' + response.exportId + '&form-id=' + formId;
										$('#submit_button').fadeIn();
										document.location.href = url;
									}
								}
							);
						}

					}( jQuery, window ));


				</script>

				<p class="textleft"><?php esc_html_e( 'Select a form below to export entries. Once you have selected a form you may select the fields you would like to export and then define optional filters for field values and the date range. When you click the download button below, Gravity Forms will create a CSV file for you to save to your computer.', 'gravityforms' ); ?></p>
				<div class="hr-divider"></div>
				<form id="gform_export" method="post" style="margin-top:10px;">
					<?php echo wp_nonce_field( 'rg_start_export_neoserra', 'rg_start_export_neoserra_nonce' ); ?>
					<table class="form-table">
						<tr valign="top">

							<th scope="row">
								<label for="export_form"><?php esc_html_e( 'Select A Form', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_select_form' ) ?>
							</th>
							<td>

								<select id="export_form" name="export_form">
									<option value=""><?php esc_html_e( 'Select a form', 'gravityforms' ); ?></option>
									<?php
									$forms = RGFormsModel::get_forms( null, 'title' );

									/**
									 * Modify list of forms available to export entries from.
									 *
									 * @since 2.4.7
									 *
									 * @param array $forms Forms to display on Export Entries page.
									 */
									$forms = apply_filters( 'gform_export_entries_forms', $forms );

									foreach ( $forms as $form ) {

										$form_object = GFAPI::get_form( $form->id );

										$settings = $add_on->get_form_settings( $form_object );
										// print_r($settings);
										if ( ! isset( $settings['active'] ) || ! boolval( $settings['active'] ) ) continue;

										?>
										<option value="<?php echo absint( $form->id ) ?>" <?php selected( rgget( 'id' ), $form->id ); ?>><?php echo esc_html( $form->title ) ?></option>
										<?php
									}
									?>
								</select>

							</td>
						</tr>
						<?php /* <tr id="export_field_container" valign="top" style="display: none;">
							<th scope="row">
								<label for="export_fields"><?php esc_html_e( 'Select Fields', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_select_fields' ) ?>
							</th>
							<td>
								<ul id="export_field_list">
								</ul>
							</td>
						</tr>
						<tr id="export_filter_container" valign="top" style="display: none;">
							<th scope="row">
								<label><?php esc_html_e( 'Conditional Logic', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_conditional_logic' ) ?>
							</th>
							<td>
								<div id="export_filters">
									<!--placeholder-->
								</div>

							</td>
						</tr> */ ?>
						<tr id="export_date_container" valign="top" style="display: none;">
							<th scope="row">
								<label for="export_date"><?php esc_html_e( 'Select Date Range', 'gravityforms' ); ?></label> <?php gform_tooltip( 'export_date_range' ) ?>
							</th>
							<td>
								<div>
									<span style="width:150px; float:left; ">
										<input type="text" id="export_date_start" name="export_date_start" style="width:90%" />
										<strong><label for="export_date_start" style="display:block;"><?php esc_html_e( 'Start', 'gravityforms' ); ?></label></strong>
									</span>

									<span style="width:150px; float:left;">
										<input type="text" id="export_date_end" name="export_date_end" style="width:90%" />
										<strong><label for="export_date_end" style="display:block;"><?php esc_html_e( 'End', 'gravityforms' ); ?></label></strong>
									</span>

									<div style="clear: both;"></div>
									<?php esc_html_e( 'Date Range is optional, if no date range is selected all entries will be exported.', 'gravityforms' ); ?>
								</div>
							</td>
						</tr>
					</table>
					<ul>
						<li id="export_submit_container" style="display:none; clear:both;">
							<br /><br />
							<button id="submit_button" class="button button-large button-primary"><?php esc_attr_e( 'Download Export File', 'gravityforms' ); ?></button>
							<span id="please_wait_container" style="display:none; margin-left:15px;">
								<i class='gficon-gravityforms-spinner-icon gficon-spin'></i> <?php esc_html_e( 'Exporting entries. Progress:', 'gravityforms' ); ?>
								<span id="progress_container">0%</span>
							</span>
						</li>
					</ul>
				</form>

			<?php
			GFExport::page_footer();
		}


		public static function ajax_process_export() {
			check_admin_referer( 'rg_start_export_neoserra', 'rg_start_export_neoserra_nonce' );

			if ( ! GFCommon::current_user_can_any( 'gravityforms_export_entries' ) ) {
				die();
			}

			$offset = absint( rgpost( 'offset' ) );
			$export_id = sanitize_key( ( rgpost( 'exportId' ) ) );

			$form_id = $_POST['export_form'];
			$form    = RGFormsModel::get_form_meta( $form_id );

			if ( empty( $export_id ) ) {
				$export_id = wp_hash( uniqid( 'export', true ) );
				$export_id = sanitize_key( $export_id );
			}

			$status = self::start_export( $form, $offset, $export_id );

			echo json_encode( $status );
			die();
		}


		public static function start_export( $form, $offset = 0, $export_id = '' ) {

			$time_start = microtime( true );
	
			/***
			 * Allows the export max execution time to be changed.
			 *
			 * When the max execution time is reached, the export routine stop briefly and submit another AJAX request to continue exporting entries from the point it stopped.
			 *
			 * @since 2.0.3.10
			 *
			 * @param int   20    The amount of time, in seconds, that each request should run for.  Defaults to 20 seconds.
			 * @param array $form The Form Object
			 */
			$max_execution_time = apply_filters( 'gform_export_max_execution_time', 20, $form ); // seconds
			$page_size          = 20;
	
			$form_id = $form['id'];
			// $fields  = $_POST['export_field'];

			$add_on = Gravity_Forms_Neoserra_Add_On::get_instance();
			$settings = $add_on->get_form_settings( $form );
			$fields = array_filter( $settings, function( $k ) { return preg_match( '/^(pc|bd|other)_field_map_/', $k ); }, ARRAY_FILTER_USE_KEY );
			$fields = array_merge( array(
				'date' => 'date_created'
			), $fields );
			// print_r($fields); die;
	
			$start_date = rgpost( 'export_date_start' );
			$end_date   = rgpost( 'export_date_end' );
	
			$search_criteria['status']        = 'active';
			$search_criteria['field_filters'] = GFCommon::get_field_filters_from_post( $form );
			if ( ! empty( $start_date ) ) {
				$search_criteria['start_date'] = $start_date;
			}
	
			if ( ! empty( $end_date ) ) {
				$search_criteria['end_date'] = $end_date;
			}
	
			//$sorting = array( 'key' => 'date_created', 'direction' => 'DESC', 'type' => 'info' );
			$sorting = array( 'key' => 'id', 'direction' => 'DESC', 'type' => 'info' );
	
			$form = GFExport::add_default_export_fields( $form );
	
			$total_entry_count     = GFAPI::count_entries( $form_id, $search_criteria );
			$remaining_entry_count = $offset == 0 ? $total_entry_count : $total_entry_count - $offset;
	
			$lines = '';
	
			// Set the separator
			$separator = gf_apply_filters( array( 'gform_export_separator', $form_id ), ',', $form_id );
	
			$field_rows = GFExport::get_field_row_count( $form, $fields, $remaining_entry_count );
	
			if ( $offset == 0 ) {
				GFCommon::log_debug( __METHOD__ . '(): Processing request for form #' . $form_id );
	
	
				/**
				 * Allows the BOM character to be excluded from the beginning of entry export files.
				 * 
				 * @since 2.1.1.21
				 *
				 * @param bool  $include_bom Whether or not to include the BOM characters. Defaults to true.
				 * @param array $form        The Form Object.
				 */
				$include_bom = apply_filters( 'gform_include_bom_export_entries', true, $form );
	
				//Adding BOM marker for UTF-8
				$lines = $include_bom ? chr( 239 ) . chr( 187 ) . chr( 191 ) : '';
	
				//writing header
				$headers = array();
				foreach ( $fields as $key => $field_id ) {
					if ( in_array( $key, array( 'bd_field_map_idea' ) ) ) continue;
					$field = RGFormsModel::get_field( $form, $field_id );
					$label = gf_apply_filters( array( 'gform_entries_field_header_pre_export', $form_id, $field_id ), GFCommon::get_label( $field, $field_id ), $form, $field );
					$label = array_key_exists( $key, self::$export_column_map ) ? self::$export_column_map[ $key ] : $key;
					$value = str_replace( '"', '""', $label );
	
					GFCommon::log_debug( "GFExport::start_export(): Header for field ID {$field_id}: {$value}" );
	
					if ( strpos( $value, '=' ) === 0 ) {
						// Prevent Excel formulas
						$value = "'" . $value;
					}
	
					$headers[ $field_id ] = $value;
	
					$subrow_count = isset( $field_rows[ $field_id ] ) ? intval( $field_rows[ $field_id ] ) : 0;
					if ( $subrow_count == 0 ) {
						$lines .= '"' . $value . '"' . $separator;
					} else {
						for ( $i = 1; $i <= $subrow_count; $i ++ ) {
							$lines .= '"' . $value . ' ' . $i . '"' . $separator;
						}
					}
	
					//GFCommon::log_debug( "GFExport::start_export(): Lines: {$lines}" );
				}
				$lines = substr( $lines, 0, strlen( $lines ) - 1 ) . "\n";
	
				if ( $remaining_entry_count == 0 ) {
					GFExport::write_file( $lines, $export_id );
				}
	
				GFCommon::log_debug( __METHOD__ . '(): search criteria: ' . print_r( $search_criteria, true ) );
				GFCommon::log_debug( __METHOD__ . '(): sorting: ' . print_r( $sorting, true ) );
			}
	
			// Paging through results for memory issues
			while ( $remaining_entry_count > 0 ) {
	
				$paging = array(
					'offset'    => $offset,
					'page_size' => $page_size,
				);
	
				GFCommon::log_debug( __METHOD__ . '(): paging: ' . print_r( $paging, true ) );
	
				$leads = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging );
	
				$leads = gf_apply_filters( array( 'gform_leads_before_export', $form_id ), $leads, $form, $paging );
	
				foreach ( $leads as $lead ) {
					$line = self::get_entry_export_line( $lead, $form, $fields, $field_rows, $separator );
					/**
					 * Filter the current line being exported.
					 *
					 * @since 2.4.11.5
					 *
					 * @param string   $line       The current line being exported.
					 * @param array    $form       The current form object.
					 * @param array    $fields     An array of field IDs to be exported.
					 * @param array    $field_rows An array of List fields
					 * @param array    $entry      The current entry.
					 * @param string   $separator  The separator
					 */
					$line = apply_filters( 'gform_export_line', $line, $form, $fields, $field_rows, $lead, $separator );
					$lines .= "$line\n";
				}
	
				$offset += $page_size;
				$remaining_entry_count -= $page_size;
	
				if ( ! seems_utf8( $lines ) ) {
					$lines = utf8_encode( $lines );
				}
	
				$lines = apply_filters( 'gform_export_lines', $lines );
	
				GFExport::write_file( $lines, $export_id );
	
				$time_end       = microtime( true );
				$execution_time = ( $time_end - $time_start );
	
				if ( $execution_time >= $max_execution_time ) {
					break;
				}
	
				$lines = '';
			}
	
			$complete = $remaining_entry_count <= 0;
	
			if ( $complete ) {
				/**
				 * Fires after exporting all the entries in form
				 *
				 * @since 2.4.5.11 Added the $export_id param.
				 * @since 1.9.3
				 *
				 * @param array  $form       The Form object to get the entries from
				 * @param string $start_date The start date for when the export of entries should take place
				 * @param string $end_date   The end date for when the export of entries should stop
				 * @param array  $fields     The specified fields where the entries should be exported from
				 * @param string $export_id  A unique ID for the export.
				 */
				do_action( 'gform_post_export_entries', $form, $start_date, $end_date, $fields, $export_id );
			}
	
			$offset = $complete ? 0 : $offset;
	
			$status = array(
				'status'   => $complete ? 'complete' : 'in_progress',
				'offset'   => $offset,
				'exportId' => $export_id,
				'progress' => $remaining_entry_count > 0 ? intval( 100 - ( $remaining_entry_count / $total_entry_count ) * 100 ) . '%' : '',
			);
	
			GFCommon::log_debug( __METHOD__ . '(): Status: ' . print_r( $status, 1 ) );
	
			return $status;
		}


		public static function get_entry_export_line( $entry, $form, $fields, $field_rows, $separator ) {
			GFCommon::log_debug( __METHOD__ . '(): Processing entry #' . $entry['id'] );
	
			$line = '';
	
			foreach ( $fields as $key => $field_id ) {
				if ( in_array( $key, array( 'bd_field_map_idea' ) ) ) continue;
				switch ( $field_id ) {
					case 'date_created' :
					case 'payment_date' :
						$value = $entry[ $field_id ];
						if ( $value ) {
							$lead_gmt_time   = mysql2date( 'G', $value );
							$lead_local_time = GFCommon::get_local_timestamp( $lead_gmt_time );
							$value           = date_i18n( 'Y-m-d H:i:s', $lead_local_time, true );
						}
						break;
					default :

						$field = GFAPI::get_field( $form, $field_id );
	
						$value = is_object( $field ) ? $field->get_value_export( $entry, $field_id, false, true ) : rgar( $entry, $field_id );
						$value = apply_filters( 'gform_export_field_value', $value, $form['id'], $field_id, $entry );

						$proxy_field_id = null;
						if ( $key == 'bd_field_map_description' && empty( $value ) ) $proxy_field_id = $fields['bd_field_map_idea'];
						if ( $proxy_field_id ) {
							$proxy_field = GFAPI::get_field( $form, $proxy_field_id );
							$value = is_object( $proxy_field ) ? $proxy_field->get_value_export( $entry, $proxy_field_id, false, true ) : rgar( $entry, $proxy_field_id );
							$value = apply_filters( 'gform_export_field_value', $value, $form['id'], $proxy_field_id, $entry );
						}

						break;
				}
	
				if ( isset( $field_rows[ $field_id ] ) ) {
					$list = empty( $value ) ? array() : $value;
	
					foreach ( $list as $row ) {
						if ( is_array( $row ) ) {
							// Entry from a multi-column list field.
							$row_values = array_values( $row );
							$row_str    = implode( '|', $row_values );
						} else {
							// Entry from a standard list field.
							$row_str = $row;
						}
	
						if ( strpos( $row_str, '=' ) === 0 ) {
							// Prevent Excel formulas
							$row_str = "'" . $row_str;
						}
	
						$line .= '"' . str_replace( '"', '""', $row_str ) . '"' . $separator;
					}
	
					//filling missing subrow columns (if any)
					$missing_count = intval( $field_rows[ $field_id ] ) - count( $list );
					for ( $i = 0; $i < $missing_count; $i ++ ) {
						$line .= '""' . $separator;
					}
				} else {
					if ( is_array( $value ) ) {
						if ( ! empty( $value[0] ) && is_array( $value[0] ) ) {
							// Entry from a multi-column list field.
							$values = array();
							foreach ( $value as $item ) {
								$values[] = implode( '|', array_values( $item ) );
							}
	
							$value = implode( ',', $values );
						} else {
							// Entry from a standard list field.
							$value = implode( '|', $value );
						}
					}
	
					if ( strpos( $value, '=' ) === 0 ) {
						// Prevent Excel formulas
						$value = "'" . $value;
					}
	
					$line .= '"' . str_replace( '"', '""', $value ) . '"' . $separator;
				}
			}
	
			$line = substr( $line, 0, strlen( $line ) - 1 );
	
			return $line;
		}
	

		public static function ajax_download_export() {
			check_admin_referer( 'gform_download_export_neoserra' );

			if ( ! function_exists( 'readfile' ) ) {
				GFCommon::log_error( __METHOD__ . '(): Aborting. The PHP readfile function is not available.' );
				die( esc_html__( 'The PHP readfile function is not available, please contact the web host.', 'gravityforms' ) );
			}

			if ( ! GFCommon::current_user_can_any( 'gravityforms_export_entries' ) ) {
				die();
			}

			$form_id = absint( rgget( 'form-id' ) );

			if ( empty( $form_id ) ) {
				die();
			}

			$form = GFAPI::get_form( $form_id );

			if ( empty( $form ) ) {
				die();
			}

			$filename = sanitize_title_with_dashes( $form['title'] ) . '-' . gmdate( 'Y-m-d', GFCommon::get_local_timestamp( time() ) ) . '.csv';

			GFCommon::log_debug( __METHOD__ . '(): Starting download of file: ' . $filename );

			$charset = get_option( 'blog_charset' );
			header( 'Content-Description: File Transfer' );
			header( "Content-Disposition: attachment; filename=$filename" );
			header( 'Content-Type: text/csv; charset=' . $charset, true );
			$buffer_length = ob_get_length(); //length or false if no buffer
			if ( $buffer_length > 1 ) {
				ob_clean();
			}

			if ( has_filter( 'sanitize_file_name' ) ) {
				GFCommon::log_debug( __METHOD__ . '(): The WordPress sanitize_file_name filter has been detected.' );
			}

			$export_folder = RGFormsModel::get_upload_root() . 'export/';
			$export_id     = rgget( 'export-id' );
			$file          = $export_folder . sanitize_file_name( 'export-' . $export_id . '.csv' );
			$result        = readfile( $file );

			if ( $result === false ) {
				GFCommon::log_error( __METHOD__ . '(): An issue occurred whilst reading the file.' );
			} else {
				@unlink( $file );
				GFCommon::log_debug( __METHOD__ . '(): Number of bytes read from the file: ' . print_r( $result, 1 ) );
			}

			exit;
		}


	}
}