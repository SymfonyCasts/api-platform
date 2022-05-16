# JSON-LD: Contexto para tus datos

Una API típica devuelve JSON. Entra en `/api/cheese_listings/2.json`. Cuando pienso en una API, esto es lo que tradicionalmente imagino en mi cabeza.

## Tus datos carecen de significado

Pero, ¿qué significado tienen estos campos? ¿Qué significan exactamente los campos `title`o `description`? ¿Son texto plano? ¿Pueden contener HTML? ¿La descripción describe este tipo de queso en general, o es específica del estado del queso exacto que estoy vendiendo? ¿Y el precio? ¿Es una cadena, un flotador, un número entero? ¿Está en dólares estadounidenses? ¿En euros? ¿Se mide en céntimos?

Si eres un humano... eres un humano, ¿verdad? Un humano suele poder "inferir" algún significado a partir de los nombres de los campos o encontrar alguna documentación legible por humanos que le ayude a saber exactamente lo que representa cada campo. Pero no hay forma de que una máquina entienda nada de lo que significan estos campos o sus tipos. ¡Incluso un algoritmo inteligente podría confundirse! Un campo llamado `title` podría ser el "título" de algo -como el título de un libro- o podría ser el título de una persona -Sr., Sra., etc.

## RDF Y HTML

Esto es lo que pretende resolver JSON-LD. De acuerdo, honestamente, hay mucho que hacer en estos días con este problema de:

> ¿Cómo damos a los datos en la web un contexto o significado que los ordenadores puedan entender?

Así que vamos a tocar algunos puntos básicos. Existe una cosa llamada RDF (Resource Description Framework), que es una especie de conjunto de reglas sobre cómo podemos "describir" el significado de los datos. Es un poco abstracto, pero es una guía sobre cómo puedes decir que un dato tiene este "tipo" o que un recurso es una "subclase" de algún otro "tipo". En HTML, puedes añadir atributos a tus elementos para añadir metadatos RDF, diciendo que algún `div` describe a una Persona y que los `name` y `telephone` de esta Persona son estos otros datos:

```html
<p typeof="http://schema.org/Person">
   My name is
   <span property="http://schema.org/Person#name">Manu Sporny</span>
   and you can give me a ring via
   <span property="http://schema.org/Person#telephone">1-800-555-0199</span>.
</p>

<!-- or equivalent using vocab -->
<p vocab="http://schema.org/" typeof="Person">
   My name is
   <span property="name">Manu Sporny</span>
   and you can give me a ring via
   <span property="telephone">1-800-555-0199</span>.
</p>
```

Esto hace que tu HTML no estructurado sea comprensible para las máquinas. Es aún más comprensible si 2 sitios diferentes utilizan exactamente la misma definición de "Persona", por lo que los "tipos" son URLs y los sitios intentan reutilizar los tipos existentes en lugar de inventar otros nuevos.

¡Es genial!

## Hola JSON-LD

JSON-LD nos permite hacer esto mismo para JSON. Cambia la URL de `.json`a `.jsonld`. Esto tiene los mismos datos, pero con unos cuantos campos extra:`@context`, `@id` y `@type`. JSON-LD no es más que un "estándar" que describe unos cuantos campos extra que puede tener tu JSON -todos ellos empiezan por `@` - que ayudan a las máquinas a saber más sobre tu API.

## JSON-LD: @id

Así que, primero: `@id`. En una API RESTful, cada URL representa un recurso y debe tener su propio identificador único. JSON-LD hace esto oficial diciendo que cada recurso debe tener un campo `@id`... lo que puede parecer redundante ahora mismo... porque... también estamos emitiendo nuestro propio campo `id`. Pero hay dos cosas especiales sobre `@id`. En primer lugar, cualquier persona, o cualquier cliente HTTP, que entienda JSON-LD sabrá buscar `@id`. Es la "clave" oficial del identificador único. Nuestra columna `id` es algo específico de nuestra API. En segundo lugar, en JSON-LD, todo se hace con URLs. Decir que el `id` es 2 está bien... ¡pero decir que el `id` es`/api/cheese_listing/2` es infinitamente más útil! ¡Es una URL que alguien podría utilizar para obtener detalles sobre este recurso! También es única dentro de toda nuestra API... o realmente... si incluyes nuestro nombre de dominio, ¡es un identificador único para ese recurso en toda la web!

Esta URL se llama en realidad un IRI: Identificador de Recursos Internacionalizado. Vamos a utilizar los IRI en todas partes en lugar de los ids enteros.

## JSON-LD @contexto y @tipo

Las otras dos claves JSON-LD - `@context` y `@type` - funcionan juntas. La idea es realmente genial: si añadimos una clave `@type` a cada recurso y luego definimos los campos exactos de ese tipo en alguna parte, eso nos da dos superpoderes. En primer lugar, sabemos al instante si dos estructuras JSON diferentes describen en realidad un listado de quesos... o si sólo se parecen y en realidad describen cosas diferentes. Y en segundo lugar, podemos mirar la definición de este tipo para saber más sobre él: qué propiedades tiene e incluso el tipo de cada propiedad.

