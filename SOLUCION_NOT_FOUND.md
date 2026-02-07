# Solución: Error "Not Found" en Frontend

## Problema
Al acceder a `http://localhost:8000` se muestra: `{"detail":"Not Found"}`

## Causas Posibles

1. **El servidor Laravel no está corriendo**
2. **Vite no está compilando los assets correctamente**
3. **Las páginas Vue no existen o tienen errores**
4. **Problema con la configuración de Inertia**

## Soluciones

### 1. Verificar que el Servidor Laravel Esté Corriendo

En una terminal, ejecuta:
```powershell
cd samaria-erp
php artisan serve
```

Deberías ver:
```
INFO  Server running on [http://127.0.0.1:8000]
```

### 2. Verificar que Vite Esté Corriendo

En otra terminal, ejecuta:
```powershell
cd samaria-erp
npm run dev
```

Deberías ver:
```
VITE v5.x.x  ready in xxx ms
➜  Local:   http://localhost:5174/
```

### 3. Verificar que las Páginas Vue Existan

Asegúrate de que existan estos archivos:
- `resources/js/Pages/Login.vue`
- `resources/js/Pages/Dashboard.vue`

### 4. Limpiar Caché

```powershell
cd samaria-erp
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 5. Verificar Rutas

```powershell
php artisan route:list
```

Deberías ver la ruta `GET /` que apunta a `login`

### 6. Verificar que los Assets Estén Compilados

Si Vite no está corriendo, compila los assets:
```powershell
npm run build
```

### 7. Verificar Logs de Laravel

Si hay errores, revisa los logs:
```powershell
# Los logs están en: storage/logs/laravel.log
Get-Content storage/logs/laravel.log -Tail 50
```

## Verificación Rápida

1. **Servidor Laravel corriendo:**
   ```powershell
   php artisan serve
   ```

2. **Vite corriendo:**
   ```powershell
   npm run dev
   ```

3. **Acceder a:**
   - http://localhost:8000 (debería mostrar Login)
   - http://localhost:8000/dashboard (debería mostrar Dashboard)

## Si el Problema Persiste

1. **Verifica que no haya errores en la consola del navegador** (F12)
2. **Verifica que Vite esté sirviendo los assets correctamente**
3. **Revisa los logs de Laravel** para ver errores específicos
4. **Asegúrate de que las páginas Vue no tengan errores de sintaxis**

## Comandos Útiles

```powershell
# Limpiar todo
php artisan optimize:clear

# Ver rutas
php artisan route:list

# Verificar configuración
php artisan config:show

# Compilar assets (si Vite no está corriendo)
npm run build
```
