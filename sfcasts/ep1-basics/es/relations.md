# Recursos relacionados

Tenemos un recurso queso y un recurso usuario. ¡Vamos a relacionarlos! Bien, el verdadero problema que tenemos que resolver es el siguiente: cada `CheeseListing` será "propiedad" de un único usuario, lo cual es algo que tenemos que configurar en la base de datos, pero también algo que tenemos que exponer en nuestra API: cuando miro un recurso `CheeseListing`, ¡necesito saber qué usuario lo ha publicado!

## Crear la relación de la base de datos

Primero vamos a configurar la base de datos. Busca tu terminal y ejecuta:

```terminal
php bin/console make:entity
```

Actualicemos la entidad `CheeseListing` y añadamos una nueva propiedad `owner`. Ésta será una `relation` a la entidad `User`... que será una relación `ManyToOne`: cada `CheeseListing` tiene una `User`. ¿Esta nueva propiedad debe ser anulable en la base de datos? Di que no: cada `CheeseListing` debe tener un `owner`en nuestro sistema.

A continuación, formula una pregunta superimportante: ¿queremos añadir una nueva propiedad a `User` para poder acceder y actualizar los listados de quesos en ella, como`$user->getCheeseListings()`. Hacer esto es opcional, y hay dos razones por las que podrías quererlo. En primer lugar, si crees que escribir `$user->getCheeseListings()`en tu código puede ser conveniente, ¡lo querrás! En segundo lugar, cuando obtengas un`User` en nuestra API, si quieres ser capaz de ver qué listados de queso posee este usuario como una propiedad en el JSON, también querrás esto. Pronto hablaremos de ello.

En cualquier caso, di que sí, llama a la propiedad `cheeseListings` y di que no a `orphanRemoval`. Si no conoces esa opción... entonces no la necesitas. Y... ¡bono! Un poco más adelante en este tutorial, te mostraré por qué y cuándo es útil esta opción.

¡Pulsa intro para terminar! Como es habitual, esto hizo algunas cosas: añadió una propiedad `$owner`a `CheeseListing` junto con los métodos `getOwner()` y `setOwner()`. En `User`, añadió una propiedad `$cheeseListings` con un método `getCheeseListings()`... pero no un método `setCheeseListings()`. En su lugar, `make:entity` generó los métodos`addCheeseListing()` y `removeCheeseListing()`. Estos serán útiles más adelante.

[[[ code('be811e5079') ]]]

[[[ code('b5e24fc8a2') ]]]

Vamos a crear la migración:

```terminal
php bin/console make:migration
```

Y abre eso... para asegurarte de que no contiene nada extra 

[[[ code('e9e9053212') ]]]

Se ve bien: alterando la tabla y configurando la clave foránea. Ejecuta eso:

```terminal
php bin/console doctrine:migrations:migrate
```

¡Oh, no! ¡Ha explotado!

> No se puede añadir o actualizar una fila hija, falla una restricción de clave foránea

... en la columna `owner_id` de `cheese_listing`. Por encima de la propiedad `owner`, ponemos `nullable=false`, lo que significa que la columna `owner_id` de la tabla no puede ser nula. Pero... como nuestra tabla `cheese_listing` ya tiene algunas filas, cuando intentamos añadir esa nueva columna... no sabe qué valor utilizar para las filas existentes y explota.

Es un clásico fallo de migración. Si nuestro sitio ya estuviera en producción, tendríamos que hacer esta migración más elegante añadiendo primero la nueva columna como anulable, establecer los valores y luego cambiarla a no anulable. Pero como aún no estamos allí... podemos simplemente eliminar todos nuestros datos e intentarlo de nuevo. Ejecuta:

```terminal
php bin/console doctrine:schema:drop --help
```

... porque esto tiene una opción que no recuerdo. Ah, aquí está: `--full-database`
nos aseguraremos de eliminar todas las tablas, incluida `migration_versions`. Ejecuta: :

```terminal
php bin/console doctrine:schema:drop --full-database --force
```

Ahora podemos ejecutar todas las migraciones para crear nuestro esquema desde cero:

```terminal
php bin/console doctrine:migrations:migrate
```

¡Bien!

## Exponer la propiedad de la relación

¡De vuelta al trabajo! En `CheeseListing`, tenemos una nueva propiedad y un nuevo getter y setter. Pero como estamos utilizando grupos de normalización y desnormalización, esta novedad no está expuesta en nuestra API.

Para empezar, éste es el objetivo: cuando creamos un `CheeseListing`, un cliente de la API debe poder especificar quién es el propietario. Y cuando leamos un `CheeseListing`, deberíamos poder ver quién es su propietario. Esto puede parecer un poco extraño al principio: ¿realmente vamos a permitir que un cliente de la API cree un `CheeseListing` y elija libremente quién es su propietario? Por ahora, sí: establecer el propietario de un listado de queso no es diferente de establecer cualquier otro campo. Más adelante, cuando tengamos un verdadero sistema de seguridad, empezaremos a bloquear las cosas para que no pueda crear un`CheeseListing` y decir que otro es su propietario.

De todos modos, para que `owner` forme parte de nuestra API, copia los `@Groups()` de `$price`... y añádelos encima de `$owner`.

[[[ code('5cb6690fdc') ]]]

¡Vamos a probarlo! Muévete y refresca los documentos. Pero antes de ver `CheeseListing`, vamos a crear un `User` para tener algunos datos con los que jugar. Le daré un correo electrónico, una contraseña cualquiera, un nombre de usuario y... Ejecuta. Genial - 201 éxito. Ajusta los datos y crea un usuario más.

Ahora, el momento de la verdad: haz clic para crear un nuevo `CheeseListing`. Interesante... dice que `owner` es una "cadena"... lo que puede ser sorprendente... ¿no vamos a establecerlo como un id entero? Vamos a averiguarlo. Intenta vender un bloque de queso desconocido por 20$, y añade una descripción.

Para el propietario, ¿qué ponemos aquí? Veamos... los dos usuarios que acabamos de crear tenían los ids 2 y 1. ¡Bien! Establece el propietario en `1` y ¡ejecuta!

¡Woh! ¡Falla con un código de estado 400!

> Se esperaba un IRI o un documento anidado para el atributo owner, se ha dado un entero.

¡Resulta que establecer owner al id no es correcto! A continuación, vamos a arreglar esto, a hablar más de los IRI y a añadir una nueva propiedad `cheeseListings` a nuestro recurso de la API `User`.
