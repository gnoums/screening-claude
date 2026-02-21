# Despliegue en cPanel — PsicoScreen
**Dominio destino:** `https://app.dev2byroca.com`

---

## Arquitectura

```
app.dev2byroca.com
  └── Document Root → /home/tu_usuario/psicoscreen/public

/home/tu_usuario/psicoscreen/    ← código Laravel (FUERA de public_html)
```

> El código fuente de Laravel **nunca** debe estar dentro de `public_html`.
> Solo el contenido de `public/` se expone al web.

---

## Checklist de despliegue

### 1. Requisitos del servidor

En cPanel → Software → PHP Selector:
- [ ] PHP **8.2 o superior**
- [ ] Extensiones activas: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `gd`, `zip`, `curl`
- [ ] `allow_url_fopen = On`, `display_errors = Off`

### 2. Crear la base de datos MySQL

En cPanel → **MySQL Databases**:
1. Crear base de datos: `tu_usuario_psicoscreen`
2. Crear usuario: `tu_usuario_dbuser` con contraseña segura (16+ caracteres)
3. Asignar usuario a la base de datos con **All Privileges**

### 3. Subir el código fuente

**Opción A — Git via SSH (recomendado)**
```bash
ssh tu_usuario@dev2byroca.com
git clone https://github.com/tuusuario/screening-claude.git /home/tu_usuario/psicoscreen
cd /home/tu_usuario/psicoscreen
git checkout main
composer install --optimize-autoloader --no-dev
```

**Opción B — FTP/SFTP**
- Subir todos los archivos EXCEPTO `/vendor` y `/node_modules`
- Conectar por SSH y ejecutar: `composer install --optimize-autoloader --no-dev`

### 4. Subir los assets compilados del frontend

Los assets CSS/JS compilados por Vite están en `public/build/` (ignorado en git).

**Compilar en local y subir por FTP:**
```bash
# En tu máquina local, dentro del proyecto:
npm install && npm run build
# Luego subir por FTP la carpeta public/build/ al servidor en:
# /home/tu_usuario/psicoscreen/public/build/
```

### 5. Configurar el Document Root del subdominio

En cPanel → **Subdominios** o **Addon Domains**:
- Subdominio: `app`
- Dominio: `dev2byroca.com`
- **Document Root**: `/home/tu_usuario/psicoscreen/public`

### 6. Configurar variables de entorno

```bash
cd /home/tu_usuario/psicoscreen
cp .env.example .env
nano .env
```

Variables críticas a llenar:
```dotenv
APP_URL=https://app.dev2byroca.com
APP_KEY=                          # se genera en el paso 7

DB_DATABASE=tu_usuario_psicoscreen
DB_USERNAME=tu_usuario_dbuser
DB_PASSWORD=tu_password_segura

MAIL_USERNAME=noreply@dev2byroca.com
MAIL_PASSWORD=contraseña_de_aplicacion_zoho

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_CREDITS_10=price_...
STRIPE_PRICE_CREDITS_30=price_...
STRIPE_PRICE_CREDITS_100=price_...
```

### 7. Generar clave de aplicación

```bash
php artisan key:generate
```

### 8. Ejecutar migraciones y seeders

```bash
php artisan migrate --force
php artisan db:seed --class=AssessmentSeeder --force
```

> Crea las 14 tablas y siembra PHQ-9 y GAD-7 con sus preguntas y reglas de interpretación.

