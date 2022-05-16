# Hydra: Describiendo las clases de la API, las operaciones y más

Así que, al menos a alto nivel, entendemos que cada recurso tendrá una clave `@type`y que esta página -a través de las claves `supportedClass` y `supportedProperty` - define qué significa ese tipo, qué propiedades tiene y mucha información sobre cada propiedad.

Ahora mismo, sólo tenemos un recurso API, por lo que sólo tenemos una entrada en`supportedClass`, ¿verdad? ¡Pues sorpresa! Hay otra llamada `Entrypoint`! Y otra llamada `ConstraintViolation`, que define el recurso que se devuelve cuando nuestra API tiene un error de validación.

## El recurso del punto de entrada

Hablemos de esta clase `Entrypoint`: es una idea bastante bonita. Ya sabemos que cuando vamos a `/api`, obtenemos, más o menos, la versión HTML de una "página de inicio" de la API. Pues bien, ¡también hay una versión JSON-LD de esta página! Hay un enlace para verla al final de esta página, pero vamos a llegar a ella de otra manera.

Busca tu terminal: podemos utilizar curl para ver el aspecto de la "página principal" para el formato JSON-LD:

```terminal
curl -X GET 'https://localhost:8000/api' -H "accept: application/ld+json"
```

En otras palabras: haz una petición GET a `/api`, pero anuncia que quieres que te devuelvan el formato JSON-LD. También enviaré eso a `jq` - una utilidad que hace que JSON tenga un aspecto bonito - simplemente sáltate eso si no lo tienes instalado. Y... ¡boom!

¡Saluda a la página de inicio de tu API! Como cada URL representa un recurso único, incluso esta página es un recurso: es... un recurso "Punto de entrada". Tiene los mismos`@context`, `@id` y `@type`, con una "propiedad" real llamada `cheeseListing`. Esa propiedad es el IRI del recurso de colección del listado de quesos.

Por cierto, ¡esto se describe en nuestro documento JSON-LD! La clase `Entrypoint` tiene una propiedad: `cheeseListing` con el tipo `hydra:Link` - eso es interesante. Y, es bastante feo, pero la parte `rdfs:range` es aparentemente una forma de describir que el recurso al que se refiere esta propiedad es una "colección" que tendrá una propiedad`hydra:member`, que será un array donde cada elemento es de tipo`CheeseListing`. ¡Woh!

## Hola Hydra

Así que JSON-LD consiste en añadir más contexto a tus datos especificando que nuestros recursos contendrán claves especiales como `@context`, `@id` y `@type`. Sigue siendo JSON normal, pero si un cliente entiende JSON-LD, va a poder obtener mucha más información sobre tu API, de forma automática.

Pero en la Plataforma API, hay otra cosa que vas a ver todo el tiempo, ¡y ya la estamos viendo! La Hidra, que es algo más que un monstruo acuático de muchas cabezas de la mitología griega.

Vuelve a `/api/docs.jsonld`. De la misma manera que esto apunta al documento externo`xmls` para que podamos referenciar cosas como `xmls:integer`, también estamos apuntando a un documento externo llamado `hydra` que define más "tipos" o "vocabulario" que podemos utilizar.

Esta es la idea: JSON-LD nos proporcionó el sistema para decir que este dato es de este tipo y este otro tipo. Hydra es una extensión de JSON-LD que añade nuevos vocablos. Dice:

> Espera un segundo. JSON-LD es genial y divertido y un excelente invitado a la cena.
> Pero para que un cliente y un servidor puedan realmente comunicarse, necesitamos más
¡> lenguaje compartido! Necesitamos una forma de definir "clases" dentro de mi API, las propiedades
> de esas clases y si cada una es legible y escribible. Ah, y también necesitamos
> también necesitamos poder comunicar las operaciones que admite un recurso:
> ¿puedo hacer una petición DELETE a este recurso para eliminarlo? ¿Puedo hacer una solicitud PUT
> para actualizarlo? ¿Qué formato de datos debo esperar de cada operación?
> ¿Y cuál es la verdadera identidad de Batman?

Hydra tomó el sistema JSON-LD y le añadió una nueva "terminología" -llamada "vocabulario"- que permite definir completamente cada aspecto de tu API.

## Hydra frente a OpenAPI

Llegados a este punto, casi seguro que estás pensando:

> Pero espera, esto suena en serio, es exactamente lo mismo que obtuvimos
> de nuestro documento JSON de OpenAPI.

Y... ¡sí! Si cambiamos la URL a `/api/docs.json`, se trata de la especificación OpenAPI. Y si la cambiamos a `.jsonld`, de repente tenemos la especificación JSON-LD con Hydra.

Entonces, ¿por qué tenemos ambos? En primer lugar, sí, estos dos documentos hacen básicamente lo mismo: describen tu API en un formato legible por la máquina. El formato JSON-LD e Hydra es un poco más potente que OpenAPI: es capaz de describir algunas cosas que OpenAPI no puede. Pero OpenAPI es más común y tiene más herramientas construidas a su alrededor. Así que, en algunos casos, tener una especificación OpenAPI será útil -como usar Swagger- y otras veces, el documento JSON-LD Hydra será útil. Con la Plataforma API, tienes ambas cosas.

¡Ufff! Bien, ¡basta de teoría! Volvamos a construir y personalizar nuestra API.
