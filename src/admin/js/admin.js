/**
 * Link Analyzer admin JavaScript.
 *
 * @package     Link Analyzer
 * @since       1.0
 * @author      Marcin Wosinek
 * @license     GPL-2.0-or-later
 */

(function () {
	"use strict";
	document.addEventListener("DOMContentLoaded", function () {
		const removeOldSessionsButton = document.getElementById(
			"link-analyzer-remove-old-sessions",
		);
		const statusMessage = document.getElementById(
			"link-analyzer-remove-old-sessions-status",
		);

		if (!removeOldSessionsButton) {
			return;
		}

		removeOldSessionsButton.addEventListener("click", function () {
			// Disable button and show processing message
			removeOldSessionsButton.disabled = true;
			statusMessage.textContent = linkAnalyzerAdmin.i18n.processing;
			statusMessage.style.color = "";

			// Use wp.apiFetch to make the API call
			wp.apiFetch({
				path: "/link-analyzer/v1/admin/remove-old-sessions",
				method: "DELETE",
			})
				.then(function (response) {
					// Handle success
					if (response.success) {
						statusMessage.textContent = response.message;
						statusMessage.style.color = "green";
					} else {
						statusMessage.textContent = response.message;
						statusMessage.style.color = "red";
					}
				})
				.catch(function (error) {
					// Handle error
					let errorMessage = linkAnalyzerAdmin.i18n.error;

					if (error.message) {
						errorMessage = error.message;
					}

					statusMessage.textContent = errorMessage;
					statusMessage.style.color = "red";
				})
				.finally(function () {
					// Re-enable button
					removeOldSessionsButton.disabled = false;
				});
		});
	});
})();
