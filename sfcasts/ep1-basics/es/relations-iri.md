# Relaciones e IRIs

Acabo de intentar crear un `CheeseListing` poniendo la propiedad `owner` a 1: el id de un usuario real en la base de datos. Pero... ¡no le ha gustado! ¿Por qué? Porque en la Plataforma API y, comúnmente, en el desarrollo moderno de APIs en general, no utilizamos ids para referirnos a los recursos: utilizamos IRIs. Para mí, esto fue extraño al principio... pero rápidamente me enamoré de esto. ¿Por qué pasar ids enteros cuando las URLs son mucho más útiles?

Mira la respuesta del usuario que acabamos de crear: como toda respuesta JSON-LD, contiene una propiedad `@id`... que no es un id, ¡es un IRI! Y esto es lo que utilizarás siempre que necesites referirte a este recurso.

Vuelve a la operación POST `CheeseListing` y establece `owner` como`/api/users/1`. Ejecuta eso. Esta vez... ¡funciona!

Y fíjate, cuando transforma el nuevo `CheeseListing` en JSON, la propiedad `owner`es ese mismo IRI. Por eso Swagger lo documenta como una "cadena"... lo cual no es del todo exacto. Claro, en la superficie, `owner` es una cadena... y eso es lo que muestra Swagger en el modelo `cheeses-Write`.

Pero sabemos... con nuestro cerebro humano, que esta cadena es especial: en realidad representa un "enlace" a un recurso relacionado. Y... aunque Swagger no lo entienda del todo, echa un vistazo a la documentación de JSON-LD: en `/api/docs.jsonld`. Veamos, busca propietario. ¡Ja! Esto es un poco más inteligente: JSON-LD sabe que se trata de un Enlace... con algunos metadatos extravagantes para decir básicamente que el enlace es a un recurso de`User`.

La gran conclusión es ésta: una relación es sólo una propiedad normal, excepto que se representa en tu API con su IRI. Muy bueno.

## Añadir quesosListados a Usuario

¿Qué pasa con el otro lado de la relación? Utiliza los documentos para ir a buscar el`CheeseListing` con id = 1. Sí, aquí está toda la información, incluido el `owner` como IRI. ¿Pero qué pasa si queremos ir en la otra dirección?

Actualicemos para cerrar todo. Ve a buscar el recurso `User` con id 1. Bastante aburrido: `email` y `username`. ¿Y si también quieres ver qué quesos ha publicado este usuario?

Eso es igual de fácil. Dentro de `User` encuentra la propiedad `$username`, copia la anotación`@Groups` y pégala encima de la propiedad `$cheeseListings`. Pero... por ahora, sólo vamos a hacer esto legible: sólo `user:read`. Más adelante hablaremos de cómo puedes modificar las relaciones de colección.

[[[ code('71506814e0') ]]]

Bien, actualiza y abre la operación de elemento GET para Usuario. Antes de intentarlo, ya anuncia que ahora devolverá una propiedad `cheeseListings` que, curiosamente, será un array de cadenas. Veamos qué aspecto tiene `User` id 1. ¡Ejecuta!

Ah... ¡es una matriz! Una matriz de cadenas IRI, por supuesto. Por defecto, cuando relacionas dos recursos, la Plataforma API mostrará el recurso relacionado como un IRI o una matriz de IRIs, lo cual es maravillosamente sencillo. Si el cliente de la API necesita más información, puede hacer otra petición a esa URL.

O... si quieres evitar esa petición adicional, puedes optar por incrustar los datos del listado de quesos directamente en el JSON del recurso del usuario. Hablemos de eso a continuación.
