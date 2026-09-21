# Documentación del proyecto central_box

## 1. ¿Qué es central_box?

**central_box** es un sistema web para administrar una tienda virtual y las ventas realizadas en el establecimiento. El proyecto permite manejar usuarios, productos, categorías, inventario, pedidos, pagos, proveedores, promociones y reportes.

La aplicación tiene dos lados principales:

- **Tienda para clientes:** el cliente puede registrarse, iniciar sesión, revisar el catálogo, agregar productos al carrito y consultar sus pedidos.
- **Panel administrativo y operativo:** el administrador y el vendedor pueden revisar productos, controlar inventario, registrar ventas, consultar pedidos y gestionar diferentes procesos de la tienda.

Está construido principalmente con:

- PHP para la lógica del servidor.
- MySQL para guardar la información.
- HTML y PHP mezclados en las vistas.
- CSS para el diseño.
- JavaScript para interacciones como el carrito, búsquedas, filtros y validaciones.
- PDO para conectarse a MySQL de una forma segura.

---

## 2. La idea más importante: arquitectura MVC

El proyecto sigue una arquitectura parecida a **MVC**, que significa:

- **Modelo:** se encarga de hablar con la base de datos.
- **Vista:** muestra la información al usuario.
- **Controlador:** recibe la solicitud, aplica reglas y decide qué modelo y qué vista usar.

Una forma sencilla de imaginarlo es esta:

```text
Usuario
   |
   v
public/index.php
   |
   v
Controlador
   |
   +--> Modelo --> Base de datos
   |
   v
Vista --> Pantalla del usuario
```

### Ejemplo de una consulta de productos

1. El usuario entra al catálogo.
2. `public/index.php` identifica que se solicitó `productos`.
3. Se ejecuta `ProductoController`.
4. El controlador usa el modelo `Producto`.
5. `Producto` consulta la tabla `productos`.
6. El controlador envía el resultado a una vista.
7. La vista muestra los productos en pantalla.

La ventaja de esta separación es que cada archivo tiene una responsabilidad más clara. Por ejemplo, la consulta SQL no debería estar repartida por todas las vistas.

---

## 3. Estructura general de carpetas

```text
central_box/
├── config/          Configuración y conexión a la base de datos
├── controllers/     Controladores de cada módulo
├── models/          Modelos y consultas SQL
├── views/           Pantallas que ve el usuario
├── Styles/          Hojas de estilo CSS
├── scripts/         Archivos JavaScript
├── img/             Logo e imágenes de productos
├── public/          Punto de entrada web
├── sql/             Estructura y datos iniciales de MySQL
├── tests/           Pruebas y herramientas de diagnóstico
└── docs/            Documentación del proyecto
```

> En algunas partes del proyecto todavía aparecen nombres antiguos como `empleado` o `vendedor`. Esto no significa que sean módulos totalmente separados. En la práctica, varias funciones operativas se encuentran dentro de `EmpleadoController` y `views/empleado`.

---

# 4. Carpeta `config`

## 4.1 `config/config.php`

Es la configuración general de la aplicación. Aquí se encuentran:

- Nombre y versión de la aplicación.
- Detección de la URL base.
- Rutas físicas del proyecto.
- Duración de la sesión.
- Tamaño y tipos permitidos para imágenes.
- Cantidad de elementos por página.
- Funciones auxiliares reutilizadas por muchos archivos.

### Constantes de rutas

Algunas constantes importantes son:

- `ROOT_PATH`: ruta raíz del proyecto.
- `PUBLIC_PATH`: carpeta pública.
- `VIEWS_PATH`: carpeta de vistas.
- `CONTROLLERS_PATH`: carpeta de controladores.
- `MODELS_PATH`: carpeta de modelos.
- `UPLOADS_PATH`: carpeta donde se guardan imágenes de productos.
- `BASE_URL`: dirección que usa el navegador.

Esto evita escribir rutas completas manualmente en todos los controladores.

### Funciones importantes

#### `redirect($path)`

Redirige al usuario a otra dirección interna usando `BASE_URL`.

#### `isLoggedIn()`

Devuelve `true` si existe un usuario guardado en la sesión.

#### `getUser()`

Devuelve los datos básicos del usuario que inició sesión: id, nombre, correo, rol y avatar.

#### `hasRole(...$roles)`

Comprueba si el usuario tiene alguno de los roles permitidos.

