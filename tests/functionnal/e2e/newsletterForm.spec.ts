import { test, expect } from '@playwright/test';
import {NewsletterPage} from './pages/newsletterPage';

//One test after another
test.describe.configure({ mode: 'serial' });
test.describe('Newsletter Form Recaptcha V2', () => {
    test('Case NL_1', async ({page}) => {
        const newsletterPage = new NewsletterPage(page);
        await newsletterPage.applyConfiguration('NL_1');
        await newsletterPage.goto();
        await newsletterPage.fillAndSubmitForm();
        await newsletterPage.assertSuccessMessage();
    });
    test('Case NL_2', async ({page}) => {
        const newsletterPage = new NewsletterPage(page);
        await newsletterPage.applyConfiguration('NL_2');
        await newsletterPage.goto();
        await newsletterPage.fillAndSubmitForm();
        await newsletterPage.assertErrorMessage();
    });
    test('Case NL_3', async ({page}) => {
        const newsletterPage = new NewsletterPage(page);
        await newsletterPage.applyConfiguration('NL_3');
        await newsletterPage.loginAsCustomer();
        await newsletterPage.goto();
        await newsletterPage.fillAndSubmitForm();
        await newsletterPage.assertSuccessMessage();
    });
    test('Case NL_4', async ({page}) => {
        const newsletterPage = new NewsletterPage(page);
        await newsletterPage.applyConfiguration('NL_4');
        await newsletterPage.goto();
        await newsletterPage.checkCaptchaV2Label();
    });
    test('Case NL_5', async ({page}) => {
        const newsletterPage = new NewsletterPage(page);
        await newsletterPage.applyConfiguration('NL_5');
        await newsletterPage.goto();
        await newsletterPage.checkDarkModeApplied();
    });
});
