# Swagger: Documentación de API instantánea e interactiva

Actualmente estamos estudiando algo llamado Swagger: una interfaz de documentación de API de código abierto. Pronto hablaremos más sobre ella, pero la idea es básicamente ésta: si tienes una API -construida en cualquier lenguaje- y creas alguna configuración que describa esa API en un formato que Swagger entienda, ¡boom! Swagger puede mostrarte esta hermosa documentación interactiva. Entre bastidores, la Plataforma API ya está preparando esa configuración para Swagger.

¡Vamos a jugar con ella! Abre la ruta POST. Dice lo que hace y muestra cómo debe ser el JSON para utilizarlo. ¡Muy bien! ¡Haz clic en "Probar"! Veamos lo que hay en mi cocina: un poco de "queso azul a medio comer", que todavía está... probablemente bien para comer. Lo venderemos por 1$. ¡Qué ganga! Y... ¡Ejecutar!

¿Qué ha pasado? Desplázate hacia abajo. ¡Woh! `POST` ¡Acaba de hacer una petición a`/api/cheese_listings` y ha enviado nuestro JSON! Nuestra aplicación ha respondido con un código de estado 201 y... unas claves JSON de aspecto extraño: `@context` `@id` y `@type`. Luego tiene los datos normales del nuevo listado de quesos: el autoincremento `id`, `title`, etc. ¡Eh! Ya tenemos una API que funciona... ¡y esto acaba de demostrarlo!

Cierra el POST y abre el `GET` que devuelve una colección de listados de quesos. Prueba éste también: ¡Ejecuta! Sí... ahí está nuestro listado... pero no es JSON crudo. Este material extra se llama JSON-LD. Es simplemente JSON normal, pero con claves especiales -como `@context` - que tienen un significado específico. Entender JSON-LD es una parte importante para aprovechar la Plataforma API, y pronto hablaremos más de ello.

De todos modos, para hacer las cosas más interesantes, vuelve a la ruta POST y crea una segunda lista de quesos: un bloque gigante de queso cheddar... por 10 $. ¡Ejecuta! Mismo resultado: código de estado 201 e id 2.

Vuelve a probar la ruta de recogida `GET`. Y... ¡bien! Dos resultados, con los identificadores 1 y 2. Y si queremos obtener sólo un listado de quesos, podemos hacerlo con la otra ruta `GET`. Como puedes ver, el `id` del listado de quesos que queremos obtener forma parte de la URL. Esta vez, cuando hacemos clic para probarlo, ¡genial! Nos da una casilla para el identificador. Usa "2" y... ¡Ejecuta!

Esto hace una petición GET muy sencilla a `/api/cheese_listings/2`, que devuelve un código de estado`200` y el conocido formato JSON.

## Negociación del tipo de contenido

¡Qué guay es esto! Un "CRUD" completo de la API sin ningún trabajo. Por supuesto, el truco será personalizar esto a nuestras necesidades exactas. Pero, ¡vaya! Es un comienzo increíble.

Intentemos acceder a nuestra API directamente -fuera de Swagger- sólo para asegurarnos de que todo esto no es un truco elaborado. Copia la URL, abre una nueva pestaña, pega y... ¡hola JSON! ¡Woh! Hola... ¿Página de documentación de la API de nuevo?

Nos ha desplazado hasta la documentación de esta ruta y la ha ejecutado con el id 2... lo cual está bien... pero ¿qué está pasando? ¿Tenemos realmente una API que funciona o no?

En la Plataforma API hay algo llamado negociación de tipo de contenido. Convenientemente, cuando ejecutas una operación, Swagger te muestra cómo podrías hacer esa misma petición utilizando curl en la línea de comandos. E incluye una pieza crítica:

> `-H "accept: application/ld+json"`

Que dice: haz una petición con una cabecera `Accept` establecida en `application/ld+json`. La petición está dando una pista a la Plataforma API de que debe devolver los datos en este formato JSON-LD. Te des cuenta o no, tu navegador también envía esta cabecera: como `text/html`... porque... es un navegador. Eso básicamente le dice a la Plataforma API:

> ¡Eh! Quiero el CheeseListing con id 2 en formato HTML.

La Plataforma API responde haciendo todo lo posible por hacer exactamente eso: devuelve la página HTML Swagger con el id 2 de CheeseListing ya mostrado.

## Fingir el Content-Type

Esto no es un problema para un cliente de la API porque establecer una cabecera `Accept` es fácil. Pero... ¿hay alguna forma de... "probar" la ruta en un navegador? Totalmente, puedes hacer trampa: añade `.jsonld` al final de la URL.

Y ¡boom! Esta es nuestra ruta de la API en formato JSON-LD. He llamado a esto "trampa" porque este pequeño "truco" de añadir la extensión sólo está pensado para el desarrollo. En el mundo real, deberías establecer la cabecera `Accept` en su lugar, como si estuvieras haciendo una petición AJAX desde JavaScript.

Y, mira esto: cambia la extensión a `.json`. ¡Eso parece un poco más familiar!

Este es un gran ejemplo de la filosofía de la Plataforma API: en lugar de pensar en rutas, controladores y respuestas, la Plataforma API quiere que pienses en crear recursos API -como `CheeseListing` - y luego exponer ese recurso en una variedad de formatos diferentes, como JSON-LD, JSON normal, XML o exponerlo a través de una interfaz GraphQL.

## ¿De dónde vienen estas rutas?

Por supuesto, por muy increíble que sea, si eres como yo, probablemente estés pensando

> Esto es genial... pero ¿cómo se han añadido mágicamente todas estas rutas a mi aplicación?

Después de todo, normalmente no añadimos una anotación a una entidad... ¡y de repente obtenemos un montón de páginas funcionales!

Busca tu terminal y ejecuta:

```terminal
php bin/console debug:router
```

¡Genial! La plataforma de la API está trayendo varias rutas nuevas: `api_entrypoint` es una especie de "página de inicio" de nuestra api, que, por cierto, puede devolverse como HTML -como hemos estado viendo- o como JSON-LD, para un "índice" legible por la máquina de lo que incluye nuestra API. Más adelante hablaremos de ello. También hay una URL `/api/docs` -que, para el HTML es lo mismo que ir a `/api`, otra llamada `/api/context` -más sobre esto en un minuto- y abajo, 5 rutas para los 5 nuevos puntos finales. Cuando añadamos más recursos más adelante, veremos más rutas.

Cuando instalamos el paquete de la Plataforma API, su receta añadió un archivo`config/routes/api_platform.yaml` 

[[[ code('ec8e8b39c5') ]]]

Así es como la Plataforma API añade mágicamente las rutas. 
No es muy interesante, pero ¿ves ese `type: api_platform`? Eso dice básicamente:

> ¡Oye! Quiero que la Plataforma API sea capaz de añadir dinámicamente las rutas que quiera.

Lo hace encontrando todas las clases marcadas con `@ApiResource` -sólo una en este momento-, creando 5 nuevas rutas para las 5 operaciones, y prefijando todas las URL con`/api`. Si quieres que tus URLs de la API vivan en la raíz del dominio, sólo tienes que cambiar esto por `prefix: /`.

Espero que ya estés emocionado, ¡pero hay mucho más de lo que parece! A continuación, vamos a hablar de la especificación OpenAPI: un formato de descripción de la API estándar en la industria que da a tu API Swagger superpoderes... gratis. Sí, tenemos que hablar un poco de teoría, pero no te arrepentirás.
