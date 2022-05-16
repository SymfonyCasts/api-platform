# Paginación

Si tenemos un millón, o mil... o incluso cien listados de quesos, no podemos devolverlos todos cuando alguien hace una petición GET a `/api/cheeses`! La forma de resolverlo en una API es realmente la misma que en la web: ¡la paginación! Y en la Plataforma API... ah, es tan aburrido... obtienes un sistema de paginación potente y flexible... sin hacer... nada.

Vayamos a la operación POST y creemos unos cuantos listados más de queso. Pondré algunos datos sencillos... y ejecutaré un montón de veces... en avance rápido. En un proyecto real, utilizaría accesorios de datos para ayudarme a obtener datos ficticios útiles.

Deberíamos tener unos 10 más o menos gracias a los 4 con los que empezamos. Ahora, dirígete a la operación de recogida GET... y pulsa Ejecutar. Seguimos viendo todos los resultados, porque la Plataforma API muestra 30 resultados por página, por defecto. Como no me apetece añadir 20 más manualmente, ¡este es un buen momento para aprender a cambiarlo!

## Controlar los elementos por página

En primer lugar, esto se puede cambiar globalmente en tu archivo `config/packages/api_platform.yaml`. No lo mostraré ahora, pero recuerda siempre que puedes ejecutar

```terminal
php bin/console debug:config api_platform
```

para ver una lista de toda la configuración válida para ese archivo y sus valores actuales. Eso revelaría una sección de `collection.pagination` que está llena de configuraciones.

Pero también podemos controlar el número de elementos por página en función de cada recurso. Dentro de la anotación `@ApiResource`, añade `attributes={}`... que es una clave que contiene una variedad de configuración aleatoria para la Plataforma API. Y luego,`"pagination_items_per_page": 10`.

[[[ code('62704bc366') ]]]

He mencionado antes que gran parte de la Plataforma API consiste en aprender exactamente qué puedes configurar dentro de esta anotación y cómo. Este es un ejemplo perfecto.

Vuelve a la documentación, no hace falta que la actualices. Simplemente pulsa Ejecutar. Veamos... el total de ítems es de 11... ¡pero si lo cuentas, sólo muestra 10 resultados! Hola paginación! También tenemos una nueva propiedad `hydra:view`. Ésta anuncia que se está produciendo la paginación y que podemos "navegar" a través de las otras páginas: podemos seguir`hydra:first`, `hydra:last` y `hydra:next` para ir a la primera, a la última o a la siguiente página. Las URLs son exactamente como yo quiero: `?page=1`, `?page=2` y así sucesivamente.

Abre una nueva pestaña y vuelve a `/api/cheeses.jsonld`. Sí, los 10 primeros resultados. Ahora añade `?page=2`... para ver el único y último resultado.

El filtrado también sigue funcionando. Prueba con `.jsonld?title=cheese`. Eso devuelve... sólo 10 resultados... ¡así que no hay paginación! Eso no es divertido. Volvamos a los documentos, abramos la ruta POST y añadamos algunas más. Pero asegurémonos de añadir uno con "queso" en el título. Pulsa Ejecutar unas cuantas veces.

Ahora ve a refrescar la operación de recogida GET con `?title=cheese`. ¡Qué bien! Tenemos 13 resultados en total y esto muestra los 10 primeros. ¡Lo que está muy bien es que los enlaces de paginación incluyen el filtro! Esto es súper útil en JavaScript: no tienes que intentar piratear la URL manualmente combinando la información de `page`y del filtro: basta con leer los enlaces de hydra y utilizarlos.

A continuación, sabemos que nuestra API puede devolver JSON-LD, JSON y HTML. Y... eso es probablemente todo lo que necesitamos, ¿verdad? Veamos lo fácil que es añadir más formatos... incluso hacer que nuestros listados de quesos se puedan descargar como CSV.
