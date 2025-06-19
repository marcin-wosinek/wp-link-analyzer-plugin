<?php
/**
 * Admin page view template.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 *
 * @var array $view_data Contains all data for the view.
 * @var string $view_data['db_version'] The database version.
 * @var array $view_data['screen_heights'] Screen height statistics.
 * @var array $view_data['links'] Link data.
 */

namespace LINK_ANALYZER;

use LINK_ANALYZER\Screen_Height_Chart;

// Ensure this file is not directly accessed.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1>Link Analyzer</h1>

	<p>DB Version: <?php echo esc_html( $view_data['db_version'] ); ?></p>

	<div class="link-analyzer-admin-actions">
		<button id="link-analyzer-remove-old-sessions" class="button button-primary">
			<?php esc_html_e( 'Remove Old Sessions', 'link-analyzer' ); ?>
		</button>
		<span id="link-analyzer-remove-old-sessions-status" class="status-message"></span>
	</div>

	<h2>Screen Height Statistics</h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Screen Height</th>
				<th>Number of Sessions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $view_data['screen_heights'] as $link_analyzer_stat ) : ?>
			<tr>
				<td><?php echo esc_html( $link_analyzer_stat['screenHeight'] ); ?>px</td>
				<td><?php echo esc_html( $link_analyzer_stat['numberOfSessions'] ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h2>Screen Height Distribution</h2>
	<?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG content is safe as it's generated internally
	echo Screen_Height_Chart::render( $view_data['screen_heights'] );
	?>

	<h2>Links above the fold</h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Link Text</th>
				<th>URL</th>
				<th>Number of Sessions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $view_data['links'] as $link_analyzer_link ) : ?>
			<tr>
				<td><?php echo esc_html( $link_analyzer_link['text'] ); ?></td>
				<td><?php echo esc_html( $link_analyzer_link['href'] ); ?></td>
				<td><?php echo esc_html( $link_analyzer_link['sessionCount'] ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
