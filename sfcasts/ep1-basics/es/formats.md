# Más formatos: HAL Y CSV

La Plataforma API admite múltiples formatos de entrada y salida. Puedes comprobarlo entrando en `/api/cheeses.json` para obtener JSON "en bruto" o `.jsonld` o incluso `.html`, que carga la documentación HTML. Pero añadir la extensión de esta manera es una especie de "truco" que la Plataforma API ha añadido sólo para facilitar el juego.

En su lugar, se supone que debes elegir qué "formato" o "representación" quieres para un recurso mediante la negociación del contenido. La documentación ya lo hace y lo muestra en los ejemplos: envía una cabecera `Accept`, que la Plataforma API utiliza para averiguar qué formato debe utilizar el serializador.

## Añadir un nuevo formato: HAL

Por defecto, la Plataforma API utiliza 3 formatos... pero en realidad admite un montón más: JSON-API, HAL JSON, XML, YAML y CSV. Busca tu terminal y ejecuta:

```terminal
php bin/console debug:config api_platform
```

Esta es nuestra configuración actual de la Plataforma API, incluyendo los valores por defecto. Echa un vistazo a `formats`. Muestra los 3 formatos que hemos visto hasta ahora y los tipos mime de cada uno, que es el valor que debe enviarse en la cabecera `Accept` para activarlos.

Vamos a añadir otro formato. Para ello, copia toda esta sección de formatos. Luego abre `config/packages/api_platform.yaml` y pégalo aquí 

[[[ code('9c59cc589d') ]]]

Así nos aseguraremos de mantener estos tres formatos. Ahora, vamos a añadir uno nuevo: `jsonhal`. 
Este es uno de los otros formatos que la Plataforma API admite de forma inmediata. A continuación, añade`mime_types:` y luego el tipo de contenido estándar para este formato: `application/hal+json`.

[[[ code('0e59e2d1cb') ]]]

¡Genial! Y así de fácil... ¡toda nuestra API soporta un nuevo formato! Actualiza los documentos y abre la operación GET para ver el listado de quesos 1. Antes de pulsar ejecutar, abre el desplegable de formato y... ¡eh! Selecciona `application/hal+json`. ¡Ejecuta!

Saluda al formato JSON HAL: una especie de formato "competidor" de JSON-LD o JSON-API, que pretenden estandarizar cómo debes estructurar tu JSON: dónde deben vivir tus datos, dónde deben vivir los enlaces, etc.

En HAL, tienes una propiedad `_links`. Ahora sólo tiene un enlace a `self`, pero éste suele contener enlaces a otros recursos.

Esto es más divertido si probamos la operación de recolección GET: selecciona`application/hal+json` y pulsa Ejecutar. Es bastante chulo ver cómo los distintos formatos "anuncian" la paginación. HAL utiliza `_links` con las claves `first`, `last` y `next`. Si estuviéramos en la página 2, también habría un campo `prev`.

Disponer de este formato puede ser útil o no para ti; lo increíble es que puedes elegir lo que quieras. Además, entender los formatos desbloquea otras posibilidades interesantes.

## Formato CSV

Por ejemplo, ¿qué pasa si, por alguna razón, tú o alguien que utilice tu API quiere poder obtener los recursos del listado de quesos como CSV? Sí, ¡es totalmente posible! Pero en lugar de hacer que ese formato esté disponible globalmente para todos los recursos, vamos a activarlo sólo para nuestro `CheeseListing`.

De nuevo dentro de esa clase, bajo esta clave especial `attributes`, añade`"formats"`. Si quieres mantener todos los formatos existentes, tendrás que enumerarlos aquí: `jsonld` `json` , luego... veamos, ah sí, `html` y `jsonhal`. Para añadir un nuevo formato, digamos `csv`, pero ponlo en una nueva matriz con `text/csv`dentro.

[[[ code('451350bf22') ]]]

Este es el tipo mime para el formato. No necesitamos añadir tipos mime para los otros formatos porque ya están configurados en nuestro archivo de configuración.

¡Vamos a probarlo! Ve a actualizar los documentos. De repente, sólo para este recurso... que, vale, ahora sólo tenemos un recurso... pero CheeseListing tiene ahora un formato CSV. Selecciónalo y ejecútalo.

¡Ya está! Y podemos probarlo directamente en el navegador añadiendo `.csv` al final. Mi navegador lo ha descargado... así que vamos a dar la vuelta y a `cat` ese archivo para ver qué aspecto tiene. Los saltos de línea parecen un poco extraños, pero es un CSV válido.

Un ejemplo mejor es obtener la lista completa: `/api/cheeses.csv`. Veamos también qué aspecto tiene en el terminal. ¡Esto es increíble! La función de descarga de CSV más rápida que he construido nunca.

Y... ¡sí! También puedes crear tu propio formato y activarlo de esta misma manera. Es una idea poderosa: nuestro único recurso de la API puede representarse de muchas maneras diferentes, incluyendo formatos -como el CSV- que no necesitas... hasta esa situación aleatoria en la que de repente los necesitas de verdad.

A continuación, es hora de dejar que los usuarios creen listados de queso con los datos que quieran. ¡Es hora de añadir la validación!
