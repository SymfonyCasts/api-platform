# Recurso API de usuario

Quiero exponer nuestra nueva entidad `User` como un recurso API. ¡Y ya sabemos cómo hacerlo! Añade... `@ApiResource`!

[[[ code('d8770f0f4c') ]]]

¡Así de fácil! ¡Sí! Nuestra documentación de la API muestra un nuevo recurso con cinco nuevas rutas, u operaciones. Y en la parte inferior, está el nuevo modelo `User`.

Hmm, pero es un poco extraño: tanto el campo `password` con hash como el array `roles` forman parte de la API. Sí, ¡podríamos crear un nuevo usuario ahora mismo y pasarle los roles que creamos que debe tener! Eso podría estar bien para un usuario administrador, pero no para cualquiera. Tomemos el control de las cosas.

## ¿Usuarios?

Una cosa que quiero que notes es que, hasta ahora, la clave primaria siempre se utiliza como "id" en nuestra API. Esto es algo que es flexible en la Plataforma API. De hecho, en lugar de utilizar un id autoincrementado, una opción es utilizar un UUID. No vamos a utilizarlos en este tutorial, pero utilizar un UUID como identificador es algo que admiten Doctrine y la Plataforma API. Los UUIDs funcionan con cualquier base de datos, pero se almacenan de forma más eficiente en PostgreSQL que en MySQL, aunque utilizamos algunos UUIDs en MySQL en algunas partes de SymfonyCasts.

Pero... ¿por qué te hablo de UUID's? ¿Qué hay de malo en autoincrementar los ids? Nada... pero.... Los UUID's pueden ayudar a simplificar tu código JavaScript. Supongamos que escribimos un JavaScript para crear un nuevo `CheeseListing`. Con los ids autoincrementados, el proceso se parece a esto: hacer una petición POST a `/api/cheeses`, esperar la respuesta, luego leer el `@id` de la respuesta y almacenarlo en algún sitio... porque normalmente necesitarás saber el id de cada lista de quesos. Con los UUID, el proceso es así: genera un UUID en JavaScript -eso es totalmente legal-, envía la petición POST y... ¡ya está! Con los UUID's, no necesitas esperar a que termine la llamada AJAX para poder leer el id: has creado el UUID en JavaScript, así que ya lo conoces. Por eso los UUID a menudo pueden ser muy útiles.

Para que todo esto funcione, tendrás que configurar tu entidad para que utilice un UUID y añadir un método `setId()` para que sea posible que la Plataforma API lo establezca. O puedes crear el id de autoincremento y añadir una propiedad UUID independiente. La Plataforma API tiene una anotación para marcar un campo como "identificador".

## Grupos de normalización y desnormalización

De todos modos, vamos a tomar el control del proceso de serialización para poder eliminar cualquier campo extraño, como que se devuelva la contraseña codificada. Haremos exactamente lo mismo que hicimos en `CheeseListing`: añadir grupos de normalización y desnormalización. Copia las dos líneas de contexto, abre `User` y pégalas. Voy a eliminar la parte de`swagger_definition_name`: realmente no la necesitamos. Para la normalización, utiliza`user:read` y para la desnormalización, `user:write`.

[[[ code('4df33d1186') ]]]

Seguimos el mismo patrón que hemos estado utilizando. Ahora... pensemos: ¿qué campos necesitamos exponer? Para `$email`, añade `@Groups({})` con `"user:read", "user:write"`: es un campo legible y escribible. Cópialo, pégalo encima de `password` y hazlo sólo con `user:write`.

[[[ code('7a3f4eac0d') ]]]

Esto... no tiene mucho sentido todavía. Es decir, ya no es legible, lo que tiene mucho sentido. Pero esto acabará almacenando la contraseña codificada, que no es algo que un cliente de la API vaya a establecer directamente. Pero... nos preocuparemos de todo eso en nuestro tutorial de seguridad. Por ahora, como la contraseña es un campo obligatorio en la base de datos, vamos a hacerla temporalmente escribible para que no nos estorbe.

Por último, haz que `username` sea legible y también escribible.

[[[ code('810da34460') ]]]

¡Vamos a probarlo! Actualiza los documentos. Al igual que con `CheeseListing`, ahora tenemos dos modelos: podemos leer `email` y `username` y podemos escribir `email`, `password`y `username`.

Lo único que nos falta para que sea un recurso de la API totalmente funcional es la validación. Para empezar, tanto `$email` como `$username` deben ser únicos. En la parte superior de la clase, añade `@UniqueEntity()` con `fields={"username"}`, y otro`@UniqueEntity()` con `fields={"email"}`.

[[[ code('df6f353d84') ]]]

Entonces, veamos, `$email` debe ser `@Assert\NotBlank()` y `@Assert\Email()`, y `$username` necesita ser `@Assert\NotBlank()`. No me preocuparé todavía de la contraseña, eso hay que arreglarlo bien de todos modos en el tutorial de seguridad.

[[[ code('0fc83dba55') ]]]

Así que, ¡creo que estamos bien! Actualiza la documentación y empecemos a crear usuarios! Haz clic en "Probar". Utilizaré mi dirección de correo electrónico personal de la vida real:`cheeselover1@example.com`. La contraseña no importa... y hagamos que el nombre de usuario coincida con el correo electrónico sin el dominio... para no confundirme. ¡Ejecuta!

¡Woohoo! ¡201 éxito! Vamos a crear un usuario más... para tener mejores datos con los que jugar.

## Validación fallida

¿Y si enviamos un JSON vacío? Pruébalo. ¡Sí! código de estado 400.

Bien... ¡hemos terminado! Tenemos 1 nuevo recurso, cinco nuevas operaciones, control sobre los campos de entrada y salida, validación, paginación y podríamos añadir fácilmente el filtrado... ¡es increíble! Este es el poder de la Plataforma API. Y a medida que vayas mejorando en su uso, desarrollarás aún más rápido.

Pero en última instancia, creamos el nuevo recurso API `User` no sólo porque crear usuarios es divertido: lo hicimos para poder relacionar cada `CheeseListing` con el `User` que lo "posee". En una API, las relaciones son un concepto clave. Y te va a encantar cómo funcionan en la Plataforma API.
