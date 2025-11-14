<?php
/**
 * Template part: Driver's Ed
 *
 * Displays driver's education course search interface
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 */
?>

<div id="drivers-ed" class="bg-gray-50 rounded-2xl p-8 shadow-sm border border-gray-100 fade-in">
	<!-- Header -->
	<div class="text-center mb-8">
		<div class="inline-block bg-primary/10 p-3 rounded-full mb-4">
			<svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
			</svg>
		</div>
		<h2 class="text-3xl font-bold text-gray-900">
			<?php esc_html_e( 'Find Driver\'s Ed Classes', 'safequote-traditional' ); ?>
		</h2>
		<p class="text-gray-600 mt-2">
			<?php esc_html_e( 'Search for local and online courses to get your teen road-ready.', 'safequote-traditional' ); ?>
		</p>
	</div>

	<!-- Search Form -->
	<form id="drivers-ed-form" class="max-w-xl mx-auto space-y-4">
		<!-- Location Input -->
		<div class="relative">
			<svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
			</svg>
			<input
				type="text"
				id="location-input"
				placeholder="<?php esc_attr_e( 'Enter your City or ZIP Code', 'safequote-traditional' ); ?>"
				class="form-input pl-10 h-12 text-base"
				required
			/>
		</div>

		<!-- Search Local Classes Button -->
		<button type="submit" class="btn btn-primary w-full h-12 text-base">
			<?php esc_html_e( 'Search Local Classes', 'safequote-traditional' ); ?>
		</button>

		<!-- Divider -->
		<div class="flex items-center justify-center my-4">
			<span class="flex-grow bg-gray-200 h-px"></span>
			<span class="px-4 text-gray-500 font-medium text-sm">
				<?php esc_html_e( 'OR', 'safequote-traditional' ); ?>
			</span>
			<span class="flex-grow bg-gray-200 h-px"></span>
		</div>

		<!-- Browse Online Classes Button -->
		<button type="button" id="browse-online-btn" class="btn btn-secondary w-full h-12 text-base flex items-center justify-center gap-2">
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20H7m6 0a9 9 0 110-18 9 9 0 010 18z"></path>
			</svg>
			<?php esc_html_e( 'Browse Online Classes', 'safequote-traditional' ); ?>
		</button>
	</form>

	<!-- Results Container (initially hidden) -->
	<div id="drivers-ed-results" class="hidden mt-8 space-y-6">
		<!-- Results will be populated dynamically -->
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const form = document.getElementById('drivers-ed-form');
	const locationInput = document.getElementById('location-input');
	const browseOnlineBtn = document.getElementById('browse-online-btn');
	const resultsContainer = document.getElementById('drivers-ed-results');

	// Handle form submission
	form?.addEventListener('submit', function(e) {
		e.preventDefault();
		const location = locationInput.value.trim();
		if (!location) return;

		// Show notification
		if (typeof showNotification === 'function') {
			showNotification('info', 'Coming Soon', '🚧 This feature isn\'t implemented yet—but don\'t worry! You can request it in your next prompt! 🚀');
		}

		// Dispatch custom event for future implementation
		const searchEvent = new CustomEvent('driversEdSearch', {
			detail: {
				location: location,
				type: 'local'
			}
		});
		document.dispatchEvent(searchEvent);
	});

	// Handle browse online button
	browseOnlineBtn?.addEventListener('click', function(e) {
		e.preventDefault();
		if (typeof showNotification === 'function') {
			showNotification('info', 'Coming Soon', '🚧 This feature isn\'t implemented yet—but don\'t worry! You can request it in your next prompt! 🚀');
		}

		// Dispatch custom event for future implementation
		const browseEvent = new CustomEvent('driversEdBrowse', {
			detail: {
				type: 'online'
			}
		});
		document.dispatchEvent(browseEvent);
	});
});
</script>
