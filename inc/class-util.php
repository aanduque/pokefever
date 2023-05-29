<?php
// phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent,WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting_path_disclosure_error_reporting
/**
 * The Util class.
 *
 * @package Pokefever
 */

namespace Pokefever;

use Exception;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

/**
 * Class Util.
 *
 * Contains utility methods used throughout the theme
 *
 * @package Pokefever
 */
class Util {

	/**
	 * Calls an API endpoint and returns the response, throwing an exception if something goes wrong.
	 *
	 * This is a simple wrapper around wp_remote_get that throws an exception if the
	 * response is a WP_Error and handles json decoding the response body.
	 *
	 * @throws Exception Throws if the response is a WP_Error.
	 *
	 * @param mixed $url The URL to call.
	 * @param array $query_args The query arguments to pass to the URL.
	 * @return mixed
	 */
	public static function call_api( $url, $query_args = array() ) {
		$response = wp_remote_get(
			$url,
			array(
				'body' => $query_args,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		return $data;
	}

	/**
	 * Downloads an image from the specified URL and attaches it to a post as a post thumbnail.
	 *
	 * @throws Exception Throws if for whatever reason we are not able to create the attachment.
	 *
	 * @param string $image_url    The URL of the image to download.
	 * @param int    $post_id The post ID the post thumbnail is to be associated with.
	 * @param string $desc    Optional. Description of the image.
	 * @return int Attachment ID, WP_Error object otherwise.
	 */
	public static function download_and_set_image_as_thumbnail( $image_url, $post_id, $desc ) : int {

		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );

		if ( ! $matches ) {
			throw new Exception( 'Image URL does not contain a valid image extension' );
		}

		/**
		 * Makes sure we have the required WordPress functions loaded, even though
		 * we might not be running this inside the proper admin context.
		 */
		require_once ABSPATH . '/wp-admin/includes/file.php';
		require_once ABSPATH . '/wp-admin/includes/media.php';
		require_once ABSPATH . '/wp-admin/includes/image.php';

		$file_array         = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $image_url );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {

			throw new Exception( $file_array['tmp_name'] );

		}

		// Do the validation and storage stuff.
		$attachment_id = media_handle_sideload( $file_array, $post_id, $desc );

		// If error storing permanently, unlink.
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $file_array['tmp_name'] );

			throw new Exception( $attachment_id->get_error_message() );
		}

		/**
		 * Set the post thumbnail to the newly added image.
		 */
		set_post_thumbnail( $post_id, $attachment_id );

		/**
		 * Return the attachment ID, as it can be useful for other things.
		 */
		return $attachment_id;

	}

	/**
	 * Extracts colors from an image and return them as an array.
	 *
	 * You can return as many colors as you like, but you need to specify the names of the colors.
	 * Colors are returned in order of relevance in the image.
	 *
	 * By default, two colors are returned: primary and secondary.
	 *
	 * To return more colors, simply add more color names to the array.
	 * e.g. $color_names = array( 'primary', 'secondary', 'tertiary', 'whatever' );
	 *
	 * @param string     $image_path The path to the image.
	 * @param null|array $color_names The names of the colors to return.
	 * @return array<array-key, mixed>
	 */
	public static function extract_colors_from_image( string $image_path, ?array $color_names = null ) {

		$default_error_reporting_level = error_reporting();

		error_reporting( 0 );

		$color_names ??= array(
			'primary',
			'secondary',
		);

		$palette = Palette::fromFilename( $image_path, Color::fromHexToInt( '#FFFFFF' ) );

		$colors = ( new ColorExtractor( $palette ) )->extract( count( $color_names ) );

		error_reporting( $default_error_reporting_level );

		return collect( $colors )->mapWithKeys(
			function( $color, $index ) use ( $color_names ) {
				return array( $color_names[ $index ] => Color::fromIntToHex( $color ) );
			}
		)->toArray();

	}

	/**
	 * Converts a hex color to an RGB array.
	 *
	 * @param mixed $hex The original hex color.
	 * @return object An object containing the RGB values as r, g, and b properties.
	 */
	public static function hex_to_rgb( $hex ) : object {
		return (object) Color::fromIntToRgb( Color::fromHexToInt( $hex ) );
	}

}
