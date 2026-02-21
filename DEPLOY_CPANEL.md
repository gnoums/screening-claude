# Despliegue en cPanel — PsicoScreen

Guía paso a paso para desplegar en hosting compartido con cPanel + MySQL.

---

## Arquitectura objetivo

```
app.midominio.com  (subdominio cPanel)
    └── public_html/app/       ← Document Root del subdominio
         ├── index.php          ← symlink o copia de public/index.php
         └── .htaccess          ← rewrite rules de Laravel
/home/usuario/psicoscreen/     ← Código fuente de Laravel (FUERA de public_html)
    ├── app/
    ├── config/
    ├── ...
    └── public/ → mapeado al Document Root
```

> **Regla de oro:** El código fuente de Laravel **nunca** debe estar dentro de `public_html`.
> Solo el contenido de `public/` se expone al web.

---

## Checklist de despliegue

### 1. Preparar el servidor

- [ ] Verificar versión de PHP: **8.2+** (en cPanel → Software → PHP Selector)
- [ ] Habilitar extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `xml`, `gd`, `zip`
- [ ] Asegurarse de que `allow_url_fopen = On` y `display_errors = Off` en producción

### 2. Subir el código

```bash
# Opción A: Git (si el hosting soporta git vía SSH)
git clone https://github.com/tuusuario/psicoscreen.git /home/usuario/psicoscreen
cd /home/usuario/psicoscreen
git checkout main

# Opción B: FTP/SFTP
# Subir todos los archivos EXCEPTO /vendor a /home/usuario/psicoscreen/
# Luego ejecutar composer install en el servidor

composer install --optimize-autoloader --no-dev
```

### 3. Configurar el Document Root del subdominio

En cPanel → **Subdominios**, crear `app.midominio.com` con Document Root:
```
/home/usuario/psicoscreen/public
```

### 4. Configurar .htaccess

El archivo `public/.htaccess` de Laravel ya maneja el rewrite. Verificar que `mod_rewrite` esté activo. Si no, añadir en cPanel → Apache Handlers.

### 5. Configurar variables de entorno

```bash
cp .env.example .env
# Editar .env con los valores reales:
nano .env
```

Valores críticos a configurar:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.midominio.com

DB_CONNECTION=mysql
DB_HOST=localhost          # en cPanel casi siempre es localhost
DB_PORT=3306
DB_DATABASE=usuario_dbname # formato cPanel: usuario_nombre
DB_USERNAME=usuario_dbuser
DB_PASSWORD=tu_password_segura

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=mail.midominio.com
MAIL_PORT=465
MAIL_SCHEME=ssl
MAIL_USERNAME=noreply@midominio.com
MAIL_PASSWORD=password_del_correo
MAIL_FROM_ADDRESS=noreply@midominio.com
MAIL_FROM_NAME="PsicoScreen"

SCREENING_TOKEN_TTL_HOURS=72
```

### 6. Generar clave de aplicación

```bash
php artisan key:generate
```

### 7. Ejecutar migraciones y seeders

```bash
php artisan migrate --force
php artisan db:seed --class=AssessmentSeeder --force
```

### 8. Optimizar para producción

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> ⚠️ Después de cualquier cambio en `.env`, correr `php artisan config:clear && php artisan config:cache`

### 9. Configurar permisos de directorios

```bash
chmod -R 755 /home/usuario/psicoscreen
chmod -R 775 /home/usuario/psicoscreen/storage
chmod -R 775 /home/usuario/psicoscreen/bootstrap/cache
```

### 10. Configurar el Cron Job para colas (CRÍTICO)

En cPanel → **Cron Jobs**, agregar:

```
* * * * * /usr/bin/php /home/usuario/psicoscreen/artisan queue:work --stop-when-empty --max-jobs=20 --max-time=50 >> /home/usuario/psicoscreen/storage/logs/queue.log 2>&1
```

**Explicación:**
- `--stop-when-empty`: el proceso muere cuando no hay jobs (compatible con hosting sin workers persistentes)
- `--max-jobs=20`: límite de jobs por ejecución del cron
- `--max-time=50`: termina antes de los 60 segundos del cron siguiente
- El cron se ejecuta cada minuto, procesando jobs pendientes

**Limpieza de jobs fallidos** (cron adicional, diario a las 2am):
```
0 2 * * * /usr/bin/php /home/usuario/psicoscreen/artisan queue:prune-failed --hours=168 >> /dev/null 2>&1
```

**Expirar tokens vencidos** (cron adicional, cada hora):
```
0 * * * * /usr/bin/php /home/usuario/psicoscreen/artisan screening:expire-tokens >> /dev/null 2>&1
```

---

## Comandos de mantenimiento

```bash
# Ver jobs en cola
php artisan queue:monitor

# Ver jobs fallidos
php artisan queue:failed

# Reintentar jobs fallidos
php artisan queue:retry all

# Limpiar caché
php artisan cache:clear

# Entrar en modo mantenimiento
php artisan down --render="errors.503"

