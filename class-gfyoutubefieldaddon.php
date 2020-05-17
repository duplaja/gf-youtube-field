<?php

GFForms::include_addon_framework();

class GFYouTubeFieldAddOn extends GFAddOn {

	protected $_version = GF_YOUTUBE_FIELD_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.8';
	protected $_slug = 'youtubefieldaddon';
	protected $_path = 'youtubefieldaddon/youtubefieldaddon.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms YouTube Field Add-On';
	protected $_short_title = 'YouTube Field Add-On';

	/**
	 * @var object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Include the field early so it is available when entry exports are being performed.
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-youtube-gf-field.php' );
		}
	}

	public function init_admin() {
		parent::init_admin();

		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_appearance_settings', array( $this, 'field_appearance_settings' ), 10, 2 );
		add_action( 'gform_field_standard_settings', array( $this, 'field_standard_settings' ), 10, 2 );
	}


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Include my_script.js when the form contains a 'youtube' type field.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gf_youtube_js',
				'src'     => $this->get_base_url() . '/js/youtube_scripts.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'field_types' => array( 'youtube' ) ),
				),
			),

		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Include my_styles.css when the form contains a 'youtube' type field.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gf_youtube_css',
				'src'     => $this->get_base_url() . '/css/youtube_styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'youtube' ) )
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}


	// # FIELD SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Add the tooltips for the field.
	 *
	 * @param array $tooltips An associative array of tooltips where the key is the tooltip name and the value is the tooltip.
	 *
	 * @return array
	 */
	public function tooltips( $tooltips ) {
		$youtube_tooltips = array(
			'youtube_id_field' => sprintf( '<h6>%s</h6>%s', esc_html__( 'YouTube Video ID', 'youtubefieldaddon' ), esc_html__( 'The video ID, from after v=, in the YouTube URL.', 'youtubefieldaddon' ) ),
			'youtube_speed_field' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Max Playback Speed', 'youtubefieldaddon' ), esc_html__( 'This determines the max speed that the user can watch at, and still count as "completed".', 'youtubefieldaddon' ) ),
			'youtube_autoplay_field' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Autoplay Video Settings', 'youtubefieldaddon' ), esc_html__( 'Whether or not you want the video to autoplay on form load.', 'youtubefieldaddon' ) ),
		);

		return array_merge( $tooltips, $youtube_tooltips );
	}

	/**
	 * Add the custom setting for the YouTube field to the Standard tab.
	 *
	 * @param int $position The position the settings should be located at.
	 * @param int $form_id The ID of the form currently being edited.
	 */

	public function field_standard_settings( $position, $form_id ) {
		// Add our custom setting just before the 'Custom CSS Class' setting.
		if ($position == 20) {
			// retrieve the data earlier stored in the database or create it
			 //$highlight_fields = array('First Choice'=>'First Choice','Second Choice'=>'Second Choice','Third Choice'=>'Third Choice');
			  ?>
			  <li class="youtube_id_setting field_setting">
   
					   <label for="youtube_id_field" class="section_label">
							  <?php esc_html_e('YouTube Video ID', 'youtubefieldaddon'); ?>
							  <?php gform_tooltip( 'youtube_id_field' ) ?>
					   </label>
   
					 <input type="input" id="youtube_id_field" onkeyup="SetYouTubeIDSetting(jQuery(this).val());" onchange="SetYouTubeIDSetting(jQuery(this).val());" /> 
   
			   </li>
			   <li class="youtube_speed_setting field_setting">
   
					<label for="youtube_speed_field" class="section_label">
							<?php esc_html_e('Max Playback Speed', 'youtubefieldaddon'); ?>
							<?php gform_tooltip( 'youtube_speed_field' ) ?>
					</label>

					<select id="youtube_speed_field" onchange="SetYouTubeSpeedSetting(jQuery(this).val());">
						<option value='1' selected='selected'>1x</option>
						<option value='1.5'>1.5x</option>
						<option value='1.75'>1.75x</option>
						<option value='2'>2x</option>
					</select> 

				</li>
				<li class="youtube_autoplay_setting field_setting">
					
					<label for="youtube_autoplay_field" class="section_label">
							<?php esc_html_e('Autoplay Video?', 'youtubefieldaddon'); ?>
							<?php gform_tooltip( 'youtube_autoplay_field' ) ?>
					</label>

					<select id="youtube_autoplay_field" onchange="SetYouTubeAutoplaySetting(jQuery(this).val());"> 
						<option value='no'>No</option>
						<option value='yes'>Yes</option>
					</select>
				</li>
		  <?php
		  }
	}

}