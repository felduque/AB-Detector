<?php
/**
 * Plugin Name: AdBlock Detector
 * Plugin URI: https://github.com/felduque/AB-Detector
 * Description: Integra fácilmente el detector de AdBlock en tu sitio web.
 * Version: 1.0.2
 * Author: Codigo 401
 * Author URI: https://github.com/felduque
 * Text Domain: adblock-detector-wp
 * Domain Path: /languages
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Definir constantes
define('ADBLOCK_DETECTOR_VERSION', '1.0.2');
define('ADBLOCK_DETECTOR_PATH', plugin_dir_path(__FILE__));
define('ADBLOCK_DETECTOR_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once ADBLOCK_DETECTOR_PATH . 'includes/class-admin.php';

// Inicializar clases
function adblock_detector_init() {
    $admin = new AdBlock_Detector_Admin();
    
    // Registrar hooks de activación y desactivación
    register_activation_hook(__FILE__, array($admin, 'activate'));
    register_deactivation_hook(__FILE__, array($admin, 'deactivate'));
    
    // Agregar el script del CDN al frontend
    add_action('wp_enqueue_scripts', 'adblock_detector_enqueue_scripts');
}
adblock_detector_init();

/**
 * Cargar el script del CDN y configurarlo
 */
function adblock_detector_enqueue_scripts() {
    $options = get_option('adblock_detector_options');
    
    // No cargar si no hay API Key configurada
    if (empty($options['api_key'])) {
        return;
    }
    
    // Cargar el script desde el CDN
    wp_enqueue_script(
        'adblock-detector',
        'https://cdn.jsdelivr.net/gh/felduque/AB-Detector@main/dk-scanner.js',
        array(),
        ADBLOCK_DETECTOR_VERSION . '?' . time(), // Añadir timestamp para evitar caché
        true
    );
    
    // Crear elemento de prueba para detección
    add_action('wp_head', function() {
        echo '<div id="ad-element" class="ad-banner" style="position: absolute; height: 1px; width: 1px; opacity: 0;">Test Ad Element</div>';
    });
    
    // Agregar variable de configuración global para que esté disponible antes de cargar el script
    add_action('wp_head', function() use ($options) {
        ?>
        <script>
        // Configuración global para el detector de AdBlock
        window.adBlockDetectorConfig = {
            apiKey: '<?php echo esc_js($options['api_key']); ?>',
            debug: <?php echo isset($options['debug_mode']) && $options['debug_mode'] ? 'true' : 'false'; ?>,
            blockLevel: '<?php echo esc_js(!empty($options['block_level']) ? $options['block_level'] : 'soft'); ?>',
            modalTitle: '<?php echo esc_js(!empty($options['modal_title']) ? $options['modal_title'] : 'Bloqueador de anuncios detectado'); ?>',
            modalMessage: '<?php echo esc_js(!empty($options['modal_message']) ? $options['modal_message'] : 'Parece que estás usando un bloqueador de anuncios. Por favor, desactívalo para continuar navegando en nuestro sitio.'); ?>',
            modalButtonText: '<?php echo esc_js(!empty($options['modal_button_text']) ? $options['modal_button_text'] : 'Continuar de todos modos'); ?>',
            modalImage: '<?php echo esc_js(!empty($options['modal_image']) ? $options['modal_image'] : 'https://cdn-icons-png.flaticon.com/512/1602/1602621.png'); ?>',
            modalImageWidth: '<?php echo esc_js(!empty($options['modal_image_width']) ? $options['modal_image_width'] : '100px'); ?>'
        };
        </script>
        <?php
    });
    
    // Inicializar el detector con las opciones configuradas
    add_action('wp_footer', function() {
        ?>
        <script>
        // Función para inicializar el detector con reintento
        function initAdBlockDetector() {
            // Verificar si el detector está disponible
            if (typeof AdBlockDetector !== 'undefined') {
                // Usar la configuración global definida en wp_head
                if (window.adBlockDetectorConfig) {
                    console.log('AdBlock Detector - Inicializando con configuración:', window.adBlockDetectorConfig);
                    AdBlockDetector.init(window.adBlockDetectorConfig);
                } else {
                    console.error('AdBlock Detector - No se encontró la configuración global.');
                }
            } else {
                console.error('AdBlock Detector no está disponible. Reintentando en 500ms...');
                // Reintentar después de un breve retraso
                setTimeout(initAdBlockDetector, 500);
            }
        }

        // Iniciar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño retraso para asegurar que el script se haya cargado
            setTimeout(initAdBlockDetector, 100);
        });
        </script>
        <?php
    });
}