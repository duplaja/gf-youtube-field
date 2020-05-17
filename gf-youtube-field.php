<?php
/*
Plugin Name: GF YouTube Field Add-On
Plugin URI: https://dandulaney.com
Description: A YouTube field add-on to allow making videos mandatory to continue, and track watch percentage for non-mandatory.
Version: 1.0.0
Author: Dan Dulaney
Author URI: https://dandulaney.com
Text Domain: youtubefieldaddon
Domain Path: /languages
GitHub Plugin URI: https://github.com/duplaja/gf-youtube-field

------------------------------------------------------------------------
Copyright 2020 Dan Dulaney.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'GF_YOUTUBE_FIELD_ADDON_VERSION', '1.0.0' );

add_action( 'gform_loaded', array( 'GF_YouTube_Field_AddOn_Bootstrap', 'load' ), 5 );

class GF_YouTube_Field_AddOn_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfyoutubefieldaddon.php' );

        GFAddOn::register( 'GFYouTubeFieldAddOn' );
    }

}