#### `requireLogin()`

Obliga a iniciar sesión. Si la persona no está autenticada, la manda al login.

#### `requireRole(...$roles)`

Además de exigir sesión, revisa que el usuario tenga un rol autorizado. Por ejemplo, una función administrativa puede permitir solamente `admin`.

#### `setFlash()` y `getFlash()`

Sirven para mostrar mensajes temporales después de una acción. Por ejemplo: “Producto guardado correctamente”.

#### `e($value)`

Escapa texto HTML usando `htmlspecialchars`. Se usa para reducir el riesgo de inyección de HTML o XSS al mostrar datos que vienen de usuarios o de la base de datos.

#### `formatPrice($price)`

Convierte un número en un precio con formato de pesos colombianos.

#### `csrfToken()`, `csrfField()` y `verifyCsrf()`

Generan y revisan un token CSRF. El token ayuda a comprobar que un formulario realmente salió de la aplicación y no de una página externa maliciosa.

---

## 4.2 `config/database.php`

Este archivo crea la conexión con MySQL mediante PDO.

La clase `Database` usa el patrón **Singleton**. Eso significa que intenta mantener una sola instancia de conexión durante la ejecución. La conexión se obtiene así:

```php
$db = Database::getInstance()->getConnection();
```

### ¿Por qué se usa PDO?

PDO permite:

- Trabajar con consultas preparadas.
- Enviar parámetros sin concatenar directamente los valores.
- Manejar errores mediante excepciones.
- Usar un código más organizado para consultar MySQL.

La configuración actual está preparada para Laragon:

- Host: `127.0.0.1`
- Puerto: `3306`
- Base de datos: `central_box`
- Usuario: `root`
- Contraseña: vacía, que es el valor común por defecto en Laragon.

En un servidor real estos datos deberían ir en variables de entorno o en una configuración protegida.

---

# 5. Carpeta `public`

## 5.1 `public/index.php`: Front Controller

Este archivo es el **punto de entrada único** de la aplicación. Casi todas las solicitudes pasan por él.

Primero inicia la sesión y carga la configuración. Después obtiene de la URL dos valores:

```text
?controller=productos&action=lista
```

- `controller=productos`: indica qué controlador se debe usar.
- `action=lista`: indica qué método del controlador se debe ejecutar.

Luego existe un arreglo que relaciona nombres de URL con clases PHP:

```php
'productos' => 'ProductoController'
```

El flujo general es:

1. Leer controlador y acción.
2. Verificar que el controlador exista en el mapa.
3. Cargar el archivo del controlador.
4. Crear una instancia de la clase.
5. Revisar que la acción exista.
6. Ejecutar el método.

Si algo no existe, se muestra un error 404 o 500.

### Ejemplos de URLs

```text
index.php?controller=auth&action=login
index.php?controller=productos&action=lista
index.php?controller=ventas&action=online
index.php?controller=ventas&action=puntoVenta
```

## 5.2 `public/create_db.php` y otros archivos públicos

Son herramientas utilizadas para crear o reparar estructuras durante el desarrollo. No deberían quedar abiertas sin protección en un servidor de producción, porque pueden permitir modificar la base de datos.

## 5.3 `public/assets`

Contiene copias de recursos públicos como estilos, scripts e imágenes que pueden ser servidos directamente por el servidor web.

---

# 6. Controladores

Los controladores están en `controllers/`. Cada uno coordina un módulo de la aplicación.

## 6.1 `AuthController.php`

Maneja la autenticación y las cuentas básicas:

- Mostrar login.
- Procesar login.
- Registro de clientes.
- Registro de administradores.
- Recuperación de contraseña para clientes.
- Cierre de sesión.

Durante el login se busca el usuario por correo, se comprueba la contraseña con `password_verify()` y se guardan sus datos en `$_SESSION`.

La sesión guarda valores como:

```php
$_SESSION['usuario_id']
$_SESSION['usuario_nombre']
$_SESSION['usuario_email']
$_SESSION['usuario_rol']
```

La contraseña no se debe guardar directamente. El modelo `Usuario` la convierte en un hash usando `password_hash()`.

## 6.2 `DashboardController.php`

Prepara los datos generales del panel principal, como:

- Total de ventas.
- Total de pedidos.
- Total de productos.
- Total de usuarios.
- Resumen de inventario.
- Pedidos recientes.

