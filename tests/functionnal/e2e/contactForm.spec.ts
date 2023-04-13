import {test, expect} from '@playwright/test';
import {ContactFormPage} from './pages/contactFormPage';

//We need to run one test after the other
test.describe.configure({mode: 'serial'});

/**
 * Check all behavior related to contact Form
 */
test.describe('Contact Form Recaptcha V2', () => {
    test('Case C_1', async ({page}) => {
        const contactFormPage = new ContactFormPage(page);
        await contactFormPage.applyConfiguration('C_1');
        await contactFormPage.goto();
        await contactFormPage.fillAndSubmitForm();
        await contactFormPage.assertSuccessMessage();
    });
    test('Case C_2', async ({page}) => {
        const contactFormPage = new ContactFormPage(page);
        await contactFormPage.applyConfiguration('C_2');
        await contactFormPage.goto();
        await contactFormPage.fillAndSubmitForm();
        await contactFormPage.hasCaptchaErrorMessage();
    });
    test('Case C_3', async ({page}) => {
        const contactFormPage = new ContactFormPage(page);
        await contactFormPage.applyConfiguration('C_3');
        await contactFormPage.loginAsCustomer();
        await contactFormPage.goto();
        await contactFormPage.fillAndSubmitForm();
        await contactFormPage.assertSuccessMessage();
    });
    test('Case C_4', async ({page}) => {
        const contactFormPage = new ContactFormPage(page);
        await contactFormPage.applyConfiguration('C_4');
        await contactFormPage.goto();
        await contactFormPage.checkCaptchaV2Label();
    });
    test('Case C_5', async ({page}) => {
        const contactFormPage = new ContactFormPage(page);
        await contactFormPage.applyConfiguration('C_5');
        await contactFormPage.goto();
        await contactFormPage.checkDarkModeApplied();
    });
});
