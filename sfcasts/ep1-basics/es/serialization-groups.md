# Grupos de serialización

Si la única forma de controlar la entrada y la salida de nuestra API fuera controlar los getters y setters de nuestra entidad, no sería tan flexible... y podría ser un poco peligroso. ¡Podrías añadir un nuevo método getter o setter para algo interno y no darte cuenta de que estabas exponiendo nuevos datos en tu API!

La solución para esto -y la forma en que recomiendo hacer las cosas en todos los casos- es utilizar grupos de serialización.

## Añadir un grupo de normalización

En la anotación, añade `normalizationContext`. Recuerda que la normalización se produce cuando pasas de tu objeto a un array. Así que esta opción está relacionada con el momento en que estás leyendo datos de tu API. El contexto es básicamente "opciones" que pasas a ese proceso. La opción más común, con diferencia, se llama `"groups"`, que se establece en otro array. Añade una cadena aquí: `cheese_listing:read`.

[[[ code('8750fdc710') ]]]

Gracias a esto, cuando se serialice un objeto, el serializador sólo incluirá los campos que estén en este grupo `cheese_listing:read`, porque, en un segundo, vamos a empezar a añadir grupos a cada propiedad.

Pero ahora mismo, no hemos añadido ningún grupo a nada. Y así, si vas e intentas tu operación de colección get... ¡oh! ¡Ah! ¡Un gran error!

## Depuración de errores

Vamos a... hacer como si lo hubiera hecho a propósito y ver cómo depurarlo! El problema es que el gigantesco error HTML es... un poco difícil de leer. Una forma de ver el error es utilizar nuestro truco de antes: ir a `https://localhost:8000/_profiler/`.

¡Woh! Vale, hay dos tipos de errores: los errores de ejecución, en los que algo ha ido mal específicamente en esa petición, y los errores de compilación, en los que alguna configuración no válida está matando todas las páginas. La mayoría de las veces, si ves una excepción, todavía hay un perfilador que puedes encontrar para esa petición utilizando el truco de ir a esta URL, encontrar esa petición en la lista - normalmente justo en la parte superior - y hacer clic en el sha en su perfilador. Una vez allí, puedes hacer clic en la pestaña "Excepción" de la izquierda para ver la gran y hermosa excepción normal.

Si tienes un error de compilación que mata todas las páginas, es aún más fácil: lo verás cuando intentes acceder a cualquier cosa.

De todos modos, el problema aquí es con mi sintaxis de anotación. Lo hago a menudo, lo cual no es un gran problema siempre que sepas cómo depurar el error. Y, ¡sí! He olvidado una coma al final.

## Añadir grupos a los campos

¡Actualiza de nuevo! El perfilador funciona, así que ahora podemos volver a darle a ejecutar. Compruébalo: tenemos `@id` y `@type` de JSON-LD... ¡pero no contiene ningún campo real porque ninguno está en el nuevo grupo `cheese_listing:read`!

Copia el nombre del grupo `cheese_listing:read`. Para añadir campos a éste, por encima del título, utiliza `@Groups()`, `{""}` y pégalo. Pongamos también eso por encima de `description`... y `price`.

[[[ code('bb90cac6bf') ]]]

Dale la vuelta y vuelve a intentarlo. ¡Muy bien! Obtenemos esos tres campos exactos. Me encanta este control.

Por cierto, el nombre `cheese_listing:read`... Me lo acabo de inventar - puedes usar cualquier cosa. Pero, voy a seguir una convención de nomenclatura de grupos que recomiendo. Te dará flexibilidad, pero mantendrá las cosas organizadas.

## Añadir grupos de desnormalización

Ahora podemos hacer lo mismo con los datos de entrada. Copia `normalizationContext`, pégalo, y añade `de` delante para hacer `denormalizationContext`. Esta vez, utiliza el grupo `cheese_listing:write`

[[[ code('2ab6c65f11') ]]]

Copia esto y... veamos... sólo añade esto a `title` y `price` por ahora. En realidad no queremos añadirlo a `description`. En su lugar, hablaremos de cómo añadir este grupo al falso `textDescription` en un minuto.

[[[ code('00f6954f3d') ]]]

Muévete y actualiza de nuevo. ¡Abre la ruta POST.... y ahora los únicos campos que podemos pasar son `title` y `price`!

