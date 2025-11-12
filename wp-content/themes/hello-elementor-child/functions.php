<?php
add_action('wp_enqueue_scripts', function () {

    // 1. Carica gli stili base del tema parent e child
    wp_enqueue_style(
        'hello-elementor-style',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['hello-elementor-style']
    );

    // 2. Carica automaticamente tutti i .css nella cartella /assets/scss/
    $scss_dir = get_stylesheet_directory() . '/assets/scss/';
    $scss_uri = get_stylesheet_directory_uri() . '/assets/scss/';

    if (file_exists($scss_dir)) {
        foreach (glob($scss_dir . '*.css') as $css_file) {
            $handle = 'child-' . basename($css_file, '.css');
            wp_enqueue_style($handle, $scss_uri . basename($css_file), ['hello-elementor-child-style']);
        }
    }
});


function hello_child_enqueue_bootstrap()
{
    // === Bootstrap CSS ===
    wp_enqueue_style(
        'bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        array(), // Nessuna dipendenza
        '5.3.3' // Versione
    );

    wp_enqueue_script(
        'bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        array('jquery'), // jQuery è già incluso da WordPress
        '5.3.3',
        true // Carica in footer
    );
}
add_action('wp_enqueue_scripts', 'hello_child_enqueue_bootstrap');


function wpcontent_svg_mime_type($mimes = array())
{
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'wpcontent_svg_mime_type');

add_action('get_footer', function () {
    // Percorso al footer personalizzato nel child theme
    $child_footer = get_stylesheet_directory() . '/template-parts/dynamic-footer.php';

    if (file_exists($child_footer)) {
        // Disattiva il footer originale
        remove_all_actions('hello_elementor_footer');

        // Includi il tuo file
        add_action('hello_elementor_footer', function () use ($child_footer) {
            include $child_footer;

        });
    }
}, 5);

function child_theme_scripts()
{
    // Assicura che jQuery sia caricato
    wp_enqueue_script('jquery');

    // Carica il tuo script personalizzato
    wp_enqueue_script(
        'child-custom-script',
        get_stylesheet_directory_uri() . '/assets/scripts/script.js',
        array('jquery'),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'child_theme_scripts');


// Shortcode per il form prenotazione
add_shortcode('vue_form', function () {
    return '<div id="vue-app" data-component="form"></div>';
});

// Shortcode per la lista veicoli
add_shortcode('vue_veicoli', function () {
    return '<div id="vue-app" data-component="lista"></div>';
});

function enqueue_vue_app()
{
    wp_enqueue_script('vue', 'https://unpkg.com/vue@3/dist/vue.global.prod.js', [], null, true);
    wp_enqueue_script('vue-app', get_stylesheet_directory_uri() . '/vue-app/dist/bundle.js', ['vue'], null, true);

    // ✅ CSS della build Vite
    wp_enqueue_style('vue-app-style', get_stylesheet_directory_uri() . '/vue-app/dist/bundle.css');

    // ✅ CSS originale del VueDatePicker (dopo la build, per sovrascrivere conflitti)
    // wp_enqueue_style('vue-datepicker', get_stylesheet_directory_uri() . '/node_modules/@vuepic/vue-datepicker/dist/main.css');

    wp_localize_script('vue-app', 'wpData', [
        'root' => esc_url_raw(rest_url()),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_vue_app');



add_action('wp_footer', function () {
    echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/vue-app/dist/bundle.css?ver=' . time() . '">';
    echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/vue-app/vendor/vue-datepicker.css?ver=' . time() . '">';
}, 999);