<?php
/**
 * This class provides the means to edit image's size and/or quality.
 *
 * @package Beans\Framework\API\Image
 *
 * @since   1.0.0
 */

/**
 * Image Editor
 *
 * @since   1.0.0
 * @ignore
 * @access  private
 *
 * @package Beans\Framework\API\Image
 */
final class _Beans_Image_Editor {

	/**
	 * The image source.
	 *
	 * @var string
	 */
	private $src;

	/**
	 * An array of editor arguments.
	 *
	 * @var array
	 */
	private $args = array();

	/**
	 * Returned format.
	 *
	 * @var bool
	 */
	private $output = false;

	/**
	 * Rebuilt path.
	 *
	 * @var string
	 */
	private $rebuilt_path;

	/**
	 * _Beans_Image_Editor constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $src         The image source.
	 * @param array  $args        An array of editor arguments, where the key is the {@see WP_Image_Editor} method name
	 *                            and the value is a numeric array of arguments for the method. Make sure that you
	 *                            specify all of the arguments the editor's method requires. Refer to
	 *                            {@link https://codex.wordpress.org/Class_Reference/WP_Image_Editor#Methods} for more
	 *                            information on the available methods and each method's arguments.
	 * @param string $output      Optional. Returned format. Accepts STRING, OBJECT, ARRAY_A, or ARRAY_N.
	 *                            Default is STRING.
	 */
	public function __construct( $src, array $args, $output = 'STRING' ) {
		$local_source       = beans_url_to_path( $this->src );
		$this->src          = file_exists( $local_source ) ? $local_source : $src;
		$this->args         = $args;
		$this->output       = $output;
		$this->rebuilt_path = $this->rebuild_image_path();
	}

	/**
	 * Run the editor.
	 *
	 * @since 1.0.0
	 *
	 * @return array|object|string
	 */
	public function run() {
		// Try to create image if it doesn't exist.
		if ( ! file_exists( $this->rebuilt_path ) ) {

			// Return original image source if it can't be edited.
			if ( ! $this->create_edited_image() ) {

				$array = array(
					'src'    => $this->src,
					'width'  => null,
					'height' => null,
				);

				switch ( $this->output ) {

					case 'STRING':
						return $this->src;

					case 'ARRAY_N':
						return array_values( $array );

					case 'ARRAY_A':
						return $array;

					case 'OBJECT':
						return (object) $array;
				}
			}
		}

		$src = beans_path_to_url( $this->rebuilt_path );

		// Simply return the source if dimensions are not requested.
		if ( 'STRING' === $this->output ) {
			return $src;
		}

		// Get the new image dimensions.
		list( $width, $height ) = @getimagesize( $this->rebuilt_path );

		$array = array(
			'src'    => $src,
			'width'  => $width,
			'height' => $height,
		);

		if ( 'ARRAY_N' === $this->output ) {
			return array_values( $array );
		}

		if ( 'OBJECT' === $this->output ) {
			return (object) $array;
		}

		return $array;
	}

	/**
	 * Edit the image and then store in rebuilt path.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Returns true when successful; else false is returned.
	 */
	private function create_edited_image() {
		$wp_editor = wp_get_image_editor( $this->src );

		// If an error occurred, bail out.
		if ( is_wp_error( $wp_editor ) ) {
			return false;
		}

		// Fire the editing task.
		foreach ( $this->args as $method => $args ) {

			if ( is_callable( array( $wp_editor, $method ) ) ) {
				call_user_func_array( array( $wp_editor, $method ), (array) $args );
			}
		}

		// Save the "edited" image as a new image.
		$wp_editor->save( $this->rebuilt_path );

		return ! is_wp_error( $wp_editor );
	}

	/**
	 * Rebuild the image's path.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function rebuild_image_path() {
		$upload_dir = beans_get_images_dir();
		$info       = pathinfo( preg_replace( '#\?.*#', '', $this->src ) );
		$query      = substr( md5( @serialize( $this->args ) ), 0, 7 ); // @codingStandardsIgnoreLine - Generic.PHP.NoSilencedErrors.Discouraged  This is a valid use case.
		$extension  = $info['extension'];
		$filename   = str_replace( '.' . $extension, '', $info['basename'] );

		return "{$upload_dir}{$filename}-{$query}.{$extension}";
	}
}
