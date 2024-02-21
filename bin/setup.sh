#!/bin/bash

echo "➡️ Reset DB ?"
if [ -f /app/wordpress/wp-config.php ]; then echo "wp-config exists reset DB" && wp db reset --yes --path=/app/wordpress; else echo "No wp-config.php, no reset"; fi
echo "➡️ Reset config"
if [ -f /app/wordpress/wp-config.php ]; then echo "wp-config exists deleting it" && rm -f /app/wordpress/wp-config.php; else echo "No wp-config.php, no rm"; fi
echo "➡️ Create config"
wp config create --dbname=wordpress-test --dbuser=wordpress-test --dbpass=wordpress-test --dbhost=database-test --path=/app/wordpress
echo "➡️ Save Woocommerce logs in database during tests"
wp config set WC_LOG_HANDLER 'WC_Log_Handler_DB'
echo "➡️ Install WP"
wp core install --url="https://shoppingfeed-for-woocommerce.lndo.site" --title="Test" --admin_user=admin --admin_password=password --admin_email=wordpress@example.com --skip-email
echo "➡️ Create WPCONTENT"
if [ ! -f /app/wordpress/wp-content ]; then echo "creating wp-content folder" && mkdir -p /app/wordpress/wp-content/plugins; else echo "wp-content folder already created"; fi
ln -s /app/ /app/wordpress/wp-content/plugins/shoppingfeed-for-woocommerce
echo "➡️ Install Woocommerce"
wp plugin install woocommerce
echo "➡️ Install Storefront"
wp theme install storefront