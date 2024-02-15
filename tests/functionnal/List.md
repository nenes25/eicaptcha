# Eicaptcha Test cases

> In order to check that a release is functional the following tests should be checked

## Recaptcha V2 

 ### Contact Form
| Case Number | Description                            | Configuration                                                    | Result |  
|-------------|----------------------------------------|------------------------------------------------------------------|--------|
 | C_1         | Contact Form Disable                   | CAPTCHA_ENABLE_CONTACT = 0                                       | OK     |
| C_2         | Contact Form Enable default            | CAPTCHA_ENABLE_CONTACT = 1 / CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 1 | OK     |
| C_3         | Contact Form Enable / Disable customer | CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 0                              | OK     |
| C_4         | Contact Form Enable / Force lang DE    | CAPTCHA_FORCE_LANG= de                                           | OK     |
| C_5         | Contact Form Enable / Theme Dark       | CAPTCHA_THEME=1                                                  | OK     |

### Customer Registration
| Case Number | Description                                       | Configuration                           | Result |  
|-------------|---------------------------------------------------|-----------------------------------------|--------|
| CU_1        | Customer registration Disable                     | CAPTCHA_ENABLE_CONTACT = 0              | OK     |
| CU_2        | Customer registration Form Enable / default       | CAPTCHA_ENABLE_CONTACT = 1              | OK     |
| CU_3        | Customer registration Form Enable / Force lang DE | CAPTCHA_FORCE_LANG= de                  | OK     |
| CU_4        | Customer registration Form Enable / Theme Dark    | CAPTCHA_THEME=1                         | OK     |
| CU_5        | Customer registration Form / Check with hook      | CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE = 0 | OK     |

### Newsletter registration
| Case Number | Description                               | Configuration                                                       | Result |  
|-------------|-------------------------------------------|---------------------------------------------------------------------|--------|
| NL_1        | Newsletter Form Disable                   | CAPTCHA_ENABLE_NEWSLETTER = 0                                       | OK     |
| NL_2        | Newsletter Form Enable / default          | CAPTCHA_ENABLE_NEWSLETTER = 1 / CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 1 | OK     |
| NL_3        | Newsletter Form Enable / Disable customer | CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 0                                 | OK     |
| NL_4        | Newsletter Form Enable / Force lang DE    | CAPTCHA_FORCE_LANG= de                                              | OK     |
| NL_5        | Newsletter Form Enable / Theme Dark       | CAPTCHA_THEME=1                                                     | OK     |


## Recaptcha V3

### Contact Form
| Case Number | Description                            | Configuration                                                    | Result |  
|-------------|----------------------------------------|------------------------------------------------------------------|--------|
| C_6         | Contact Form Enable default            | CAPTCHA_ENABLE_CONTACT = 1 / CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 1 | OK     |
| C_7         | Contact Form Enable / Disable customer | CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 0                              | OK     |

### Customer Registration
| Case Number | Description                                                    | Configuration                                                             | Result |  
|-------------|----------------------------------------------------------------|---------------------------------------------------------------------------|--------|
| CU_6        | Customer registration Form Enable / default                    | CAPTCHA_ENABLE_CONTACT = 1 / CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE = 1      | OK     |
| CU_7        | Customer registration Form / Check with hook                   | CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE = 0                                   | OK     |

### Newsletter registration
| Case Number | Description                               | Configuration                                                       | Result |  
|-------------|-------------------------------------------|---------------------------------------------------------------------|--------|
| NL_6        | Newsletter Form Enable / default          | CAPTCHA_ENABLE_NEWSLETTER = 1 / CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 1 | OK     |
| NL_7        | Newsletter Form Enable / Disable customer | CAPTCHA_ENABLE_LOGGED_CUSTOMERS = 0                                 | OK     |