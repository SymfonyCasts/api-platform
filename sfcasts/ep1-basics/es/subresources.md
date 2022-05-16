# Subrecursos

En algún momento, un cliente de la API... que podría ser nuestro JavaScript, probablemente querrá obtener una lista de todos los `cheeseListings` de un `User` específico. Y... ya podemos hacerlo de dos maneras diferentes: buscar un propietario específico aquí a través de nuestro filtro... o recuperar el `User` específico y mirar su propiedad `cheeseListings`.

Si lo piensas, un `CheeseListing` es casi como un recurso "hijo" de un `User`: los listados de queso pertenecen a los usuarios. Y por esa razón, a algunas personas les gustaría poder obtener los listados de queso de un usuario yendo a una URL como ésta: `/api/users/4/cheeses`... o algo similar.

Pero... eso no funciona. Esta idea se llama "subrecursos". Ahora mismo, cada recurso tiene su propia, especie de, URL base: `/api/cheeses` y `/api/users`. Pero es posible, más o menos, "mover" los quesos bajo los usuarios.

He aquí cómo: en `User`, busca la propiedad `$cheeseListings` y añade `@ApiSubresource`.

[[[ code('4636f39c9e') ]]]

¡Vamos a refrescar los documentos! ¡Woh! Tenemos una nueva ruta!`/api/users/{id}/cheese_listings`. Aparece en dos sitios... porque está algo relacionado con los usuarios... y algo relacionado con los listados de queso. La URL es cheese_listings por defecto, pero se puede personalizar.

Así que... ¡probemos! Cambia la URL a `/cheese_listings`. Ah, y añade el`.jsonld` al final. ¡Ya está! El recurso de la colección de todos los quesos que pertenecen a este `User`.

¡Los sub-recursos son bastante chulos! Pero... también son un poco innecesarios: ya hemos añadido una forma de obtener la colección de listados de quesos de un usuario a través de `SearchFilter`en `CheeseListing`. Y el uso de subrecursos significa que tienes más rutas que controlar y, cuando llegamos a la seguridad, más rutas significan más control de acceso en el que pensar.

Así que, utiliza subrecursos si quieres, pero no recomiendo añadirlos en todas partes, ya que hay un coste por la complejidad añadida. Ah, y por cierto, hay un montón de cosas que puedes personalizar en los subrecursos, como los grupos de normalización, la URL, etc. Está todo en los documentos y es bastante similar a los tipos de personalización que hemos visto hasta ahora.

En el caso de nuestra aplicación, voy a eliminar el subrecurso para simplificar las cosas.

Y... ¡hemos terminado! Bueno, hay muchas más cosas interesantes que cubrir - ¡incluyendo la seguridad! Ese es el tema del próximo tutorial de esta serie. Pero ¡déjate llevar por el salto y choca los cinco! ¡Ya hemos desbloqueado una gran cantidad de poder! Podemos exponer entidades como recursos de la API, personalizar las operaciones, tomar el control total del serializador de un montón de maneras diferentes y un montón más. Así que empieza a construir tu preciosa nueva API, cuéntanoslo y, como siempre, si tienes preguntas, puedes encontrarnos en la sección de comentarios.

Muy bien amigos, ¡hasta la próxima!
