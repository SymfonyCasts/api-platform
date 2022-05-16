# El serializador

Busca en Google el serializador de Symfony y encuentra una página llamada [El componente serializador](https://symfony.com/doc/current/components/serializer.html).

La Plataforma API está construida sobre los componentes Symfony. ¡Y todo el proceso de cómo convierte nuestro objeto `CheeseListing` en JSON... y JSON de nuevo en un objeto`CheeseListing`, lo hace el Serializador de Symfony! Si entendemos cómo funciona, ¡estamos en el negocio!

Y, al menos en la superficie, es maravillosamente sencillo. Mira el diagrama que muestra cómo funciona. Pasar de un objeto a JSON se llama serialización, y de JSON a un objeto se llama deserialización. Para ello, internamente, pasa por un proceso llamado normalización: primero toma tu objeto y lo convierte en una matriz. Y luego lo codifica en JSON o en el formato que sea.

## Cómo se convierten los objetos en datos brutos

En realidad, hay un montón de clases "normalizadoras" diferentes que ayudan en esta tarea, como una que es muy buena para convertir los objetos de `DateTime` en una cadena y viceversa. Pero la clase principal -la que está en el centro de este proceso- se llama `ObjectNormalizer`. Entre bastidores, utiliza otro componente de Symfony llamado `PropertyAccess`, que tiene un superpoder: si le das un nombre de propiedad, como `title`, es realmente bueno para encontrar y utilizar métodos getter y setter para acceder a esa propiedad.

En otras palabras, cuando la plataforma API intenta "normalizar" un objeto en una matriz, ¡utiliza los métodos getter y setter para hacerlo!

Por ejemplo, ve que hay un método `getId()`, y así, lo convierte en una clave`id` en el array... y finalmente en el JSON. Hace lo mismo con`getTitle()` - que se convierte en `title`. ¡Es así de sencillo!

Cuando enviamos datos, ¡hace lo mismo! Como tenemos un método `setTitle()`, podemos enviar JSON con una clave `title`. El normalizador tomará el valor que enviamos, llamará a `setTitle()` ¡y lo pasará!

Es una forma sencilla, pero muy útil, de permitir que tus clientes de la API interactúen con tu objeto, tu recurso de la API, utilizando sus métodos getter y setter. Por cierto, el componente PropertyAccess también admite propiedades públicas, hassers, issers, adders, removers - básicamente un montón de convenciones de nomenclatura de métodos comunes, además de getters y setters.

## Añadir un "campo" personalizado

De todos modos, ahora que sabemos cómo funciona esto, ¡somos súper peligrosos! En serio, ahora podemos enviar un campo `description`. Imaginemos que esta propiedad puede contener HTML en la base de datos. Pero la mayoría de nuestros usuarios no entienden realmente el HTML y, en su lugar, se limitan a escribir en un cuadro con saltos de línea. Vamos a crear un nuevo campo personalizado llamado `textDescription`. Si un cliente de la API envía un campo `textDescription`, convertiremos las nuevas líneas en saltos de línea HTML antes de guardarlo en la propiedad`description`.

¿Cómo podemos crear un campo de entrada personalizado totalmente nuevo para nuestro recurso? Busca `setDescription()`, duplícalo y llámalo `setTextDescription()`. Dentro de, por ejemplo, `$this->description = nl2br($description);`. Es un ejemplo tonto, pero incluso olvidando la Plataforma API, esto es una buena y aburrida codificación orientada a objetos: hemos añadido una forma de establecer la descripción si quieres que las nuevas líneas se conviertan en saltos de línea.

[[[ code('0b6a7fea41') ]]]

Pero ahora, actualiza y abre de nuevo la operación POST. ¡Vaya! ¡Dice que todavía podemos enviar un campo `description`, pero también podemos pasar `textDescription`! Pero si intentas la operación GET... seguimos obteniendo sólo `description`.

¡Eso tiene sentido! Hemos añadido un método setter -que permite enviar este campo- pero no hemos añadido un método getter. También puedes ver el nuevo campo descrito abajo en la sección de modelos.

## Eliminar la "descripción" como entrada

Pero, probablemente no queramos permitir que el usuario envíe tanto `description`como `textDescription`. Es decir, se podría, pero es un poco raro: si el cliente enviara ambos, chocarían entre sí y la última clave ganaría porque su método setter sería llamado en último lugar. Así que vamos a eliminar `setDescription()`.

Actualiza ahora. ¡Me encanta! Para crear o actualizar un listado de quesos, el cliente enviará`textDescription`. Pero cuando recojan los datos, siempre obtendrán de vuelta `description`. De hecho, probemos... con el id 1. Abre la operación PUT y establece `textDescription`en algo con algunos saltos de línea. Sólo quiero actualizar este campo, así que podemos eliminar los demás campos. Y... ¡a ejecutar! código de estado 200 y... ¡un campo `description` con algunos saltos de línea!

Por cierto, el hecho de que nuestros campos de entrada no coincidan con los de salida está totalmente bien. La coherencia está muy bien, y pronto te mostraré cómo podemos arreglar esta incoherencia. Pero no hay ninguna regla que diga que tus datos de entrada tienen que coincidir con los de salida.

## Eliminar createdAt Input

Bien, ¿qué más podemos hacer? Bueno, tener un campo `createdAt` en la salida está muy bien, pero probablemente no tenga sentido permitir que el cliente lo envíe: el servidor debería establecerlo automáticamente.

¡No hay problema! ¿No quieres que se permita el campo `createdAt` en la entrada? Busca el método`setCreatedAt()` y elimínalo. Para autoconfigurarlo, vuelve a la buena y antigua programación orientada a objetos. Añade `public function __construct()` y, dentro, `$this->createdAt = new \DateTimeImmutable()`.

[[[ code('bb8e91b1ff') ]]]

Ve a actualizar los documentos. Sí, aquí ha desaparecido... pero cuando intentamos la operación GET, sigue estando en la salida.

## Añadir un campo de fecha personalizado

¡Estamos en racha! ¡Así que vamos a personalizar una cosa más! Digamos que, además del campo `createdAt` -que está en este formato feo, pero estándar-, también queremos devolver la fecha como una cadena -algo así como `5 minutes ago` o `1 month ago`.

Para ayudarnos a hacerlo, busca tu terminal y ejecuta

```terminal
composer require nesbot/carbon
```

Esta es una práctica utilidad de DateTime que puede darnos fácilmente esa cadena. Ah, mientras esto se instala, volveré a la parte superior de mi entidad y eliminaré el`path` personalizado en la operación `get`. Es un ejemplo genial... pero no hagamos que nuestra API sea rara sin motivo.

[[[ code('b458601aa9') ]]]

Sí, eso se ve mejor.

De vuelta a la terminal.... ¡hecho! En `CheeseListing`, busca `getCreatedAt()`, pasa por debajo, y añade `public function getCreatedAtAgo()` con un tipo de retorno `string`. Luego,`return Carbon::instance($this->getCreatedAt())->diffForHumans()`.

[[[ code('bf8573e66d') ]]]

Ya sabes lo que hay que hacer: con sólo añadir un getter, cuando actualizamos... y miramos el modelo, tenemos un nuevo `createdAtAgo` - ¡campo de sólo lectura! Y, por cierto, también sabe que `description` es de sólo lectura porque no tiene ningún setter.

Desplázate hacia arriba y prueba la operación de recogida GET. Y... genial: `createdAt` y`createdAtAgo`.

Por muy bonito que sea controlar las cosas simplemente ajustando tus métodos getter y setter, no es lo ideal. Por ejemplo, para evitar que un cliente de la API establezca el campo `createdAt`, tuvimos que eliminar el método `setCreatedAt()`. Pero, ¿qué pasa si, en algún lugar de mi aplicación -como un comando que importa listados de queso heredados- necesitamos establecer manualmente la fecha `createdAt`? Vamos a aprender a controlar esto con los grupos de serialización.
