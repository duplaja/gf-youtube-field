<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class YouTube_GF_Field extends GF_Field {

	/**
	 * @var string $type The field type.
	 */
	public $type = 'youtube';

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'YouTube', 'youtubefieldaddon' );
	}

	/**
	 * Assign the field button to the Advanced Fields group.
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'description_setting',
			'rules_setting',
			'youtube_id_setting',
			'youtube_speed_setting',
			'youtube_autoplay_setting',
			'css_class_setting',
			'admin_label_setting',
			'conditional_logic_field_setting',
		);
	}

	/**
	 * Enable this field for use with conditional logic.
	 *
	 * @return bool
	 */
	public function is_conditional_logic_supported() {
		return true;
	}

	/**
	 * The scripts to be included in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {

		// set the default field label for the youtube type field
		$script = sprintf( "function SetDefaultValues_youtube(field) {field.label = '%s';}", $this->get_form_editor_field_title() ) . PHP_EOL;

		// initialize the fields custom settings
		
		$script .= "jQuery(document).bind('gform_load_field_settings', function (event, field, form) {" .
							"var youtubeID = field.youtubeID == undefined ? '' : field.youtubeID;" . //Youtube ID
						   "jQuery('#youtube_id_field').val(youtubeID);" .
						   "var youtubeSpeed = field.youtubeSpeed == undefined ? '2' : field.youtubeSpeed;" . //Max Playback Speed
						   "jQuery('#youtube_speed_field').val(youtubeSpeed);" .
						   "var youtubeAutoplay = field.youtubeAutoplay == undefined ? 'no' : field.youtubeAutoplay;" . //Autoplay y/n
		           		"jQuery('#youtube_autoplay_field').val(youtubeAutoplay);" .
		           "});" . PHP_EOL;

		// saving the youtube setting
		$script .= "function SetYouTubeIDSetting(value) {SetFieldProperty('youtubeID', value);}" . PHP_EOL;
		$script .= "function SetYouTubeSpeedSetting(value) {SetFieldProperty('youtubeSpeed', value);}" . PHP_EOL;
		$script .= "function SetYouTubeAutoplaySetting(value) {SetFieldProperty('youtubeAutoplay', value);}" . PHP_EOL;

		return $script;
	}

	/**
	 * Define the fields inner markup.
	 *
	 * @param array $form The Form Object currently being processed.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$id              = absint( $this->id );
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		// Prepare the value of the input ID attribute.
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$value = esc_attr( $value );

		// Get the value of the youtubeID property for the current field.
		$youtubeID = $this->youtubeID;
		$youtubeSpeed = $this->youtubeSpeed;
		$youtubeAutoplay = $this->youtubeAutoplay;

		if(empty($youtubeSpeed)) {
			$youtubeSpeed = 2;
		}

		// Prepare the input classes.
		$size         = $this->size;
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $size . $class_suffix;

		// Prepare the other input attributes.
		$tabindex              = $this->get_tabindex();
		$logic_event           = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';
		$required = $this->isRequired ? 1 : 0;

		if(!empty($youtubeID)) {
			// Prepare the input tag for this field.
			$input = "
			<div id='yt_container_{$field_id}' class='yt-container-container'>
			<div id='video_container_{$field_id}' class='video-container' data-maxplayback='{$youtubeSpeed}' data-required='{$required}' style='border:2px;'>
			<iframe class='youtube-iframe' id='video_iframe_{$field_id}' width='805' height='453' src='https://www.youtube.com/embed/{$youtubeID}?rel=0&modestbranding=1&controls=1&enablejsapi=1";
			if(!empty($youtubeAutoplay) && $youtubeAutoplay == 'yes') {
				$input .= "&autoplay=1";
			}
			$input .="' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>
			</div></div>
			<div id='message_video_container_{$field_id}' style='margin-top:10px'></div>
			
			<script type='text/javascript'>
	
			</script>
			<input name='input_{$id}' id='{$field_id}' type='hidden' value='{$value}' class='{$class}' {$tabindex} {$logic_event} {$placeholder_attribute} {$required_attribute} {$invalid_attribute} {$disabled_text}'/>";
		} else {
			$input = '<p>The form administrator must enter a YouTube video ID, before this field can be used.</p>';
		}
		return sprintf( "<div class='ginput_container ginput_container_%s'>%s</div>", $this->type, $input );
	}
}
add_filter( 'gform_field_validation', function($result, $value, $form, $field){

	if($field->type == 'youtube' && $field->isRequired) {

		$fieldstring = '#input_'.$form['id'].'_'.$field->id;

		$fieldstring_encoded = base64_encode($fieldstring);

		$value_needed = '100.00% watched - '.$fieldstring_encoded;


		if ($value != $value_needed && $result['is_valid']) {
        	$result['is_valid'] = false;
        	$result['message'] = 'You must watch the video all the way through, from the start. You will know you are finished when the border turns green.';
		}
	}
    return $result;

}, 10, 4 );

GF_Fields::register( new YouTube_GF_Field() );
