# Filtrar y buscar

¡Lo estamos haciendo muy bien! Entendemos cómo exponer una clase como recurso de la API, podemos elegir qué operaciones queremos, y tenemos un control total sobre los campos de entrada y salida, incluyendo algunos campos "falsos" como `textDescription`. Hay mucho más que saber, ¡pero lo estamos haciendo bien!

Entonces, ¿qué más necesita toda API? Se me ocurren algunas cosas, como la paginación y la validación. Pronto hablaremos de ambas cosas. Pero, ¿qué pasa con el filtrado? Tu cliente de la API -que podría ser simplemente tu código JavaScript- no va a querer siempre obtener cada uno de los `CheeseListing` del sistema. ¿Y si necesitas la posibilidad de ver sólo los listados publicados? ¿O qué pasa si tienes una búsqueda en el front-end y necesitas encontrar por título? Esto se llama "filtros": formas de ver un "subconjunto" de una colección en función de algún criterio. ¡Y la Plataforma API viene con un montón de ellos incorporados!

## Filtrar por publicado/no publicado

Empecemos por hacer posible que sólo se devuelvan los listados de quesos publicados. Bueno, en un [futuro tutorial](https://symfonycasts.com/screencast/api-platform-security/query-extension), vamos a hacer posible ocultar automáticamente los listados no publicados de la colección. Pero, por ahora, nuestra colección de listados de queso lo devuelve todo. Así que, al menos, hagamos posible que un cliente de la API pida sólo los publicados.

En la parte superior de `CheeseListing`, activa nuestro primer filtro con `@ApiFilter()`. Luego, elige el filtro específico por su nombre de clase: `BooleanFilter::class`... porque estamos filtrando sobre una propiedad booleana. Termina pasando la opción`properties={}` a `"isPublished"`.

[[[ code('92b37a881a') ]]]

¡Genial! ¡Vamos a ver qué ha hecho esto! ¡Refrescar! Oh... ¡lo que hizo fue romper nuestra aplicación!

> La clase de filtro `BooleanFilter` no implementa `FilterInterface`.

No está súper claro, pero ese error significa que hemos olvidado una sentencia `use`. Este `BooleanFilter::class` está haciendo referencia a una clase específica y necesitamos una sentencia `use`para ella. Es... una forma un poco extraña de utilizar los nombres de las clases, por eso PhpStorm no lo autocompletó para nosotros.

No hay problema, al principio de tu clase, añade `use BooleanFilter`. Pero... cuidado... la mayoría de los filtros soportan Doctrine ORM y Doctrine con MongoDB. Asegúrate de elegir la clase para el ORM.

[[[ code('e5579433be') ]]]

Bien, ahora muévete y actualiza de nuevo.

¡Volvemos a la vida! Haz clic en "Probar". ¡Tenemos un pequeño cuadro de entrada del filtro `isPublished`! Si lo dejamos en blanco y lo ejecutamos... parece que hay 4 resultados.

Elige `true` en lugar de `isPublished` e inténtalo de nuevo. ¡Nos quedamos con dos resultados! Y comprueba cómo funciona con la URL: sigue siendo `/api/cheeses`, pero con un precioso `?isPublished=true` o `?isPublished=false`. Así que, sin más, nuestros usuarios de la API pueden filtrar una colección en un campo booleano.

Además, en la respuesta hay una nueva propiedad `hydra:search`. OoooOOO. Es un poco técnico, pero esto explica que ahora puedes buscar utilizando un parámetro de consulta `isPublished`. También da información sobre a qué propiedad se refiere en el recurso.

## Búsqueda de texto: SearchFilter

¿De qué otra forma podemos filtrar? ¿Qué tal si buscamos por texto? Sobre la clase, añade otro filtro: `@ApiFilter()`. Éste se llama `SearchFilter::class` y tiene la misma opción `properties`... pero con un poco más de configuración. Digamos que `title` está configurado como `partial`. También hay configuraciones para que coincida con una cadena `exact`, con la `start`de una cadena, con la `end` de una cadena o con la `word_start`.

De todos modos, esta vez, recuerdo que tenemos que añadir la declaración `use` manualmente. Digamos `use SearchFilter` y autocompletar la del ORM.

[[[ code('1986d6da79') ]]]

Ah, y antes de comprobarlo, haré clic para abrir `SearchFilter`. Éste vive en un directorio llamado `Filter` y... si hago doble clic en él... ¡eh! Podemos ver un montón de otros: `ExistsFilter`, `DateFilter`, `RangeFilter`,`OrderFilter` y más. Todos ellos están documentados, pero también puedes entrar directamente y ver cómo funcionan.

En cualquier caso, ve a refrescar los documentos, abre la operación de la colección `GET` y haz clic para probarla. Ahora tenemos un cuadro de filtro `title`. Prueba... um... `cheese` y... Ejecuta.

Oh, ¡magnífico! Añade `?title=cheese` a la URL... y coincide con tres de nuestros cuatro listados. La propiedad `hydra:search` contiene ahora una segunda entrada que anuncia esta nueva forma de filtrar.

Si queremos poder buscar por otra propiedad, podemos añadirla también:`description` ajustada a `partial`.

Esto es fácil de configurar, pero este tipo de búsqueda en la base de datos sigue siendo bastante básico. Afortunadamente, aunque no lo cubriremos en este tutorial, si necesitas una búsqueda realmente robusta, la Plataforma API puede integrarse con Elasticsearch: exponiendo tus datos de Elasticsearch como recursos legibles de la API. ¡Es una maravilla!

Vamos a ver dos filtros más: un filtro de "rango", que será súper útil para nuestra propiedad de precio y otro que es... un poco especial. En lugar de filtrar el número de resultados, permite que el cliente de la API elija un subconjunto de propiedades para devolverlas en el resultado. Eso a continuación.
