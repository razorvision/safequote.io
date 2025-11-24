<?php
/**
 * The template for displaying the front page
 *
 * @package SafeQuote_Traditional
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php get_template_part('template-parts/hero'); ?>

    <!-- Main Content Container -->
    <div class="container mx-auto px-4 py-12 space-y-20" id="start">

        <?php get_template_part('template-parts/features'); ?>

        <?php get_template_part('template-parts/safety-ratings'); ?>

        <!-- Flow Content Container (for dynamic content) -->
        <div id="flow-content" class="hidden space-y-12" aria-live="polite" aria-label="<?php esc_attr_e('Main content area', 'safequote-traditional'); ?>">
            <!-- Dynamic content will be loaded here based on flow selection -->
            <button id="start-over" class="text-primary hover:underline font-semibold">
                &larr; <?php esc_html_e('Start Over', 'safequote-traditional'); ?>
            </button>

            <!-- Vehicle Search Section (shown in findCar flow) -->
            <div id="vehicles-section" class="hidden space-y-8" aria-live="assertive" aria-label="<?php esc_attr_e('Vehicle search results', 'safequote-traditional'); ?>">
                <div id="vehicles">
                    <?php
                    // Include Top Safety Picks template part with NHTSA ratings merged in
                    require_once SAFEQUOTE_THEME_DIR . '/inc/vehicle-data-nhtsa.php';
                    set_query_var('vehicles', safequote_get_top_safety_picks_from_db(4)); // Get top 4 safety-rated vehicles with NHTSA data
                    get_template_part('template-parts/top-safety-picks');
                    ?>
                </div>

                <?php
                // Include Search Filters template part
                set_query_var('filters', array(
                    'year'             => '',
                    'make'             => '',
                    'model'            => '',
                    'minSafetyRating'  => 0,
                ));
                get_template_part('template-parts/search-filters');
                ?>

                <?php
                // Include Vehicle Grid template part with NHTSA data
                require_once SAFEQUOTE_THEME_DIR . '/inc/vehicle-data-nhtsa.php';
                set_query_var('vehicles', safequote_get_vehicles_from_nhtsa(array('limit' => 10))); // Get top 10 NHTSA-rated vehicles
                get_template_part('template-parts/vehicle-grid');
                ?>

                <!-- Insurance Comparison (shown when vehicle is selected in findCar flow) -->
                <div id="vehicle-insurance-section" class="hidden" aria-live="assertive" aria-label="<?php esc_attr_e('Insurance quotes for selected vehicle', 'safequote-traditional'); ?>">
                    <?php
                    set_query_var('quotes', array());
                    set_query_var('vehicle', array('make' => 'your', 'model' => 'car'));
                    set_query_var('section_id', 'vehicle-insurance-comparison');
                    get_template_part('template-parts/insurance-comparison');
                    ?>
                </div>
            </div>

            <!-- Insurance Section (shown in getInsurance flow) -->
            <div id="insurance-section" class="hidden space-y-8" aria-live="assertive" aria-label="<?php esc_attr_e('Insurance comparison results', 'safequote-traditional'); ?>">
                <?php
                // Include Insurance Comparison template part
                set_query_var('quotes', array());
                set_query_var('vehicle', array('year' => date('Y'), 'make' => 'your', 'model' => 'car'));
                set_query_var('section_id', 'standalone-insurance-comparison');
                get_template_part('template-parts/insurance-comparison');
                ?>
            </div>

            <!-- Driver's Ed Section (shown in driversEd flow) -->
            <div id="drivers-ed-section" class="hidden space-y-8" aria-live="polite" aria-label="<?php esc_attr_e('Driver education resources', 'safequote-traditional'); ?>">
                <?php get_template_part('template-parts/drivers-ed'); ?>
            </div>
        </div>

        <!-- Login Modal -->
        <?php get_template_part('template-parts/modal-login'); ?>

        <!-- Call-to-Action Section -->
        <?php get_template_part('template-parts/cta'); ?>
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