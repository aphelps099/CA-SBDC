<?php
/**
 * Adobe Fonts for typography PRO component
 *
 * @package ghostkit-pro
 */

/**
 * Class GhostKit_PRO_Adobe_Fonts
 */
class GhostKit_PRO_Adobe_Fonts {
    /**
     * GhostKit_Fonts constructor.
     */
    public function __construct() {
        add_filter( 'gkt_fonts_list', array( $this, 'add_adobe_fonts' ) );
        // enqueue fonts.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_all_fonts_assets' ), 15 );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_all_fonts_assets' ), 15 );
    }

    /**
     * Enqueue frontend & editor assets
     */
    public function enqueue_all_fonts_assets() {
        $adobe_key = $this->get_adobe_project_key();

        if ( $adobe_key ) {
            wp_enqueue_style( 'ghostkit-fonts-adobe', 'https://use.typekit.net/' . $adobe_key . '.css', array(), '1.7.3' );
        }
    }

    /**
     * Add Adobe fonts list.
     *
     * @param array $fonts - fonts list.
     *
     * @return array
     */
    public function add_adobe_fonts( $fonts ) {
        $custom_fonts = get_option( 'ghostkit_fonts_settings', array() );
        $adobe_fonts  = array();

        if ( isset( $custom_fonts['adobe'] ) ) {
            $unstructed_adobe_fonts = $custom_fonts['adobe'];

            if ( isset( $unstructed_adobe_fonts['fonts'] ) && is_array( $unstructed_adobe_fonts['fonts'] ) ) {

                foreach ( $unstructed_adobe_fonts['fonts']['families'] as $family ) {
                    $weights = array();

                    foreach ( $family['variations'] as $variation ) {
                        if ( false !== strripos( $variation, 'i' ) ) {
                            $weights[] = str_replace( 'i', '', $variation ) . '00i';
                        }
                        if ( false !== strripos( $variation, 'n' ) ) {
                            $weights[] = str_replace( 'n', '', $variation ) . '00';
                        }
                    }
                    $category = 'sans-serif';
                    if ( isset( $family['css_stack'] ) ) {
                        $category = substr( $family['css_stack'], strrpos( $family['css_stack'], ',' ) + 1 );
                    }

                    $adobe_fonts[] = array(
                        'label'     => $family['name'],
                        'name'      => $family['slug'],
                        'widths'    => $weights,
                        'category'  => $category,
                        'subsets'   => array(
                            $family['subset'],
                        ),
                    );
                }
            }
        }

        $fonts['adobe-fonts'] = array(
            'name'  => __( 'Adobe Fonts', 'ghostkit-pro' ),
            'fonts' => $adobe_fonts,
        );

        return $fonts;
    }

    /**
     * Add adobe project key.
     *
     * @return mixed
     */
    public function get_adobe_project_key() {
        $custom_fonts = get_option( 'ghostkit_fonts_settings', array() );
        $project_key  = false;

        if ( isset( $custom_fonts['adobe'] ) ) {
            $unstructed_adobe_fonts = $custom_fonts['adobe'];

            if ( isset( $unstructed_adobe_fonts['kit'] ) && ! empty( $unstructed_adobe_fonts['kit'] ) ) {
                $project_key = $unstructed_adobe_fonts['kit'];
            }
        }

        return $project_key;
    }
}
new GhostKit_PRO_Adobe_Fonts();