### 9. Optimizar para producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link
```

> ⚠️ Después de cualquier cambio en `.env`: `php artisan config:clear && php artisan config:cache`

### 10. Permisos de directorios

```bash
chmod -R 755 /home/tu_usuario/psicoscreen
chmod -R 775 /home/tu_usuario/psicoscreen/storage
chmod -R 775 /home/tu_usuario/psicoscreen/bootstrap/cache
```

### 11. Cron Jobs en cPanel

En cPanel → **Cron Jobs**, agregar estas 3 tareas:

**Procesador de colas de email** (cada minuto):
```
* * * * * /usr/bin/php /home/tu_usuario/psicoscreen/artisan queue:work --stop-when-empty --max-jobs=20 --max-time=50 >> /home/tu_usuario/psicoscreen/storage/logs/queue.log 2>&1
```

**Expirar tokens vencidos** (cada hora):
```
0 * * * * /usr/bin/php /home/tu_usuario/psicoscreen/artisan screening:expire-tokens >> /dev/null 2>&1
```

**Limpiar jobs fallidos** (diario a las 2am):
```
0 2 * * * /usr/bin/php /home/tu_usuario/psicoscreen/artisan queue:prune-failed --hours=168 >> /dev/null 2>&1
```

> Si no sabes la ruta de PHP en el servidor: `which php`

### 12. Configurar SSL

En cPanel → **SSL/TLS** → Let's Encrypt:
- [ ] Emitir certificado para `app.dev2byroca.com`
- [ ] Habilitar **Force HTTPS Redirect**

### 13. Configurar Stripe Webhook

En el [Dashboard de Stripe](https://dashboard.stripe.com/webhooks):
1. **Add endpoint** → URL: `https://app.dev2byroca.com/stripe/webhook`
2. Eventos a escuchar: `checkout.session.completed`
3. Copiar el **Signing secret** (`whsec_...`) → pegar en `.env` como `STRIPE_WEBHOOK_SECRET`

---

## Configurar Zoho Mail (SMTP)

1. En [Zoho Mail Admin Console](https://mailadmin.zoho.com):
   - Verificar el dominio `dev2byroca.com` (registro TXT en tu DNS)
   - Crear dirección: `noreply@dev2byroca.com`
2. En la cuenta Zoho → **Seguridad** → **Contraseñas de aplicación**:
   - Crear contraseña para "PsicoScreen SMTP"
   - Usar esa contraseña (no la de tu cuenta Zoho) en `MAIL_PASSWORD`
3. Registros DNS recomendados para evitar spam:
   ```
   SPF:   v=spf1 include:zoho.com ~all
   DKIM:  generado en Zoho Admin Console → Email Authentication
   DMARC: v=DMARC1; p=none; rua=mailto:noreply@dev2byroca.com
   ```

---

## Crear productos en Stripe

En Stripe Dashboard → **Products** → **Add product**:

| Producto | Precio | Tipo | Variable .env |
|----------|--------|------|---------------|
| 10 créditos PsicoScreen | $199 MXN | One-time | `STRIPE_PRICE_CREDITS_10` |
| 30 créditos PsicoScreen | $499 MXN | One-time | `STRIPE_PRICE_CREDITS_30` |
| 100 créditos PsicoScreen | $1,499 MXN | One-time | `STRIPE_PRICE_CREDITS_100` |

Configurar moneda como **MXN**: Stripe Dashboard → Settings → Business settings.

---

## Crear cuenta de administrador

Regístrate como usuario normal en la app y luego promoverte a admin:

```bash
php artisan tinker
>>> \App\Models\User::where('email','tu@correo.com')->update(['role' => 'admin']);
```

---

## Checklist final antes de ir a producción

- [ ] `APP_DEBUG=false` en `.env`
- [ ] `APP_ENV=production` en `.env`
- [ ] SSL activo y Force HTTPS habilitado
- [ ] `.env` no accesible vía web: `curl https://app.dev2byroca.com/.env` debe retornar 404
- [ ] Verificar email: `php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('tu@correo.com')->subject('test'));`
- [ ] Stripe en modo **live** (claves `pk_live_` / `sk_live_`)
- [ ] Webhook de Stripe configurado y verificado (evento de prueba desde Dashboard)
- [ ] Los 3 Cron Jobs activos
- [ ] Backups automáticos de BD activados en cPanel
- [ ] `SESSION_SECURE_COOKIE=true` con SSL activo

---

## Comandos de mantenimiento post-deploy

```bash
# Ver jobs en cola
php artisan queue:monitor

# Ver jobs fallidos
php artisan queue:failed

# Reintentar jobs fallidos
php artisan queue:retry all

# Limpiar caché tras cambios de código
php artisan config:clear && php artisan cache:clear && php artisan view:clear

# Modo mantenimiento
php artisan down --render="errors.503"
php artisan up

# Actualizar código (tras git pull)
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
