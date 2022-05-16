# Añadir elementos a una propiedad de la colección

Utiliza los documentos para consultar la página `User` con id=2. Cuando leemos un recurso, podemos decidir exponer cualquier propiedad, y una propiedad que contiene una colección, como `cheeseListings`, no es diferente. Exponemos esa propiedad añadiendo`@Groups("user:read")` sobre ella. Y como ésta contiene una colección de objetos relacionados, también podemos decidir si la propiedad `cheeseListings` debe exponerse como una matriz de cadenas IRI o como una matriz de objetos incrustados, añadiendo este mismo grupo a al menos una propiedad dentro de la propia `CheeseListing`.

Genial. ¡Nuevo reto! Podemos leer la propiedad `cheeseListings` en `User`... pero ¿podríamos también modificar esta propiedad?

Por ejemplo, bueno, es un ejemplo un poco extraño, pero supongamos que un administrador quiere poder editar un `User` y convertirlo en propietario de algunos objetos`CheeseListing` existentes en el sistema. Esto ya se puede hacer editando un`CheeseListing` y cambiando su `owner`. Pero, ¿podríamos hacerlo también editando un`User` y pasándole una propiedad `cheeseListings`?

En realidad, ¡vamos a ponernos aún más locos! Quiero poder crear un nuevo`User` y especificar uno o más listados de queso que este `User` debería poseer... todo en una sola petición.

## Hacer que los listados de queso sean modificables

Ahora mismo, la propiedad `cheeseListings` no es modificable. La razón es sencilla: esa propiedad sólo tiene el grupo de lectura. ¡Genial! Haré que ese grupo sea un array y añadiré `user:write`.

[[[ code('16d4b0a91c') ]]]

Ahora, vuelve, refresca los documentos y mira la operación POST: sí tenemos una propiedad `cheeseListings`. ¡Vamos a hacerlo! Empieza con la aburrida información del usuario: correo electrónico, la contraseña no importa y el nombre de usuario. Para `cheeseListings`, esto tiene que ser un array... porque esta propiedad contiene un array. Dentro, añade sólo un elemento - un IRI - `/api/cheeses/1`.

En un mundo perfecto, esto creará un nuevo `User` y luego irá a buscar el`CheeseListing` con el id `1` y lo cambiará para que sea propiedad de este usuario. Respira hondo. ¡Ejecuta!

¿Ha funcionado? Es decir, ¡ha funcionado! Un código de estado 201: ¡ha creado el nuevo `User` y ese `User` es ahora el propietario de este `CheeseListing`! Espera un segundo... ¿cómo ha funcionado?

## Métodos de adición y eliminación de colecciones

Compruébalo: entendemos cómo se manejan `email`, `password` y `username`: cuando hacemos un POST, el serializador llamará a `setEmail()`. En este caso, estamos enviando un campo`cheeseListings`... pero si vamos a buscar `setCheeseListings()`, ¡no existe!

En su lugar, busca `addCheeseListing()`. Ahhh. El comando `make:entity` es inteligente: cuando genera una relación de colección como ésta, en lugar de generar un método`setCheeseListings()`, genera `addCheeseListing()` y`removeCheeseListing()`. Y el serializador es lo suficientemente inteligente como para utilizarlos Ve el único IRI de `CheeseListing` que estamos enviando, consulta la base de datos en busca de ese objeto, llama a `addCheeseListing()` y lo pasa como argumento.

La razón por la que `make:entity` genera el sumador -en lugar de sólo`setCheeseListings()` - es que nos permite hacer cosas cuando se añade o elimina un listado de quesos. ¡Y eso es clave! Compruébalo: dentro del código generado, llama a `$cheeseListing->setOwner($this)`. Por eso el propietario cambió al nuevo usuario, para este `CheeseListing` con id=1. Entonces... ¡todo se guarda!

Siguiente: cuando estemos creando o editando un usuario, en lugar de reasignar un`CheeseListing` existente a un nuevo propietario, vamos a hacer posible la creación de listados de quesos totalmente nuevos. Sí, ¡nos estamos volviendo locos! Pero esto nos permitirá aprender aún más sobre cómo piensa y funciona el serializador.
