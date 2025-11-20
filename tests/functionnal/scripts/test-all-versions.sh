#!/bin/bash
set -e

# Script to run E2E tests on all PrestaShop versions
# Usage: ./test-all-versions.sh [playwright-options]
# Example: ./test-all-versions.sh
# Example: ./test-all-versions.sh --headed

echo "========================================="
echo "Running E2E Tests on All Versions"
echo "========================================="

VERSIONS=("1.7.8" "8" "9")
FAILED_VERSIONS=()
PASSED_VERSIONS=()

# Store additional playwright arguments
PLAYWRIGHT_ARGS="$@"

for VERSION in "${VERSIONS[@]}"; do
    echo ""
    echo "========================================="
    echo "Testing PrestaShop ${VERSION}"
    echo "========================================="

    # Stop any running containers first
    docker-compose down 2>/dev/null || true

    # Run tests for this version
    if ./scripts/test-local.sh $VERSION $PLAYWRIGHT_ARGS; then
        PASSED_VERSIONS+=("$VERSION")
        echo "✓ PrestaShop ${VERSION} tests PASSED"
    else
        FAILED_VERSIONS+=("$VERSION")
        echo "✗ PrestaShop ${VERSION} tests FAILED"
    fi

    # Clean up containers after each version
    echo "Cleaning up containers..."
    docker-compose down 2>/dev/null || true
    sleep 5
done

# Summary
echo ""
echo "========================================="
echo "Test Summary"
echo "========================================="

if [ ${#PASSED_VERSIONS[@]} -gt 0 ]; then
    echo ""
    echo "✓ PASSED (${#PASSED_VERSIONS[@]} versions):"
    for VERSION in "${PASSED_VERSIONS[@]}"; do
        echo "  - PrestaShop ${VERSION}"
    done
fi

if [ ${#FAILED_VERSIONS[@]} -gt 0 ]; then
    echo ""
    echo "✗ FAILED (${#FAILED_VERSIONS[@]} versions):"
    for VERSION in "${FAILED_VERSIONS[@]}"; do
        echo "  - PrestaShop ${VERSION}"
    done
    echo ""
    echo "To debug failed tests, run:"
    echo "  ./scripts/test-local.sh <version> --headed"
    exit 1
else
    echo ""
    echo "All versions passed! 🎉"
    exit 0
fi
