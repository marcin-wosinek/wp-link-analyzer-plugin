<?php
/**
 * View Renderer.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

namespace LINK_ANALYZER;

/**
 * View Renderer class for handling admin views.
 */
class View_Renderer {
	/**
	 * Base path for views.
	 *
	 * @var string Base path for views.
	 */
	private $views_path;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->views_path = dirname( __DIR__ ) . '/admin/views/';
	}

	/**
	 * Render a view with data.
	 *
	 * @param string $view The view name.
	 * @param array  $data The data to pass to the view.
	 * @return void
	 */
	public function render( $view, $data = array() ) {
		// Include the view file.
		$view_file = $this->views_path . $view . '.php';

		// Make data available for the view.
		$view_data = $data;

		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			wp_die( sprintf( 'View file not found: %s', esc_html( $view_file ) ) );
		}
	}
}
