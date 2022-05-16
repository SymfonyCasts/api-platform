# Depuración de la API con el Perfilador

Depurar una API... puede ser difícil... porque no ves los resultados -o los errores- como grandes páginas HTML. Así que, para ayudarnos en el camino, ¡vamos a nivelar nuestra capacidad de depuración! En una aplicación tradicional de Symfony, una de las mejores características es la barra de herramientas de depuración web... que ahora mismo no vemos aquí abajo porque aún no está instalada.

## Utilizar el perfilador en una API

Pero... ¿deberíamos siquiera molestarnos? Quiero decir, no es que podamos ver la barra de herramientas de depuración web en una respuesta de la API JSON, ¿verdad? ¡Por supuesto que podemos! Bueno, más o menos.

Busca tu terminal y consigue instalar el perfilador con:

```terminal
composer require profiler --dev
```

También puedes ejecutar `composer require debug --dev` para instalar algunas herramientas adicionales. Esto instala el `WebProfilerBundle`, que añade un par de archivos de configuración para ayudarle a hacer su magia.

Gracias a ellos, cuando actualizamos... ¡ahí está! La barra de herramientas de depuración web flotando en la parte inferior. Esto es literalmente la barra de herramientas de depuración web de esta página de documentación... que probablemente no sea tan interesante.

Pero si empezamos a hacer peticiones... compruébalo. Cuando ejecutamos una operación a través de Swagger, hace una petición AJAX para completar la operación. Y la barra de herramientas de depuración web de Symfony tiene una pequeña y genial función en la que rastrea esas peticiones AJAX y las añade a una lista Cada vez que pulso ejecutar, ¡obtengo una nueva!

¡La verdadera magia es que puedes hacer clic en el pequeño enlace "sha" para ver el perfil de esa petición de la API! Así que... ¡sí! No puedes ver la barra de herramientas de depuración de la web para una respuesta que devuelve JSON, pero sí puedes ver el perfil, que contiene muchos más datos de todos modos, como los parámetros POST, las cabeceras de la petición, el contenido de la petición -que es realmente importante cuando envías JSON- y todas las cosas buenas que esperas: caché, rendimiento, seguridad, Doctrine, etc.

## Encontrar el perfil de una petición de API

Además del pequeño rastreador AJAX de la barra de herramientas de depuración web que acabamos de ver, hay algunas otras formas de encontrar el perfil para una petición específica de la API. En primer lugar, cada respuesta tiene una cabecera `x-debug-token-link` con una URL a su página de perfil, que puedes leer para saber a dónde ir. O bien, puedes ir a `/_profiler`para ver una lista de las peticiones más recientes. Aquí está la de `/api/cheese_listings`. Haz clic en el token para saltar a su perfilador.

## El panel de la Plataforma API

Ah, y la Plataforma API añade su propio panel del perfilador, que es una buena manera de ver en qué recurso operaba esta petición y los metadatos de la misma, incluyendo esta operación del elemento y la operación de recogida -hablaremos de ellas muy pronto-. También muestra información sobre los "proveedores de datos" y los "perseguidores de datos", dos conceptos importantes de los que hablaremos más adelante.

Pero antes de llegar ahí, de vuelta a la página de documentación, tenemos que hablar de estas cinco rutas -llamadas operaciones- y de cómo podemos personalizarlas.
