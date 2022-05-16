# Filtrar en las relaciones

Ve directamente a `/api/users/5.jsonld`. Este usuario posee un `CheeseListing`... y hemos decidido incrustar los campos `title` y `price` en lugar de mostrar sólo el IRI. ¡Genial!

Antes hemos hablado de un filtro muy chulo llamado `PropertyFilter`, que nos permite, por ejemplo, añadir `?properties[]=username` a la URL si sólo queremos recuperar ese campo. Lo hemos añadido a `CheeseListing`, pero no a `User`. ¡Arreglemos eso!

Por encima de `User`, añade `@ApiFilter(PropertyFilter::class)`. Y recuerda que tenemos que añadir manualmente la declaración `use` para las clases de filtro: `use PropertyFilter`.

[[[ code('c818db1ea9') ]]]

Y... ¡hemos terminado! Cuando actualizamos, ¡funciona! Aparte de las propiedades JSON-LD estándar, sólo vemos `username`.

## Selección de propiedades de relación incrustadas

Pero espera, ¡hay más! Quita la parte de `?properties[]=` por un segundo para que podamos ver la respuesta completa. ¿Qué pasaría si quisiéramos obtener sólo la propiedad `username` y la propiedad `title` de la relación incrustada `cheeseListings`? ¿Es posible? Totalmente, sólo tienes que conocer la sintaxis. Vuelve a poner `?properties[]=username`. Ahora añade `&properties[`, pero dentro de los corchetes, pon `cheeseListings`. Luego`[]=` y el nombre de la propiedad: `title`. ¡Dale caña! ¡Muy bien! Bueno, el `title` está vacío en este `CheeseListing`, pero te haces una idea. La cuestión es ésta: `PropertyFilter`
es una buena idea y puede utilizarse para filtrar datos incrustados sin ningún trabajo adicional.

## Buscar en propiedades relacionadas

Hablando de filtros, hemos dado a `CheeseListing` un montón de ellos, incluyendo la posibilidad de buscar por `title` o `description` y filtrar por `price`. Vamos a añadir otro.

Desplázate hasta la parte superior de `CheeseListing` para encontrar `SearchFilter`. Vamos a dividir esto en varias líneas 

[[[ code('7049bbb6e2') ]]]

Buscar por `title` y `description` está muy bien. ¿Pero qué pasa si quiero buscar por propietario: encontrar todos los `CheeseListings` que pertenecen a un `User` concreto? Bueno, ya podemos hacerlo de otra manera: obtener los datos de ese usuario y mirar su propiedad `cheeseListings`. Pero tenerlo como filtro podría ser súper útil. Diablos, ¡entonces podríamos buscar todos los listados de quesos propiedad de un usuario concreto y que coincidan con algún título! Y... si los usuarios empiezan a tener muchos `cheeseListings`, podríamos decidir no exponer esa propiedad en `User` en absoluto: la lista podría ser demasiado larga. La ventaja de un filtro es que podemos obtener todos los listados de quesos de un usuario en una colección paginada.

Para ello... añade `owner` ajustado a `exact`.

[[[ code('dd301f85a4') ]]]

Ve a actualizar los documentos y prueba la ruta GET. Tenemos un nuevo cuadro de filtrado, incluso podemos buscar por varios propietarios. Dentro de la caja, añade el IRI - `/api/users/4`. También puedes filtrar por `id`, pero se recomienda el IRI.

Ejecuta y... ¡sí! Obtenemos el `CheeseListing` para ese `User`. Y la sintaxis de la URL es maravillosamente sencilla: `?owner=` y el IRI... que sólo parece feo porque está codificado en la URL.

## Buscar listados de quesos por nombre de usuario del propietario

¡Pero podemos volvernos aún más locos! Añade un filtro más: `owner.username` ajustado a `partial`.

[[[ code('1a2d66f266') ]]]

Esto es muy bonito. Vuelve a actualizar los documentos y abre la operación de recogida. Aquí está nuestro nuevo cuadro de filtro, para `owner.username`. Fíjate en esto: Busca "cabeza" porque tenemos un montón de nombres de usuario con cabeza de queso. ¡Ejecuta! Esto encuentra dos listados de queso propiedad de los usuarios 4 y 5.

Busquemos a todos los usuarios... para estar seguros y... ¡sí! Los usuarios 4 y 5 coinciden con la búsqueda del nombre de usuario. Probemos a buscar exactamente este `cheesehead3`. Ponlo en la casilla y... ¡Ejecuta! ¡Ya está! La búsqueda exacta también funciona. Y, aunque estamos filtrando a través de una relación, la URL está bastante limpia:`owner.username=cheesehead3`.

Vale, sólo un tema más breve para esta parte de nuestro tutorial: los subrecursos.
