# Instrucciones para Resolver el Error de Node.js

## Problema Resuelto

He actualizado `package.json` para usar versiones compatibles con Node.js 18:
- Vite: `^5.4.0` (compatible con Node.js 18)
- Laravel Vite Plugin: `^1.0.0` (compatible)

## Estado Actual

✅ Las dependencias se han actualizado automáticamente.

## Próximos Pasos

### 1. Verificar que las dependencias estén instaladas

```powershell
cd samaria-erp
npm install
```

### 2. Iniciar el servidor de desarrollo

**Terminal 1 - Laravel:**
```powershell
cd samaria-erp
php artisan serve
```

**Terminal 2 - Vite:**
```powershell
cd samaria-erp
npm run dev
```

### 3. Acceder a la aplicación

- Frontend: `http://localhost:8000`
- API: `http://localhost:8000/api`

## Si Aún Tienes Problemas

### Opción A: Actualizar Node.js (Recomendado a largo plazo)

1. Descarga Node.js 20 LTS desde: https://nodejs.org/
2. Instala y reinicia PowerShell
3. Verifica: `node --version` (debería ser v20.x o superior)

### Opción B: Usar Build en lugar de Dev

Si `npm run dev` no funciona, puedes compilar los assets:

```powershell
cd samaria-erp
npm run build
```

Esto compilará los assets una vez. Para ver cambios, necesitarás ejecutar `npm run build` nuevamente.

## Nota

La versión actual de Vite (5.4.0) es compatible con Node.js 18, pero se recomienda actualizar a Node.js 20 LTS para mejor rendimiento y compatibilidad futura.
