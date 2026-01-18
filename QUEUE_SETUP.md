# Queue Worker Setup for Production

## Option 1: Supervisor (Linux)

Create file: `/etc/supervisor/conf.d/internalos-worker.conf`

```ini
[program:internalos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/internalos/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/internalos/storage/logs/worker.log
stopwaitsecs=3600
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start internalos-worker:*
```

## Option 2: Windows Service (Production on Windows)

Use NSSM (Non-Sucking Service Manager):

1. Download NSSM from https://nssm.cc/download
2. Run as administrator:
```cmd
nssm install InternalOSQueue
```

3. Configure:
   - Path: `C:\laragon\bin\php\php-8.x\php.exe`
   - Arguments: `C:\laragon\www\internalos\artisan queue:work --sleep=3 --tries=3`
   - Startup directory: `C:\laragon\www\internalos`

4. Start service:
```cmd
nssm start InternalOSQueue
```

## Option 3: Laravel Horizon (Advanced)

For Redis queues with monitoring dashboard:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

Access dashboard at: `/horizon`

## Development (Current Setup)

Keep the queue worker running during development:

```bash
php artisan queue:work --tries=3
```

Or use queue:listen for auto-reload:
```bash
php artisan queue:listen
```

## Monitoring

Check queue status:
```bash
php artisan queue:monitor database
```

View failed jobs:
```bash
php artisan queue:failed
```

Retry failed jobs:
```bash
php artisan queue:retry all
```

Clear failed jobs:
```bash
php artisan queue:flush
```

## Queue Configuration

Your queue is configured in `.env`:
```env
QUEUE_CONNECTION=database
```

Jobs are stored in the `jobs` table. Failed jobs in `failed_jobs` table.

## Testing Queue

To test without queue worker (sync mode):
```env
QUEUE_CONNECTION=sync
```

This processes jobs immediately (useful for debugging).