Después carga la vista compartida del dashboard.

## 6.3 `EmpleadoController.php`

Es el controlador del panel operativo de empleados y vendedores. Sus funciones principales incluyen:

- Login del empleado.
- Dashboard del empleado.
- Catálogo interno.
- Nueva venta.
- Confirmar venta.
- Inventario o stock.
- Factura.
- Historial de ventas.
- Promociones activas.

### Flujo de una nueva venta

1. `nuevaVenta()` carga productos, categorías y promociones.
2. La vista permite elegir productos y cantidades.
3. JavaScript mantiene el carrito visual.
4. El formulario envía los productos a `confirmarVenta()`.
5. Se revisa CSRF y que haya productos.
6. Si no se eligió cliente, se usa “Cliente Presencial”.
7. El modelo `Pedido` valida los productos, los precios y el stock.
8. Se crea el pedido y se descuentan las unidades.
9. El usuario es enviado a la factura.

### Historial

`historial()` consulta las ventas y permite filtrar por:

- Cliente o número de venta.
- Fecha inicial.
- Fecha final.
- Estado del pedido.

## 6.4 `VentasController.php`

Es el controlador que concentra la entrada del módulo de ventas general. Sus acciones principales son:

- `online()`: muestra ventas realizadas e historial.
- `puntoVenta()`: muestra el formulario para registrar una nueva venta.
- `guardarVenta()`: recibe y guarda una venta.

La diferencia importante es:

- **Ventas:** consulta y gestión de ventas ya realizadas.
- **Nueva venta/Punto de Venta:** captura una venta nueva.

La acción de historial consulta `Pedido::getAll()` y también calcula datos de inventario para informar si no hay productos o si están agotados.

## 6.5 `ProductoController.php`

Maneja los productos:

- Lista de productos.
- Catálogo público.
- Ver detalle de un producto.
- Crear producto.
- Guardar producto.
- Editar producto.
- Actualizar producto.
- Eliminar o desactivar producto.

La eliminación normalmente es lógica: se cambia `activo` a `0` en lugar de borrar toda la fila.

## 6.6 `CategoriaController.php`

Permite listar, crear, editar, actualizar y desactivar categorías. Las categorías se relacionan con productos por medio de `categoria_id`.

## 6.7 `ClienteController.php`

Maneja las funciones propias del cliente, como:

- Dashboard del cliente.
- Catálogo.
- Carrito.
- Pedidos propios.
- Detalle de pedido.
- Métodos de pago.
- Promociones.
- Puntos de lealtad.
- Ayuda y soporte.

El cliente no debería poder consultar ventas internas de otros usuarios.

## 6.8 `CarritoController.php`

Coordina las acciones del carrito:

- Ver carrito.
- Agregar productos.
- Actualizar cantidades.
- Quitar productos.
- Vaciar carrito.
- Pasar al checkout.

El modelo `Carrito` trabaja principalmente con la sesión, por eso el contenido puede existir antes de crear el pedido definitivo.

## 6.9 `PedidoController.php`

Gestiona los pedidos de clientes y sus estados. Un pedido puede pasar por estados como:

```text
pendiente -> confirmado -> enviado -> entregado
```

También puede terminar en `cancelado`.

## 6.10 `InventarioController.php`

Muestra el resumen del inventario, productos con bajo stock y movimientos. También permite registrar ajustes de inventario para usuarios autorizados.

Un movimiento puede ser de tipo:

- `entrada`: llegan unidades nuevas.
- `salida`: salen unidades.
- `ajuste`: se corrige una cantidad.

## 6.11 `ProveedoresController.php`

Maneja el proceso de compra a proveedores:

1. Crear pedido al proveedor.
2. Confirmarlo.
3. Registrar el pago.
4. Recibir el pedido.
5. Actualizar el stock.
6. Cancelarlo si es necesario.

Este flujo es importante cuando los productos se quedan sin existencias.

## 6.12 `PagosController.php`

Administra métodos y registros de pago. En la venta se manejan valores como:

- Efectivo.
- Tarjeta.
- Transferencia.

## 6.13 `PromocionesController.php`

Permite administrar promociones con nombre, descuento, fechas y estado de activación. Las ventas consultan las promociones activas para mostrarlas en el POS.

## 6.14 `ReportesController.php`

Prepara reportes de ventas, pedidos o inventario para apoyar la toma de decisiones del administrador.

