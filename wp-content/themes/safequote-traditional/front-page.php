<?php
/**
 * The template for displaying the front page
 *
 * @package SafeQuote_Traditional
 */

get_header();
?>

<main id="primary" class="site-main">
    <!-- Hero Section - EXACT MATCH to React Hero.jsx -->
    <section class="hero relative text-white py-20 md:py-32 overflow-hidden">
        <!-- Background Image and Gradients -->
        <div class="absolute inset-0">
            <img
                alt="Mother and son examining a car together"
                class="w-full h-full object-cover"
                src="<?php echo SAFEQUOTE_THEME_URI; ?>/assets/images/hero-parent-teen-car.jpg" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-primary/30 to-transparent"></div>
        </div>

        <!-- Hero Content -->
        <div class="container mx-auto px-4 relative">
            <div class="text-center max-w-3xl mx-auto space-y-8 hero-animate">
                <h1 class="text-4xl md:text-6xl font-bold" style="text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                    <?php esc_html_e('Find the Safest Car & Lowest Insurance Rate', 'safequote-traditional'); ?>
                </h1>
                <p class="text-xl md:text-2xl text-white/90" style="text-shadow: 0 1px 5px rgba(0,0,0,0.5);">
                    <?php esc_html_e("Discover top-rated safe cars and compare insurance quotes instantly. Your teen's safety and your budget, perfectly aligned.", 'safequote-traditional'); ?>
                </p>
                <button id="hero-get-started" class="inline-flex items-center bg-white text-primary hover:bg-white/90 text-lg px-8 py-6 rounded-lg font-semibold transition-all">
                    <?php esc_html_e('Get Started', 'safequote-traditional'); ?>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <div class="container mx-auto px-4 py-12 space-y-20" id="start">

        <!-- Features Section - EXACT MATCH to React Features.jsx -->
        <section class="features py-16 bg-white fade-in" id="features">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                        <?php esc_html_e('Your Journey to a Safer Drive Starts Here', 'safequote-traditional'); ?>
                    </h2>
                    <p class="text-gray-600 max-w-3xl mx-auto">
                        <?php esc_html_e("Find the ideal safe car, get insurance quotes for a vehicle you already own, or find the best driver's ed classes.", 'safequote-traditional'); ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Find a Safe Car Button -->
                    <div class="w-full transform hover:scale-105 transition-transform">
                        <button id="flow-find-car" class="w-full h-full p-6 text-lg flex flex-col gap-2 bg-gradient-to-r from-primary to-teal-500 hover:from-primary/90 hover:to-teal-500/90 text-white shadow-lg rounded-lg transition-all">
                            <!-- Car Icon (matching React's lucide-react Car) -->
                            <svg class="w-8 h-8 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 002 12v4c0 .6.4 1 1 1h2"></path>
                                <circle cx="7" cy="17" r="2" stroke-width="2"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6"></path>
                                <circle cx="17" cy="17" r="2" stroke-width="2"></circle>
                            </svg>
                            <span><?php esc_html_e('Find a Safe Car', 'safequote-traditional'); ?></span>
                            <span class="text-sm font-normal text-white/80"><?php esc_html_e('Compare safety & prices', 'safequote-traditional'); ?></span>
                        </button>
                    </div>

                    <!-- Get Insurance Quotes Button -->
                    <div class="w-full transform hover:scale-105 transition-transform">
                        <button id="flow-get-insurance" class="w-full h-full p-6 text-lg border-2 border-primary text-primary hover:bg-primary/10 hover:text-primary/90 flex flex-col gap-2 shadow-lg rounded-lg transition-all">
                            <!-- Shield Icon -->
                            <svg class="w-8 h-8 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                            </svg>
                            <span><?php esc_html_e('Get Insurance Quotes', 'safequote-traditional'); ?></span>
                            <span class="text-sm font-normal text-primary/80"><?php esc_html_e('For a car you own', 'safequote-traditional'); ?></span>
                        </button>
                    </div>

                    <!-- Find Driver's Ed Button -->
                    <div class="w-full transform hover:scale-105 transition-transform">
                        <button id="flow-drivers-ed" class="w-full h-full p-6 text-lg border-2 border-primary text-primary hover:bg-primary/10 hover:text-primary/90 flex flex-col gap-2 shadow-lg rounded-lg transition-all">
                            <!-- Book Open Icon -->
                            <svg class="w-8 h-8 mb-2 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <span><?php esc_html_e("Find Driver's Ed", 'safequote-traditional'); ?></span>
                            <span class="text-sm font-normal text-primary/80"><?php esc_html_e('Local & online classes', 'safequote-traditional'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Safety Ratings Section - EXACT MATCH to React SafetyRatings.jsx -->
        <section class="safety-ratings py-16 bg-white rounded-2xl shadow-lg border border-gray-100" id="safety-ratings">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <svg class="w-12 h-12 mx-auto text-primary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"></path>
                    </svg>
                    <h2 class="text-3xl md:text-4xl font-bold mb-3 text-gray-900">
                        <?php esc_html_e('Check Vehicle Safety Ratings', 'safequote-traditional'); ?>
                    </h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">
                        <?php esc_html_e('Get official 5-star safety ratings from the National Highway Traffic Safety Administration (NHTSA).', 'safequote-traditional'); ?>
                    </p>
                </div>

                <!-- Safety Ratings Form - EXACT MATCH to React SafetyRatings.jsx -->
                <form id="safety-ratings-form" class="max-w-3xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-8">
                    <div class="md:col-span-1">
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Year', 'safequote-traditional'); ?></label>
                        <input id="year" type="text" value="2024" placeholder="e.g., 2024" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                    </div>
                    <div class="md:col-span-1">
                        <label for="make" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Make', 'safequote-traditional'); ?></label>
                        <input id="make" type="text" value="Toyota" placeholder="e.g., Toyota" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                    </div>
                    <div class="md:col-span-1">
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e('Model', 'safequote-traditional'); ?></label>
                        <input id="model" type="text" value="Camry" placeholder="e.g., Camry" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                    </div>
                    <button type="submit" class="w-full md:col-span-1 bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-md transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <?php esc_html_e('Check Rating', 'safequote-traditional'); ?>
                    </button>
                </form>

                <!-- Safety Ratings Results (will be populated via JavaScript) -->
                <div id="safety-ratings-results" class="hidden">
                    <!-- Results will be dynamically inserted here -->
                </div>
            </div>
        </section>

        <!-- Flow Content Container (for dynamic content) -->
        <div id="flow-content" class="hidden space-y-12">
            <!-- Dynamic content will be loaded here based on flow selection -->
            <button id="start-over" class="text-primary hover:underline font-semibold">
                &larr; <?php esc_html_e('Start Over', 'safequote-traditional'); ?>
            </button>

            <!-- Vehicle Search Section (shown in findCar flow) -->
            <div id="vehicles-section" class="hidden space-y-8">
                <div id="vehicles">
                    <?php
                    // Include Top Safety Picks template part
                    set_query_var('vehicles', safequote_get_top_safety_picks(4)); // Get top 4 safety-rated vehicles
                    get_template_part('template-parts/top-safety-picks');
                    ?>
                </div>

                <?php
                // Include Search Filters template part
                set_query_var('filters', array(
                    'condition'       => 'all',
                    'vehicleType'     => 'all',
                    'minSafetyRating' => 0,
                    'maxPrice'        => 50000,
                ));
                get_template_part('template-parts/search-filters');
                ?>

                <?php
                // Include Vehicle Grid template part
                set_query_var('vehicles', safequote_get_vehicles()); // Get all vehicles
                get_template_part('template-parts/vehicle-grid');
                ?>

                <!-- Insurance Comparison (shown when vehicle is selected in findCar flow) -->
                <div id="vehicle-insurance-section" class="hidden">
                    <?php
                    set_query_var('quotes', array());
                    set_query_var('vehicle', array('make' => 'your', 'model' => 'car'));
                    set_query_var('section_id', 'vehicle-insurance-comparison');
                    get_template_part('template-parts/insurance-comparison');
                    ?>
                </div>
            </div>

            <!-- Insurance Section (shown in getInsurance flow) -->
            <div id="insurance-section" class="hidden space-y-8">
                <?php
                // Include Insurance Comparison template part
                set_query_var('quotes', array());
                set_query_var('vehicle', array('year' => date('Y'), 'make' => 'your', 'model' => 'car'));
                set_query_var('section_id', 'standalone-insurance-comparison');
                get_template_part('template-parts/insurance-comparison');
                ?>
            </div>

            <!-- Driver's Ed Section (shown in driversEd flow) -->
            <div id="drivers-ed-section" class="hidden space-y-8">
                <?php get_template_part('template-parts/drivers-ed'); ?>
            </div>
        </div>

        <!-- Login Modal -->
        <?php get_template_part('template-parts/modal-login'); ?>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const flowContent = document.getElementById('flow-content');
    const featuresSection = document.getElementById('features');
    const safetyRatingsSection = document.getElementById('safety-ratings');
    const startOverBtn = document.getElementById('start-over');
    const vehiclesSection = document.getElementById('vehicles-section');
    const insuranceSection = document.getElementById('insurance-section');
    const driversEdSection = document.getElementById('drivers-ed-section');
    const vehicleInsuranceSection = document.getElementById('vehicle-insurance-section');

    // Flow button listeners
    const flowFindCarBtn = document.getElementById('flow-find-car');
    const flowGetInsuranceBtn = document.getElementById('flow-get-insurance');
    const flowDriversEdBtn = document.getElementById('flow-drivers-ed');

    // Flow state
    let currentFlow = null;
    let selectedVehicle = null;

    function setFlow(flowName) {
        console.log('Setting flow:', flowName);
        currentFlow = flowName;

        // Hide features and safety ratings, show flow content
        featuresSection.style.display = 'none';
        safetyRatingsSection.style.display = 'none';
        flowContent.classList.remove('hidden');

        // Hide all flow sections
        vehiclesSection.classList.add('hidden');
        insuranceSection.classList.add('hidden');
        driversEdSection.classList.add('hidden');

        // Show the appropriate section based on flow
        if (flowName === 'findCar') {
            vehiclesSection.classList.remove('hidden');
            vehicleInsuranceSection.classList.add('hidden'); // Hide insurance until vehicle selected
            setTimeout(() => {
                document.getElementById('vehicles')?.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        } else if (flowName === 'getInsurance') {
            console.log('Insurance section found:', insuranceSection);
            console.log('Insurance section classes before:', insuranceSection.className);
            insuranceSection.classList.remove('hidden');
            console.log('Insurance section classes after:', insuranceSection.className);
            setTimeout(() => {
                const scrollTarget = document.getElementById('standalone-insurance-comparison');
                console.log('Scroll target found:', scrollTarget);
                scrollTarget?.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        } else if (flowName === 'driversEd') {
            driversEdSection.classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('drivers-ed')?.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }
    }

    function resetFlow() {
        currentFlow = null;
        selectedVehicle = null;
        flowContent.classList.add('hidden');
        featuresSection.style.display = 'block';
        safetyRatingsSection.style.display = 'block';

        // Reset form inputs
        const filterForm = document.getElementById('vehicle-filters-form');
        if (filterForm) filterForm.reset();

        // Hide vehicle insurance section
        vehicleInsuranceSection?.classList.add('hidden');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Handle vehicle selection (from both top picks and grid)
    document.addEventListener('click', function(e) {
        const vehicleButton = e.target.closest('[data-vehicle-id]');
        if (vehicleButton && currentFlow === 'findCar') {
            selectedVehicle = {
                id: vehicleButton.getAttribute('data-vehicle-id'),
                make: vehicleButton.getAttribute('data-vehicle-make'),
                model: vehicleButton.getAttribute('data-vehicle-model'),
                year: vehicleButton.getAttribute('data-vehicle-year')
            };

            // Show insurance section
            vehicleInsuranceSection?.classList.remove('hidden');

            // Scroll to insurance section
            setTimeout(() => {
                vehicleInsuranceSection?.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }
    });

    // Attach event listeners to flow buttons
    flowFindCarBtn?.addEventListener('click', () => setFlow('findCar'));
    flowGetInsuranceBtn?.addEventListener('click', () => setFlow('getInsurance'));
    flowDriversEdBtn?.addEventListener('click', () => setFlow('driversEd'));
    startOverBtn?.addEventListener('click', resetFlow);

    // Handle URL parameters for initial flow
    const params = new URLSearchParams(window.location.search);
    const flowParam = params.get('flow');
    if (flowParam && ['findCar', 'getInsurance', 'driversEd'].includes(flowParam)) {
        setFlow(flowParam);
    }
});
</script>

<?php
get_footer();
?>