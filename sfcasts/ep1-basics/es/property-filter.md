# Filtro de propiedades: Conjuntos de campos dispersos

En sólo unos minutos, hemos dado a nuestros clientes de la API la posibilidad de filtrar por listados de quesos publicados y buscar por título y descripción. Puede que también necesiten la posibilidad de filtrar por precio. Eso parece un trabajo para... ¡ `RangeFilter`! Añade otro `@ApiFilter()` con `RangeFilter::class`. Subamos inmediatamente y añadamos la declaración `use` para eso: la del ORM. Luego,`properties={"price"}`.

[[[ code('4aaaac201a') ]]]

Este filtro es un poco loco. Dale la vuelta, refresca los documentos y mira la operación de recogida GET. ¡Vaya! Ahora tenemos un montón de casillas de filtro, para precio entre, mayor que, menor que, mayor o igual, etc. Busquemos todo lo que sea mayor que 20 y... Ejecuta. Esto añade `?price[gt]=20` a la URL. ¡Oh, excepto que eso es una búsqueda de todo lo que sea mayor de 20 céntimos! Prueba con 1000 en su lugar.

Esto devuelve sólo un elemento y, una vez más, anuncia los nuevos filtros dentro de `hydra:search`.

Los filtros son súper divertidos. Hay montones de filtros incorporados, pero puedes añadir los tuyos propios. Desde un alto nivel, un filtro es básicamente una forma de modificar la consulta de Doctrine que se realiza cuando se obtiene una colección.

## Añadir una breve descripción

Hay otro filtro del que quiero hablar... y es un poco especial: en lugar de devolver menos resultados, se trata de devolver menos campos. Imaginemos que la mayoría de las descripciones son súper largas y contienen HTML. En el front-end, queremos poder obtener una colección de listados de quesos, pero sólo vamos a mostrar una versión muy corta de la descripción. Para que eso sea súper fácil, vamos a añadir un nuevo campo que devuelva esto. Busca `getDescription()` y añade un nuevo método a continuación llamado `public function getShortDescription()`. Esto devolverá una cadena anulable, en caso de que la descripción no esté establecida todavía. Añadamos inmediatamente esto a un grupo - `cheese_listing:read` para que aparezca en la API.

[[[ code('aa0f8ed1b0') ]]]

En el interior, si el `description` ya tiene menos de 40 caracteres, simplemente devuélvelo. En caso contrario, devuelve un `substr` de la descripción -consigue los primeros 40 caracteres, y luego un pequeño `...` al final. Ah, y, en un proyecto real, para mejorar esto - probablemente deberías usar `strip_tags()` en la descripción antes de hacer nada de esto para que no dividamos ninguna etiqueta HTML.

[[[ code('ed7c07d610') ]]]

Actualiza los documentos... y luego abre la operación GET del artículo. Busquemos el id de listado de queso 1. Y... ¡ahí está! La descripción apenas supera los 40 caracteres. Copiaré la URL, la pondré en una nueva pestaña y añadiré `.jsonld` al final para verlo mejor.

En este punto, añadir el nuevo campo no era nada especial. Pero... si algunas partes de mi frontend sólo necesitan el `shortDescription`... es un poco inútil que la API envíe también el campo `description`... ¡sobre todo si ese campo es muy, muy grande! ¿Es posible que un cliente de la API le diga a nuestra API que no devuelva determinados campos?

## Hola PropertyFilter

En la parte superior de nuestra clase, añade otro filtro con `PropertyFilter::class`. Sube, escribe `use PropertyFilter` y pulsa el tabulador para autocompletar. Esta vez, sólo hay una de estas clases.

[[[ code('c2673c06b2') ]]]

Este filtro tiene algunas opciones, pero funciona perfectamente sin hacer nada más.

Ve a refrescar nuestros documentos. Hmm, esto no supone ninguna diferencia aquí... no es una característica de nuestra API que pueda expresarse en el documento de especificaciones de la OpenAPI.

Pero, este recurso de nuestra API sí tiene un nuevo superpoder. En la otra pestaña, elige las propiedades exactas que quieres con`?properties[]=title&properties[]=shortDescription`. ¡Dale caña! ¡Precioso! Seguimos obteniendo los campos JSON-LD estándar, pero entonces sólo recuperamos esos dos campos. Esta idea se denomina a veces "conjunto de campos dispersos", y es una forma estupenda de permitir que tu cliente de la API pida exactamente lo que quiere, sin dejar de organizarlo todo en torno a recursos concretos de la API.

Ah, y el usuario no puede intentar seleccionar nuevos campos que no formen parte de nuestros datos originales - no puedes intentar conseguir `isPublished` - simplemente no funciona, aunque puedes habilitarlo.

Siguiente: hablemos de la paginación. Sí, ¡las APIs necesitan totalmente la paginación! Si tenemos 10.000 listados de quesos en la base de datos, no podemos devolverlos todos a la vez.
