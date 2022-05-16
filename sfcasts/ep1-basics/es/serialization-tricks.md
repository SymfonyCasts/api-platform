# @NombreSerializado y Args del Constructor

Cuando leemos un recurso `CheeseListing`, obtenemos un campo `description`. Pero cuando enviamos datos, se llama `textDescription`. Y... eso está técnicamente bien: nuestros campos de entrada no tienen por qué coincidir con los de salida. Pero... si pudiéramos hacer que fuesen iguales, eso facilitaría la vida a cualquiera que utilice nuestra API.

Es bastante fácil adivinar cómo se crean estas propiedades: las claves dentro del JSON coinciden literalmente con los nombres de las propiedades dentro de nuestra clase. Y en el caso de una propiedad falsa como `textDescription`, la Plataforma API elimina la parte "set" y la convierte en minúscula. Por cierto, como todo en la Plataforma API, la forma en que los campos se transforman en claves es algo que puedes controlar a nivel global: se llama "convertidor de nombres".

## Controlar los nombres de los campos: @NombreSerializado

De todos modos, estaría bien que el campo de entrada se llamara simplemente `description`. Tendríamos entrada `description`, salida `description`. Claro que, internamente, sabríamos que se llama `setTextDescription()` en la entrada y `getDescription()` en la salida, pero el usuario no tendría que preocuparse ni ocuparse de esto.

Y... ¡sí! Puedes controlar totalmente esto con una anotación súper útil. Por encima de`setTextDescription()`, añade `@SerializedName()` con `description`.

[[[ code('556648f4d0') ]]]

¡Actualiza la documentación! Si probamos la operación GET... no ha cambiado: sigue siendo`description`. Pero para la operación POST... ¡sí! El campo se llama ahora`description`, pero el serializador llamará internamente a `setTextDescription()`.

## ¿Qué pasa con los argumentos del constructor?

Vale, ya sabemos que al serializador le gusta trabajar llamando a métodos getter y setter... o utilizando propiedades públicas o algunas otras cosas como los métodos hasser o isser. ¿Pero qué pasa si quiero dar a mi clase un constructor? Bueno, ahora mismo tenemos un constructor, pero no tiene ningún argumento necesario. Eso significa que el serializador no tiene problemas para instanciar esta clase cuando enviamos un nuevo `CheeseListing`.

Pero... ¿sabes qué? Como todo `CheeseListing` necesita un título, me gustaría darle a éste un nuevo argumento obligatorio llamado `$title`. Definitivamente no necesitas hacer esto, pero para mucha gente tiene sentido: si una clase tiene propiedades requeridas: ¡obliga a pasarlas a través del constructor!

¡Y ahora que tenemos esto, también puedes decidir que no quieres tener un método`setTitle()`! Desde una perspectiva orientada a objetos, esto hace que la propiedad`title` sea inmutable: sólo puedes establecerla una vez al crear el`CheeseListing`. Es un ejemplo un poco tonto. En el mundo real, probablemente querríamos que el título fuera modificable. Pero, desde una perspectiva orientada a objetos, hay situaciones en las que quieres hacer exactamente esto.

Ah, y no olvides decir `$this->title = $title` en el constructor.

[[[ code('261360b41e') ]]]

La pregunta ahora es... ¿podrá el serializador trabajar con esto? ¿Se va a enfadar mucho porque hemos eliminado `setTitle()`? Y cuando pongamos un nuevo POST, ¿será capaz de instanciar el `CheeseListing` aunque tenga un arg requerido?

¡Vaya! ¡Vamos a probarlo! ¿Qué tal unas migajas de queso azul... por 5$? Ejecuta y... ¡funciona! ¡El título es correcto!

Um... ¿cómo diablos ha funcionado? Como la única forma de establecer el título es a través del constructor, parece que sabía pasar la clave del título allí? ¿Cómo?

La respuesta es... ¡magia! ¡Es una broma! La respuesta es... ¡por pura suerte! No, sigo mintiendo totalmente. La respuesta es por el nombre del argumento.

Comprueba esto: cambia el argumento por `$name`, y actualiza el código de abajo. Desde una perspectiva orientada a objetos, eso no debería cambiar nada. Pero vuelve a pulsar ejecutar.

[[[ code('7b476e810a') ]]]

¡Un gran error! Un código de estado 400:

> No se puede crear una instancia de `CheeseListing` a partir de datos serializados porque
> su constructor requiere que el parámetro "nombre" esté presente.

Mis felicitaciones al creador de ese mensaje de error: ¡es impresionante! Cuando el serializador ve un argumento del constructor llamado... `$name` busca una clave `name`en el JSON que estamos enviando. Si no existe, ¡boom! ¡Error!

Así que mientras llamemos al argumento `$title`, todo funciona bien.

## El argumento del constructor puede cambiar los errores de validación

Pero hay un caso límite. Imagina que estamos creando un nuevo `CheeseListing`y nos olvidamos de enviar el campo `title` por completo - como si tuviéramos un error en nuestro código JavaScript. Pulsa Ejecutar.

Nos devuelve un error 400... lo cual es perfecto: significa que la persona que hace la petición tiene algo mal en su petición. Pero, el `hydra:title` no es muy claro:

> Se ha producido un error

¡Fascinante! El `hydra:description` es mucho más descriptivo... en realidad, demasiado descriptivo: muestra algunas cosas internas de nuestra API... que quizá no quiera hacer públicas. Al menos el `trace` no aparecerá en producción.

Puede que mostrar estos detalles dentro de `hydra:description` te parezca bien... Pero si quieres evitar esto, tienes que recurrir a la validación, que es un tema del que hablaremos en unos minutos. Pero lo que debes saber ahora es que la validación no puede producirse a menos que el serializador sea capaz de crear con éxito el objeto `CheeseListing`. En otras palabras, tienes que ayudar al serializador haciendo que este argumento sea opcional.

[[[ code('120e49ac3a') ]]]

Si lo vuelves a intentar... ¡ja! ¡Un error 500! Sí crea el objeto `CheeseListing`con éxito... y luego explota cuando intenta añadir un título nulo en la base de datos. Pero, eso es exactamente lo que queremos, porque permitirá que la validación haga su trabajo... una vez que lo añadamos dentro de unos minutos.

***TIP
En realidad, la autovalidación no estaba habilitada por defecto en Symfony 4.3, pero puede que lo esté en Symfony 4.4.
***

Ah, y si estás usando Symfony 4.3, ¡puede que ya veas un error de validación! Eso se debe a una nueva característica que puede convertir automáticamente tus reglas de base de datos -el hecho de que le hayamos dicho a Doctrine que `title` es necesario en la base de datos- en reglas de validación. Como dato curioso, esta función fue aportada a Symfony por Kèvin Dunglas, el desarrollador principal de la Plataforma API. Kèvin, tómate un descanso de vez en cuando

A continuación: vamos a explorar los filtros: un potente sistema para permitir a tus clientes de la API buscar y filtrar a través de nuestros recursos CheeseListing.
