<?php

class MAEX_MakeenExtension extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'maex-makeen-extension';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'makeen-extension';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * MAEX_MakeenExtension constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'makeen-extension', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );

		$plugin_dir_arr = explode( '/wp-content/', $this->plugin_dir );
		$plugin_dir_path_clean = str_replace(
			[
				'includes/',
			],
			[
				'',
			],
			$plugin_dir_arr[1]
		);

		$plugin_dir_url = (
			$_SERVER['REQUEST_SCHEME'] .'://'.
			$_SERVER['HTTP_HOST'] .'/wp-content/'.
			$plugin_dir_path_clean
		);

		$this->plugin_dir_url = $plugin_dir_url;

		parent::__construct( $name, $args );
	}
}

new MAEX_MakeenExtension;
