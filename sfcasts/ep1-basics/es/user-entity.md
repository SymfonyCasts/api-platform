# Creación de la entidad de usuario

No vamos a hablar específicamente de la seguridad en este tutorial; lo haremos en nuestro próximo curso y le prestaremos la debida atención. Pero, incluso olvidando la seguridad y el inicio de sesión y todo eso, es muy probable que tu API tenga algún concepto de "usuarios". En nuestro caso, un "usuario" publicará un listado de quesos y se convertirá en su "propietario". Y puede que después, para comprar un listado de quesos, un usuario envíe un mensaje a otro usuario. Es hora de llevar nuestra aplicación al siguiente nivel creando esa entidad.

## make:usuario

Y aunque te diga que no pienses en la seguridad, en lugar de crear la entidad usuario con `make:entity` como haría normalmente, voy a utilizar `make:user`,

```terminal-silent
php bin/console make:user
```

Sí, esto configurará algunas cosas relacionadas con la seguridad... pero nada que vayamos a utilizar todavía. Mira la parte 2 de esta serie para ver todas esas cosas.

En cualquier caso, llama a la clase `User`, y quiero almacenar los usuarios en la base de datos. Para el nombre único de visualización, voy a hacer que los usuarios se registren a través del correo electrónico, así que usa eso. Y entonces:

> ¿Esta aplicación necesita hacer un hash o comprobar las contraseñas de los usuarios?

Hablaremos más de esto en el tutorial de seguridad. Pero si los usuarios van a tener que iniciar sesión en tu sitio a través de una contraseña y tu aplicación va a ser la responsable de comprobar si esa contraseña es válida -no te limitas a enviar la contraseña a algún otro servicio para que la verifique-, entonces responde que sí. No importa si el usuario va a introducir la contraseña a través de una aplicación de iPhone que habla con tu API o a través de un formulario de inicio de sesión: responde que sí si tu aplicación es responsable de gestionar las contraseñas de los usuarios.

Utilizaré el hashtag de contraseñas Argon2i. Pero si no ves esta pregunta, ¡no pasa nada! A partir de Symfony 4.3, no es necesario que elijas un algoritmo de hashing de contraseñas porque Symfony puede elegir el mejor disponible de forma automática. Algo realmente genial.

¡Vamos a ver qué hace esto! Me alegra decir que... ¡no mucho! En primer lugar, ahora tenemos una entidad`User`. Y... no tiene nada de especial: tiene algunos métodos adicionales relacionados con la seguridad, como `getRoles()`, `getPassword()`, `getSalt()`y `eraseCredentials()`, pero no afectarán a lo que estamos haciendo. En su mayor parte, tenemos una entidad normal y aburrida con `$id`, `$email`, una propiedad de matriz `$roles`, y `$password`, que finalmente almacenará la contraseña con hash.

[[[ code('10d7c23982') ]]]

Esto también ha creado la entidad normal `UserRepository` y ha hecho un par de cambios en`security.yaml`: ha creado `encoders` - esto podría decir `auto` para ti, gracias a la nueva característica de Symfony 4.3 - y el proveedor de usuarios. Cosas de las que hablaremos más adelante. Así que... olvida que están aquí y en su lugar di... ¡vaya! ¡Tenemos una entidad`User`!

[[[ code('fb035fa9ca') ]]]

[[[ code('99e381dc01') ]]]

## Añadiendo el campo de nombre de usuario

Gracias al comando, la entidad tiene una propiedad `email`, y pienso hacer que los usuarios se registren utilizando eso. Pero también quiero que cada usuario tenga un "nombre de usuario" que podamos mostrar públicamente. Vamos a añadirlo: busca tu terminal y ejecuta

```terminal
php bin/console make:entity
```

Actualiza `User` y añade `username` como `string`, 255, no anulable en la base de datos, y pulsa intro para terminar.

Ahora abre `User`... y desplázate hasta `getUsername()`. El comando `make:user`generó esto y devolvió `$this->email`... porque eso es lo que elegí como mi nombre "para mostrar" por seguridad. Ahora que realmente tenemos un campo de nombre de usuario, devuelve `$this->username`.

[[[ code('1239a1c605') ]]]

Ah, y mientras hacemos esta clase, simplemente, increíble, el comando `make:user` sabía que `email` debía ser único, así que añadió `unique=true`. Añadamos también eso a `username`: `unique=true`.

[[[ code('290e49035c') ]]]

¡Esa es una bonita entidad! Vamos a sincronizar nuestra base de datos ejecutando:

```terminal
php bin/console make:migration
```

Muévete... y vuelve a comprobar el SQL: `CREATE TABLE user` - ¡se ve bien! 

[[[ code('d509f044ae') ]]]

Ejecútalo con:

```terminal
php bin/console doctrine:migration:migrate
```

¡Perfecto! Tenemos una nueva y preciosa entidad Doctrine... pero en lo que respecta a la Plataforma API, seguimos teniendo sólo un recurso API: `CheeseListing`.

Lo siguiente: vamos a exponer `User` como un Recurso API y a utilizar todos nuestros nuevos conocimientos para perfeccionar ese nuevo recurso en... unos 5 minutos.