# Salir del modo mantenimiento
php artisan up
```

---

## Checklist de seguridad post-despliegue

- [ ] `APP_DEBUG=false` en producción
- [ ] `.env` no accesible vía web (verificar con `curl https://app.midominio.com/.env`)
- [ ] Directorio `storage/` no accesible vía web
- [ ] SSL activo (cPanel → SSL/TLS → Let's Encrypt)
- [ ] `SESSION_ENCRYPT=true` en producción
- [ ] Contraseña DB con al menos 16 caracteres y caracteres especiales
- [ ] Copia de seguridad automática de BD en cPanel habilitada

---

## Estructura de carpetas del proyecto

```
app/
├── Exceptions/
│   ├── InvalidTokenException.php   # Errores de token de paciente
│   └── ScoringException.php        # Errores de cálculo de puntaje
├── Http/
│   └── Controllers/
│       ├── Public/
│       │   └── ScreeningController.php  # Rutas públicas del paciente
│       └── Psychologist/
│           ├── DashboardController.php
│           ├── PatientController.php
│           ├── ReportController.php
│           └── ScreeningRequestController.php
├── Jobs/
│   └── SendScreeningEmailJob.php   # Job de cola para envío de email
├── Mail/
│   └── ScreeningInvitationMail.php
├── Models/
│   ├── Assessment.php
│   ├── AssessmentOption.php
│   ├── AssessmentQuestion.php
│   ├── AssessmentResponse.php
│   ├── AssessmentResponseAnswer.php
│   ├── AssessmentRule.php
│   ├── AuditLog.php
│   ├── CreditsLedger.php
│   ├── Patient.php
│   ├── ReportExport.php
│   ├── ScreeningRequest.php
│   ├── ScreeningRequestItem.php
│   ├── ScreeningToken.php
│   ├── User.php
│   └── UserProfile.php
└── Services/
    ├── CreditsService.php           # Libro mayor de créditos
    ├── PdfExportService.php         # Generación de PDF con DomPDF
    ├── ScreeningRequestService.php  # Orquestador de envío
    ├── ScoringService.php           # Cálculo de puntaje e interpretación
    └── TokenService.php             # Generación y validación de tokens

database/
├── migrations/                      # 14 migraciones en orden cronológico
└── seeders/
    ├── AssessmentSeeder.php         # PHQ-9 y GAD-7 con preguntas y reglas
    └── DatabaseSeeder.php

resources/views/
├── emails/
│   └── screening-invitation.blade.php
├── pdf/
│   └── screening-report.blade.php  # Plantilla del PDF
└── public/screening/
    ├── show.blade.php               # Formulario de tamizaje (móvil-friendly)
    ├── completed.blade.php
    └── invalid-token.blade.php

tests/
├── Feature/
│   ├── PublicScreeningTest.php      # Flujo completo del paciente
│   └── TokenServiceTest.php         # Seguridad de tokens
└── Unit/
    └── ScoringServiceTest.php       # PHQ-9 scoring y reglas
```

---

## Módulos del MVP

| Módulo | Estado | Descripción |
|--------|--------|-------------|
| Auth (Breeze) | ✅ | Login/registro de psicólogas con Blade |
| Gestión de pacientes | ✅ | CRUD con soft deletes |
| Catálogo de pruebas | ✅ | PHQ-9, GAD-7 sembrados |
| Envío de solicitudes | ✅ | Multi-prueba, créditos, email en cola |
| Token seguro | ✅ | Hash SHA-256, expiración, no reuse |
| Formulario paciente | ✅ | Móvil-friendly, sin cuenta |
| Motor de scoring | ✅ | Configurable por reglas en DB |
| Export PDF | ✅ | DomPDF + Blade, solo la dueña |
| Créditos | ✅ | Libro mayor, recarga, consumo |
| Auditoría | ✅ | IP, user-agent, eventos |
| Panel admin | ⏳ | Siguiente iteración |
| Stripe/pagos | ⏳ | Siguiente iteración |

---

## Roadmap de implementación por pasos

### Paso 1 — Base (completado en este MVP)
- [x] Laravel 11 + Breeze + Blade
- [x] Modelos y migraciones (14 tablas)
- [x] Flujo de token seguro
- [x] ScoringService con PHQ-9 y GAD-7
- [x] PDF con DomPDF
- [x] Colas con database driver (compatible cPanel)

### Paso 2 — Créditos y pagos
- [ ] Integrar Stripe Checkout para recarga de créditos
- [ ] Webhook de Stripe para confirmar pagos
- [ ] Panel de facturación de la psicóloga

### Paso 3 — Panel admin interno
- [ ] Gestión de usuarios y créditos
- [ ] Visualización de audit logs
- [ ] Gestión de pruebas/preguntas desde UI

### Paso 4 — Mejoras UX
- [ ] Recordatorios automáticos por email (scheduled tasks)
- [ ] Dashboard con gráficas de evolución del paciente
- [ ] Múltiples pruebas en un solo enlace (ya soportado en DB)
- [ ] Firma electrónica del consentimiento informado

### Paso 5 — Producción hardening
- [ ] Backup automático en S3/Backblaze
- [ ] Monitoreo de errores (Sentry o Flare)
- [ ] Rate limiting avanzado por usuario
- [ ] GDPR/LGPD compliance (retención y eliminación de datos)
