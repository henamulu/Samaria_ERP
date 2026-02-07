# Solución: Error de Versión de Node.js

## Problema
```
You are using Node.js 18.19.0. Vite requires Node.js version 20.19+ or 22.12+.
TypeError: crypto.hash is not a function
```

## Soluciones

### Opción 1: Actualizar Node.js (Recomendado)

#### Usando nvm (Node Version Manager) - Si lo tienes instalado:

```powershell
# Ver versiones disponibles
nvm list available

# Instalar Node.js 20 LTS
nvm install 20.19.0

# O instalar Node.js 22 LTS
nvm install 22.12.0

# Usar la versión instalada
nvm use 20.19.0
```

#### Descargar e Instalar Manualmente:

1. Ve a: https://nodejs.org/
2. Descarga la versión **LTS (Long Term Support)** - actualmente Node.js 20.x o 22.x
3. Instala el ejecutable
4. Reinicia PowerShell/Terminal
5. Verifica la versión:
   ```powershell
   node --version
   ```

### Opción 2: Usar Versión Compatible de Vite (Alternativa)

Si no puedes actualizar Node.js, puedes usar una versión anterior de Vite compatible con Node.js 18:

```powershell
cd samaria-erp
npm install vite@^5.4.0 --save-dev
npm install laravel-vite-plugin@^1.0 --save-dev
```

Luego intenta de nuevo:
```powershell
npm run dev
```

### Opción 3: Usar Build en lugar de Dev (Temporal)

Si solo necesitas compilar los assets una vez:

```powershell
cd samaria-erp
npm run build
```

Esto compilará los assets y podrás usar la aplicación, aunque no tendrás hot-reload.

## Verificar Instalación

Después de actualizar Node.js:

```powershell
node --version
npm --version
```

Deberías ver:
- Node.js: v20.19.0 o superior (o v22.12.0+)
- npm: versión compatible

## Continuar con el Desarrollo

Una vez resuelto, ejecuta:

```powershell
# Terminal 1 - Laravel
cd samaria-erp
php artisan serve

# Terminal 2 - Vite
cd samaria-erp
npm run dev
```

## Nota

La versión de Node.js 18.19.0 es antigua y no es compatible con las últimas versiones de Vite. Se recomienda actualizar a Node.js 20 LTS o 22 LTS para mejor compatibilidad y seguridad.
