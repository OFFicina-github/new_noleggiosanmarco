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

wp_localize_script('vue-app', 'themeUrl', get_stylesheet_directory_uri());

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
    // echo '<link rel="stylesheet" href="' . get_stylesheet_directory_uri() . '/vue-app/vendor/vue-datepicker.css?ver=' . time() . '">';
}, 999);


add_filter('acf/format_value', function ($value, $post_id, $field) {
    // Se il campo è di tipo immagine e il valore è un ID
    if ($field['type'] === 'image' && is_numeric($value)) {
        return wp_get_attachment_url($value); // restituisce l’URL completo
    }

    // Puoi aggiungere altri tipi se serve (es. file, gallery)
    if ($field['type'] === 'gallery' && is_array($value)) {
        return array_map(function ($img) {
            return is_numeric($img) ? wp_get_attachment_url($img) : $img;
        }, $value);
    }

    return $value;
}, 10, 3);


add_action('rest_api_init', function () {
    register_rest_route('noleggio/v1', '/preventivo', [
        'methods' => 'POST',
        'callback' => 'noleggio_handle_preventivo',
        'permission_callback' => '__return_true', // meglio poi usare nonce
    ]);
});

wp_localize_script('vue-app', 'wpData', [
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest'),
]);

function noleggio_handle_preventivo(WP_REST_Request $request)
{
    $data = $request->get_json_params();

    // 1. invio email
    noleggio_invia_email_preventivo($data);

    // 2. salva su Google Sheets
    noleggio_invia_a_google_sheets($data);

    // eventualmente: log, salvataggio custom post, ecc.

    return new WP_REST_Response(['success' => true], 200);
}

function noleggio_invia_email_preventivo($dati)
{
    $to = 'daniele@offitaly.it'; // cambia
    $subject = 'Nuova richiesta preventivo';
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    ob_start();
    $template = locate_template('email-preventivo.php');
    if ($template) {
        include $template;
    }
    $body = ob_get_clean();

    wp_mail($to, $subject, $body, $headers);
}


function noleggio_invia_a_google_sheets($dati)
{
    $url = 'https://script.google.com/macros/s/AKfycbwQTfyOPYxsqzxJXTVvvcXFw2tu7WAn2phjUOYub7N4DT_nGdMZ4TeEO2pjAZHKYj3c/exec'; // URL dello script


    $payload = [
        'dataPrenotazione' => date('d/m/Y H:i:s'),
        'email' => $dati['email'] ?? '',
        'veicolo' => $dati['veicoloSelezionato']['acf']['nome'] ?? '',
        'tipoVeicolo' => $dati['tipoSelezionato'] ?? '',
        'ritiro' => $dati['ritiro'] ?? '',
        'dataRitiro' => $dati['dataRitiro'] ?? '',
        'oraRitiro' => $dati['oraRitiro'] ?? '',
        'riconsegna' => $dati['riconsegna'] ?? '',
        'dataRiconsegna' => $dati['dataRiconsegna'] ?? '',
        'oraRiconsegna' => $dati['oraRiconsegna'] ?? '',
        'telefono' => $dati['telefono'] ?? '',
    ];


    // print_r(($row));
    // print_r(($dati));
    // print_r(gettype($row));
    // print_r(gettype($dati));
    // print_r("salve");
    // print_r(wp_json_encode($row));

    // print_r(wp_json_encode($payload));
    // die();


    wp_remote_post($url, [
        'body' => wp_json_encode($payload),
        'headers' => ['Content-Type' => 'application/json'],
        'timeout' => 10,
    ]);
}


add_action('wp_enqueue_scripts', function () {

    // carica il bundle JS VUE
    wp_enqueue_script(
        'vue-app',
        get_stylesheet_directory_uri() . '/vue-app/dist/bundle.js',
        [],
        filemtime(get_stylesheet_directory() . '/vue-app/dist/bundle.js'),
        true
    );

    // passa nonce + URL API al frontend
    wp_localize_script(
        'vue-app',
        'wpData',
        [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ]
    );

});
