<?php
defined( 'ABSPATH' ) or die( 'WordPress is not loaded' );

/**
 * Enqueue admin scripts.
 */
function qrcodes_admin_scripts() {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script(
        'qrcodes-admin',
        plugin_dir_url( __FILE__ ) . 'scripts/admin.js',
        array( 'wp-color-picker', 'jquery' )
    );
}

add_action( 'admin_enqueue_scripts', 'qrcodes_admin_scripts' );

/**
 * Display option page.
 *
 * Display the QRCodes option page.
 */
function qrcodes_option_page() {
	?>
	<div class="wrap">
		<h2><?php _e( 'QRCodes options', 'qrcodes' ); ?></h2>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'qrcodes-group' );
			do_settings_sections( 'qrcodes' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Display introduction before general settings.
 *
 * Literally do nothing.
 */
function qrcodes_options_section_general() {
	// Nothing to do
}

/**
 * Sanitize the qrcode data.
 *
 * Sanitize the data that will be encoded in the QRCode.
 *
 * @param string $data The data to sanitize.
 *
 * @return string
 */
function qrcodes_options_sanitize_data( $data ) {
	return $data;
}

/**
 * Sanitize the media query value.
 *
 * Sanitize the media query value used by the build in style.
 *
 * @param string $media_queries The media query string.
 *
 * @return string
 */
function qrcodes_options_sanitize_media( $media_queries ) {
	$media_queries = strtolower( $media_queries );
    $media_queries = preg_replace( '/^\\s*@media\\s/', '', $media_queries );
	$media_queries = trim( $media_queries );

    if ( 0 == strlen( $media_queries ) ) {
        $media_queries = 'all';
    }

    $matches = preg_split('/\\s*(\\sand\\s|,)\\s*/', $media_queries);
    $matches = array_filter( $matches, 'strlen' );

    $matched_media = array();
    $matched_conditions = array();
    foreach ( $matches as $subject ) {
        $subject = preg_replace( '/^\\s*((only|not)\\s+)+/', '', $subject );

        $conditions = array();
        if ( preg_match( '/^(?P<filter>[\\w-]+)\\s*:\\s*(?P<value>.+)/', $subject, $conditions ) ) {
            $matched_conditions[] = array(
                'value'  => $conditions['value'],
                'filter' => $conditions['filter'],
            );
        } else {
            $matched_media[] = $subject;
        }
    }

    $standard_media = array(
        'tv',
        'all',
        'tty',
        'print',
        'aural',
        'speech',
        'screen',
        'braille',
        'handheld',
        'embossed',
        'projection',
    );
    $diffs    = array_diff( $matched_media, $standard_media );
    $nb_diffs = count( $diffs );
    if ( 0 < $nb_diffs ) {
        add_settings_error(
            'qrcodes_media',
            'standard-media',
            sprintf(
                _n(
                    'This medium is not standard: %s',
                    'Theses media are not standard: %s',
                    $nb_diffs,
                    'qrcodes'
                ),
                implode( ',', array_map( 'esc_html', $diffs ) )
            ),
            'error'
        );
    }

    $filters = array(
        'grid',
        'scan',
        'color',
        'width',
        'height',
        'monochrome',
        'resolution',
        'color-index',
        'orientation',
        'aspect-ratio',
        'device-width',
        'device-height',
        'device-aspect-ratio',
    );
    foreach ( $matched_conditions as $condition ) {
        if ( ! in_array( $condition['filter'], $filters ) ) {
            add_settings_error(
                'qrcodes_media',
                'standard-condition',
                sprintf(
                    __(
                        'This condition is not standard: %s',
                        'qrcodes'
                    ),
                    $condition['filter']
                ),
                'error'
            );

            return $media_queries;
        }
    }

	return $media_queries;
}

/**
 * Display an <input/>.
 *
 * Display an <input/> with some html attribute from $args.
 *
 * @param array $args {
 *     Data used for building an input.
 *
 *     @type string $type      The type of the input. Default text.
 *     @type string $label_for The id of the input.
 *     @type string $class     To set the input class to "{$class}-field".
 *     @type string $name      Name of the input.
 *     @type string $disabled  True to disabled the input.
 *     @type string $setting   Name of the setting if the input is a part of an array option.
 *     @type string $value     value of the input.
 * }
 */
function qrcodes_options_display_input( $args ) {
    $args = wp_parse_args( $args, array(
            'type'     => 'text',
            'disabled' => false,
    ) );

    if ( isset( $args['setting'] ) ) {
        $args['name'] = $args['setting'] . '[' . $args['name'] . ']';
    }
    ?>
    <input
            type="<?php echo esc_attr( $args['type'] ); ?>"
            <?php if ( isset( $args['label_for'] ) ) : ?>
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
            <?php endif; ?>
            <?php if ( isset( $args['class'] ) ) : ?>
                class="<?php echo esc_attr( $args['class'] ); ?>-field"
            <?php endif; ?>
            name="<?php echo esc_attr( $args['name'] ); ?>"
            value="<?php echo esc_attr( $args['value'] ); ?>"
            <?php disabled( true, $args['disabled'] ); ?>
    />
    <?php
}

/**
 * Display an <input/>.
 *
 * Display an <input/> with some html attribute from $args.
 *
 * @param array $args {
 *     Data used for building an input.
 *
 *     @type string $type      The type of the input. Default text.
 *     @type string $label_for The id of the input.
 *     @type string $class     To set the input class to "{$class}-field".
 *     @type string $name      Name of the input.
 *     @type string $disabled  True to disabled the input.
 *     @type string $setting   Name of the setting if the input is a part of an array option.
 *     @type string $value     value of the input.
 *     @type array  $choices   Array of possible value. $value => $label.
 * }
 */
function qrcodes_options_display_select( $args ) {
    $args = wp_parse_args( $args, array(
        'type'     => 'text',
        'disabled' => false,
    ) );

    if ( isset( $args['setting'] ) ) {
        $args['name'] = $args['setting'] . '[' . $args['name'] . ']';
    }
    ?>
    <select
            type="<?php echo esc_attr( $args['type'] ); ?>"
            <?php if ( isset( $args['label_for'] ) ) : ?>
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
            <?php endif; ?>
            <?php if ( isset( $args['class'] ) ) : ?>
                class="<?php echo esc_attr( $args['class'] ); ?>-field"
            <?php endif; ?>
            name="<?php echo esc_attr( $args['name'] ); ?>"
            <?php disabled( 'disabled', $args['disabled'] ); ?>
    >
        <?php foreach ( $args['choices'] as $value => $label ) : ?>
            <option
                    value="<?php echo esc_attr( $value ); ?>"
                    <?php selected( @$args['value'], $value ); ?>
            >
                <?php echo esc_html( $label ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Sanitize $data as a hexadecimal color.
 *
 * @param string $data    Data to sanitize
 * @param string $default (Optional) Default color if error.
 *
 * @return string
 */
function qrcodes_options_sanitize_color( $data, $default = '#FFFFFF' ) {
    $matches = array();
    if ( ! preg_match('/^#?(?P<color>([A-Fa-f0-9]{3}){1,2})$/', $data, $matches ) ) {
        add_settings_error(
            'qrcodes_data',
            'not-hex',
            sprintf(
                __(
                    'Incorrect color value. Changed to %s.',
                    'qrcodes'
                ),
                $default
            ),
            'error'
        );

        return $default;
    }

    $color = $matches['color'];
    if ( 3 == strlen( $color ) ) {
        return '#' .
               $color[0] . $color[0] .
               $color[1] . $color[1] .
               $color[2] . $color[2];
    }

    return '#' . $color;
}

/**
 * Sanitize correction level for qrcodejs.
 *
 * Possible values are :
 *
 * - QRCode.CorrectLevel.H
 * - QRCode.CorrectLevel.Q
 * - QRCode.CorrectLevel.M
 * - QRCode.CorrectLevel.L
 *
 * @param string $data Data to sanitize.
 *
 * @return string
 */
function qrcodes_options_sanitize_correction_level( $data ) {
    $matches = array();
    if ( ! preg_match( '/QRCode\\.CorrectLevel\\.(?P<level>[HQML])/', $data, $matches ) ) {
        add_settings_error(
            'qrcodes_correction_level',
            'not-hex',
                __(
                    'Incorrect correction level. Changed to High.',
                    'qrcodes'
                ),
            'error'
        );

        return 'QRCode.CorrectLevel.Q';
    }

    return 'QRCode.CorrectLevel.' . $matches['level'];
}

/**
 * Sanitize general settings.
 *
 * @param array $data {
 *      Array of settings.
 *
 *      @type string     $text
 *      @type string|int $width
 *      @type string     $media
 *      @type string     $primary-color
 *      @type string     $secondary-color
 *      @type string     $correction-level
 * }
 *
 * @return array
 */
function qrcodes_options_sanitize_general( $data ) {
    return array(
        'text'             => qrcodes_options_sanitize_data( @$data['text'] ),
        'width'            => absint( @$data['width'] ),
        'media'            => qrcodes_options_sanitize_media( @$data['media'] ),
        'primary-color'    => qrcodes_options_sanitize_color( @$data['primary-color'], '#000000' ),
        'secondary-color'  => qrcodes_options_sanitize_color( @$data['secondary-color'], '#FFFFFF' ),
        'correction-level' => qrcodes_options_sanitize_correction_level( @$data['correction-level'] ),
    );
}

/**
 * Register settings.
 *
 * Add section, settings and fields for QRCodes options.
 */
function qrcodes_register_settings() {
    $theme_supports = current_theme_supports( 'qrcodes' );

    $values = qrcodes_get_general_options();

    add_settings_section(
		'general',
		__( 'General', 'qrcodes' ),
		'qrcodes_options_section_general',
		'qrcodes'
	);

	register_setting(
		'qrcodes-group',
		'qrcodes_data',
		'qrcodes_options_sanitize_general'
	);

	add_settings_field(
		'qrcodes_data',
		__( 'Data', 'qrcodes' ),
		'qrcodes_options_display_input',
		'qrcodes',
		'general',
		array(
            'name'      => 'text',
            'value'     => $values['text'],
            'setting'   => 'qrcodes_data',
            'label_for' => 'qrcodes-data-text',
		)
	);

    add_settings_field(
        'qrcodes_media',
        __( 'Media Query', 'qrcodes' ),
        'qrcodes_options_display_input',
        'qrcodes',
        'general',
        array(
            'name'      => 'media',
            'value'     => $values['media'],
            'disabled'  => $theme_supports,
            'label_for' => 'qrcodes_media',
        )
    );

    add_settings_field(
        'qrcodes_primary_color',
        __( 'Primary color', 'qrcodes' ),
        'qrcodes_options_display_input',
        'qrcodes',
        'general',
        array(
            'name'      => 'primary-color',
            'class'     => 'color-picker',
            'value'     => $values['primary-color'],
            'setting'   => 'qrcodes_data',
            'disabled'  => $theme_supports,
            'label_for' => 'qrcodes-data-primary-color',
        )
    );

    add_settings_field(
        'qrcodes_secondary_color',
        __( 'Secondary color', 'qrcodes' ),
        'qrcodes_options_display_input',
        'qrcodes',
        'general',
        array(
            'name'      => 'secondary-color',
            'class'     => 'color-picker',
            'value'     => $values['secondary-color'],
            'setting'   => 'qrcodes_data',
            'disabled'  => $theme_supports,
            'label_for' => 'qrcodes-data-secondary-color',
        )
    );

    add_settings_field(
        'qrcodes_width',
        __( 'Width', 'qrcodes' ),
        'qrcodes_options_display_input',
        'qrcodes',
        'general',
        array(
            'type'      => 'number',
            'name'      => 'width',
            'value'     => $values['width'],
            'setting'   => 'qrcodes_data',
            'disabled'  => $theme_supports,
            'label_for' => 'qrcodes-data-width',
        )
    );

    add_settings_field(
        'qrcodes_correction_level',
        __( 'Correction level', 'qrcodes' ),
        'qrcodes_options_display_select',
        'qrcodes',
        'general',
        array(
            'type'      => 'select',
            'name'      => 'correction-level',
            'value'     => $values['correction-level'],
            'choices'   => array(
                'QRCode.CorrectLevel.H' => __( 'High', 'qrcodes' ),
                'QRCode.CorrectLevel.Q' => __( 'Good', 'qrcodes' ),
                'QRCode.CorrectLevel.M' => __( 'Medium', 'qrcodes' ),
                'QRCode.CorrectLevel.L' => __( 'Low', 'qrcodes' ),
            ),
            'setting'   => 'qrcodes_data',
            'label_for' => 'qrcodes-data-correction-level',
        )
    );
}

add_action( 'admin_init', 'qrcodes_register_settings' );

/**
 * Add option page.
 *
 * Add QRCodes option page.
 */
function qrcodes_admin_menu() {
	add_options_page(
		__( 'QRCodes plugin', 'qrcodes' ),
		__( 'QRCodes', 'qrcodes' ),
		'manage_options',
		'qrcodes',
		'qrcodes_option_page'
	);
}

add_action( 'admin_menu', 'qrcodes_admin_menu' );