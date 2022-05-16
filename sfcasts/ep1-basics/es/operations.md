# Operaciones

Pongamos manos a la obra para personalizar nuestra API. Una API RESTful se basa en recursos. Tenemos un recurso -nuestro `CheeseListing` - y, por defecto, la Plataforma API ha generado 5 rutas para él. Estos se llaman "operaciones".

## Operaciones de colección y de elementos

Las operaciones se dividen en dos categorías. En primer lugar, las operaciones de "colección". Son las URL que no incluyen `{id}` y en las que el "recurso" sobre el que operas es técnicamente la "colección de listados de quesos". Por ejemplo, estás "obteniendo" la colección o estás "añadiendo" a la colección con POST.

Y en segundo lugar, las operaciones de "artículos". Estas son las URL que sí tienen la parte `{id}`, cuando estás "operando" sobre un único recurso de listado de quesos.

Lo primero que podemos personalizar es qué operaciones queremos realmente Por encima de`CheeseListing`, dentro de la anotación, añade `collectionOperations={}` con`"get"` y `"post"` dentro. Luego `itemOperations` con `{"get", "put", "delete"}`.

***TIP
A partir de ApiPlatform 2.5, también hay una operación `patch`. Funciona como la operación `put` y se recomienda sobre `put` cuando sólo quieres cambiar algunos campos (que es la mayoría de las veces). Para permitir la operación `patch`, añade esta configuración:```
// config/packages/api_platform.yaml
api_platform:
    patch_formats:
        json: ['application/merge-patch+json']
```. Luego, al hacer una petición `PATCH`, establece la cabecera `Content-Type` con `application/merge-patch+json`. Consulta la documentación interactiva, allí verás un ejemplo ;).
***

[[[ code('4eafb7ea76') ]]]

Gran parte del dominio de la Plataforma API se reduce a aprender qué opciones puedes pasar dentro de esta anotación. Esta es básicamente la configuración por defecto: queremos las cinco operaciones. Así que no es de extrañar que, cuando actualizamos, no veamos ningún cambio. Pero, ¿qué pasa si no queremos permitir a los usuarios eliminar un listado de quesos? Tal vez, en lugar de eso, en el futuro, añadamos una forma de "archivar" los mismos. Eliminar `"delete"`.

[[[ code('70cbcf174b') ]]]

En cuanto hagamos eso... ¡boom! Desaparece de nuestra documentación. Sencillo, ¿verdad? ¡Sí! Pero acaban de ocurrir un montón de cosas geniales. Recuerda que, entre bastidores, la interfaz de usuario de Swagger se construye a partir de un documento de especificaciones de la API abierta, que puedes ver en `/api/docs.json`. La razón por la que el punto final "eliminar" desapareció de Swagger es que desapareció de aquí. La Plataforma API mantiene actualizado nuestro documento de "especificaciones". Si miraras el documento de especificaciones JSON-LD, verías lo mismo.

Y, por supuesto, también ha eliminado por completo la ruta -puedes comprobarlo ejecutando:

```terminal
php bin/console debug:router
```

Sí, sólo `GET`, `POST`, `GET` y `PUT`.

## Personalizar la URL del recurso (shortName)

Hmm, ahora que lo veo, no me gusta la parte `cheese_listings` de las URLs... La Plataforma API la genera a partir del nombre de la clase. Y realmente, en una API, no deberías obsesionarte con el aspecto de tus URLs, no es importante, especialmente -como verás- cuando tus respuestas a la API incluyen enlaces a otros recursos. Pero... podemos controlar esto.

Vuelve a dar la vuelta y añade otra opción: `shortName` ajustada a `cheeses`.

[[[ code('f041112cf9') ]]]

Ahora ejecuta de nuevo `debug:router`:

```terminal-silent
php bin/console debug:router
```

¡Eh! `/api/cheeses`! ¡Mucho mejor! Y ahora vemos lo mismo en nuestros documentos de la API.

## Personalizar los detalles de la ruta de la operación

Vale: así podemos controlar qué operaciones queremos en un recurso. Y más adelante aprenderemos a añadir operaciones personalizadas. Pero también podemos controlar bastante sobre las operaciones individuales.

Sabemos que cada operación genera una ruta, y la Plataforma API te da un control total sobre el aspecto de esa ruta. Compruébalo: divide `itemOperations` en varias líneas. Entonces, en lugar de decir simplemente `"get"`, podemos decir `"get"={}` y pasar esta configuración extra.

Prueba a poner `"path"=` en, no sé, `"/i❤️️cheeses/{id}"`.

[[[ code('e0bf4ca943') ]]]

Ve a ver los documentos ¡Ja! ¡Eso funciona! ¿Qué más puedes poner aquí? Para empezar, cualquier cosa que se pueda definir en una ruta, se puede añadir aquí - como`method`, `hosts`, etc.

¿Qué más? Bueno, a lo largo del camino, aprenderemos sobre otras cosas específicas de la plataforma API que puedes poner aquí, como `access_control` para la seguridad y formas de controlar el proceso de serialización.

De hecho, ¡vamos a aprender sobre ese proceso ahora mismo! ¿Cómo transforma la Plataforma API nuestro objeto `CheeseListing` -con todas estas propiedades privadas- en el JSON que hemos estado viendo? Y cuando creamos un nuevo `CheeseListing`, ¿cómo convierte nuestro JSON de entrada en un objeto `CheeseListing`?

Entender el proceso de serialización puede ser la pieza más importante para desbloquear la Plataforma API.
