# Relación incrustada

Cuando dos recursos están relacionados entre sí, esto puede expresarse de dos maneras diferentes en una API. La primera es con IRIs, básicamente un "enlace" al otro recurso. No podemos ver los datos del `CheeseListing` relacionado, pero si los necesitamos, podríamos hacer una segunda petición a esta URL y... ¡boom! Lo tenemos.

Pero, por motivos de rendimiento, podrías decir:

> ¿Sabes qué? No quiero tener que hacer una petición para obtener los datos del usuario
> y luego otra petición para obtener los datos de cada lista de quesos que posean:
> ¡Quiero obtenerlos todos de una vez!

Y eso describe la segunda forma de expresar una relación: en lugar de devolver sólo un enlace a un listado de quesos, ¡puedes incrustar sus datos justo dentro!

## Incrustar el listado de quesos en el usuario

Como recordatorio, cuando normalizamos un `User`, incluimos todo en el grupo`user:read`. Así que eso significa `$email`, `$username` y `$cheeseListings`, que es la razón por la que esa propiedad aparece en absoluto.

Para hacer que esta propiedad devuelva datos, en lugar de sólo un IRI, esto es lo que tienes que hacer: entra en la entidad relacionada -así que `CheeseListing` - y añade este grupo `user:read`a al menos una propiedad. Por ejemplo, añade `user:read` por encima de `$title`... y qué tal también por encima de `$price`.

[[[ code('b1f0216d29') ]]]

¡A ver qué pasa! Ni siquiera necesitamos actualizar, sólo ejecutar. ¡Vaya! En lugar de una matriz de cadenas, ¡ahora es una matriz de objetos! Bueno, este usuario sólo posee un CheeseListing, pero te haces una idea. Cada elemento tiene los estándares `@type`y `@id` más las propiedades que hayamos añadido al grupo: `title` y `price`.

Es muy sencillo: el serializador sabe que debe serializar todos los campos del grupo`user:read`. Primero busca en `User` y encuentra `email`, `username` y`cheeseListings`. Luego sigue adelante y, dentro de `CheeseListing`, encuentra ese grupo en `title` y `price`.

## Cadenas de relación frente a objetos

Esto significa que cada propiedad de la relación puede ser una cadena -el IRI- o un objeto. Y un cliente de la API puede distinguir la diferencia. Si recibe un objeto, sabe que tendrá `@id`, `@type` y algunas otras propiedades de datos. Si obtienes una cadena, sabes que es un IRI que puedes utilizar para obtener los datos reales.

## Incrustar el usuario en CheeseListing

Podemos hacer lo mismo en el otro lado de la relación. Utiliza los documentos para obtener el `CheeseListing` con `id = 1`. ¡Sí! La propiedad `owner` es una cadena. Pero puede ser conveniente que el JSON de CheeseListing contenga al menos el nombre de usuario del propietario... para que no tengamos que ir a buscar todo el Usuario sólo para mostrar quién es el propietario.

Dentro de `CheeseListing`, el proceso de normalización serializará todo en el grupo `cheese_listing:read`. Copiadlo. La propiedad `owner`, por supuesto, ya tiene este grupo encima, por eso lo vemos en nuestra API. Dentro de `User`, busca`$username`... y añade `cheese_listing:read` a eso.

[[[ code('2bd7945b33') ]]]

¡Vamos a probar esto! Muévete hacia atrás y... ¡Ejecuta! Y... ¡ja! Perfecto! Se expande a un objeto e incluye el `username`.

## Incrustar datos sólo cuando se obtiene un único artículo

¿Funciona si obtenemos la colección de listados de quesos? ¡Pruébalo! Bueno... vale, ahora mismo sólo hay un `CheeseListing` en la base de datos, pero ¡claro! Incrusta el propietario de la misma manera.

Así que... sobre eso... ¡nuevo reto! ¿Qué pasa si queremos incrustar los datos de `owner` cuando obtengo un solo `CheeseListing`... pero, para que la respuesta no sea gigantesca... no queremos incrustar los datos cuando obtenemos la colección. ¿Es posible?

Totalmente De nuevo, para `CheeseListing`, cuando normalizamos, incluimos todo en el grupo `cheese_listing:read`. Esto es así independientemente de si estamos obteniendo la colección de listados de quesos o sólo obtenemos un único artículo. Pero, un montón de cosas -incluidos los grupos- pueden cambiarse operación por operación.

Por ejemplo, en `itemOperations`, rompe la configuración de la operación `get` en varias líneas y añade `normalization_context`. Una de las cosas complicadas de la configuración aquí es que las claves de nivel superior son minúsculas, como`normalizationContext`. Pero las claves más profundas suelen ser mayúsculas y minúsculas, como`normalization_context`. Eso... puede ser un poco incoherente, y es fácil estropearlo. Ten cuidado.

En cualquier caso, el objetivo es anular el contexto de normalización, pero sólo para esta operación. Establece esto en la normalidad `groups` y en otra matriz. Dentro, vamos a decir:

> Cuando se obtiene un único elemento, quiero incluir todas las
> propiedades que tiene el grupo `cheese_listing:read` como es normal. Pero también
> quiero incluir todas las propiedades de un nuevo grupo `cheese_listing:item:get`.

[[[ code('0eb08286dd') ]]]

Hablaremos de ello más adelante, pero estoy utilizando una convención de nomenclatura específica para este grupo específico de la operación: el "nombre de la entidad", dos puntos, el elemento o la colección, dos puntos, y luego el método HTTP: `get`, `post`, `put`, etc.

Si volvemos a obtener un único `CheeseListing`.... no hay ninguna diferencia: estamos incluyendo un nuevo grupo para la serialización - yaaaay - pero no hay nada en el nuevo grupo.

Aquí está la magia. Copia el nombre del nuevo grupo, abre `User`, y sobre la propiedad`$username`, sustituye `cheese_listing:read` por `cheese_listing:item:get`.

[[[ code('9aec44a25c') ]]]

Ya está Vuelve a la documentación y busca un solo `CheeseListing`. Y... perfecto - sigue incorporando al propietario - ahí está el nombre de usuario. Pero ahora, cierra eso y ve al punto final de la colección GET. ¡Ejecuta! ¡Sí! ¡El propietario vuelve a ser un IRI!

Estos grupos de serialización pueden ser un poco complejos de pensar, pero vaya si son potentes.

A continuación... cuando obtenemos un `CheeseListing`, algunos de los datos del propietario se incrustan en la respuesta. Así que... tengo una pregunta un poco loca: cuando actualizamos un `CheeseListing`... ¿podríamos actualizar también algunos datos del propietario enviando datos incrustados? Um... ¡sí! Eso a continuación.
