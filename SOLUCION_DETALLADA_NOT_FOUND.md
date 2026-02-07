# Solución Detallada: Error "Not Found" en Frontend

## Diagnóstico

El error `{"detail":"Not Found"}` indica que Laravel está devolviendo una respuesta JSON en lugar de HTML. Esto puede ocurrir cuando:

1. La petición se está enrutando incorrectamente a la API
2. El middleware está forzando respuestas JSON
3. Hay un problema con la configuración de excepciones

## Solución Implementada

He actualizado `bootstrap/app.php` para que solo devuelva JSON en rutas API:

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
        // Solo devolver JSON para rutas API
        return $request->is('api/*');
    });
})
```

## Pasos para Resolver

### 1. Limpiar Caché

```powershell
cd samaria-erp
php artisan optimize:clear
```

### 2. Reiniciar Servidor Laravel

Detén el servidor actual (Ctrl+C) y reinícialo:

```powershell
php artisan serve
```

### 3. Verificar que Vite Esté Corriendo

En otra terminal:

```powershell
cd samaria-erp
npm run dev
```

### 4. Acceder al Navegador

1. Abre tu navegador
2. Ve a: **http://localhost:8000**
3. Presiona `Ctrl + Shift + R` para recargar sin caché

### 5. Verificar en Herramientas de Desarrollador

1. Presiona `F12` para abrir las herramientas de desarrollador
2. Ve a la pestaña **Network**
3. Recarga la página
4. Verifica que la petición a `/` devuelva `Content-Type: text/html`

## Si Aún No Funciona

### Verificar Logs

```powershell
Get-Content storage/logs/laravel.log -Tail 50
```

### Probar Ruta Directamente

```powershell
# Debería devolver HTML, no JSON
Invoke-WebRequest -Uri "http://localhost:8000" -UseBasicParsing | Select-Object StatusCode, @{Name='ContentType';Expression={$_.Headers.'Content-Type'}}
```

### Verificar que Inertia Esté Instalado

```powershell
composer show | Select-String -Pattern "inertia"
```

Deberías ver: `inertiajs/inertia-laravel`

### Compilar Assets Manualmente

Si Vite no funciona:

```powershell
npm run build
```

Luego accede a http://localhost:8000

## Configuración Correcta

Asegúrate de que:

1. ✅ **Rutas web configuradas** - `routes/web.php` tiene la ruta `/`
2. ✅ **Middleware de Inertia** - Configurado en `bootstrap/app.php`
3. ✅ **Vista app.blade.php** - Existe y tiene `@inertia`
4. ✅ **Páginas Vue** - `Login.vue` y `Dashboard.vue` existen
5. ✅ **Vite corriendo** - `npm run dev` está activo
6. ✅ **Servidor Laravel** - `php artisan serve` está corriendo

## Verificación Final

Después de seguir los pasos, deberías ver:

- ✅ Página de Login renderizada (no JSON)
- ✅ Sin errores en la consola del navegador
- ✅ Assets cargándose desde Vite (puerto 5174)
