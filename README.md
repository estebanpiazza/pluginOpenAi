# Plugin OpenAI Chatbot para Moodle

Este plugin permite integrar un chatbot basado en OpenAI Assistants API en Moodle, utilizando una base de conocimientos vectorizada.

## Instalación

1. **Copiar archivos del plugin:**
   - Copia toda la carpeta `pluginOpenAi` a `[moodle]/local/openai_chatbot/`

2. **Instalar el plugin:**
   - Ve a Administración del sitio → Notificaciones
   - Sigue el proceso de instalación del plugin

## Configuración

### 1. Configurar OpenAI
- Ve a Administración del sitio → Plugins → Plugins locales → Chatbot OpenAI
- Introduce tu **API Key de OpenAI**
- Introduce tu **Assistant ID** (debe estar configurado con vectores en OpenAI)

### 2. Permisos
- Asegúrate de que los usuarios tengan el permiso `moodle/site:config` o ajusta los permisos según necesites

## Uso

1. Accede a `/local/openai_chatbot/index.php` en tu instalación de Moodle
2. Escribe tu pregunta en el campo de texto
3. Haz clic en "Hacer pregunta"
4. El asistente responderá basándose en su base de conocimientos vectorizada

## Resolución de problemas

### Error: "No pude crear el thread"
- **Verifica tu API Key:** Asegúrate de que sea válida y tenga créditos
- **Verifica el Assistant ID:** Debe existir en tu cuenta de OpenAI
- **Revisa los logs:** El plugin ahora muestra la respuesta completa de la API para debugging

### Errores comunes:
- **HTTP 401:** API Key inválida
- **HTTP 404:** Assistant ID no encontrado
- **HTTP 429:** Límite de rate alcanzado

### Debug
El plugin incluye debugging detallado que muestra:
- Respuestas completas de la API de OpenAI
- Códigos de estado HTTP
- Errores de cURL

## Estructura del plugin

```
local/openai_chatbot/
├── index.php              # Interfaz principal del chatbot
├── version.php            # Información del plugin
├── settings.php           # Configuración en admin
├── lang/
│   ├── en/
│   │   └── local_openai_chatbot.php
│   └── es/
│       └── local_openai_chatbot.php
└── README.md
```

## Requisitos

- Moodle 3.9 o superior
- PHP 7.4 o superior
- Extensión cURL habilitada
- Cuenta de OpenAI con Assistant configurado
- API Key de OpenAI con créditos disponibles

## Características

- ✅ Integración completa con OpenAI Assistants API
- ✅ Soporte para bases de conocimientos vectorizadas
- ✅ Interfaz multiidioma (español e inglés)
- ✅ Manejo robusto de errores
- ✅ Debugging detallado
- ✅ Validación SSL
- ✅ Timeouts configurables

## Próximas mejoras

- [ ] Historial de conversaciones
- [ ] Integración con bloques de Moodle
- [ ] Configuración de timeouts desde admin
- [ ] Logs más detallados
- [ ] Soporte para múltiples asistentes