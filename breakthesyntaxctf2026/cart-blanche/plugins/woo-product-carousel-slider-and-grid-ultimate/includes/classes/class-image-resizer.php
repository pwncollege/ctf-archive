<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image resizer class.
 */
class WPCSU_Image_Resizer {

	/**
	 * The attachment image ID
	 *
	 * @var int
	 */
	protected $attachment_id;

	/**
	 * Constructor
	 *
	 * @param  int $attachment_id
	 * @return void
	 */
	public function __construct( $attachment_id ) {
		$this->attachment_id = $attachment_id;
	}

	/**
	 * Resizes an attachment image
	 *
	 * @param int     $width
	 * @param int     $height
	 * @param boolean $crop
	 * @param int     $quality
	 * @return array
	 */
	public function resize( $width, $height, $crop = true, $quality = 100 ) {
		// Get the attachment
		$attachment_url = wp_get_attachment_url( $this->attachment_id, 'full' );

		// Bail if we don't have an attachment URL
		if ( ! $attachment_url) {
			return array(
				'url'    => $attachment_url,
				'width'  => $width,
				'height' => $height,
			);
		}

		// Get the image file path
		$document_root = str_replace( '\\', '/', realpath( $_SERVER['DOCUMENT_ROOT'] ) );
		$file_path = $document_root . wp_parse_url( $attachment_url, PHP_URL_PATH );

		// Additional handling for multisite
		if ( is_multisite() ) {
			global $blog_id;

			$blog_details = get_blog_details( $blog_id );
			$file_path    = str_replace( $blog_details->path . 'files/', '/wp-content/blogs.dir/' . $blog_id . '/files/', $file_path );
		}

		// Destination width and height variables
		$dest_width  = apply_filters( 'easingslider_resize_image_width',  $width,  $attachment_url );
		$dest_height = apply_filters( 'easingslider_resize_image_height', $height, $attachment_url );

		// File name suffix (appended to original file name)
		$suffix = "{$dest_width}x{$dest_height}";

		// Some additional info about the image
		$info = pathinfo( $file_path );
		$dir  = $info['dirname'];
		$ext  = $info['extension'];
		$name = wp_basename( $file_path, ".{$ext}" );

		// Suffix applied to filename
		$suffix = "{$dest_width}x{$dest_height}";

		// Get the destination file name
		$dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";

		// Execute the resizing if resized image doesn't already exist.
		if ( ! file_exists( $dest_file_name ) ) {

			// Load Wordpress Image Editor
			$editor = wp_get_image_editor( $file_path );

			// Bail if we encounter a WP_Error
			if ( is_wp_error( $editor ) ) {
				return array(
					'url'    => $attachment_url,
					'width'  => $width,
					'height' => $height,
				);
			}

			// Set the quality
			$editor->set_quality( $quality );

			// Get the original image size
			$size        = $editor->get_size();
			$orig_width  = $size['width'];
			$orig_height = $size['height'];

			$src_x = $src_y = 0;
			$src_w = $orig_width;
			$src_h = $orig_height;

			// Handle cropping
			if ( $crop ) {
				$cmp_x = $orig_width / $dest_width;
				$cmp_y = $orig_height / $dest_height;

				// Calculate x or y coordinate, and width or height of source
				if ( $cmp_x > $cmp_y ) {
					$src_w = round( $orig_width / $cmp_x * $cmp_y);
					$src_x = round( ( $orig_width - ( $orig_width / $cmp_x * $cmp_y ) ) / 2 );
				} else if ($cmp_y > $cmp_x) {
					$src_h = round( $orig_height / $cmp_y * $cmp_x );
					$src_y = round( ( $orig_height - ( $orig_height / $cmp_y * $cmp_x ) ) / 2 );
				}
			}

			// Time to crop the image
			$editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );

			// Now let's save the image
			$saved = $editor->save( $dest_file_name );

			// Get resized image information
			$resized_url    = str_replace( basename( $attachment_url ), basename( $saved['path'] ), $attachment_url );
			$resized_width  = $saved['width'];
			$resized_height = $saved['height'];
			$resized_type   = $saved['mime-type'];

			/**
			 * Add the resized dimensions to original image metadata
			 *
			 * This ensures our resized images are deleted when the original image is deleted from the Media Library
			 */
			$metadata = wp_get_attachment_metadata( $this->attachment_id );
			if ( isset( $metadata['image_meta'] ) ) {
				$metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
				wp_update_attachment_metadata( $this->attachment_id, $metadata );
			}

			// Create the image array
			$resized_image = array(
				'url'    => $resized_url,
				'width'  => $resized_width,
				'height' => $resized_height,
				'type'   => $resized_type
			);
		} else {
			$resized_image = array(
				'url'    => str_replace( basename( $attachment_url ), basename( $dest_file_name ), $attachment_url ),
				'width'  => $dest_width,
				'height' => $dest_height,
				'type'   => $ext
			);
		}

		// And we're done!
		return $resized_image;
	}
}