Así que `normalizationContext` y `denormalizationContext` son dos configuraciones totalmente separadas para las dos direcciones: lectura de nuestros datos - normalización - y escritura de nuestros datos - desnormalización.

## Los modelos de lectura y escritura de la API abierta

En la parte inferior de los documentos, también te darás cuenta de que ahora tenemos dos modelos: el modelo de lectura - que es el contexto de normalización con `title`, `description` y`price`, y el modelo de escritura con `title` y `price`.

Y, no es realmente importante, pero puedes controlar estos nombres si quieres. Añade otra opción: `swagger_definition_name` ajustada a "Lectura". Y a continuación lo mismo... ajustado a Escritura.

[[[ code('6a589d6804') ]]]

Normalmente no me importa esto, pero si quieres controlarlo, puedes hacerlo.

## Añadir grupos a los campos falsos

Pero, ¡nos faltan algunos campos! Cuando leemos los datos, obtenemos `title`,`description` y `price`. ¿Pero qué pasa con nuestro campo `createdAt` o nuestro campo personalizado `createdAtAgo`?

Imaginemos que sólo queremos exponer `createdAtAgo`. ¡No hay problema! Sólo tienes que añadir la anotación `@Groups` a esa propiedad... oh, espera... no hay ninguna propiedad `createdAtAgo`. Ah, es igual de fácil: busca el getter y pon la anotación allí:`@Groups({"cheese_listing:read"})`. Y ya que estamos aquí, añadiré algo de documentación a ese método:

> Hace cuánto tiempo en texto que se añadió este listado de quesos.

[[[ code('69efb9d8b8') ]]]

¡Vamos a probarlo! Actualiza la documentación. Abajo, en la sección de modelos... ¡qué bien! Ahí está nuestro nuevo campo `createdAtAgo` de sólo lectura. Y la documentación que hemos añadido aparece aquí. ¡Muy bien! No es de extrañar que cuando lo probamos... el campo aparezca.

Para la desnormalización -para el envío de datos- tenemos que volver a añadir nuestro campo falso `textDescription`. Busca el método `setTextDescription()`. Para evitar que los clientes de la API nos envíen directamente el campo `description`, eliminamos el método `setDescription()`. Por encima de `setTextDescription()`, añadimos `@Groups({"cheese_listing:write"})`. Y de nuevo, vamos a darle a esto algunos documentos adicionales.

[[[ code('794f4ff08a') ]]]

Esta vez, cuando refresquemos los documentos, podrás verlo en el modelo de escritura y, por supuesto, en los datos que podemos enviar a la operación POST.

## Ten los Getters y Setters que quieras

Y... ¡esto nos lleva a una gran noticia! Si decidimos que algo interno de nuestra aplicación necesita establecer la propiedad de descripción directamente, ahora es perfectamente posible volver a añadir el método original `setDescription()`. Eso no formará parte de nuestra API.

[[[ code('ba9aa2825a') ]]]

## Valor predeterminado de isPublished

Vamos a probar todo esto. Actualiza la página de documentos. Creemos un nuevo listado: Delicioso chèvre -disculpa mi francés- por 25 dólares y una descripción con algunos saltos de línea. ¡Ejecuta!

¡Woh! ¡Un error 500! Podría ir a mirar esta excepción en el perfilador, pero ésta es bastante fácil de leer: una excepción en nuestra consulta: `is_published` no puede ser nulo. Oh, eso tiene sentido: el usuario no está enviando `is_published`... así que nadie lo está estableciendo. Y está establecido como no nulo en la base de datos. No te preocupes: pon la propiedad por defecto en `false`.

[[[ code('a5d6654c9b') ]]]

***TIP
En realidad, la autovalidación no estaba activada por defecto en Symfony 4.3, pero puede que lo esté en Symfony 4.4.
***

Por cierto, si estás usando Symfony 4.3, en lugar de un error de Doctrine, puede que hayas obtenido un error de validación. Esto se debe a una nueva función en la que las restricciones de la base de datos Doctrine pueden utilizarse automáticamente para añadir validación. Así que, si ves un error de validación, ¡genial!

De todos modos, intenta ejecutarlo de nuevo. ¡Funciona! Tenemos exactamente los campos de entrada y salida que queremos. El campo `isPublished` no está expuesto en absoluto en nuestra API, pero se está configurando en segundo plano.

A continuación, vamos a aprender algunos trucos más de serialización, como el control del nombre del campo y el manejo de los argumentos del constructor.
