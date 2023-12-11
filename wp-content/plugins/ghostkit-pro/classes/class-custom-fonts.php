<?php
/**
 * Custom Fonts for typography PRO component
 *
 * @package ghostkit-pro
 */

/**
 * Class GhostKit_PRO_Custom_Fonts
 */
class GhostKit_PRO_Custom_Fonts {
    /**
     * GhostKit_Fonts constructor.
     */
    public function __construct() {
        add_filter( 'gkt_fonts_list', array( $this, 'add_custom_fonts' ) );

        // enqueue fonts.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_all_fonts_assets' ), 15 );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_all_fonts_assets' ), 15 );

        add_filter( 'upload_mimes', array( $this, 'add_fonts_to_allowed_mimes' ) );
    }

    /**
     * Enqueue frontend & editor assets
     */
    public function enqueue_all_fonts_assets() {
        $custom_fonts = get_option( 'ghostkit_fonts_settings', array() );

        if ( isset( $custom_fonts['custom'] ) && ! empty( $custom_fonts['custom'] ) ) {
            $result = '';

            foreach ( $custom_fonts['custom'] as $font_data ) {
                $font_face = array(
                    'font-family'  => esc_attr( $font_data['slug'] ),
                    'src'          => '',
                    'font-weight'  => '400',
                    'font-style'   => 'normal',
                    'font-display' => 'swap',
                );

                if ( isset( $font_data['woff'] ) && $font_data['woff'] ) {
                    if ( $font_face['src'] ) {
                        $font_face['src'] .= ', ';
                    }

                    $font_face['src'] .= 'url("' . esc_url( $font_data['woff'] ) . '") format("woff")';
                }

                if ( isset( $font_data['woff2'] ) && $font_data['woff2'] ) {
                    if ( $font_face['src'] ) {
                        $font_face['src'] .= ', ';
                    }

                    $font_face['src'] .= 'url("' . esc_url( $font_data['woff2'] ) . '") format("woff2")';
                }

                if ( isset( $font_data['weight'] ) && $font_data['weight'] ) {
                    $font_face['font-weight'] = esc_attr( $font_data['weight'] );
                }

                if ( isset( $font_data['style'] ) && $font_data['style'] ) {
                    $font_face['font-style'] = esc_attr( $font_data['style'] );
                }

                $result .= '@font-face { ';

                foreach ( $font_face as $name => $val ) {
                    $result .= $name . ': ' . $val . '; ';
                }

                $result .= '}';
            }

            wp_register_style( 'ghostkit-fonts-custom', false, array(), '1.7.3' );
            wp_enqueue_style( 'ghostkit-fonts-custom' );
            wp_add_inline_style( 'ghostkit-fonts-custom', $result );
        }
    }

    /**
     * Add Custom Fonts list.
     *
     * @param array $fonts - fonts list.
     *
     * @return array
     */
    public function add_custom_fonts( $fonts ) {
        $custom_fonts = get_option( 'ghostkit_fonts_settings', array() );
        $result       = array();

        if ( isset( $custom_fonts['custom'] ) && is_array( $custom_fonts['custom'] ) ) {
            $categorized_result = array();

            // The same thing we need to prepare in settings/pages/fonts.js
            // inside addFontsToGlobalSettings method.
            foreach ( $custom_fonts['custom'] as $font_data ) {
                $weights = array();

                if ( isset( $categorized_result[ $font_data['slug'] ]['widths'] ) && is_array( $categorized_result[ $font_data['slug'] ]['widths'] ) ) {
                    $weights = $categorized_result[ $font_data['slug'] ]['widths'];
                }

                if ( ! in_array( $font_data['weight'], $weights, true ) ) {
                    $weights[] = $font_data['weight'];
                }

                $categorized_result[ $font_data['slug'] ] = array(
                    'label'     => $font_data['name'],
                    'name'      => $font_data['slug'],
                    'widths'    => $weights,
                    'category'  => 'sans-serif',
                );
            }
            foreach ( $categorized_result as $font_data ) {
                $result[] = $font_data;
            }
        }

        $fonts['custom-fonts'] = array(
            'name'  => __( 'Custom Fonts', 'ghostkit-pro' ),
            'fonts' => $result,
        );

        return $fonts;
    }

    /**
     * Allowed mime types and file extensions
     *
     * @param array $mimes Current array of mime types.
     * @return array $mimes Updated array of mime types.
     */
    public function add_fonts_to_allowed_mimes( $mimes ) {
        $mimes['woff']  = 'application/x-font-woff';
        $mimes['woff2'] = 'application/x-font-woff2';

        return $mimes;
    }
}
new GhostKit_PRO_Custom_Fonts();
