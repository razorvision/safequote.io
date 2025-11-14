<?php
/**
 * Template part: Login Modal
 *
 * Modal dialog for user registration and login
 *
 * @package SafeQuote_Traditional
 * @since 1.0.0
 */
?>

<div id="login-modal" class="modal-backdrop hidden" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
	<div class="modal-content" role="document">
		<!-- Modal Header -->
		<div class="modal-header">
			<h2 id="login-modal-title" class="text-2xl font-bold text-center text-gray-900 flex-grow">
				<?php esc_html_e( 'Unlock Your Personalized Dashboard', 'safequote-traditional' ); ?>
			</h2>
			<button
				id="close-login-modal"
				class="text-gray-500 hover:text-gray-700 transition-colors"
				aria-label="<?php esc_attr_e( 'Close modal', 'safequote-traditional' ); ?>"
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
				</svg>
			</button>
		</div>

		<!-- Modal Body -->
		<div class="modal-body">
			<!-- Description -->
			<p class="text-center text-gray-600 mb-6">
				<?php esc_html_e( 'Create a free account to get the most out of SafeQuote.', 'safequote-traditional' ); ?>
			</p>

			<!-- Benefits List -->
			<ul class="space-y-3 mb-6">
				<!-- Heart Icon - Save Favorites -->
				<li class="flex items-center gap-3">
					<div class="bg-gray-100 p-2 rounded-full flex-shrink-0">
						<svg class="w-5 h-5 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
						</svg>
					</div>
					<span class="text-gray-700">
						<?php esc_html_e( 'Save your favorite cars to your personal garage.', 'safequote-traditional' ); ?>
					</span>
				</li>

				<!-- Zap Icon - Faster Results -->
				<li class="flex items-center gap-3">
					<div class="bg-gray-100 p-2 rounded-full flex-shrink-0">
						<svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0110 2v5H5a1 1 0 00-.82 1.573l7 10A1 1 0 0013 17v-5h5a1 1 0 00.82-1.573l-7-10a1 1 0 00-1.48 0z" clip-rule="evenodd"></path>
						</svg>
					</div>
					<span class="text-gray-700">
						<?php esc_html_e( 'Get faster, personalized search results.', 'safequote-traditional' ); ?>
					</span>
				</li>

				<!-- Check Circle Icon - Track Quotes -->
				<li class="flex items-center gap-3">
					<div class="bg-gray-100 p-2 rounded-full flex-shrink-0">
						<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
						</svg>
					</div>
					<span class="text-gray-700">
						<?php esc_html_e( 'Track and compare insurance quotes easily.', 'safequote-traditional' ); ?>
					</span>
				</li>
			</ul>
		</div>

		<!-- Modal Footer -->
		<div class="modal-footer flex-col gap-3">
			<button id="login-submit-btn" class="btn btn-primary w-full">
				<?php esc_html_e( 'Login or Sign Up (Coming Soon!)', 'safequote-traditional' ); ?>
			</button>
			<button id="login-cancel-btn" class="btn btn-outline w-full">
				<?php esc_html_e( 'Maybe Later', 'safequote-traditional' ); ?>
			</button>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const modal = document.getElementById('login-modal');
	const backdrop = modal;
	const closeBtn = document.getElementById('close-login-modal');
	const cancelBtn = document.getElementById('login-cancel-btn');
	const submitBtn = document.getElementById('login-submit-btn');

	// Close modal function
	function closeModal() {
		modal.classList.add('hidden');
		document.body.style.overflow = 'auto';
	}

	// Open modal function
	window.openLoginModal = function() {
		modal.classList.remove('hidden');
		document.body.style.overflow = 'hidden';
	};

	// Event listeners
	closeBtn?.addEventListener('click', closeModal);
	cancelBtn?.addEventListener('click', closeModal);
	backdrop?.addEventListener('click', function(e) {
		if (e.target === backdrop) {
			closeModal();
		}
	});

	submitBtn?.addEventListener('click', function() {
		if (typeof showNotification === 'function') {
			showNotification('info', 'Coming Soon', '🚧 Login isn\'t implemented yet, but it\'s coming soon! 🚀');
		}
		closeModal();
	});

	// Close on Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
			closeModal();
		}
	});
});
</script>
