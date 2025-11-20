# eicaptcha Module - Tests Documentation

This directory contains all automated tests for the **eicaptcha** PrestaShop module, including functional end-to-end tests using Playwright.

## Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Running Tests Locally with Docker](#running-tests-locally-with-docker)
- [Running Tests on Multiple PrestaShop Versions](#running-tests-on-multiple-prestashop-versions)
- [GitHub Actions CI/CD](#github-actions-cicd)
- [Project Structure](#project-structure)
- [Database Snapshots](#database-snapshots)
- [Test Configuration](#test-configuration)
- [Adding New Tests](#adding-new-tests)
- [Troubleshooting](#troubleshooting)

---

## Overview

The eicaptcha module uses **Playwright** for end-to-end (E2E) testing to validate that the reCAPTCHA integration works correctly across:

- **Contact forms** (reCAPTCHA v2 and v3)
- **Customer registration forms**
- **Newsletter subscription forms**

Tests are run against multiple PrestaShop versions:
- **PrestaShop 1.7.8.x** (latest 1.7 version)
- **PrestaShop 8.x** (current stable)
- **PrestaShop 9.x** (latest version)

---

## Prerequisites

### For Local Development

- **Docker** (20.10+) and **Docker Compose** (1.29+)
- **Node.js** (14+ for PS 1.7.8, 16+ for PS 8.x, 18+ for PS 9.x)
- **npm** (comes with Node.js)
- **Git** (to clone the repository)

### For GitHub Actions

No local setup required. Tests run automatically on:
- Push to `main` or `develop` branches
- Pull requests affecting the `modules/eicaptcha/` directory
- Manual workflow dispatch

---

## Quick Start

### 1. Initialize Test Environment

Navigate to the functional tests directory:

```bash
cd modules/eicaptcha/tests/functionnal
```

Install Node.js dependencies:

```bash
npm install
```

### 2. Set Up PrestaShop with Docker

Initialize PrestaShop for a specific version (this creates a database snapshot):

```bash
# For PrestaShop 8.x (default)
./scripts/init-prestashop.sh 8

# For PrestaShop 1.7.8
./scripts/init-prestashop.sh 1.7.8

# For PrestaShop 9.x
./scripts/init-prestashop.sh 9
```

**What this does:**
- Starts Docker containers (PrestaShop + MySQL)
- Installs PrestaShop
- Installs the eicaptcha module
- Creates a test user (`dev@dev.com` / `dev1234%123`)
- Generates a database snapshot for fast restoration

### 3. Run Tests

```bash
# Run tests on PrestaShop 8.x
./scripts/test-local.sh 8

# Run tests on PrestaShop 1.7.8
./scripts/test-local.sh 1.7.8

# Run tests with Playwright in headed mode (see browser)
./scripts/test-local.sh 8 --headed

# Run tests in debug mode
./scripts/test-local.sh 8 --debug
```

---

## Running Tests Locally with Docker

### Architecture

The Docker setup includes:

- **PrestaShop** container (official `prestashop/prestashop` image)
- **MySQL** container (MySQL 5.7 for PS 1.7.8, MySQL 8.0 for PS 8.x/9.x)
- **Playwright** tests run from the host machine (for better debugging)

### Available Scripts

| Script | Description |
|--------|-------------|
| `./scripts/init-prestashop.sh <version>` | Initial setup: installs PrestaShop, module, creates snapshot |
| `./scripts/restore-db.sh <version>` | Restores database from snapshot (fast reset) |
| `./scripts/test-local.sh <version>` | Runs E2E tests on specified version |
| `./scripts/test-all-versions.sh` | Runs tests on all supported versions sequentially |

### Environment Configuration

Each PrestaShop version has its own environment file:

- `.env.ps178` - PrestaShop 1.7.8.11
- `.env.ps8` - PrestaShop 8.1.7
- `.env.ps9` - PrestaShop 9.0.0

You can create a `.env.local` file to override settings for local development:

```bash
# .env.local example
PS_VERSION=8.1.7
CI_ENVIRONMENT_URL=http://localhost:8080
MYSQL_ROOT_PASSWORD=custom_password
```

### Docker Commands

```bash
# View PrestaShop logs
docker-compose logs prestashop

# View MySQL logs
docker-compose logs mysql

# Access PrestaShop container
docker-compose exec prestashop bash

# Access MySQL container
docker-compose exec mysql mysql -u prestashop -pprestashop prestashop

# Stop all containers
docker-compose down

# Stop and remove volumes (clean slate)
docker-compose down -v
```

---

## Running Tests on Multiple PrestaShop Versions

### Test All Versions Sequentially

```bash
./scripts/test-all-versions.sh
```

This will:
1. Run tests on PrestaShop 1.7.8
2. Clean up containers
3. Run tests on PrestaShop 8.x
4. Clean up containers
5. Run tests on PrestaShop 9.x
6. Display a summary of results

### Test Specific Versions

```bash
# Test only 1.7.8 and 8.x
for version in 1.7.8 8; do
  ./scripts/test-local.sh $version
done
```

---

## GitHub Actions CI/CD

### Workflow Overview

The GitHub Actions workflow (`.github/workflows/eicaptcha-e2e-tests.yml`) runs tests on a matrix of:

- **PrestaShop versions:** 1.7.8.11, 8.1.7, 9.0.0
- **PHP versions:** 7.4, 8.1, 8.3 (compatibility-based)
- **MySQL versions:** 5.7, 8.0

### Matrix Configuration

```yaml
matrix:
  include:
    # PS 1.7.8 with PHP 7.4 & 8.1
    - prestashop: '1.7.8.11'
      php: '7.4'
      mysql: '5.7'
    - prestashop: '1.7.8.11'
      php: '8.1'
      mysql: '5.7'

    # PS 8.x with PHP 8.1
    - prestashop: '8.1.7'
      php: '8.1'
      mysql: '8.0'

    # PS 9.x with PHP 8.1 & 8.3
    - prestashop: '9.0.0'
      php: '8.1'
      mysql: '8.0'
    - prestashop: '9.0.0'
      php: '8.3'
      mysql: '8.0'
```

### Triggering Workflows

**Automatic:**
- Push to `main` or `develop`
- Pull requests affecting the module

**Manual:**
- Go to **Actions** tab in GitHub
- Select **"eicaptcha E2E Tests"**
- Click **"Run workflow"**

### Viewing Results

- **Test Reports:** Uploaded as artifacts (retain for 30 days)
- **Screenshots:** Available on test failures
- Download artifacts from the workflow run page

---

## Project Structure

```
modules/eicaptcha/tests/
├── README.md                          # This file
└── functionnal/
    ├── docker-compose.yml             # Docker services configuration
    ├── .env.ps178                     # PrestaShop 1.7.8 environment
    ├── .env.ps8                       # PrestaShop 8.x environment
    ├── .env.ps9                       # PrestaShop 9.x environment
    ├── .gitignore                     # Git ignore rules
    ├── package.json                   # Node.js dependencies
    ├── playwright.config.ts           # Playwright configuration
    ├── apply_case.php                 # PHP script to apply test configurations
    ├── List.md                        # Test cases documentation
    ├── scripts/
    │   ├── init-prestashop.sh         # Initialize PrestaShop + module
    │   ├── restore-db.sh              # Restore database snapshot
    │   ├── test-local.sh              # Run tests locally
    │   └── test-all-versions.sh       # Run tests on all versions
    ├── snapshots/
    │   ├── prestashop-1.7.8.x-snapshot.sql
    │   ├── prestashop-8.x-snapshot.sql
    │   └── prestashop-9.x-snapshot.sql
    └── e2e/
        ├── contactForm.spec.ts        # Contact form tests
        ├── customerRegistrationForm.spec.ts
        ├── newsletterForm.spec.ts
        └── pages/
            ├── defaultPage.ts         # Base page object
            ├── contactFormPage.ts
            ├── customerRegistrationPage.ts
            └── newsletterPage.ts
```

---

## Database Snapshots

### Why Snapshots?

Database snapshots allow fast test environment resets without reinstalling PrestaShop each time:

- **Installation**: ~2-3 minutes
- **Snapshot restore**: ~5-10 seconds

### Creating Snapshots

Snapshots are automatically created when you run `init-prestashop.sh`:

```bash
./scripts/init-prestashop.sh 8
```

This generates: `snapshots/prestashop-8.x-snapshot.sql`

### Restoring Snapshots

To reset the database to a clean state:

```bash
./scripts/restore-db.sh 8
```

### Regenerating Snapshots

If you need to update a snapshot (e.g., after module changes):

```bash
# Stop containers
docker-compose down -v

# Reinitialize (creates new snapshot)
./scripts/init-prestashop.sh 8
```

**Note:** Snapshots are gitignored due to their size. Regenerate them locally as needed.

---

## Test Configuration

### Test Cases

Tests use predefined configuration cases defined in `apply_case.php`:

**Contact Form Tests:**
- `C_1`: reCAPTCHA v2 disabled
- `C_2`: reCAPTCHA v2 enabled (guests)
- `C_3`: reCAPTCHA v2 enabled (logged customers exempt)
- `C_4`: reCAPTCHA v2 with German language
- `C_5`: reCAPTCHA v2 with dark theme
- `C_6`: reCAPTCHA v3 enabled
- `C_7`: reCAPTCHA v3 (logged customers exempt)

**Customer Registration Tests:**
- `CU_1` to `CU_7`: Similar variations for registration form

**Newsletter Tests:**
- `NL_1` to `NL_7`: Similar variations for newsletter form

### Test User Credentials

All tests use a pre-created test user:

- **Email:** `dev@dev.com`
- **Password:** `dev1234%123`

This user is created during initialization and included in snapshots.

### reCAPTCHA Test Keys

Tests use Google's official test keys:

**reCAPTCHA v2:**
- Site Key: `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI`
- Secret Key: `6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe`

**reCAPTCHA v3:**
- Site Key: `6LfLDMceAAAAAEWfCM1_p3D2CkuPfDnC0R2DE3ZS`
- Secret Key: `6LfLDMceAAAAANazDlF6_Ddhlj5odSwxyzSmdJxr`

---

## Adding New Tests

### 1. Define Test Configuration

Add a new case to `apply_case.php`:

```php
'NEW_1' => [
    'CAPTCHA_VERSION' => 2,
    'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
    'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
    'CAPTCHA_ENABLE_CUSTOM_FORM' => 1,
    // ... other settings
],
```

### 2. Create Page Object (if needed)

Create `e2e/pages/customFormPage.ts`:

```typescript
import { DefaultPage } from './defaultPage';

export class CustomFormPage extends DefaultPage {
    protected pageUrl = 'en/custom-form';

    async fillAndSubmitForm() {
        // Test logic here
    }
}
```

### 3. Write Test Spec

Create `e2e/customForm.spec.ts`:

```typescript
import { test, expect } from '@playwright/test';
import { CustomFormPage } from './pages/customFormPage';

test.describe.configure({ mode: 'serial' });

test.describe('Custom Form reCAPTCHA', () => {
    test('Case NEW_1', async ({ page }) => {
        const customFormPage = new CustomFormPage(page);
        await customFormPage.applyConfiguration('NEW_1');
        await customFormPage.goto();
        await customFormPage.fillAndSubmitForm();
        await customFormPage.assertSuccessMessage();
    });
});
```

### 4. Run New Tests

```bash
./scripts/test-local.sh 8
```

---

## Troubleshooting

### PrestaShop Container Won't Start

**Problem:** `docker-compose logs prestashop` shows errors

**Solutions:**
```bash
# Clean restart
docker-compose down -v
./scripts/init-prestashop.sh 8

# Check logs
docker-compose logs -f prestashop
```

### Tests Fail to Connect to PrestaShop

**Problem:** `Error: page.goto: net::ERR_CONNECTION_REFUSED`

**Solutions:**
```bash
# Verify PrestaShop is running
curl http://localhost:8080/

# Check if port is available
lsof -i :8080

# Restart containers
docker-compose restart prestashop
```

### Database Connection Issues

**Problem:** MySQL errors during initialization

**Solutions:**
```bash
# Wait longer for MySQL
docker-compose logs mysql

# Manually check MySQL
docker-compose exec mysql mysql -u prestashop -pprestashop -e "SHOW DATABASES;"

# Reset MySQL
docker-compose down -v
docker-compose up -d mysql
```

### Module Not Found in Tests

**Problem:** `apply_case.php` can't load `config.inc.php`

**Solution:**
The script tries multiple paths automatically. If it still fails:

```bash
# Check module mounting
docker-compose exec prestashop ls -la /var/www/html/modules/eicaptcha

# Verify volume mapping in docker-compose.yml
```

### Playwright Browser Issues

**Problem:** Playwright can't find browsers

**Solutions:**
```bash
# Install Playwright browsers
npx playwright install firefox

# Install system dependencies
npx playwright install-deps firefox
```

### Snapshot Restore Fails

**Problem:** Database restore errors

**Solutions:**
```bash
# Check snapshot exists
ls -lh snapshots/

# Regenerate snapshot
docker-compose down -v
./scripts/init-prestashop.sh 8

# Manual restore
docker-compose exec -T mysql mysql -u prestashop -pprestashop prestashop < snapshots/prestashop-8.x-snapshot.sql
```

### Tests Run Sequentially Too Slowly

**Problem:** Tests take too long

**Optimization:**
```bash
# Run specific test file
npx playwright test contactForm.spec.ts --workers=1

# Run specific test case
npx playwright test -g "Case C_2" --workers=1
```

### Clean Slate Reset

When all else fails:

```bash
# Nuclear option: remove everything and start fresh
docker-compose down -v
rm -rf node_modules playwright-report test-results
npm install
npx playwright install firefox
./scripts/init-prestashop.sh 8
./scripts/test-local.sh 8
```

---

## Support and Contributions

### Reporting Issues

If you encounter bugs or have feature requests:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review existing GitHub issues
3. Open a new issue with:
   - PrestaShop version
   - PHP version
   - Steps to reproduce
   - Error logs

### Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass locally
5. Submit a pull request

---

## Additional Resources

- [Playwright Documentation](https://playwright.dev/docs/intro)
- [PrestaShop Documentation](https://devdocs.prestashop-project.org/)
- [reCAPTCHA Documentation](https://developers.google.com/recaptcha/docs/display)
- [Docker Compose Reference](https://docs.docker.com/compose/)

---

**Last Updated:** 2025-01-19
