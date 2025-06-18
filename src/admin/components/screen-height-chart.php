<?php

namespace LINK_ANALYZER;

class ScreenHeightChart {

	/**
	 * Render the SVG bar chart.
	 *
	 * @param array $data Array of screen height data from get_screen_height_stats().
	 * @return string SVG markup.
	 */
	public static function render( $data ) {
		// Calculate chart dimensions.
		$chart_width  = 800;
		$chart_height = 400;
		$bar_width    = 30;
		$bar_spacing  = 10;
		$margin       = 40;

		// Find max values for scaling.
		$max_height = 0;
		foreach ( $data as $item ) {
			$max_height = max( $max_height, $item['numberOfSessions'] );
		}

		// Calculate bar heights.
		$bar_heights = array_map(
			function ( $item ) use ( $max_height, $chart_height, $margin ) {
				return ( $item['numberOfSessions'] / $max_height ) * ( $chart_height - 2 * $margin );
			},
			$data
		);

		// Generate SVG.
		$svg = "<svg width='" . $chart_width . "' height='" . $chart_height . "' xmlns='http://www.w3.org/2000/svg'>";

		// Add title.
		$svg .= "<text x='50%' y='20' text-anchor='middle' font-size='16' font-weight='bold'>Screen Height Distribution</text>";

		// Add X axis label.
		$svg .= "<text x='50%' y='" . ( $chart_height - 10 ) . "' text-anchor='middle' font-size='12'>Screen Height (px)</text>";

		// Add Y axis label.
		$svg .= "<text x='" . ( $margin - 20 ) . "' y='" . ( $chart_height / 2 ) . "' text-anchor='middle' font-size='12' transform='rotate(-90 " . ( $margin - 20 ) . ',' . ( $chart_height / 2 ) . ")'>Number of Sessions</text>";

		// Add X axis line.
		$svg .= "<line x1='" . $margin . "' y1='" . ( $chart_height - $margin ) . "' x2='" . ( $chart_width - $margin ) . "' y2='" . ( $chart_height - $margin ) . "' stroke='black' stroke-width='1' />";

		// Add Y axis line.
		$svg .= "<line x1='" . $margin . "' y1='" . $margin . "' x2='" . $margin . "' y2='" . ( $chart_height - $margin ) . "' stroke='black' stroke-width='1' />";

		// Add bars and labels.
		$x = $margin;
		foreach ( $data as $index => $item ) {
			$bar_height = $bar_heights[ $index ];
			$y          = $chart_height - $margin - $bar_height;

			// Add bar.
			$svg .= "<rect x='" . $x . "' y='" . $y . "' width='" . $bar_width . "' height='" . $bar_height . "' fill='#4CAF50' opacity='0.8' />";

			// Add data label.
			$svg .= "<text x='" . ( $x + $bar_width / 2 ) . "' y='" . ( $y - 5 ) . "' text-anchor='middle' font-size='10' fill='black'>" . $item['numberOfSessions'] . '</text>';

			// Add screen height label.
			$svg .= "<text x='" . ( $x + $bar_width / 2 ) . "' y='" . ( $chart_height - $margin + 20 ) . "' text-anchor='middle' font-size='10' fill='black'>" . $item['screenHeight'] . 'px</text>';

			$x += $bar_width + $bar_spacing;
		}

		$svg .= '</svg>';

		return $svg;
	}
}
