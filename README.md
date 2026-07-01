[![GitHub release](https://img.shields.io/github/v/release/bambinounos/eicaptcha)](https://github.com/bambinounos/eicaptcha/releases)
[![Github All Releases](https://img.shields.io/github/downloads/bambinounos/eicaptcha/total.svg)]()

# eicaptcha — PrestaShop 9 Compatible Fork

> Fork of [nenes25/eicaptcha](https://github.com/nenes25/eicaptcha) with full **PrestaShop 9.0.x** and **PHP 8.1+** compatibility.
>
> PR submitted to original repo: [#332](https://github.com/nenes25/eicaptcha/pull/332)

## What's different in this fork?

- **PrestaShop 9.0.x support** (tested on PS 9.0.3 Basic Edition)
- **PHP 8.1+ strict compatibility** (strict comparisons, `count()`, return types)
- **No jQuery dependency** — all JS rewritten to vanilla JavaScript
- **Updated google/recaptcha library** to 1.3.1 (full reCAPTCHA v3 support)
- Added `hookDisplayHeader()` canonical method for PS9 hook dispatch
- Removed hard `contactform` dependency (not included in PS9 Basic Edition)
- Added missing install defaults and hook registrations
- PS9-aware debug checks

## Installation

1. Download the latest ZIP from [Releases](https://github.com/bambinounos/eicaptcha/releases)
2. Upload via PrestaShop admin: **Modules → Upload a module**
3. Configure your reCAPTCHA keys in module settings

> `Please do not use the GitHub "Download ZIP" button — use the Releases page instead.`

## Important for PrestaShop 9 Users

If the reCAPTCHA badge does not appear after installing, check this configuration value:

```sql
SELECT value FROM ps_configuration WHERE name = 'PS_DISABLE_NON_NATIVE_MODULE';
```

If the value is `1`, PrestaShop is **silently blocking all non-native modules** from executing hooks. Fix it with:

```sql
UPDATE ps_configuration SET value = '0' WHERE name = 'PS_DISABLE_NON_NATIVE_MODULE';
```

Or via admin: **Advanced Parameters → Performance → Disable non PrestaShop modules → No**

Then clear the PrestaShop cache and restart your web server:

```bash
rm -rf /path/to/prestashop/var/cache/prod/*
rm -rf /path/to/prestashop/var/cache/dev/*
```

## Features

This module displays Google reCAPTCHA on the following forms:
- Contact form
- Account creation form
- Newsletter subscription (since 2.1.0)
- Custom module forms (since 2.4.0 — see [documentation](https://www.h-hennes.fr/blog/2022/08/22/prestashop-ajouter-un-captcha-sur-les-formulaires-de-vos-modules/))
- Force the script to load everywhere (since 2.4.5) for other cases

The module is compatible with both **V2** and **V3** reCAPTCHA keys (since v2.3.0).

For PrestaShop 8+, the module uses **native hooks** instead of controller overrides.
For newsletter subscription, it requires **ps_emailsubscription >= 2.6.0**.

## Screenshots with V2 keys

<p align="center">
	Captcha on contact form <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2017/07/eicaptcha-17-contact.jpg" alt="Captcha Contact Form" />
</p>

<p align="center">
	Captcha on account creation form <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2017/07/eicaptcha-17-account.jpg" alt="Captcha on account creation form" />
</p>

<p align="center">
	Captcha on newsletter form <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2021/03/captcha-newsletter.png" alt="Captcha on newsletter subscription form" />
</p>

## Screenshots with V3 keys (invisible reCAPTCHA)

With V3 keys you just need to check if the reCAPTCHA badge is present in the bottom right corner.

<p align="center">
	V3 captcha <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2021/10/eicaptcha-v3.png" alt="Captcha V3" />
</p>

## Compatibility

| PrestaShop Version   | Compatible |
|----------------------|------------|
| 1.6.1.x and under   | :x: use version 0.4.x or 0.5.x instead |
| 1.7.0.x to 1.7.8.x  | :heavy_check_mark: |
| 8.0.x                | :heavy_check_mark: |
| 8.1.x                | :heavy_check_mark: |
| **9.0.x**            | :heavy_check_mark: **(this fork)** |

| PHP Version | Compatible |
|-------------|------------|
| 7.x         | :heavy_check_mark: (PS 1.7/8 only) |
| **8.0+**    | :heavy_check_mark: |
| **8.1+**    | :heavy_check_mark: |

## Additional information

- Original module: https://github.com/nenes25/eicaptcha
- Author blog (French): https://www.h-hennes.fr/blog/2017/07/11/module-catpcha-pour-prestashop-1-7/

## License

Academic Free License (AFL 3.0)
