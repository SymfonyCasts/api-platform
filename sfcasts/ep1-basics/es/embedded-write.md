# Escritura incrustada

He aquí una cuestión interesante: si recuperamos un solo `CheeseListing`, podemos ver que el `username` aparece en la propiedad `owner`. Y, obviamente, si nosotros, editamos un `CheeseListing` concreto, podemos cambiar totalmente el propietario por otro distinto. En realidad, probemos esto: establezcamos `owner` a `/api/users/2`. Ejecuta y... ¡sí! ¡Se ha actualizado!

Eso es genial, y funciona más o menos como una propiedad escalar normal. Pero... volviendo a mirar los resultados de la operación GET... aquí está, si podemos leer la propiedad `username` del propietario relacionado, en lugar de cambiar el propietario por completo, ¿podríamos actualizar el nombre de usuario del propietario actual mientras actualizamos un `CheeseListing`?

Es un ejemplo un poco raro, pero la edición de datos a través de una relación incrustada es posible... y, como mínimo, es una forma impresionante de entender realmente cómo funciona el serializador.

## Intentando actualizar el propietario incrustado

De todos modos... ¡probemos! En lugar de establecer el propietario a un IRI, establécelo a un objeto e intenta actualizar el `username` a `cultured_cheese_head`. ¡Vamos, vamos, vamos!

Y... no funciona:

> No se permiten documentos anidados para el atributo "owner". Utiliza en su lugar IRIs.

Entonces... ¿es esto posible, o no?

Bueno, la razón por la que `username` se incrusta al serializar un `CheeseListing`es que, por encima de `username`, hemos añadido el grupo `cheese_listing:item:get`, que es uno de los grupos que se utilizan en la operación "obtener" el elemento.

La misma lógica se utiliza cuando se escribe un campo, o se desnormaliza. Si queremos que`username` se pueda escribir mientras se desnormaliza un `CheeseListing`, tenemos que ponerlo en un grupo que se utilice durante la desnormalización. En este caso, es`cheese_listing:write`.

Cópialo y pégalo encima de `username`.

[[[ code('6c066eceac') ]]]

En cuanto lo hagamos -porque la propiedad `owner` ya tiene este grupo- ¡se podrá escribir la propiedad `username` incrustada! Volvamos a intentarlo: seguimos intentando pasar un objeto con `username`. ¡Ejecuta!

## Envío de nuevos objetos frente a referencias en JSON

Y... oh... ¡sigue sin funcionar! ¡Pero el error es fascinante!

> Se ha encontrado una nueva entidad a través de la relación `CheeseListing.owner` que no estaba
> no estaba configurada para realizar operaciones de persistencia en cascada para la entidad Usuario.

Si llevas un tiempo en Doctrine, puede que reconozcas este extraño error. Ignorando por un momento la Plataforma API, significa que algo creó un objeto `User` totalmente nuevo, lo estableció en la propiedad `CheeseListing.owner` y luego intentó guardar. Pero como nadie llamó a `$entityManager->persist()` en el nuevo objeto`User`, ¡Doctrine entró en pánico!

Así que... ¡sí! ¡En lugar de consultar el propietario existente y actualizarlo, la Plataforma API tomó nuestros datos y los utilizó para crear un objeto `User` totalmente nuevo! ¡Eso no es en absoluto lo que queríamos! ¿Cómo podemos decirle que actualice el objeto `User` existente en su lugar?

Aquí está la respuesta, o en realidad, aquí está la regla simple: si enviamos una matriz de datos, o en realidad, un "objeto" en JSON, la Plataforma API asume que se trata de un nuevo objeto y así... crea un nuevo objeto. Si quieres indicar que, en cambio, quieres actualizar un objeto existente, sólo tienes que añadir la propiedad `@id`. Establécela como`/api/users/2`. Gracias a esto, la Plataforma API consultará ese usuario y lo modificará.

Vamos a probarlo de nuevo. ¡Funciona! Bueno... probablemente ha funcionado: parece que ha tenido éxito, pero no podemos ver el nombre de usuario aquí. Desplázate hacia abajo y busca el usuario con id 2.

¡Ahí está!

## ¿Crear nuevos usuarios?

Así pues, ahora sabemos que, al actualizar... o realmente crear... un `CheeseListing`, podemos enviar los datos de `owner` incrustados y señalar a la Plataforma API que debe actualizar un `owner` existente a través de la propiedad `@id`.

Y cuando no añadimos `@id`, intenta crear un nuevo objeto `User`... que no funciona por ese error de persistencia. Pero, podemos arreglar totalmente ese problema con un persist en cascada... que mostraré en unos minutos para resolver un problema diferente.

Entonces, espera... ¿significa esto que, en teoría, podríamos crear un `User`completamente nuevo mientras editamos un `CheeseListing`? La respuesta es.... ¡sí! Bueno... casi. Hay dos cosas que lo impiden ahora mismo: primero, la falta de persistencia de la cascada, que nos dio ese gran error de Doctrine. Y en segundo lugar, en `User`, también tendríamos que exponer los campos `$password` y `$email` porque ambos son necesarios en la base de datos. Cuando empiezas a hacer que las cosas incrustadas sean escribibles, sinceramente se añade complejidad. Asegúrate de llevar un registro de lo que es posible y lo que no es posible en tu API. No quiero que se creen usuarios accidentalmente al actualizar un `CheeseListing`, así que esto es perfecto.

## Validación incrustada

Pero queda una cosa rara. Establece `username` como una cadena vacía. Eso no debería funcionar porque tenemos un `@NotBlank()` por encima de `$username`.

Intenta actualizar de todos modos. Por supuesto Me sale el error 500 en cascada - déjame volver a poner la propiedad `@id`. Inténtalo de nuevo.

¡Woh! ¡Un código de estado 200! ¡Parece que ha funcionado! Baja y recupera este usuario... con id=2. ¡No tiene nombre de usuario! ¡No te preocupes!

Esto... es un poco de gotcha. Cuando modificamos el `CheeseListing`, se ejecutan las reglas de validación: `@Assert\NotBlank()`, `@Assert\Length()`, etc. Pero cuando el validador ve el objeto `owner` incrustado, no continúa hacia abajo en ese objeto para validarlo. Eso es normalmente lo que queremos: si sólo estábamos actualizando un `CheeseListing`, ¿por qué debería intentar validar también un objeto `User` relacionado que ni siquiera hemos modificado? No debería

Pero cuando haces actualizaciones de objetos incrustados como nosotros, eso cambia: sí queremos que la validación continúe hasta este objeto. Para forzar eso, encima de la propiedad `owner`, añade `@Assert\Valid()`.

[[[ code('157e4fdbfd') ]]]

Bien, vuelve atrás y... intenta de nuevo nuestra ruta de edición. Ejecuta. ¡Lo tengo!

> owner.username: Este valor no debe estar en blanco

¡Muy bien! Volvamos atrás y démosle un nombre de usuario válido... para no tener un usuario malo en nuestra base de datos. ¡Perfecto!

Poder hacer modificaciones en las propiedades incrustadas está muy bien... pero añade complejidad. Hazlo si lo necesitas, pero recuerda también que podemos actualizar un `CheeseListing` y un `User` de forma más sencilla haciendo dos peticiones a dos rutas.

A continuación, vamos a ponernos aún más locos y a hablar de la actualización de colecciones: ¿qué ocurre si intentamos modificar la propiedad `cheeseListings` directamente en un `User`?
