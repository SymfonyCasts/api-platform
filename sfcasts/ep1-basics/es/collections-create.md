# Crear objetos incrustados

En lugar de asignar un `CheeseListing` existente al usuario, ¿podríamos crear uno totalmente nuevo incrustando sus datos? ¡Vamos a averiguarlo!

Esta vez, no enviaremos una cadena IRI, sino un objeto de datos. Veamos... necesitamos un `title` y... Haré trampa y miraré la ruta `POST` para los quesos. Bien: necesitamos `title`, `price` `owner` y `description`. Establece `price`en 20 dólares y pasa un `description`. Pero no voy a enviar una propiedad `owner`. ¿Por qué? Bueno... olvídate de la Plataforma API e imagina que utilizas esta API. Si enviamos una petición POST a `/api/users` para crear un nuevo usuario... ¿no es bastante obvio que queremos que el nuevo listado de quesos sea propiedad de este nuevo usuario? Por supuesto, es nuestro trabajo hacer que esto funcione realmente, pero así es como yo querría que funcionara.

Ah, y antes de que lo intentemos, cambia el `email` y el `username` para asegurarte de que son únicos en la base de datos.

¿Preparado? ¡Ejecuta! ¡Funciona! No, no, estoy mintiendo: no es tan fácil. Tenemos un error conocido:

> No se permiten documentos anidados para el atributo "cheeseListings". Utiliza en su lugar IRIs.

## Permitir que los cheeseListings incrustados se desnormalicen

Bien, retrocedamos. El campo `cheeseListings` es escribible en nuestra API porque la propiedad `cheeseListings` tiene el grupo `user:write` encima. Pero si no hiciéramos nada más, esto significaría que podemos pasar una matriz de IRIs a esta propiedad, pero no un objeto JSON de datos incrustados.

Para permitirlo, tenemos que entrar en `CheeseListing` y añadir ese grupo `user:write` a todas las propiedades que queramos permitir pasar. Por ejemplo, sabemos que, para crear un `CheeseListing`, necesitamos poder establecer `title`,`description` y `price`. Así que, ¡añadamos ese grupo! `user:write` por encima de `title`,`price` y... aquí abajo, busca `setTextDescription()`... y añádelo ahí.

[[[ code('0e4bac8f13') ]]]    

Me encanta lo limpio que es elegir los campos que quieres que se incrusten... pero la vida se complica. Ten en cuenta ese coste de "complejidad" si decides admitir este tipo de cosas en tu API

## Persistencia en cascada

En cualquier caso, ¡probemos! Ooh - un error 500. ¡Estamos más cerca! ¡Y también conocemos este error!

> Se ha encontrado una nueva entidad a través de la relación `User.cheeseListings` que
> no estaba configurada para persistir en cascada.

¡Excelente! Esto me dice que la Plataforma API está creando un nuevo `CheeseListing`y lo está configurando en la propiedad `cheeseListings` del nuevo `User`. Pero nada llama a `$entityManager->persist()` en ese nuevo `CheeseListing`, por lo que Doctrine no sabe qué hacer cuando intenta guardar el Usuario.

Si se tratara de una aplicación Symfony tradicional, en la que yo escribiera personalmente el código para crear y guardar estos objetos, probablemente me limitaría a encontrar dónde se está creando ese `CheeseListing`y llamaría a `$entityManager->persist()` sobre él. Pero como la Plataforma API se encarga de todo eso por nosotros, podemos utilizar una solución diferente.

Abre `User`, busca la propiedad `$cheeseListings`, y añade `cascade={"persist"}`. Gracias a esto, cada vez que se persista un `User`, Doctrine persistirá automáticamente cualquier objeto `CheeseListing` en esta colección.

[[[ code('455e97b909') ]]]

Bien, veamos qué ocurre. ¡Ejecuta! Woh, ¡ha funcionado! Esto ha creado un nuevo `User`, un nuevo `CheeseListing` y los ha vinculado en la base de datos.

## Pero, ¿quién estableció CheeseListing.owner?

Pero... ¿cómo sabía Doctrine... o la Plataforma API que debía establecer la propiedad `owner` del nuevo `CheeseListing` en el nuevo `User`... si no pasábamos una clave `owner` en el JSON? Si creas un `CheeseListing` de la forma normal, ¡es totalmente necesario!

Esto funciona... no por ninguna plataforma de la API ni por la magia de Doctrine, sino gracias a un código bueno, anticuado y bien escrito en nuestra entidad. Internamente, el serializador instala un nuevo `CheeseListing`, le pone datos y luego llama a`$user->addCheeseListing()`, pasando ese nuevo objeto como argumento. Y ese código se encarga de llamar a`$cheeseListing->setOwner()` y establecerlo en `$this`Usuario. Me encanta eso: nuestro código generado de `make:entity` y el serializador están trabajando juntos. ¿Qué va a funcionar? ¡El trabajo en equipo!

## Validación incrustada

Pero, al igual que cuando incrustamos los datos de `owner` al editar un `CheeseListing`, cuando permites que se cambien o creen recursos incrustados como éste, tienes que prestar especial atención a la validación. Por ejemplo, cambia los `email` y `username`para que vuelvan a ser únicos. Ahora se trata de un usuario válido. Pero establece el `title` del`CheeseListing` a una cadena vacía. ¿La validación detendrá esto?

No Ha permitido que el `CheeseListing` se guarde sin título, ¡a pesar de que tenemos la validación para evitarlo! Esto se debe a que, como hemos hablado antes, cuando el validador procesa el objeto `User`, no baja automáticamente a la matriz `cheeseListings` y valida también esos objetos. Puedes forzarlo añadiendo `@Assert\Valid()`.

[[[ code('1ca01098e3') ]]]

Asegurémonos de que eso ha servido de algo: vuelve a subir, haz que los objetos `email` y `username`vuelvan a ser únicos y... ¡Ejecuta! ¡Perfecto! Un código de estado 400 porque

> el campo `cheeseListings[0].title` no debería estar en blanco.

Vale, hemos hablado de cómo añadir nuevos listados de quesos a un usuario, ya sea pasando el IRI de un `CheeseListing` existente o incrustando datos para crear un nuevo`CheeseListing`. Pero, ¿qué pasaría si un usuario tuviera 2 listados de quesos... y realizáramos una petición para editar ese `User`... y sólo incluyéramos el IRI de uno de esos listados? Eso debería... eliminar el `CheeseListing` que le falta al usuario, ¿no? ¿Funciona? Y si es así, ¿pone el `owner` de ese CheeseListing en cero? ¿O lo elimina por completo? ¡Busquemos algunas respuestas a continuación!
