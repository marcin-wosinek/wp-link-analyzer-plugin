<?php

namespace LINK_ANALYZER;

// Define the plugin base directory constant if not already defined.
if ( ! defined( 'LINK_ANALYZER_PLUGIN_DIR' ) ) {
	define( 'LINK_ANALYZER_PLUGIN_DIR', plugin_dir_path( dirname( __DIR__ ) ) );
}

// Core files.
require_once LINK_ANALYZER_PLUGIN_DIR . 'src/db.php';

// Component files.
require_once LINK_ANALYZER_PLUGIN_DIR . 'src/admin/components/screen-height-chart.php';

/**
 * View for the admin page
 */
function admin_page_view() {
	// Get screen height statistics.
	$screen_heights = DB_Handler::get_screen_height_stats();

	// Get link data.
	$links = DB_Handler::get_link_data();

	?>
	<div class="wrap">
		<h1>Link Analyzer</h1>

		<p>DB Version: <?php echo esc_html( get_option( 'link_analyzer_db_version' ) ); ?></p>

		<h2>Screen Height Statistics</h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>Screen Height</th>
					<th>Number of Sessions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $screen_heights as $stat ) : ?>
				<tr>
					<td><?php echo esc_html( $stat['screenHeight'] ); ?>px</td>
					<td><?php echo esc_html( $stat['numberOfSessions'] ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2>Screen Height Distribution</h2>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG content is safe as it's generated internally
		echo ScreenHeightChart::render( $screen_heights );
		?>

		<h2>Link Data</h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th>Link Text</th>
					<th>URL</th>
					<th>Number of Sessions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $links as $link ) : ?>
				<tr>
					<td><?php echo esc_html( $link['text'] ); ?></td>
					<td><?php echo esc_html( $link['href'] ); ?></td>
					<td><?php echo esc_html( $link['sessionCount'] ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
