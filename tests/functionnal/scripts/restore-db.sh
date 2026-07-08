#!/bin/bash
set -e

# Script to restore database snapshot
# Usage: ./restore-db.sh [version]
# Example: ./restore-db.sh 8

VERSION=${1:-8}

echo "========================================="
echo "Restoring PrestaShop Database Snapshot"
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

# Check if snapshot exists
SNAPSHOT_PATH="./snapshots/${SNAPSHOT_FILE}"
if [ ! -f "$SNAPSHOT_PATH" ]; then
    echo "Error: Snapshot file not found: $SNAPSHOT_PATH"
    echo ""
    echo "Please create the snapshot first by running:"
    echo "  ./scripts/init-prestashop.sh ${VERSION}"
    exit 1
fi

# Ensure MySQL is running
echo "Checking MySQL container..."
if ! docker-compose ps mysql | grep -q "Up"; then
    echo "Starting MySQL container..."
    docker-compose --env-file $ENV_FILE up -d mysql

    echo "Waiting for MySQL to be ready..."
    sleep 10
    until docker-compose exec -T mysql mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} --silent; do
        echo "MySQL is unavailable - sleeping"
        sleep 5
    done
fi

echo "Dropping and recreating database..."
docker-compose exec -T mysql mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "
    DROP DATABASE IF EXISTS ${MYSQL_DATABASE};
    CREATE DATABASE ${MYSQL_DATABASE};
"

echo "Restoring database from snapshot..."
docker-compose exec -T mysql mysql -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} < "$SNAPSHOT_PATH"

echo "========================================="
echo "Database restored successfully!"
echo "========================================="

# Restart PrestaShop container to clear cache
if docker-compose ps prestashop | grep -q "Up"; then
    echo "Restarting PrestaShop container..."
    docker-compose restart prestashop

    echo "Waiting for PrestaShop to be ready..."
    sleep 10
    until docker-compose exec -T prestashop curl -f http://localhost/ > /dev/null 2>&1; do
        echo "PrestaShop is starting - sleeping"
        sleep 5
    done

    echo "PrestaShop is ready!"
fi

echo ""
echo "You can now run tests with: ./scripts/test-local.sh ${VERSION}"
