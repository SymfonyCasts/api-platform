# ¡Instalación de la plataforma API!

¡Hola amigos! Es hora de hablar de... redoble de tambores... cómo hacer un delicioso pastel que parece una Oreo. Espera... ¡ah! Tutorial equivocado. Es hora de hablar de la Plataforma API... tan divertida que es casi tan deliciosa como un pastel con forma de Oreo.

La Plataforma API está arrasando estos días. Tengo la impresión de que en todas partes alguien habla maravillas de ella Su desarrollador principal, Kévin Dunglas, es un colaborador principal de Symfony, un tipo muy agradable y está ampliando absolutamente los límites de lo que pueden hacer las API. Vamos a verlo de primera mano. Además, ¡ha tenido la amabilidad de guiarnos en este tutorial!

## Las API modernas son difíciles. La Plataforma API no lo es

Si sólo necesitas construir unas pocas rutas de API para soportar algo de JavaScript, puede que pienses:

> ¿Cuál es el problema? ¡Devolver algo de JSON aquí y allá ya es bastante fácil!

Yo he tenido esta misma opinión durante un tiempo. Pero poco a poco, creo que esto es cada vez menos cierto. Al igual que nacieron los frameworks cuando las aplicaciones web se volvieron más y más complejas, se han creado herramientas como API Platform porque lo mismo está ocurriendo actualmente con las API.

Hoy en día, las API son algo más que devolver JSON: se trata de ser capaz de serializar y deserializar tus modelos de forma coherente, quizá en múltiples formatos, como JSON o XML, pero también JSON-LD o HAL JSON. Luego están los hipermedios, los datos enlazados, los códigos de estado, los formatos de error, la documentación -incluida la documentación de las especificaciones de la API que puede alimentar a Swagger-. Luego está la seguridad, el CORS, el control de acceso y otras características importantes como la paginación, el filtrado, la validación, la negociación del tipo de contenido, el GraphQL... y... sinceramente, podría seguir.

Por eso existe la Plataforma API: para permitirnos construir APIs increíbles y amar el proceso ¿Y esa gran lista de cosas que acabo de mencionar que necesita una API? La Plataforma API viene con todo ello. Y no es sólo para construir una enorme API, sino que es la herramienta perfecta, incluso si sólo necesitas unas pocas rutas para potenciar tu propio JavaScript.

## Distribución de la Plataforma API

¡Así que vamos a hacerlo! La Plataforma API es una biblioteca PHP independiente que está construida sobre los componentes de Symfony. No es necesario que la utilices desde dentro de una aplicación Symfony, pero, como puedes ver aquí, así es como recomiendan utilizarla, lo cual es genial para nosotros.

Si sigues su documentación, tienen su propia distribución de la Plataforma API: una estructura de directorios personalizada con un montón de cosas: un directorio para tu API impulsada por Symfony, otro para tu frontend de JavaScript, otro para un frontend de administración, ¡todo conectado con Docker! ¡Vaya! Puede parecer un poco "grande" para empezar, pero obtienes todas las características fuera de la caja... incluso más de lo que acabo de describir. Si eso suena increíble, puedes utilizarlo totalmente.

Pero vamos a hacer algo diferente: vamos a instalar la Plataforma API como un paquete en una aplicación Symfony normal y tradicional. Esto hace que el aprendizaje de la Plataforma API sea un poco más fácil. Una vez que te sientas seguro, para tu proyecto, puedes hacerlo de esta misma manera o lanzarte a utilizar la distribución oficial. Como he dicho, es súper potente.

## Configuración del proyecto

De todos modos, para convertirte en el héroe de la API que todos necesitamos, deberías codificar conmigo descargando el código del curso desde esta página. Después de descomprimirlo, encontrarás un directorio `start/` dentro con el mismo código que ves aquí... que en realidad es sólo un nuevo proyecto esqueleto de Symfony 4.2: no hay nada especial instalado ni configurado todavía. Sigue el archivo `README.md` para las instrucciones de configuración.

El último paso será abrir un terminal, entrar en el proyecto e iniciar el servidor Symfony con:

```terminal
symfony serve -d
```

Esto utiliza el ejecutable `symfony` - una pequeña e impresionante herramienta de desarrollo que puedes obtener en https://symfony.com/download. Esto inicia un servidor web en el puerto 8000 que se ejecuta en segundo plano. Lo que significa que podemos encontrar nuestro navegador, dirigirnos a`localhost:8000` y ver... bueno, ¡básicamente nada! Sólo la bonita página de bienvenida que se ve en una aplicación Symfony vacía.

## Instalar la plataforma API

Ahora que tenemos nuestra aplicación Symfony vacía, ¿cómo podemos instalar la Plataforma API? Es increíble. Busca tu terminal y ejecuta:

```terminal
composer require api:1.2.0
```

Eso es. Te darás cuenta de que esto está instalando algo llamado`api-platform/api-pack`. Si recuerdas nuestra serie sobre Symfony, un "paquete" es una especie de biblioteca "falsa" que ayuda a instalar varias cosas a la vez.

Por ejemplo, puedes ver esto en `https://github.com/api-platform/api-pack`: es un único archivo`composer.json` que requiere varias bibliotecas, como Doctrine, un paquete CORS del que hablaremos más adelante, anotaciones, la propia Plataforma API y algunas partes de Symfony, como el sistema de validación, el componente de seguridad e incluso Twig, que se utiliza para generar una documentación realmente interesante que veremos en un minuto.

Pero aún no hay nada tan interesante: sólo la Plataforma API y algunos paquetes estándar de Symfony.

De vuelta al terminal, ¡ya está hecho! Y tiene algunos detalles sobre cómo empezar. También se han ejecutado algunas recetas que nos han proporcionado algunos archivos de configuración. Antes de hacer nada más, vuelve al navegador y dirígete a`https://localhost:8000/api` para ver... ¡woh! ¡Tenemos la documentación de la API! Bueno, todavía no tenemos ninguna API... así que aquí no hay nada. Pero ésta va a ser una característica enorme y gratuita que obtendrás con la Plataforma API: a medida que construyamos nuestra API, esta página se actualizará automáticamente.

Veamos eso a continuación creando y exponiendo nuestro primer Recurso API.
