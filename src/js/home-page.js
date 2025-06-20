/**
 * Link Analyzer frontend JavaScript for the home page.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

// TODO decide on the trade-off: as we wait for images to load, the user may be
// already scrolling down, therefore affecting what links are above the fold.
window.addEventListener("load", function (event) {
	const screenWidth = window.innerWidth,
		screenHeight = window.innerHeight;

	// all links that are not WP admin bar
	const allLinks = document.querySelectorAll("body > div:not(#wpadminbar) a[href]");

	const aboveFoldLinks = Array.from(allLinks).filter((element) => {
		const { y } = element.getBoundingClientRect();

		return y < screenHeight;
	});

	const linkData = aboveFoldLinks.map((element) => {
		return {
			text: element.innerText,
			href: element.href,
		};
	});

	const data = { screenWidth, screenHeight, linkData };

	// optimistic data send
	wp.apiFetch({
		path: "/link-analyzer/v1/add-data",
		method: "POST",
		data,
	});
});
