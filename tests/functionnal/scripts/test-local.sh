#!/bin/bash
set -e

# Script to run E2E tests locally with Docker
# Usage: ./test-local.sh [version] [options]
# Example: ./test-local.sh 8
# Example: ./test-local.sh 8 --headed
# Example: ./test-local.sh 8 --debug

VERSION=${1:-8}
shift || true  # Remove first argument (version) from $@

echo "========================================="
echo "Running E2E Tests"
echo "PrestaShop Version: ${VERSION}"
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
    echo "Usage: $0 [1.7.8|8|9] [playwright-options]"
    exit 1
    ;;
esac

if [ ! -f "$ENV_FILE" ]; then
    echo "Error: Environment file $ENV_FILE not found"
    exit 1
fi

echo "Loading environment from $ENV_FILE..."
export $(grep -v '^#' $ENV_FILE | xargs)

# Start PrestaShop and MySQL
echo "Starting Docker containers..."
docker-compose --env-file $ENV_FILE up -d mysql prestashop

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until docker-compose exec -T mysql mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} --silent 2>/dev/null; do
    echo "MySQL is unavailable - sleeping"
    sleep 5
done
echo "MySQL is ready!"

# Wait for PrestaShop to be ready
echo "Waiting for PrestaShop to be ready..."
sleep 10
MAX_ATTEMPTS=30
ATTEMPT=0
until docker-compose exec -T prestashop curl -f http://localhost/ > /dev/null 2>&1; do
    ATTEMPT=$((ATTEMPT + 1))
    if [ $ATTEMPT -ge $MAX_ATTEMPTS ]; then
        echo "Error: PrestaShop failed to start after ${MAX_ATTEMPTS} attempts"
        docker-compose logs prestashop
        exit 1
    fi
    echo "PrestaShop is starting (attempt ${ATTEMPT}/${MAX_ATTEMPTS}) - sleeping"
    sleep 10
done
echo "PrestaShop is ready!"

# Check if node_modules exists, if not install dependencies
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# Install Playwright browsers if not already installed
echo "Checking Playwright browsers..."
npx playwright install firefox || echo "Playwright browsers already installed"

# Run tests from host machine (not in Docker for better debugging)
echo ""
echo "========================================="
echo "Running Playwright tests..."
echo "========================================="

# Pass additional arguments to playwright
npx playwright test --workers=1 "$@"

TEST_EXIT_CODE=$?

echo ""
echo "========================================="
echo "Test Results"
echo "========================================="

if [ $TEST_EXIT_CODE -eq 0 ]; then
    echo "✓ All tests passed!"
else
    echo "✗ Some tests failed (exit code: $TEST_EXIT_CODE)"
    echo ""
    echo "To view the test report, run:"
    echo "  npx playwright show-report"
fi

echo ""
echo "To stop the containers, run:"
echo "  docker-compose down"
echo ""
echo "To view logs:"
echo "  docker-compose logs prestashop"
echo "  docker-compose logs mysql"

exit $TEST_EXIT_CODE
