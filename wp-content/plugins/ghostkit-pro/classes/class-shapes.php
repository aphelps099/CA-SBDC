<?php
/**
 * Shapes for Shapes Divider block
 *
 * @package ghostkit-pro
 */

/**
 * GhostKit_Pro_Shapes
 */
class GhostKit_Pro_Shapes {
    /**
     * GhostKit_Pro_Shapes constructor.
     */
    public function __construct() {
        add_filter( 'gkt_shapes_list', array( $this, 'add_pro_shapes' ) );
    }

    /**
     * Add new PRO shapes.
     *
     * @param array $shapes - shapes list.
     *
     * @return array
     */
    public static function add_pro_shapes( $shapes ) {
        $shapes['pro'] = array(
            'name'   => esc_html__( 'PRO', 'ghostkit-pro' ),
            'shapes' => array(
                array(
                    'label'                 => esc_html__( 'Wave Particles', 'ghostkit-pro' ),
                    'name'                  => 'wave-particles',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/wave-particles.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Wave Particles 2', 'ghostkit-pro' ),
                    'name'                  => 'wave-particles-2',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/wave-particles-2.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Ellipse Ragged', 'ghostkit-pro' ),
                    'name'                  => 'ellipse-ragged',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/ellipse-ragged.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Waves', 'ghostkit-pro' ),
                    'name'                  => 'waves',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/waves.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Waves 2', 'ghostkit-pro' ),
                    'name'                  => 'waves-2',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/waves-2.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Waves 3', 'ghostkit-pro' ),
                    'name'                  => 'waves-3',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/waves-3.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Waves 4', 'ghostkit-pro' ),
                    'name'                  => 'waves-4',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/waves-4.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Waves 5', 'ghostkit-pro' ),
                    'name'                  => 'waves-5',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/waves-5.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Storm', 'ghostkit-pro' ),
                    'name'                  => 'storm',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/storm.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Hills', 'ghostkit-pro' ),
                    'name'                  => 'hills',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/hills.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Hill', 'ghostkit-pro' ),
                    'name'                  => 'hill',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/hill.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Hill 2', 'ghostkit-pro' ),
                    'name'                  => 'hill-2',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/hill-2.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Tilts', 'ghostkit-pro' ),
                    'name'                  => 'tilts',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/tilts.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Pit', 'ghostkit-pro' ),
                    'name'                  => 'pit',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/pit.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Pit Angle', 'ghostkit-pro' ),
                    'name'                  => 'pit-angle',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/pit-angle.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Pit Angle Asymm', 'ghostkit-pro' ),
                    'name'                  => 'pit-angle-asymmetrical',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/pit-angle-asymmetrical.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Pit Wave', 'ghostkit-pro' ),
                    'name'                  => 'pit-wave',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/pit-wave.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Pit Wave Asymm', 'ghostkit-pro' ),
                    'name'                  => 'pit-wave-asymmetrical',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/pit-wave-asymmetrical.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Waves Cross', 'ghostkit-pro' ),
                    'name'                  => 'waves-cross',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/waves-cross.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Waves Cross 2', 'ghostkit-pro' ),
                    'name'                  => 'waves-cross-2',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/waves-cross-2.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Floor', 'ghostkit-pro' ),
                    'name'                  => 'floor',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/floor.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Lines', 'ghostkit-pro' ),
                    'name'                  => 'lines',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/lines.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Equalizer', 'ghostkit-pro' ),
                    'name'                  => 'equalizer',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/equalizer.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Burrow', 'ghostkit-pro' ),
                    'name'                  => 'burrow',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/burrow.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Paint', 'ghostkit-pro' ),
                    'name'                  => 'paint',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/paint.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Flame', 'ghostkit-pro' ),
                    'name'                  => 'flame',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/flame.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Rock', 'ghostkit-pro' ),
                    'name'                  => 'rock',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/rock.svg',
                ),
                array(
                    'label'                 => esc_html__( 'City', 'ghostkit-pro' ),
                    'name'                  => 'city',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => true,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/city.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Fence', 'ghostkit-pro' ),
                    'name'                  => 'fence',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/fence.svg',
                ),
                array(
                    'label'                 => esc_html__( 'Diamond', 'ghostkit-pro' ),
                    'name'                  => 'diamond',
                    'allow_flip_vertical'   => true,
                    'allow_flip_horizontal' => false,
                    'path'                  => ghostkit_pro()->plugin_path . '/shapes/diamond.svg',
                ),
            ),
        );

        return $shapes;
    }
}

new GhostKit_Pro_Shapes();
