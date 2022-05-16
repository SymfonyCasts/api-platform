# Eliminar elementos de una colección

Cierra la operación POST. Quiero hacer una petición GET a la colección de usuarios. Veamos aquí - el usuario con id 4 tiene un `CheeseListing` adjunto - id 2. Ok, cierra esa operación y abre la operación para `PUT`: Quiero editar ese usuario. Introduce el 4 como id.

En primer lugar, voy a hacer algo que ya hemos visto: vamos a actualizar el campo`cheeseListings`: establécelo como una matriz con un IRI dentro: `/api/cheeses/2`. Si no hiciéramos nada más, esto establecería esta propiedad como... exactamente lo que ya es: el id de usuario 4 ya tiene este `CheeseListing`.

Pero ahora, añade otro IRI: `/api/cheeses/3`. Que ya existe, pero es propiedad de otro usuario. Cuando pulso Execute.... pfff - me sale un error de sintaxis, porque me he dejado una coma de más en mi JSON. Boo Ryan. Vamos a... intentarlo de nuevo. Esta vez... ¡bah! Un código de estado 400:

> Este valor no debería estar en blanco

¡Mis experimentos con la validación acaban de volverse en mi contra! Hemos puesto el `title` de`CheeseListing` 3 en una cadena vacía en la base de datos... es básicamente un registro "malo" que se coló cuando jugábamos con la validación incrustada. Podríamos arreglar ese título... o... simplemente cambiar esto por `/api/cheeses/1`. ¡Ejecutar!

## El serializador sólo llama a los agregadores para los nuevos elementos

Esta vez, ¡funciona! Pero, no es una sorpresa, ¡básicamente ya lo hemos hecho! Internamente, el serializador ve el IRI existente de `CheeseListing` - `/api/cheeses/2`, se da cuenta de que ya está establecido en nuestro `User`, y... no hace nada. Es decir, quizá vaya a tomar un café o a dar un paseo. Pero, definitivamente, no llama a`$user->addCheeseListing()`... ni hace realmente nada. Pero cuando ve el nuevo IRI - `/api/cheeses/1`, se da cuenta de que este `CheeseListing` no existe todavía en el `User`, y entonces, sí llama a `$user->addCheeseListing()`. Por eso son tan útiles los métodos de adición y eliminación: el serializador es lo suficientemente inteligente como para llamarlos sólo cuando realmente se está añadiendo o eliminando un objeto.

## Eliminar elementos de una colección

Ahora, hagamos lo contrario: imagina que queremos eliminar un `CheeseListing`de este `User` - eliminar `/api/cheeses/2`. ¿Qué crees que ocurrirá? Ejecuta y... ¡woh! ¡Un error de restricción de integridad!

> Se ha producido una excepción al ejecutar UPDATE cheese_listing SET owner_id=NULL -
> la columna `owner_id` no puede ser nula.

¡Esto es genial! El serializador se ha dado cuenta de que hemos eliminado el `CheeseListing` con id = 2. Y así, llamó correctamente a `$user->removeCheeseListing()` y le pasó a`CheeseListing` el id 2. Entonces, nuestro código generado estableció el propietario en ese `CheeseListing`como nulo.

Dependiendo de la situación y de la naturaleza de la relación y las entidades, ¡esto podría ser exactamente lo que quieres! O, si se tratara de una relación ManyToMany, el resultado de ese código generado sería básicamente "desvincular" los dos objetos.

## orphanRemoval

Pero en nuestro caso, no queremos que un `CheeseListing` sea nunca "huérfano" en la base de datos. De hecho... ¡es exactamente por lo que hicimos `owner` `nullable=false` y por lo que vemos este error! No, si se elimina un `CheeseListing` de un `User`... ¡supongo que tenemos que eliminar ese `CheeseListing` por completo!

Y... ¡sí, hacer eso es fácil! Todo el camino de vuelta por encima de la propiedad `$cheeseListings`, añade `orphanRemoval=true`.

[[[ code('2595134de7') ]]]

Esto significa que, si alguno de los `CheeseListings` de esta matriz de repente... no está en esta matriz, Doctrine lo borrará. Simplemente, ten en cuenta que si intentas reasignar un `CheeseListing` a otro `User`, seguirá borrando ese`CheeseListing`. Así que asegúrate de que sólo utilizas esto cuando no sea un caso de uso. Hemos cambiado el propietario de los listados de queso un montón... pero sólo como ejemplo: no tiene realmente sentido, así que esto es perfecto.

Ejecuta una vez más. Funciona... y sólo está `/api/cheeses/1`. Y si volvemos a buscar la colección de listados de quesos... sí,`CheeseListing` id 2 ha desaparecido.

A continuación, cuando combinas las relaciones y el filtrado... bueno... obtienes una potencia bastante importante.