## 6.15 `UsuariosController.php`

Es exclusivo del administrador y permite:

- Listar usuarios.
- Buscar usuarios.
- Registrar empleados.
- Cambiar estado activo/inactivo.
- Desactivar usuarios.
- Consultar perfil.

---

# 7. Modelos

Los modelos se encuentran en `models/`. Cada uno contiene consultas y operaciones relacionadas con una entidad.

## 7.1 `Usuario.php`

Trabaja con la tabla `usuarios`. Sus funciones principales son:

- `getAll()`: consultar usuarios.
- `findById()`: buscar por id.
- `findByEmail()`: buscar por correo.
- `create()`: crear usuario y proteger su contraseña.
- `update()`: actualizar datos.
- `delete()`: desactivar usuario.
- `authenticate()`: comprobar login.
- `countByRole()`: contar usuarios por rol.

## 7.2 `Producto.php`

Trabaja con productos y categorías relacionadas. Permite buscar, filtrar, paginar, crear, actualizar y desactivar productos.

La función `getAll()` puede recibir categoría, texto de búsqueda, límite, desplazamiento y el parámetro para consultar solo activos.

## 7.3 `Categoria.php`

Contiene las consultas de categorías activas y las operaciones CRUD de categorías.

## 7.4 `Pedido.php`

Es uno de los modelos más importantes. Maneja:

- Crear pedidos.
- Consultar pedidos.
- Consultar pedidos por cliente.
- Obtener un pedido por id.
- Obtener detalles.
- Cambiar estado.
- Calcular estadísticas.
- Consultar ventas del día.
- Consultar productos vendidos.
- Consultar la última venta.

### Transacción al crear una venta

La función `crear()` usa una transacción:

```text
beginTransaction()
   validar productos y stock
   crear cabecera del pedido
   descontar inventario
   guardar detalle del pedido
commit()
```

Si ocurre un error, se ejecuta `rollBack()` y se deshacen los cambios. Esto evita que se guarde un pedido incompleto.

También se valida que:

- El producto exista.
- El producto esté activo.
- La cantidad sea mayor que cero.
- El stock sea suficiente.
- El precio utilizado sea el de la base de datos.

## 7.5 `Carrito.php`

Administra el carrito guardado en la sesión del usuario. No representa todavía una venta confirmada; es solo una selección temporal de productos.

## 7.6 `Inventario.php`

Calcula:

- Cantidad de productos.
- Unidades disponibles.
- Productos con bajo stock.
- Productos sin stock.
- Valor total del inventario.
- Movimientos realizados.

## 7.7 `Proveedor.php`

Maneja proveedores, pedidos de compra, detalles y recepción de mercancía. Al recibir un pedido, se puede aumentar el stock de los productos.

## 7.8 `Pago.php`

Guarda y consulta información relacionada con pagos y métodos de pago.

## 7.9 `Promocion.php`

Maneja promociones, fechas de vigencia, descuentos y estado activo.

## 7.10 `Reporte.php`

Contiene consultas para generar información resumida de la operación de la tienda.

---

# 8. Base de datos

El archivo principal es `sql/central_box.sql`. Crea la base de datos `central_box` y sus tablas.

## 8.1 Tabla `usuarios`

Guarda administradores, vendedores y clientes.

Campos principales:

- `id`: identificador.
- `nombre`: nombre de la persona.
- `email`: correo único.
- `password`: contraseña protegida.
- `rol`: `admin`, `vendedor` o `cliente`.
- `activo`: permite activar o desactivar la cuenta.

### Tablas operativas adicionales

El esquema tambien incluye las tablas normalizadas `pago`, `venta` y
`detalle_venta`. Los pedidos existentes se conservan por compatibilidad y cada
nueva venta intenta mantener ambos registros dentro de la misma transaccion.

`carrito` y `detalle_carrito` persisten el carrito del cliente autenticado; la
sesion se mantiene como respaldo temporal. `recuperacion_contrasena` almacena
tokens de recuperacion con fecha de expiracion y marca de uso.

Las solicitudes de empleados se pueden guardar en `solicitud_promocion`,
relacionada con `promociones`, `usuarios` y `productos`.

## 8.2 Tabla `categorias`

Guarda las categorías de productos. Tiene el campo `activa` para desactivar una categoría sin eliminarla.

## 8.3 Tabla `productos`

