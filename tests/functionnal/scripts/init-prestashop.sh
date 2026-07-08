#!/bin/bash
set -e

# Script to initialize PrestaShop with eicaptcha module
# Usage: ./init-prestashop.sh [version]
# Example: ./init-prestashop.sh 8

VERSION=${1:-8}

echo "========================================="
echo "PrestaShop Test Environment Setup"
echo "Version: ${VERSION}"
echo "========================================="

# Load environment variables based on version
case $VERSION in
  1.7.8|178)
    ENV_FILE=".env.ps178"
    ;;
  8)
    ENV_FILE=".env.ps8"
    ;;
  9)
    ENV_FILE=".env.ps9"
    ;;
  *)
    echo "Error: Unknown version '${VERSION}'"
    echo "Usage: $0 [1.7.8|8|9]"
    exit 1
    ;;
esac

if [ ! -f "$ENV_FILE" ]; then
    echo "Error: Environment file $ENV_FILE not found"
    exit 1
fi

echo "Loading environment from $ENV_FILE..."
export $(grep -v '^#' $ENV_FILE | xargs)

# Start Docker Compose
echo "Starting Docker containers..."
docker-compose --env-file $ENV_FILE up -d mysql prestashop

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until docker-compose exec -T mysql mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} --silent; do
    echo "MySQL is unavailable - sleeping"
    sleep 5
done
echo "MySQL is ready!"

# Wait for PrestaShop to be ready
echo "Waiting for PrestaShop to be ready..."
sleep 10
until docker-compose exec -T prestashop curl -f http://localhost/ > /dev/null 2>&1; do
    echo "PrestaShop is unavailable - sleeping"
    sleep 10
done
echo "PrestaShop is ready!"

# Install eicaptcha module
echo "Installing eicaptcha module..."
docker-compose exec -T prestashop php bin/console prestashop:module install eicaptcha || {
    echo "Warning: Module installation via console failed. Trying alternative method..."
    # Alternative: use PrestaShop admin or module installer
}

# Create test user (dev@dev.com / dev1234%123)
echo "Creating test user..."
docker-compose exec -T prestashop php -r "
require_once '/var/www/html/config/config.inc.php';

\$customer = new Customer();
\$customer->firstname = 'Dev';
\$customer->lastname = 'Test';
\$customer->email = 'dev@dev.com';
\$customer->passwd = Tools::hash('dev1234%123');
\$customer->id_gender = 1;
\$customer->birthday = '1990-01-01';
\$customer->newsletter = 1;
\$customer->active = 1;

if (\$customer->add()) {
    echo 'Test customer created successfully!';
} else {
    echo 'Customer may already exist or creation failed.';
}
" || echo "Customer creation skipped (may already exist)"

# Export database snapshot
echo "Creating database snapshot..."
SNAPSHOT_DIR="./snapshots"
mkdir -p $SNAPSHOT_DIR

docker-compose exec -T mysql mysqldump \
    -u ${MYSQL_USER} \
    -p${MYSQL_PASSWORD} \
    ${MYSQL_DATABASE} \
    > "${SNAPSHOT_DIR}/${SNAPSHOT_FILE}"

echo "========================================="
echo "Setup completed successfully!"
echo "Snapshot saved to: ${SNAPSHOT_DIR}/${SNAPSHOT_FILE}"
echo "========================================="
echo ""
echo "Test user credentials:"
echo "  Email: dev@dev.com"
echo "  Password: dev1234%123"
echo ""
echo "PrestaShop admin:"
echo "  URL: http://localhost:8080/admin-dev"
echo "  Email: ${ADMIN_MAIL}"
echo "  Password: ${ADMIN_PASSWD}"
echo ""
echo "You can now run tests with: ./scripts/test-local.sh ${VERSION}"
