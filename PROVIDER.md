# Guide de développement de providers pour eicaptcha

Ce document explique comment créer et intégrer un nouveau provider de captcha dans le module eicaptcha.

## Table des matières

- [Architecture](#architecture)
- [Créer un nouveau provider](#créer-un-nouveau-provider)
- [Implémenter les méthodes](#implémenter-les-méthodes)
- [Créer le template](#créer-le-template)
- [Enregistrer le provider](#enregistrer-le-provider)
- [Tests et validation](#tests-et-validation)
- [Exemples complets](#exemples-complets)
- [Best practices](#best-practices)

## Architecture

Le module eicaptcha v3.0 utilise une architecture modulaire basée sur le pattern Strategy :

```
src/Provider/
├── CaptchaProviderInterface.php      # Interface à implémenter
├── AbstractCaptchaProvider.php       # Classe de base (optionnelle)
└── [VotreProvider]Provider.php       # Votre implémentation
```

### Composants principaux

- **CaptchaProviderInterface** : Contrat que tous les providers doivent respecter
- **AbstractCaptchaProvider** : Classe abstraite avec logique commune (recommandée)
- **CaptchaFactory** : Factory pour instancier les providers dynamiquement

## Créer un nouveau provider

### Étape 1 : Créer la classe du provider

Créez un nouveau fichier dans `src/Provider/` nommé `[NomProvider]Provider.php`.

**Exemple : Cloudflare Turnstile**

```php
<?php

namespace Eicaptcha\Module\Provider;

use Configuration;
use Tools;

/**
 * Class TurnstileProvider
 *
 * Provider for Cloudflare Turnstile
 *
 * @since 3.1.0
 */
class TurnstileProvider extends AbstractCaptchaProvider
{
    /**
     * API verification endpoint
     */
    const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'turnstile';
    }

    /**
     * @inheritDoc
     */
    public function getDisplayName()
    {
        return 'Cloudflare Turnstile';
    }

    /**
     * @inheritDoc
     */
    public function validate($response, $remoteIp)
    {
        // Implémentation de la validation
        // Voir section "Implémenter les méthodes" ci-dessous
    }

    /**
     * @inheritDoc
     */
    public function getConfigFields()
    {
        // Configuration des champs admin
        // Voir section "Implémenter les méthodes" ci-dessous
    }

    /**
     * @inheritDoc
     */
    public function renderHeader(array $context = [])
    {
        // Scripts JS à charger
        // Voir section "Implémenter les méthodes" ci-dessous
    }

    /**
     * @inheritDoc
     */
    public function getTemplateVars()
    {
        // Variables pour le template
        // Voir section "Implémenter les méthodes" ci-dessous
    }

    /**
     * @inheritDoc
     */
    public function isConfigured()
    {
        // Vérifier que le provider est configuré
        // Voir section "Implémenter les méthodes" ci-dessous
    }

    /**
     * @inheritDoc
     */
    public function getTemplatePath()
    {
        return 'module:eicaptcha/views/templates/hook/providers/turnstile.tpl';
    }
}
```

## Implémenter les méthodes

### Méthodes obligatoires

#### 1. `getName()` : string

Retourne l'identifiant unique du provider (utilisé en interne).

```php
public function getName()
{
    return 'turnstile'; // lowercase, pas d'espaces
}
```

#### 2. `getDisplayName()` : string

Retourne le nom affiché dans l'interface admin.

```php
public function getDisplayName()
{
    return 'Cloudflare Turnstile';
}
```

#### 3. `validate($response, $remoteIp)` : bool

Valide la réponse du captcha côté serveur.

**Paramètres :**
- `$response` : Token de réponse du captcha (depuis le formulaire)
- `$remoteIp` : Adresse IP de l'utilisateur

**Retour :** `true` si valide, `false` sinon

```php
public function validate($response, $remoteIp)
{
    // 1. Vérifier si le captcha doit être affiché
    if (!$this->shouldDisplayToCustomer()) {
        return true;
    }

    // 2. Vérifier que la réponse n'est pas vide
    if (empty($response)) {
        $this->setLastError($this->l('Please validate the captcha field'));
        return false;
    }

    // 3. Récupérer les clés de configuration
    $secretKey = $this->getConfig('CAPTCHA_TURNSTILE_SECRET_KEY');

    if (empty($secretKey)) {
        $this->setLastError($this->l('Provider not configured'));
        return false;
    }

    // 4. Préparer la requête API
    $data = [
        'secret' => $secretKey,
        'response' => $response,
        'remoteip' => $remoteIp,
    ];

    // 5. Appeler l'API de vérification
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $verify = file_get_contents(self::VERIFY_URL, false, $context);

    if ($verify === false) {
        $this->setLastError($this->l('Unable to verify captcha'));
        return false;
    }

    // 6. Traiter la réponse
    $verifyResponse = json_decode($verify, true);

    if (!isset($verifyResponse['success']) || $verifyResponse['success'] !== true) {
        $this->setLastError($this->l('Captcha validation failed'));

        // Logger les erreurs si disponibles
        if (isset($verifyResponse['error-codes'])) {
            $this->log('Errors: ' . print_r($verifyResponse['error-codes'], true));
        }

        return false;
    }

    // 7. Validation réussie
    $this->log($this->l('Captcha validated successfully'));
    return true;
}
```

#### 4. `getConfigFields()` : array

Retourne les champs de configuration à afficher dans l'admin PrestaShop.

**Format :** Array compatible avec `HelperForm` de PrestaShop

```php
public function getConfigFields()
{
    return [
        [
            'type' => 'text',
            'label' => $this->l('Turnstile Site Key'),
            'name' => 'CAPTCHA_TURNSTILE_SITE_KEY',
            'required' => true,
            'desc' => $this->l('Get your site key from Cloudflare dashboard'),
            'tab' => 'general',
        ],
        [
            'type' => 'text',
            'label' => $this->l('Turnstile Secret Key'),
            'name' => 'CAPTCHA_TURNSTILE_SECRET_KEY',
            'required' => true,
            'desc' => $this->l('Get your secret key from Cloudflare dashboard'),
            'tab' => 'general',
        ],
        [
            'type' => 'radio',
            'label' => $this->l('Widget Theme'),
            'name' => 'CAPTCHA_TURNSTILE_THEME',
            'values' => [
                ['id' => 'light', 'value' => 'light', 'label' => $this->l('Light')],
                ['id' => 'dark', 'value' => 'dark', 'label' => $this->l('Dark')],
                ['id' => 'auto', 'value' => 'auto', 'label' => $this->l('Auto')],
            ],
            'tab' => 'general',
        ],
    ];
}
```

**Types de champs disponibles :**
- `text` : Champ texte simple
- `textarea` : Zone de texte multi-lignes
- `radio` : Boutons radio
- `switch` : Interrupteur ON/OFF
- `select` : Liste déroulante
- `html` : Contenu HTML personnalisé

#### 5. `renderHeader(array $context = [])` : string

Retourne le HTML/JavaScript à insérer dans le `<head>` de la page.

**Paramètre :**
- `$context` : Array contenant `controller`, `form_type`, etc.

```php
public function renderHeader(array $context = [])
{
    if (!$this->shouldDisplayToCustomer()) {
        return '';
    }

    $controller = $this->context->controller;
    $loadEverywhere = Configuration::get('CAPTCHA_LOAD_EVERYWHERE') == 1;

    $shouldLoad = (
        ($controller instanceof \AuthController && Configuration::get('CAPTCHA_ENABLE_ACCOUNT'))
        || ($controller instanceof \ContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT'))
        || $loadEverywhere
    );

    if ($shouldLoad) {
        $siteKey = $this->getConfig('CAPTCHA_TURNSTILE_SITE_KEY');

        return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }

    return '';
}
```

#### 6. `getTemplateVars()` : array

Retourne les variables Smarty à passer au template.

```php
public function getTemplateVars()
{
    // Récupérer les variables de base (displayCaptcha, provider, lang, theme)
    $vars = parent::getTemplateVars();

    // Ajouter des variables spécifiques
    $vars['siteKey'] = $this->getConfig('CAPTCHA_TURNSTILE_SITE_KEY');
    $vars['turnstileTheme'] = $this->getConfig('CAPTCHA_TURNSTILE_THEME', 'auto');

    return $vars;
}
```

#### 7. `isConfigured()` : bool

Vérifie que le provider est correctement configuré.

```php
public function isConfigured()
{
    $siteKey = $this->getConfig('CAPTCHA_TURNSTILE_SITE_KEY');
    $secretKey = $this->getConfig('CAPTCHA_TURNSTILE_SECRET_KEY');

    return !empty($siteKey) && !empty($secretKey);
}
```

#### 8. `getTemplatePath()` : string

Retourne le chemin du template Smarty.

```php
public function getTemplatePath()
{
    return 'module:eicaptcha/views/templates/hook/providers/turnstile.tpl';
}
```

### Méthodes optionnelles

#### `getCssFiles()` : array

Retourne un array de fichiers CSS à charger.

```php
public function getCssFiles()
{
    return [
        'modules/eicaptcha/views/css/turnstile.css',
    ];
}
```

#### `getJsFiles()` : array

Retourne un array de fichiers JavaScript à charger.

```php
public function getJsFiles()
{
    return [
        'modules/eicaptcha/views/js/turnstile.js',
    ];
}
```

## Créer le template

### Étape 2 : Créer le template Smarty

Créez un fichier `views/templates/hook/providers/[nom_provider].tpl`.

**Exemple : turnstile.tpl**

```smarty
{*
* Cloudflare Turnstile Template
*
* @author    Your Name
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $displayCaptcha}
    <div class="form-group row eicaptcha-field eicaptcha-turnstile">
        <label class="col-md-3 form-control-label">{l s='Captcha' mod='eicaptcha'}</label>
        <div class="col-md-9">
            <div class="cf-turnstile"
                 data-sitekey="{$siteKey|escape:'html'}"
                 data-theme="{$turnstileTheme|escape:'html'}"
                 data-language="{$captchalang|escape:'html'}"></div>
        </div>
    </div>
{/if}
```

### Variables Smarty disponibles

Variables fournies par `AbstractCaptchaProvider::getTemplateVars()` :

- `$displayCaptcha` : bool - Si le captcha doit être affiché
- `$provider` : string - Nom du provider (`getName()`)
- `$captchalang` : string - Code langue (ex: 'fr', 'en')
- `$captchatheme` : string - Thème ('light' ou 'dark')

Variables personnalisées ajoutées dans `getTemplateVars()` de votre provider.

## Enregistrer le provider

### Étape 3 : Ajouter le provider à la Factory

Éditez `src/Factory/CaptchaFactory.php` et ajoutez votre provider :

```php
private static $providers = [
    'google_recaptcha' => GoogleRecaptchaProvider::class,
    'google_enterprise' => GoogleEnterpriseProvider::class,
    'hcaptcha' => HCaptchaProvider::class,
    'math' => MathCaptchaProvider::class,
    'turnstile' => TurnstileProvider::class, // <- Ajouter ici
];
```

N'oubliez pas d'ajouter le `use` en haut du fichier :

```php
use Eicaptcha\Module\Provider\TurnstileProvider;
```

### Étape 4 : Mettre à jour ConfigForm.php

Ajoutez les nouveaux champs de configuration dans `src/ConfigForm.php` :

**Dans `postProcess()` :**

```php
// Turnstile
Configuration::updateValue('CAPTCHA_TURNSTILE_SITE_KEY', Tools::getValue('CAPTCHA_TURNSTILE_SITE_KEY'));
Configuration::updateValue('CAPTCHA_TURNSTILE_SECRET_KEY', Tools::getValue('CAPTCHA_TURNSTILE_SECRET_KEY'));
Configuration::updateValue('CAPTCHA_TURNSTILE_THEME', Tools::getValue('CAPTCHA_TURNSTILE_THEME'));
```

**Dans `getConfigFieldsValues()` :**

```php
// Turnstile
'CAPTCHA_TURNSTILE_SITE_KEY' => Tools::getValue('CAPTCHA_TURNSTILE_SITE_KEY', Configuration::get('CAPTCHA_TURNSTILE_SITE_KEY')),
'CAPTCHA_TURNSTILE_SECRET_KEY' => Tools::getValue('CAPTCHA_TURNSTILE_SECRET_KEY', Configuration::get('CAPTCHA_TURNSTILE_SECRET_KEY')),
'CAPTCHA_TURNSTILE_THEME' => Tools::getValue('CAPTCHA_TURNSTILE_THEME', Configuration::get('CAPTCHA_TURNSTILE_THEME') ?: 'auto'),
```

### Étape 5 : Mettre à jour Installer.php (optionnel)

Si vous voulez définir des valeurs par défaut :

```php
protected function installConfigurations()
{
    return Configuration::updateGlobalValue('CAPTCHA_ENABLE_ACCOUNT', 0)
        // ... autres configs ...
        && Configuration::updateValue('CAPTCHA_TURNSTILE_THEME', 'auto');
}
```

### Étape 6 : Adapter le code pour récupérer la réponse

Si votre provider utilise un nom de champ différent de `g-recaptcha-response`, adaptez `eicaptcha.php::_validateCaptcha()` :

```php
protected function _validateCaptcha()
{
    $provider = $this->getCaptchaProvider();

    if (!$provider->shouldDisplayToCustomer()) {
        return true;
    }

    // Adaptez selon votre provider
    $fieldName = 'g-recaptcha-response'; // Défaut

    if ($provider->getName() === 'turnstile') {
        $fieldName = 'cf-turnstile-response';
    } elseif ($provider->getName() === 'math') {
        $fieldName = 'math-captcha-response';
    }

    $response = Tools::getValue($fieldName);
    $remoteIp = Tools::getRemoteAddr();

    $isValid = $provider->validate($response, $remoteIp);

    if (!$isValid) {
        $this->context->controller->errors[] = $provider->getLastError();
    }

    return $isValid;
}
```

**Ou mieux, créez une méthode dans l'interface :**

```php
// Dans CaptchaProviderInterface.php
public function getResponseFieldName();

// Dans votre provider
public function getResponseFieldName()
{
    return 'cf-turnstile-response';
}
```

## Tests et validation

### Liste de vérification

- [ ] Le provider apparaît dans la liste déroulante admin
- [ ] Les champs de configuration s'affichent correctement
- [ ] Le captcha s'affiche sur le formulaire de contact
- [ ] Le captcha s'affiche sur le formulaire d'inscription
- [ ] La validation fonctionne (accepte les bonnes réponses)
- [ ] La validation rejette les mauvaises réponses
- [ ] Les messages d'erreur sont affichés
- [ ] Le debug log fonctionne
- [ ] Compatible avec les clients connectés (si désactivé)
- [ ] Le template s'adapte aux thèmes

### Tests manuels

1. **Installation**
   ```bash
   # Vider le cache PrestaShop
   rm -rf var/cache/*
   ```

2. **Configuration**
   - Aller dans Modules > Ei Captcha
   - Sélectionner votre provider
   - Renseigner les clés
   - Sauvegarder

3. **Tests formulaires**
   - Tester formulaire contact (avec/sans captcha valide)
   - Tester inscription client (avec/sans captcha valide)
   - Vérifier les erreurs s'affichent

4. **Debug**
   - Activer le mode debug
   - Vérifier `logs/debug.log`

## Exemples complets

### Exemple 1 : Provider avec API simple (comme hCaptcha)

Voir `src/Provider/HCaptchaProvider.php` pour un exemple complet.

**Caractéristiques :**
- Validation via POST à une API REST
- Deux clés : Site Key + Secret Key
- Réponse JSON simple

### Exemple 2 : Provider sans dépendances externes (Math Captcha)

Voir `src/Provider/MathCaptchaProvider.php` pour un exemple complet.

**Caractéristiques :**
- Génération côté serveur
- Stockage en session
- Protection CSRF
- Pas d'API externe

### Exemple 3 : Provider avec librairie externe (Google Enterprise)

Voir `src/Provider/GoogleEnterpriseProvider.php` pour un exemple complet.

**Caractéristiques :**
- Utilise SDK Google Cloud
- Vérification de dépendances composer
- Fallback si librairie absente

## Best practices

### Sécurité

1. **Toujours valider côté serveur**
   ```php
   // ✅ BON
   public function validate($response, $remoteIp)
   {
       // Validation API serveur
   }

   // ❌ MAUVAIS
   // Faire confiance uniquement au JavaScript
   ```

2. **Envoyer l'IP utilisateur**
   ```php
   $data = [
       'secret' => $secretKey,
       'response' => $response,
       'remoteip' => $remoteIp, // Important pour détecter abus
   ];
   ```

3. **Logger les erreurs en mode debug**
   ```php
   if (isset($errors)) {
       $this->log('Validation errors: ' . print_r($errors, true));
   }
   ```

4. **Utiliser `setLastError()` pour feedback utilisateur**
   ```php
   $this->setLastError($this->l('Captcha validation failed'));
   ```

### Performance

1. **Lazy loading des scripts**
   ```php
   // Ne charger que si nécessaire
   if ($controller instanceof ContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT')) {
       return $script;
   }
   ```

2. **Mettre en cache si possible**
   ```php
   // La factory met déjà en cache les instances
   // Pas besoin de recréer à chaque fois
   ```

### Compatibilité

1. **Vérifier les dépendances**
   ```php
   public function isConfigured()
   {
       if (!class_exists('SomeRequiredClass')) {
           return false;
       }
       return !empty($this->getConfig('KEY'));
   }
   ```

2. **Fallback gracieux**
   ```php
   try {
       // Tentative avec nouvelle méthode
   } catch (\Exception $e) {
       $this->log('Error: ' . $e->getMessage());
       // Fallback vers méthode alternative
   }
   ```

3. **Support multi-langues**
   ```php
   // Toujours utiliser $this->l() pour les traductions
   $this->l('Error message')
   ```

### Code quality

1. **Docblocks complets**
   ```php
   /**
    * Validate the captcha response
    *
    * @param string $response The captcha response token
    * @param string $remoteIp The user's IP address
    *
    * @return bool True if valid, false otherwise
    *
    * @since 3.1.0
    */
   public function validate($response, $remoteIp)
   ```

2. **Constantes pour URLs**
   ```php
   const VERIFY_URL = 'https://api.example.com/verify';

   // Utiliser
   file_get_contents(self::VERIFY_URL, ...);
   ```

3. **Validation des configs**
   ```php
   public function getConfig($key, $default = null)
   {
       $value = Configuration::get($key);
       return $value !== false ? $value : $default;
   }
   ```

## Support et contribution

### Soumettre un nouveau provider

1. Créer une branche : `git checkout -b feature/add-[provider]-support`
2. Implémenter le provider selon ce guide
3. Ajouter des tests si possible
4. Mettre à jour `changelog.txt`
5. Créer une Pull Request

### Documentation

- Ajouter un README spécifique dans `docs/providers/[provider].md`
- Documenter les prérequis (clés API, comptes nécessaires)
- Fournir des liens vers la documentation officielle

### Questions ?

- GitHub Issues : https://github.com/nenes25/eicaptcha/issues
- Wiki : https://github.com/nenes25/eicaptcha/wiki

---

**Version de ce guide :** 3.0.0
**Dernière mise à jour :** 2025-11-21