Guarda:

- Nombre.
- Descripción.
- Precio.
- Stock actual.
- Stock mínimo.
- Imagen.
- Categoría.
- Vendedor que lo registró.
- Estado activo.

## 8.4 Tabla `pedidos`

Representa la cabecera de una compra o venta. Guarda cliente, total, estado, método de pago, notas y fechas.

## 8.5 Tabla `detalle_pedidos`

Guarda los productos que pertenecen a cada pedido. Un pedido puede tener muchos detalles.

Relación:

```text
usuarios 1 ---- N pedidos
pedidos 1 ---- N detalle_pedidos
productos 1 ---- N detalle_pedidos
```

## 8.6 Tabla `movimientos_inventario`

Registra quién hizo un movimiento, qué producto modificó, qué tipo de movimiento fue y cuál era el stock antes y después.

## 8.7 Tablas de proveedores

`proveedores`, `pedidos_proveedor` y `detalle_pedido_proveedor` permiten controlar compras de inventario a proveedores.

### Punto importante sobre las tablas de clientes

El esquema incluye una tabla `clientes`, pero gran parte del código utiliza la tabla `usuarios` con el rol `cliente`. Para entender el proyecto actual, el cliente operativo normalmente es un usuario de `usuarios` cuyo rol es `cliente`.

---

# 9. Vistas y layouts

Las vistas están en `views/`. Su función es mostrar los datos recibidos desde el controlador.

## 9.1 `views/Layouts`

Contiene el diseño general usado por varios módulos:

- `header.php`: estructura HTML, título, estilos, usuario y mensajes.
- `sidebar.php`: menú lateral según el rol.
- `footer.php`: cierre del contenido y scripts comunes.

El menú cambia según el rol. Un cliente no debería ver las opciones de administración, mientras que un administrador sí puede verlas.

## 9.2 `views/auth`

Contiene login, registro y recuperación de contraseña.

## 9.3 `views/compartido`

Contiene pantallas reutilizadas por diferentes roles, como dashboard, productos, inventario, pedidos y perfil.

## 9.4 `views/admin`

Contiene vistas de usuarios, categorías, pagos, promociones, proveedores y reportes administrativos.

## 9.5 `views/empleado`

Contiene las vistas del panel operativo:

- `dashboard.php`: resumen del empleado.
- `catalogo.php`: catálogo interno.
- `nueva_venta.php`: formulario POS.
- `historial.php`: historial de ventas.
- `factura.php`: comprobante.
- `stock.php`: consulta de inventario.
- `layouts/header.php`: layout propio del panel empleado.

### Nueva venta frente a ventas realizadas

Esta diferencia evita confusiones:

- `empleado&action=nuevaVenta` y `ventas&action=puntoVenta`: registrar una venta nueva.
- `empleado&action=historial` y `ventas&action=online`: ver ventas ya realizadas.

---

# 10. CSS y diseño

Los estilos principales están en `Styles/` y también existen recursos públicos en `public/assets/Styles/`.

## Archivos principales

- `main.css`: variables, layout general y estilos base.
- `components.css`: botones, tarjetas, tablas, alertas y componentes comunes.
- `auth.css`: login y registro.
- `dashboard.css`: panel principal.
- `empleado.css`: diseño del panel operativo.
- `ventas.css`: estilos relacionados con ventas y POS.
- `pedidos.css`: módulo de pedidos.
- `proveedores.css`: módulo de proveedores.
- `pagos.css`: módulo de pagos.

Un error común al modificar el POS es cargar solo `ventas.css` cuando la vista también usa clases que empiezan por `emp-`. Por eso el controlador del POS debe cargar tanto `empleado.css` como `ventas.css`.

---

# 11. JavaScript

Los scripts están en `scripts/` y existen copias públicas en `public/assets/scripts/`.

## `main.js`

Maneja comportamientos generales, como:

- Menú lateral.
- Cierre del menú en móvil.
- Iconos.
- Alertas o notificaciones.

## `auth.js`

Apoya las validaciones visuales del login y los formularios de autenticación.

## `dashboard.js`

Maneja animaciones o contadores del dashboard.

## `pos.js` y `punto_venta.js`

Apoyan la lógica del punto de venta:

- Agregar productos.
- Cambiar cantidades.
- Quitar productos.
- Calcular totales.
- Calcular cambio.
- Aplicar promociones.
- Buscar clientes.
- Filtrar productos.
- Mostrar mensajes.

