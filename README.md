# Detector de AdBlock

Un script ligero y eficaz para detectar bloqueadores de anuncios en sitios web. Compatible con WordPress, Vue, React, NextJS y otras plataformas web.

## Características

- Detección de AdBlock mediante múltiples técnicas
- Fácil integración en cualquier sitio web
- Personalización completa de mensajes y comportamiento
- Soporte para bloqueo suave (advertencia) o duro (restricción de acceso)
- Compatible con todos los navegadores modernos
- Funciona en WordPress, Vue, React, NextJS y otras plataformas

## Instalación

### Método 1: Integración con CDN (Recomendado)

Agrega el siguiente código en la sección `<head>` de tu página HTML:

```html
<script src="https://cdn.jsdelivr.net/gh/usuario/adblockdetector/adblock-detector.min.js"></script>

<!-- Configurar con API Key -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    if (window.AdBlockDetector) {
      window.AdBlockDetector.init({
        apiKey: "tu-api-key-aquí", // Reemplazar con tu API Key
      });
    }
  });
</script>
```

Esta es la forma más sencilla de integrar el detector sin necesidad de descargar archivos. Si no tienes un Api Key, puedes adquirir el servicio en nuestro servidor de discord [CLICK AQUI](https://discord.gg/RWaEaNw9yd).

### Método 2: WordPress

1. Sube el archivo `adblock-detector.js` a tu tema o plugin
2. Agrega el siguiente código en tu archivo `functions.php`:

```php
function load_adblock_detector() {
  wp_enqueue_script('adblock-detector', get_template_directory_uri() . '/adblock-detector.js', array(), '1.0', true);
  wp_localize_script('adblock-detector', 'adblock_detector_config', array(
    'apiKey' => 'tu-api-key-aquí', // Reemplazar con tu API Key
  ))
}
```

### Método 3: React/Next.js

Importa y utiliza el detector en tu componente principal:

```jsx
import React, { useEffect } from "react";

function App() {
  useEffect(() => {
    if (window.AdBlockDetector) {
      window.AdBlockDetector.init({
        apiKey: "tu-api-key-aquí", // Reemplazar con tu API Key
      });
    }
  }, []);

  return <div>{/* Tu contenido aquí */}</div>;
}
```

### Método 4: Vue.js

En tu archivo `main.js` o en un componente principal:

```javascript
// En main.js
import AdBlockDetector from "./path/to/adblock-detector.js";

Vue.use(AdBlockDetector, {
  apiKey: "tu-api-key-aquí", // Reemplazar con tu API Key
});

// En tu componente
export default {
  created() {
    if (this.$adBlockDetector) {
      this.$adBlockDetector.init({
        apiKey: "tu-api-key-aquí", // Reemplazar con tu API Key
      });
    }
  },
  // Tu código aquí
};
```

## Configuración

Puedes personalizar el comportamiento del detector pasando un objeto de configuración al método `init`:

```javascript
window.addEventListener("DOMContentLoaded", () => {
  if (window.AdBlockDetector) {
    window.AdBlockDetector.init({
      delay: 500, // Retraso antes de la detección (ms). No se recomienda cambiarlo
      blockLevel: "soft", // 'soft': muestra advertencia, 'hard': bloquea acceso
      modalTitle: "Bloqueador de anuncios detectado",
      modalMessage:
        "Por favor, desactiva tu bloqueador de anuncios para continuar.",
      modalButtonText: "Continuar de todos modos",
      callback: function (detected) {
        // Función callback personalizada
        console.log("AdBlock detectado:", detected);
        // Aquí puedes agregar tu lógica personalizada
      },
    });
  }
});
```

## Opciones de configuración

| Opción             | Tipo     | Predeterminado                                              | Descripción                                                                                             |
| ------------------ | -------- | ----------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| `apiKey`           | String   | `null`                                                      | Necesario para el funcionamiento del Detector                                                           |
| `delay`            | Number   | `500`                                                       | Retraso en milisegundos antes de iniciar la detección                                                   |
| `blockLevel`       | String   | `'soft'`                                                    | Nivel de bloqueo: 'soft' (advertencia) o 'hard' (bloqueo total)                                         |
| `modalTitle`       | String   | `'Bloqueador de anuncios detectado'`                        | Título del modal de advertencia                                                                         |
| `modalMessage`     | String   | `'Parece que estás usando...'`                              | Mensaje del modal de advertencia                                                                        |
| `modalButtonText`  | String   | `'Continuar de todos modos'`                                | Texto del botón del modal (solo en modo 'soft')                                                         |
| `modalImage`       | String   | `'https://cdn-icons-png.flaticon.com/512/1602/1602621.png'` | Url de la Imagen de la parte superior de la alerta puedes colocar por valor `null` si no quieres imagen |
| `modalImageAlt`    | String   | `'Bloqueador detectado'`                                    | Alt de la imagen                                                                                        |
| `modalCustomClass` | String   | `'Clase CSS personalizada para el modal'`                   | Contenedor principal del modal del detector                                                             |
| `callback`         | Function | `null`                                                      | Función que se ejecuta después de la detección                                                          |

## API

El detector expone los siguientes métodos y propiedades:

- `AdBlockDetector.init(config)`: Inicializa el detector con configuración personalizada
- `AdBlockDetector.detect()`: Ejecuta la detección manualmente
- `AdBlockDetector.isDetected`: Propiedad booleana que indica si se detectó un bloqueador
- `AdBlockDetector.showModal(forceBlock)`: Muestra el modal de advertencia

## Ejemplo de uso avanzado

```javascript
// Inicializar con configuración personalizada
AdBlockDetector.init({
  blockLevel: "hard",
  modalTitle: "Bloqueador de anuncios detectado",
  modalMessage:
    "Nuestro contenido es gratuito gracias a los anuncios. Por favor, desactiva tu bloqueador.",
  // Nueva opción: Imagen personalizada
  modalImage: "https://ejemplo.com/ruta/a/tu-imagen.png",
  modalImageAlt: "Icono de advertencia",
  modalImageWidth: "150px",
  // Nueva opción: Clase CSS personalizada
  modalCustomClass: "mi-tema-personalizado",
  callback: function (detected) {
    // Enviar analítica
    if (detected) {
      fetch("/api/analytics", {
        method: "POST",
        body: JSON.stringify({ event: "adblock_detected" }),
        headers: { "Content-Type": "application/json" },
      });
    }
  },
});

// Verificar manualmente en cualquier momento
const checkButton = document.getElementById("check-adblock");
checkButton.addEventListener("click", async () => {
  const detected = await AdBlockDetector.detect();
  alert(detected ? "AdBlock detectado" : "No se detectó AdBlock");
});
```

Para ver un ejemplo completo de integración con la API, consulta el archivo `api-integration-example.html`.

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.
