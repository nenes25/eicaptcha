import { test, expect } from '@playwright/test';
import {CustomerRegistrationPage} from './pages/customerRegistrationPage';


//One test after another
test.describe.configure({ mode: 'serial' });

test.describe('Registration Form Recaptcha V2', () => {
   test('Case CU_1', async ({page}) => {
        const customerRegistrationPage = new CustomerRegistrationPage(page);
        await customerRegistrationPage.applyConfiguration('CU_1');
        await customerRegistrationPage.goto();
        await customerRegistrationPage.fillAndSubmitForm();
        await customerRegistrationPage.checkUserIsLogged();
    });
    test('Case CU_2', async ({page}) => {
        const customerRegistrationPage = new CustomerRegistrationPage(page);
        await customerRegistrationPage.applyConfiguration('CU_2');
        await customerRegistrationPage.goto();
        await customerRegistrationPage.fillAndSubmitForm();
        await customerRegistrationPage.hasCaptchaErrorMessage();
    });
    test('Case CU_3', async ({page}) => {
        const customerRegistrationPage = new CustomerRegistrationPage(page);
        await customerRegistrationPage.applyConfiguration('CU_3');
        await customerRegistrationPage.goto();
        await customerRegistrationPage.checkCaptchaV2Label();
    });
    test('Case CU_4', async ({page}) => {
        const customerRegistrationPage = new CustomerRegistrationPage(page);
        await customerRegistrationPage.applyConfiguration('CU_4');
        await customerRegistrationPage.goto();
        await customerRegistrationPage.checkDarkModeApplied();

    });
    test('Case CU_5', async ({page}) => {
        const customerRegistrationPage = new CustomerRegistrationPage(page);
        await customerRegistrationPage.applyConfiguration('CU_5');
        await customerRegistrationPage.goto();
        await customerRegistrationPage.fillAndSubmitForm();
        await customerRegistrationPage.hasCaptchaErrorMessage();
    });
});
