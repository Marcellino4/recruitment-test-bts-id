#!/bin/sh
set -e

# Run migrations
php /var/www/html/artisan migrate --force

# Run seeders only if DB is empty (cek dari tabel users)
USER_COUNT=$(php /var/www/html/artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1)
if [ "$USER_COUNT" = "0" ]; then
    echo "Database kosong, menjalankan seeder..."
    php /var/www/html/artisan db:seed --force
else
    echo "Database sudah ada data, skip seeder."
fi

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
