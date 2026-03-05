# Despliegue en cPanel — PsicoScreen
**Dominio destino:** `https://app.dev2byroca.com`

---

## Stack de herramientas

| Capa | Herramienta |
|------|-------------|
| Backend | Laravel 11, PHP 8.2+ |
| Frontend | Blade + Tailwind CSS, compilado con Vite |
| Base de datos | MySQL (cPanel) |
| PDF | barryvdh/laravel-dompdf |
| Email transaccional | **Resend** (`resend.com`) |
| Pagos | **Stripe** (paquetes de créditos one-time) |
| Hosting | **cPanel** (shared/VPS) |
| SSL | Let's Encrypt vía cPanel |

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
APP_NAME="PsicoScreen"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.dev2byroca.com
APP_KEY=                          # se genera en el paso 7

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_usuario_psicoscreen
DB_USERNAME=tu_usuario_dbuser
DB_PASSWORD=tu_password_segura

# Email — Resend
MAIL_MAILER=resend
RESEND_KEY=re_xxxxxxxxxxxxxxxxxxxx
MAIL_FROM_ADDRESS="noreply@dev2byroca.com"
MAIL_FROM_NAME="PsicoScreen"

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_STARTER=price_...       # 5 créditos  – $19.90 USD
STRIPE_PRICE_PROFESSIONAL=price_...  # 15 créditos – $49.90 USD
STRIPE_PRICE_CLINIC=price_...        # 35 créditos – $99.90 USD

SESSION_SECURE_COOKIE=true
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

> Crea las tablas y siembra PHQ-9 y GAD-7 con sus preguntas y reglas de interpretación.

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

## Configurar Resend (email transaccional)

Resend es el servicio de email que usa la app para enviar los links de tamizaje a los pacientes.

1. Crear cuenta en [resend.com](https://resend.com)
2. En el dashboard → **Domains** → **Add Domain**:
   - Agregar `dev2byroca.com`
   - Resend genera los registros DNS (MX, SPF, DKIM) que debes agregar en tu panel de DNS
   - Verificar el dominio (botón "Verify DNS Records")
3. En **API Keys** → **Create API Key**:
   - Nombre: `psicoscreen-production`
   - Permiso: `Sending access`
   - Copiar la clave `re_xxxx...` → pegar en `.env` como `RESEND_KEY`
4. El `MAIL_FROM_ADDRESS` debe usar el dominio verificado (`noreply@dev2byroca.com`)

> Laravel 11 incluye soporte nativo para Resend — no requiere paquetes adicionales.

---

## Crear productos en Stripe

En Stripe Dashboard → **Products** → **Add product** (crear uno por paquete):

| Paquete | Créditos | Precio | Variable `.env` |
|---------|----------|--------|-----------------|
| Starter | 5 | $19.90 USD | `STRIPE_PRICE_STARTER` |
| Professional | 15 | $49.90 USD | `STRIPE_PRICE_PROFESSIONAL` |
| Clinic / Team | 35 | $99.90 USD | `STRIPE_PRICE_CLINIC` |

- Tipo de precio: **One-time** (no recurrente)
- Copiar el `Price ID` (`price_...`) de cada producto al `.env`

---

## Crear cuenta de administrador

Regístrate como usuario normal en la app y luego promuévete a admin:

```bash
php artisan tinker
>>> \App\Models\User::where('email','tu@correo.com')->update(['role' => 'admin']);
```

---

## Checklist final antes de ir a producción

- [ ] `APP_DEBUG=false` en `.env`
- [ ] `APP_ENV=production` en `.env`
- [ ] `SESSION_SECURE_COOKIE=true` en `.env`
- [ ] SSL activo y Force HTTPS habilitado
- [ ] `.env` no accesible vía web: `curl https://app.dev2byroca.com/.env` debe retornar 404
- [ ] Verificar email con Resend: enviar un tamizaje de prueba y confirmar que llega
- [ ] Dominio verificado en Resend (DNS en verde)
- [ ] Stripe en modo **live** (claves `pk_live_` / `sk_live_`)
- [ ] Webhook de Stripe configurado y verificado (usar "Send test event" desde el Dashboard)
- [ ] Los 3 Cron Jobs activos en cPanel
- [ ] Backups automáticos de BD activados en cPanel

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
