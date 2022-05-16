# Especificación OpenAPI

Hora de la confesión: este tutorial trata de mucho más que de la Plataforma API. El mundo de las APIs ha experimentado grandes cambios en los últimos años, introduciendo nuevos formatos hipermedia, estándares, especificaciones, herramientas de rendimiento, etc. La Plataforma API se encuentra justo en medio de todo esto: aportando las mejores prácticas de vanguardia a tu aplicación. Si realmente quieres dominar la Plataforma API, tienes que entender el desarrollo moderno de las API.

Ya te he dicho que lo que estamos viendo se llama Swagger. Swagger es básicamente una interfaz de documentación de la API, una especie de README interactivo. Busca Swagger en Google y abre su sitio. En la sección de herramientas, la que estamos utilizando se llama Swagger UI.

¡Sí!

> Swagger UI permite a cualquiera visualizar e interactuar con los recursos de tu API
> sin tener ninguna implementación en marcha.

Literalmente, podrías describir primero tu API -qué rutas tendrá, qué devolverá, qué campos esperar- y luego utilizar Swagger UI para visualizar tu futura API, antes de escribir ni siquiera una línea de código para ella.

Deja que te muestre lo que quiero decir: tienen una demostración en vivo que se parece mucho a nuestros documentos de la API. ¿Ves esa URL de `swagger.json` en la parte superior? Cópiala, abre una nueva pestaña y pégala. ¡Woh! ¡Es un enorme archivo JSON que describe la API! Así es como funciona Swagger UI: lee este archivo JSON y construye una interfaz visual e interactiva para él. Diablos, ¡es posible que esta API ni siquiera exista! Siempre que tengas este archivo de descripción JSON, puedes utilizar Swagger UI.

El archivo JSON contiene todas tus rutas, una descripción de lo que hace cada una, los parámetros de la entrada, qué salida esperar, detalles relacionados con la seguridad... básicamente trata de describir completamente tu API.

Así que si tienes uno de estos archivos de configuración JSON, puedes conectarlo a la interfaz Swagger y... ¡boom! Obtienes una interfaz rica y descriptiva.

## Hola OpenAPI

El formato de este archivo se llama OpenAPI. Así pues, Swagger UI es la interfaz y entiende esta especie de formato de especificación oficial para describir APIs llamado OpenAPI. Para hacer las cosas un poco más confusas, la especificación OpenAPI solía llamarse Swagger. A partir de OpenAPI 3.0, se llama OpenAPI y Swagger es sólo la interfaz.

¡Uf!

De todos modos, todo esto está muy bien... pero crear una API ya es suficiente trabajo, sin necesidad de intentar construir y mantener este gigantesco documento JSON al margen. Por eso la Plataforma API lo hace por ti.

Recuerda: La filosofía de la Plataforma API es la siguiente: crea algunos recursos, modifica cualquier configuración que necesites -no lo hemos hecho, pero lo haremos pronto- y deja que la Plataforma API exponga esos recursos como una API. Eso es lo que hace, pero para ser un buen amigo más, también crea una especificación OpenAPI. Compruébalo: ve a `/api/docs.json`.

¡Hola documento gigante de la especificación OpenAPI! Fíjate en que dice `swagger: "2.0"`. La versión 3 de OpenAPI es todavía bastante nueva, así que la Plataforma API 2 sigue utilizando el formato antiguo. Añade`?spec_version=3` a la URL para ver... ¡sí! Este es el mismo documento en la última versión del formato.

Ahora, vuelve a nuestra página de inicio del documento de la API y ve el código fuente HTML. ¡Ja! ¡Los datos JSON de OpenAPI ya se están incluyendo en esta página a través de una pequeña etiqueta de script `swagger-data`! ¡Así es como funciona esta página!

Para generar la Swagger UI de la versión 3 de OpenAPI, puedes añadir el mismo `?spec_version=3`a la URL. Sí, puedes ver la etiqueta `OAS3`. Esto no cambia mucho en el frontend, pero hay unos cuantos datos nuevos que Swagger puede utilizar ahora gracias a la nueva versión de las especificaciones.

## ¿Qué más puede hacer OpenAPI? ¡Generar código!

Pero... aparte del hecho de que nos proporciona esta bonita interfaz de Swagger, ¿por qué debería importarnos que se cree una gigantesca especificación JSON de OpenAPI entre bastidores? Volviendo al sitio de Swagger, una de las otras herramientas se llama Swagger CodeGen: ¡una herramienta para crear un SDK para tu API en casi cualquier lenguaje! Piénsalo: si tu API está completamente documentada en un lenguaje comprensible para la máquina, ¿no deberíamos poder generar una biblioteca JavaScript o PHP personalizada para hablar con tu API? ¡Se puede perfectamente!

Lo último que quiero señalar es que, además de los puntos finales, o "rutas", la especificación OpenAPI también tiene información sobre los "modelos". En la especificación JSON, desplázate hasta el final: describe nuestro modelo `CheeseListing` y los campos que hay que esperar al enviar y recibir este modelo. Puedes ver esta misma información en Swagger.

Y ¡oh! De alguna manera ya sabe que el `id` es un `integer` y que es`readonly`. También sabe que el precio es un `integer` y que `createdAt` es una cadena con formato`datetime`. ¡Eso es increíble! La Plataforma API lee esa información directamente de nuestro código, lo que significa que nuestros documentos de la API se mantienen actualizados sin que tengamos que pensar en ello. Aprenderemos más sobre cómo funciona esto a lo largo del camino.

Pero antes de llegar ahí, tenemos que hablar de otra cosa súper importante que ya estamos viendo: el formato JSON-LD e Hydra que devuelven las respuestas de nuestra API.
