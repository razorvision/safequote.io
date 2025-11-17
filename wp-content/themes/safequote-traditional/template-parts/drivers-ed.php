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
			<!-- BookOpen icon from lucide-react -->
			<svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
				<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
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
			<!-- MapPin icon from lucide-react -->
			<svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
				<circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
			</svg>
			<input
				type="text"
				id="location-input"
				placeholder="<?php esc_attr_e( 'Enter your City or ZIP Code', 'safequote-traditional' ); ?>"
				class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10 h-12 text-base"
				required
			/>
		</div>

		<!-- Search Local Classes Button -->
		<button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full h-12 text-base">
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
		<button type="button" id="browse-online-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2 w-full h-12 text-base flex items-center gap-2">
			<!-- Globe icon from lucide-react -->
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></circle>
				<path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
				<path d="M2 12h20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
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
