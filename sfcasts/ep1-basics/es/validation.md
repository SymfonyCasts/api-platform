# Validación

Hay un montón de formas diferentes en las que un cliente de la API puede enviar datos erróneos: puede enviar un JSON mal formado... o enviar un campo `title` en blanco... quizás porque un usuario se olvidó de rellenar un campo en el frontend. El trabajo de nuestra API es responder a todas estas situaciones de forma informativa y coherente, de modo que los errores se puedan entender, analizar y comunicar fácilmente a los humanos.

## Manejo de JSON inválido

Ésta es una de las áreas en las que la Plataforma API realmente destaca. Hagamos algunos experimentos: ¿qué ocurre si enviamos accidentalmente un JSON no válido? Elimina la última llave.

¡Pruébalo! ¡Woh! ¡Qué bien! Esto nos devuelve un nuevo "tipo" de recurso: un `hydra:error`. Si un cliente de la API entiende a Hydra, sabrá al instante que esta respuesta contiene detalles del error. E incluso si alguien no ha oído hablar nunca de Hydra, ésta es una respuesta súper clara. Y, lo más importante, todos los errores tienen la misma estructura.

El código de estado también es 400 -lo que significa que el cliente ha cometido un error en la petición- y `hydra:description` dice "Error de sintaxis". Sin hacer nada, la Plataforma API ya está gestionando este caso. Ah, y el `trace`, aunque puede ser útil ahora mismo durante el desarrollo, no aparecerá en el entorno de producción.

## Validación de campos

¿Qué pasa si simplemente borramos todo y enviamos una petición vacía? Ah... eso sigue siendo técnicamente un JSON no válido. Prueba sólo con `{}`.

Ah... esta vez obtenemos un error 500: la base de datos está explotando porque algunas de las columnas no pueden ser nulas. Ah, y como he mencionado antes, si utilizas Symfony 4.3, es posible que ya veas un error de validación en lugar de un error de base de datos debido a una nueva función en la que las reglas de validación se añaden automáticamente al leer las reglas de la base de datos de Doctrine.

Pero, tanto si ves un error 500, como si Symfony añade al menos una validación básica por ti, los datos de entrada permitidos son algo que queremos controlar: quiero decidir las reglas exactas para cada campo.

***TIP
En realidad, la auto-validación no estaba habilitada por defecto en Symfony 4.3, pero puede que lo esté en Symfony 4.4.
***

Añadir reglas de validación es... oh, tan bonito. Y, a menos que seas nuevo en Symfony, esto te parecerá deliciosamente aburrido. Por encima de `title`, para que sea obligatorio, añade`@Assert\NotBlank()`. Añadamos también aquí `@Assert\Length()` con, por ejemplo,`min=2` y `max=50`. Incluso pongamos en `maxMessage` 

> Describe tu queso en 50 caracteres o menos

[[[ code('4d04c62b2b') ]]]

¿Qué más? Por encima de `description`, añade `@Assert\NotBlank`. Y para el precio,`@Assert\NotBlank()`. También podrías añadir una restricción `GreaterThan` para asegurarte de que está por encima de cero.

[[[ code('40a8181ab1') ]]]

Bien, vuelve a cambiar y prueba a no enviar datos de nuevo. ¡Woh! ¡Es increíble! ¡El `@type` es `ConstraintViolationList`! ¡Es uno de los tipos descritos por nuestra documentación JSON-LD!

Ve a `/api/docs.jsonld`. Debajo de `supportedClasses`, está `EntryPoint` y aquí están `ConstraintViolation` y `ConstraintViolationList`, que describen el aspecto de cada uno de estos tipos.

Y los datos de la respuesta son realmente útiles: una matriz `violations` en la que cada error tiene un `propertyPath` -para que sepamos de qué campo procede ese error- y `message`. Así que... ¡todo funciona!

Y si intentas pasar un `title` de más de 50 caracteres... y se ejecuta, ahí está nuestro mensaje personalizado.

## Validación para pasar tipos no válidos

¡Perfecto! ¡Ya hemos terminado! Pero espera... ¿no nos falta un poco de validación en el campo`price`? Tenemos `@NotBlank`... pero ¿qué nos impide enviar el texto de este campo? ¿Algo?

¡Vamos a intentarlo! Establece el precio en `apple`, y ejecuta.

¡Ja! ¡Falla con un código de estado 400! ¡Es increíble! Dice:

> El tipo del atributo precio debe ser int, cadena dada

Si te fijas, está fallando durante el proceso de deserialización. Técnicamente no es un error de validación, es un error de serialización. Pero para el cliente de la API, parece casi lo mismo, excepto que esto devuelve un tipo de error en lugar de un `ConstraintViolationList`... lo que probablemente tiene sentido: si algún JavaScript está haciendo esta petición, ese JavaScript probablemente debería tener algunas reglas de validación incorporadas para evitar que el usuario añada texto al campo del precio.

La cuestión es: La Plataforma API, bueno, en realidad, el serializador, conoce los tipos de tus campos y se asegurará de que no se pase nada insano. En realidad, sabe que el precio es un entero por dos fuentes: los metadatos de Doctrine `@ORM\Column` sobre el campo y la sugerencia de tipo de argumento en `setPrice()`.

De lo único que tenemos que preocuparnos realmente es de añadir la validación de "reglas de negocio": añadir las restricciones de validación de `@Assert` para decir que este campo es obligatorio, que ese campo tiene una longitud mínima, etc. Básicamente, la validación en la Plataforma API funciona exactamente igual que la validación en cualquier aplicación Symfony. Y la Plataforma API se encarga del aburrido trabajo de asignar los fallos de serialización y validación a un código de estado 400 y a respuestas de error descriptivas y coherentes.

A continuación, ¡creemos un segundo Recurso API! ¡Un usuario! Porque las cosas se pondrán realmente interesantes cuando empecemos a crear relaciones entre recursos.