Caramba, ¡esto no es nada nuevo! ¡Lo hacemos todo el tiempo en PHP! Cuando creamos una clase en lugar de una simple matriz, estamos dando a nuestros datos un "tipo". Esto nos permite saber exactamente con qué tipo de datos estamos tratando y podemos mirar la clase para saber más sobre sus propiedades. Así que... sí, el campo `@type` transforma los datos de una matriz sin estructura en una clase concreta que podemos entender

Pero... ¿dónde se define este tipo `CheeseListing`? Ahí es donde entra `@context`: básicamente dice:

> Para obtener más detalles, o "contexto" sobre los campos utilizados en estos datos, ve
> a esta otra URL.

Para que esto tenga sentido, tenemos que pensar como una máquina: una máquina que quiere desesperadamente aprender todo lo posible sobre nuestra API, sus campos y lo que significan. Cuando una máquina ve ese `@context`, lo sigue. Sí, pongamos literalmente esa URL en el navegador: `/api/contexts/CheeseListing`. Y... interesante. Es otro`@context`. Sin entrar en demasiados detalles locos, `@context` nos permite utilizar nombres de propiedades "abreviados", llamados "términos". Nuestra respuesta JSON real incluye campos como `title` y `description`. Pero en lo que respecta a JSON-LD, cuando se tiene en cuenta el `@context`, es como si la respuesta tuviera un aspecto similar al siguiente:

```json
{
    "@context": {
        "@vocab": "https://localhost:8000/api/docs.jsonld#"
    },
    "@id": "/api/cheese_listing/2",
    "@type": "CheeseListing",
    "CheeseListing/title": "Giant block of cheddar cheese",
    "CheeseListing/description": "mmmmmm",
    "CheeseListing/price": 1000,
}
```

La idea es que sabemos que, en general, este recurso es del tipo `CheeseListing`, y cuando encontremos su documentación, deberíamos encontrar información también sobre el significado y los tipos de las propiedades `CheeseListing/title` o `CheeseListing/price`. ¿Dónde está esa documentación? Sigue el enlace `@vocab` a `/api/docs.jsonld`.

Es una descripción completa de nuestra API en JSON-LD. Y, compruébalo. ¡Tiene una sección llamada `supportedClasses`, con una clase `CheeseListing` y todas las diferentes propiedades debajo de ella! Así es como una máquina puede entender lo que significa la propiedad`CheeseListing/title`: tiene una etiqueta, detalles sobre si es o no necesaria, si es o no legible y si es o no escribible. Para `CheeseListing/price`, ya sabe que se trata de un número entero.

¡Esta es una información poderosa para una máquina! Y si estás pensando

> ¡Un momento! ¿No es ésta exactamente la misma información que nos daba la especificación OpenAPI?

Pues no te equivocas. Pero hablaremos de ello dentro de un rato.

En cualquier caso, lo más interesante es que la Plataforma API obtiene todos los datos sobre nuestra clase y sus propiedades de nuestro código Por ejemplo, mira la propiedad`CheeseListing/price`: tiene un título, el tipo de `xmls:integer`y algunos datos.

Por cierto, incluso ese tipo `xmls:integer` proviene de otro documento. No lo he mostrado, pero al principio de esta página, hacemos referencia a otro documento que define más tipos, incluido lo que significa el "tipo" `xmls:integer` en un formato legible por la máquina.

De todos modos, de vuelta a nuestro código, por encima del precio, añade algo de phpdoc:

> El precio de este delicioso queso en céntimos.

Actualiza ahora nuestro documento JSON-LD. ¡Bum! ¡De repente tenemos un campo `hydra:description`! A continuación hablaremos de lo que es "Hydra".

## Cómo se ve esto para una máquina

Lo sé, lo sé, todo esto es un poco confuso, bueno, al menos para mí. Pero, intenta imaginarte cómo se ve esto para una máquina. Vuelve al JSON original: decía `@type: "CheeseListing"`. Al "seguir" la URL `@context`, y luego seguir`@vocab` -casi de la misma manera que seguimos los enlaces dentro de un navegador-, ¡podemos acabar encontrando detalles sobre lo que realmente significa ese "tipo"! Y haciendo referencia a documentos externos en `@context`, podemos, en cierto modo, "importar" más tipos. Cuando una máquina ve `xmls:integer`, sabe que puede seguir este enlace `xmls`para saber más sobre ese tipo. Y si todas las APIs utilizaran este mismo identificador para los tipos enteros, bueno, de repente, las APIs serían súper comprensibles para las máquinas.

De todos modos, no es necesario que puedas leer estos documentos y que tengan un sentido perfecto. Mientras entiendas lo que todo esto de los "datos enlazados" y los "tipos" compartidos intentan conseguir, estarás bien.

Vale, ya casi hemos terminado con todo este rollo teórico, lo prometo. Pero antes, tenemos que hablar de lo que es "Hydra", y ver algunas otras entradas geniales que ya están en `hydra:supportedClass`.