La validación de JavaScript mejora la experiencia, pero la seguridad real debe estar en el servidor. Un usuario puede modificar el HTML o desactivar JavaScript, por eso el controlador y el modelo vuelven a validar cantidades, precios y stock.

---

# 12. Roles y permisos

El sistema maneja tres roles principales:

| Rol | Qué puede hacer |
|---|---|
| `admin` | Gestionar usuarios, productos, categorías, inventario, ventas, reportes, pagos y proveedores |
| `vendedor` | Registrar ventas, consultar productos, inventario, pedidos y proveedores según las reglas del controlador |
| `cliente` | Consultar catálogo, carrito, pedidos propios, promociones y perfil |

Los permisos se aplican principalmente con:

```php
requireLogin();
requireRole('admin');
```

o:

```php
requireRole('admin', 'vendedor');
```

Nunca se debe confiar solo en ocultar un botón. Aunque un enlace no aparezca, el controlador debe verificar el rol cuando alguien intente entrar directamente por URL.

---

# 13. Flujo completo de una venta

Este es uno de los flujos más importantes para estudiar.

## Paso 1: abrir el punto de venta

El usuario entra a:

```text
index.php?controller=empleado&action=nuevaVenta
```

o:

```text
index.php?controller=ventas&action=puntoVenta
```

El controlador verifica sesión y rol, consulta productos y muestra la vista.

## Paso 2: seleccionar productos

La vista muestra los productos disponibles. JavaScript mantiene un arreglo del carrito en memoria del navegador y limita la cantidad visualmente según el stock.

## Paso 3: enviar formulario

El formulario envía:

- Token CSRF.
- Cliente, si se seleccionó.
- Productos.
- Cantidades.
- Método de pago.
- Notas.
- Promoción aplicada.

## Paso 4: validar en servidor

El servidor comprueba:

- Que la solicitud sea POST.
- Que el token CSRF sea válido.
- Que exista al menos un producto.
- Que el método de pago sea permitido.
- Que cada producto sea válido.
- Que el producto esté activo.
- Que haya stock suficiente.

## Paso 5: guardar en una transacción

`Pedido::crear()` valida el precio actual en la base de datos, calcula el total, crea el pedido, guarda sus detalles y descuenta stock.

Si falla una parte, se hace rollback.

## Paso 6: generar comprobante

Después de guardar, el usuario es enviado a la factura del pedido.

## Paso 7: consultar historial

La venta aparece en el historial con cliente, total, estado, método de pago y fecha.

---

# 14. Flujo de inventario y proveedores

Cuando no hay stock, no se debería permitir una venta del producto agotado.

El proceso correcto es:

1. Revisar el inventario.
2. Crear un pedido a proveedor.
3. Confirmar el pedido.
4. Registrar el pago si corresponde.
5. Recibir el pedido.
6. Aumentar el stock.
7. Volver al punto de venta.

El inventario no se debe actualizar solamente cambiando una etiqueta en pantalla. La entrada debe quedar reflejada en la base de datos y, cuando aplique, en `movimientos_inventario`.

---

# 15. Seguridad básica del proyecto

## Contraseñas

Se protegen con `password_hash()` y se comprueban con `password_verify()`.

## Consultas preparadas

Los modelos usan `prepare()` y parámetros para evitar inyección SQL.

## CSRF

Los formularios importantes incluyen un token y los controladores lo verifican antes de guardar.

## Sesiones y roles

Las acciones privadas llaman a `requireLogin()` y `requireRole()`.

## Escape HTML

Los datos que se imprimen en una vista deben pasar por `e()` cuando vienen de formularios o de la base de datos.

## Soft delete

Algunos registros se desactivan con `activo = 0` en vez de borrarse permanentemente. Esto conserva la información histórica.

## Recomendaciones para producción

- No mostrar errores detallados al usuario final.
- No dejar scripts de creación o reparación abiertos.
- Cambiar credenciales por defecto.
- Usar HTTPS.
- Mover credenciales de base de datos a variables de entorno.
- Validar permisos en cada acción.
- Crear copias de seguridad de MySQL.

---

# 16. Cómo instalar y ejecutar localmente

## Requisitos

- Laragon.
- Apache o el servidor de PHP.
- MySQL.
- PHP compatible con el proyecto.
- Navegador web.

