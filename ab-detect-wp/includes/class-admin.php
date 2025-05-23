<?php
/**
 * Clase para manejar la administración del plugin
 */
class AdBlock_Detector_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Agregar menú en el panel de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Registrar configuraciones
        add_action('admin_init', array($this, 'register_settings'));
        
        // Agregar enlaces en la página de plugins
        add_filter('plugin_action_links_' . plugin_basename(ADBLOCK_DETECTOR_PATH . 'adblock-detector-wp.php'), 
            array($this, 'add_plugin_links'));
    }
    
    /**
     * Función que se ejecuta al activar el plugin
     */
    public function activate() {
        // Configuración por defecto
        $default_options = array(
            'api_key' => '',
            'block_level' => 'soft',
            'modal_title' => 'Bloqueador de anuncios detectado',
            'modal_message' => 'Parece que estás usando un bloqueador de anuncios. Por favor, desactívalo para continuar navegando en nuestro sitio.',
            'modal_button_text' => 'Continuar de todos modos',
            'modal_image' => 'https://cdn-icons-png.flaticon.com/512/1602/1602621.png',
            'modal_image_width' => '100px',
            'debug_mode' => false
        );
        
        // Guardar opciones por defecto si no existen
        if (!get_option('adblock_detector_options')) {
            add_option('adblock_detector_options', $default_options);
        }
    }
    
    /**
     * Función que se ejecuta al desactivar el plugin
     */
    public function deactivate() {
        // No eliminamos las opciones para mantener la configuración
    }
    
    /**
     * Agregar menú en el panel de administración
     */
    public function add_admin_menu() {
        add_options_page(
            __('AdBlock Detector', 'adblock-detector-wp'),
            __('AdBlock Detector', 'adblock-detector-wp'),
            'manage_options',
            'adblock-detector',
            array($this, 'display_admin_page')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting(
            'adblock_detector_options_group',
            'adblock_detector_options',
            array($this, 'sanitize_options')
        );
        
        add_settings_section(
            'adblock_detector_main_section',
            __('Configuración General', 'adblock-detector-wp'),
            array($this, 'section_info'),
            'adblock-detector'
        );
        
        add_settings_field(
            'api_key',
            __('API Key', 'adblock-detector-wp'),
            array($this, 'api_key_callback'),
            'adblock-detector',
            'adblock_detector_main_section'
        );
        
        add_settings_field(
            'block_level',
            __('Nivel de Bloqueo', 'adblock-detector-wp'),
            array($this, 'block_level_callback'),
            'adblock-detector',
            'adblock_detector_main_section'
        );
        
        add_settings_field(
            'modal_settings',
            __('Configuración del Modal', 'adblock-detector-wp'),
            array($this, 'modal_settings_callback'),
            'adblock-detector',
            'adblock_detector_main_section'
        );
        
        add_settings_field(
            'debug_mode',
            __('Modo Debug', 'adblock-detector-wp'),
            array($this, 'debug_mode_callback'),
            'adblock-detector',
            'adblock_detector_main_section'
        );
    }
    
    /**
     * Sanitizar opciones
     */
    public function sanitize_options($input) {
        $new_input = array();
        
        $new_input['api_key'] = sanitize_text_field($input['api_key']);
        $new_input['block_level'] = sanitize_text_field($input['block_level']);
        $new_input['modal_title'] = sanitize_text_field($input['modal_title']);
        $new_input['modal_message'] = wp_kses_post($input['modal_message']);
        $new_input['modal_button_text'] = sanitize_text_field($input['modal_button_text']);
        $new_input['modal_image'] = esc_url_raw($input['modal_image']);
        $new_input['modal_image_width'] = sanitize_text_field($input['modal_image_width']);
        $new_input['debug_mode'] = isset($input['debug_mode']) ? true : false;
        
        return $new_input;
    }
    
    /**
     * Información de la sección
     */
    public function section_info() {
        echo '<p>' . __('Configura el detector de AdBlock para tu sitio. El script se carga desde nuestro CDN para un rendimiento óptimo.', 'adblock-detector-wp') . '</p>';
    }
    
    /**
     * Callback para API Key
     */
    public function api_key_callback() {
        $options = get_option('adblock_detector_options');
        ?>
        <input type="text" id="api_key" name="adblock_detector_options[api_key]" 
               value="<?php echo esc_attr($options['api_key']); ?>" class="regular-text" />
        <p class="description">
            <?php _e('Introduce tu API Key para validar el uso del detector.', 'adblock-detector-wp'); ?>
        </p>
        <?php
    }
    
    /**
     * Callback para nivel de bloqueo
     */
    public function block_level_callback() {
        $options = get_option('adblock_detector_options');
        ?>
        <select id="block_level" name="adblock_detector_options[block_level]">
            <option value="soft" <?php selected($options['block_level'], 'soft'); ?>>
                <?php _e('Suave (muestra advertencia)', 'adblock-detector-wp'); ?>
            </option>
            <option value="hard" <?php selected($options['block_level'], 'hard'); ?>>
                <?php _e('Estricto (bloquea acceso)', 'adblock-detector-wp'); ?>
            </option>
        </select>
        <?php
    }
    
    /**
     * Callback para configuración del modal
     */
    public function modal_settings_callback() {
        $options = get_option('adblock_detector_options');
        ?>
        <div class="modal-settings-container">
            <p>
                <label for="modal_title"><?php _e('Título del Modal:', 'adblock-detector-wp'); ?></label><br>
                <input type="text" id="modal_title" name="adblock_detector_options[modal_title]" 
                       value="<?php echo esc_attr($options['modal_title']); ?>" class="regular-text" />
            </p>
            
            <p>
                <label for="modal_message"><?php _e('Mensaje:', 'adblock-detector-wp'); ?></label><br>
                <textarea id="modal_message" name="adblock_detector_options[modal_message]" 
                          rows="4" class="large-text"><?php echo esc_textarea($options['modal_message']); ?></textarea>
            </p>
            
            <p>
                <label for="modal_button_text"><?php _e('Texto del Botón:', 'adblock-detector-wp'); ?></label><br>
                <input type="text" id="modal_button_text" name="adblock_detector_options[modal_button_text]" 
                       value="<?php echo esc_attr($options['modal_button_text']); ?>" class="regular-text" />
            </p>
            
            <p>
                <label for="modal_image"><?php _e('URL de la Imagen:', 'adblock-detector-wp'); ?></label><br>
                <input type="text" id="modal_image" name="adblock_detector_options[modal_image]" 
                       value="<?php echo esc_url($options['modal_image']); ?>" class="regular-text" />
            </p>
            
            <p>
                <label for="modal_image_width"><?php _e('Ancho de la Imagen:', 'adblock-detector-wp'); ?></label><br>
                <input type="text" id="modal_image_width" name="adblock_detector_options[modal_image_width]" 
                       value="<?php echo esc_attr($options['modal_image_width']); ?>" class="small-text" />
            </p>
        </div>
        <?php
    }
    
    /**
     * Callback para modo debug
     */
    public function debug_mode_callback() {
        $options = get_option('adblock_detector_options');
        ?>
        <label>
            <input type="checkbox" id="debug_mode" name="adblock_detector_options[debug_mode]" 
                   <?php checked(isset($options['debug_mode']) ? $options['debug_mode'] : false); ?> />
            <?php _e('Activar modo debug (solo para desarrollo)', 'adblock-detector-wp'); ?>
        </label>
        <?php
    }
    
    /**
     * Mostrar página de administración
     */
    public function display_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('adblock_detector_options_group');
                do_settings_sections('adblock-detector');
                submit_button(__('Guardar Cambios', 'adblock-detector-wp'));
                ?>
            </form>
            
            <div class="adblock-detector-help">
                <h2><?php _e('Ayuda e Información', 'adblock-detector-wp'); ?></h2>
                <p>
                    <?php _e('Para obtener una API Key o soporte, te invitamos a unirte a nuestro discord', 'adblock-detector-wp'); ?> 
                    <a href="https://discord.gg/U3A9FAUV" target="_blank">
                        <?php _e('nuestro servidor de Discord', 'adblock-detector-wp'); ?>
                    </a>.
                </p>
                <p>
                    <?php _e('El detector de AdBlock se carga automáticamente en todas las páginas de tu sitio.', 'adblock-detector-wp'); ?>
                </p>

            </div>
        </div>
        <?php
    }
    
    /**
     * Agregar enlaces en la página de plugins
     */
    public function add_plugin_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('options-general.php?page=adblock-detector') . '">' . 
            __('Configuración', 'adblock-detector-wp') . '</a>'
        );
        return array_merge($plugin_links, $links);
    }
}