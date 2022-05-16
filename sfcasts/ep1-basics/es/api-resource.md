# Nuestro primer ApiRecurso

Pregunta: ¿has ido alguna vez a la tienda y has comprado accidentalmente demasiado queso? Es la historia de mi vida. O tal vez tengas el problema contrario: ¡estás organizando una gran fiesta y no tienes suficiente queso! Esta es nuestra nueva idea del billón de dólares: una plataforma en la que puedes vender ese trozo extra de Brie que nunca te acabaste o comprar un camión lleno de camembert a alguien que va demasiado excitado al mercado del queso. Sí, es lo que el mundo está pidiendo: un mercado de queso entre iguales. Lo llamamos: Cheese Whiz.

Para el sitio, quizá lo convirtamos en una aplicación de una sola página construida en React o Vue... o quizá sea un poco más tradicional: una mezcla de páginas HTML y JavaScript que haga peticiones AJAX. Y tal vez incluso tengamos una aplicación móvil. En realidad no importa, porque todas estas opciones significan que tenemos que ser capaces de exponer nuestra funcionalidad principal a través de una API.

## Generar la entidad

Pero para empezar: olvídate de la API y haz como si este fuera un proyecto Symfony normal y aburrido. El paso 1 es... hmm, probablemente crear algunas entidades de la base de datos.

Abramos nuestro archivo `.env` y modifiquemos el `DATABASE_URL`. Mi equipo utiliza`root` sin contraseña... y qué tal `cheese_whiz` para el nombre de la base de datos.

[[[ code('b92d6c0ac7') ]]]

También puedes crear un archivo `.env.local` y anular allí `DATABASE_URL`. Usar`root` y sin contraseña es bastante estándar, así que me gusta añadirlo a `.env` y confirmarlo como predeterminado.

¡Genial! A continuación, en tu terminal, ejecuta

```terminal
composer require maker:1.11 --dev
```

para obtener el MakerBundle de Symfony... para que podamos ser perezosos y generar nuestra entidad. Cuando termine, ejecuta

```terminal
php bin/console make:entity
```

Llama a la primera entidad: `CheeseListing`, que representará cada "queso" que esté a la venta en el sitio. Pulsa enter y... ¡oh! te pide:

> ¿Marcar esta clase como un recurso de la Plataforma API?

MakerBundle pregunta esto porque se ha dado cuenta de que la Plataforma API está instalada. Di "sí". Y antes de añadir ningún campo, ¡vamos a ver qué ha hecho! En mi editor, ¡sí! Esto creó los habituales `CheeseListing` y `CheeseListingRepository`. No hay nada especial. Ahora mismo, la única propiedad que tiene la entidad es `id`. Entonces, ¿qué nos dio la respuesta afirmativa a la pregunta sobre el recurso de la Plataforma API? Esta pequeña anotación de aquí: `@ApiResource`

[[[ code('beab5706e8') ]]]

La verdadera pregunta es: ¿qué activa eso? Lo veremos pronto.

Pero primero, vamos a añadir algunos campos. Veamos, cada listado de quesos probablemente necesite un `title`, `string`, `255`, no anulable, un `description`, que será un gran campo de texto, `price`, que haré un `integer` -este será el precio en céntimos- por lo que 10 dólares serían 1000, `createdAt` como `datetime` y un `isPublished`booleano. Bien: ¡buen comienzo! Pulsa enter para terminar.

¡Enhorabuena! Tenemos una clase `CheeseEntity` perfectamente aburrida: 7 propiedades con getters y setters 

[[[ code('990e4e48f5') ]]]

A continuación, genera la migración con:

```terminal
php bin/console make:migration
```

¡Oh! ¡Migraciones no está instalado todavía! No hay problema, sigue la recomendación:

```terminal
composer require migrations:2.0.0
```

Pero antes de intentar generarla de nuevo, tengo que asegurarme de que mi base de datos existe:

```terminal
php bin/console doctrine:database:create
```

Y ahora ejecuta `make:migration`:

```terminal-silent
php bin/console make:migration
```

Vamos a comprobarlo para asegurarnos de que no hay ninguna sorpresa:

> CREATE TABLE cheese_listing...

[[[ code('aa7c60ad0f') ]]]

¡Sí! ¡Tiene buena pinta! Cierra eso y ejecuta:

```terminal
php bin/console doctrine:migrations:migrate
```

## ¡Saluda a tu API!

¡Genial! Llegados a este punto, tenemos una entidad Doctrine completamente tradicional, excepto por ésta, la anotación `@ApiResource()`. Pero esto lo cambia todo. Esto le dice a la Plataforma API que quieres exponer esta clase como una API.

Compruébalo: actualiza la página `/api`. ¡Vaya! ¡De repente esto dice que tenemos cinco nuevas rutas, u "operaciones"! Una operación `GET` para recuperar una colección de CheeseListings, una operación `POST` para crear una nueva, `GET` para recuperar una sola`CheeseListing`, `DELETE` para... ya sabes... borrar y `PUT` para actualizar una `CheeseListing` existente. ¡Eso es un CRUD completo, basado en la API!

Y esto no es sólo documentación: estas nuevas rutas ya funcionan. Vamos a comprobarlos a continuación, a saludar a algo llamado JSON-LD y a aprender un poco sobre cómo funciona esta magia entre bastidores.