## Pasos generales

1. Colocar el proyecto dentro de `C:\laragon\www\central_box`.
2. Iniciar Apache y MySQL desde Laragon.
3. Crear la base de datos ejecutando `sql/central_box.sql`.
4. Revisar los datos de conexión en `config/database.php`.
5. Abrir el proyecto desde el navegador.

Ejemplo con Laragon:

```text
http://localhost/central_box/public/index.php?controller=auth&action=login
```

Si se usa el servidor integrado de PHP en el puerto 8000, la configuración detecta el host y usa rutas bajo `/assets`.

---

# 17. Pruebas y diagnóstico

La carpeta `tests/` contiene scripts de apoyo. Algunos sirven para:

- Probar el login.
- Crear datos de prueba.
- Revisar tablas.
- Crear tablas que falten.
- Revisar modelos.
- Preparar usuarios de prueba.
- Mostrar diagnósticos del dashboard.

Las pruebas actuales son scripts PHP sencillos y no necesariamente un framework automatizado. Antes de ejecutar una prueba conviene revisar sus rutas `require_once`, porque algunos archivos fueron creados para ejecutarse desde otra ubicación.

Una validación rápida de sintaxis se puede hacer con:

```text
php -l controllers/NombreController.php
php -l models/Nombre.php
php -l views/ruta/vista.php
```

En Laragon, si `php` no está disponible en el PATH, se puede utilizar la ruta completa del ejecutable PHP instalado.

---

# 18. Preguntas tipo examen

## ¿Cuál es el punto de entrada de la aplicación?

`public/index.php`. Es el Front Controller y decide qué controlador y acción ejecutar según la URL.

## ¿Qué diferencia hay entre modelo, vista y controlador?

El modelo consulta o modifica datos, la vista presenta la información y el controlador coordina la solicitud y las reglas del proceso.

## ¿Dónde se conecta el sistema a MySQL?

En `config/database.php`, mediante la clase `Database` y PDO.

## ¿Qué hace `requireRole()`?

Verifica que el usuario tenga uno de los roles autorizados para ejecutar una acción.

## ¿Por qué se usa una transacción al crear una venta?

Porque la venta afecta varias tablas y también el inventario. Si una operación falla, el rollback evita dejar datos incompletos.

## ¿Qué pasa si no hay stock suficiente?

El modelo detiene la operación y lanza un error. No debe crear una venta válida ni dejar el stock en valores negativos.

## ¿Qué diferencia hay entre el carrito y el pedido?

El carrito es una selección temporal del usuario. El pedido se crea cuando la venta o compra se confirma y se guarda en la base de datos.

## ¿Qué función tiene el token CSRF?

Ayuda a comprobar que la petición de un formulario proviene de una sesión válida de la aplicación.

## ¿Qué significa `activo = 0`?

Normalmente significa que el registro está desactivado, pero se conserva en la base de datos.

## ¿Qué roles existen?

`admin`, `vendedor` y `cliente`.

## ¿Qué módulo maneja las ventas?

El flujo se encuentra principalmente en `VentasController`, `EmpleadoController`, `Pedido.php`, `nueva_venta.php` e `historial.php`.

## ¿Qué debe hacer un administrador si no hay inventario?

Registrar productos con stock inicial o crear y recibir un pedido a proveedor, dependiendo de si todavía no existen productos o si los productos están agotados.

---

# 19. Resumen para memorizar

Si hubiera que explicar el proyecto rápidamente en un examen, se puede decir lo siguiente:

> central_box es una aplicación web de gestión para una tienda, desarrollada en PHP con una arquitectura MVC y una base de datos MySQL. `public/index.php` recibe las solicitudes y dirige cada una a un controlador. Los controladores aplican permisos y reglas, los modelos trabajan con la base de datos mediante PDO y las vistas muestran la información. El sistema maneja usuarios con roles de administrador, vendedor y cliente. Entre sus procesos principales están el catálogo, carrito, pedidos, pagos, inventario, proveedores y ventas. Para registrar una venta se valida el formulario, se comprueba el stock, se calcula el total, se guarda el pedido y sus detalles, se descuenta el inventario dentro de una transacción y finalmente se genera el comprobante.

La idea clave es que una venta no es solo un botón: involucra usuario, permisos, productos, cliente, pago, stock, pedido, detalle y comprobante.
