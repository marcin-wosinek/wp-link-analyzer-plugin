// TODO decide on the trade-off: as we wait for images to load, the user may be
// scrolling down.
window.addEventListener("load", function (event) {
	console.log("Link Analyzer Plugin: Hello World!");

	const screenWidth = window.innerWidth,
		screenHeight = window.innerHeight;

	console.log(`${screenWidth}, ${screenHeight}`);

	// all links that are not WP admin bar
	const links = document.querySelectorAll(
		"body > div:not(#wpadminbar) a[href]",
	);

	// consider using lodash
	const aboveFoldLinks = Array.from(links).filter((element) => {
		const { y } = element.getBoundingClientRect();

		return y < screenHeight;
	});

	console.log(aboveFoldLinks);
});
