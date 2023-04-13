import {expect, Page} from '@playwright/test';
import {DefaultPage} from "./defaultPage";

export class CustomerRegistrationPage extends DefaultPage
{
    //Page url
    protected readonly pageUrl = 'en/login?create_account=1';

    //Selectors information
    private readonly firstNameSelector: string = '#field-firstname';
    private readonly lastNameSelector: string = '#field-lastname';
    private readonly emailSelector: string = '#field-email';
    private readonly passwordSelector: string = '#field-password';
    private readonly dataPrivacySelector: string  = 'input[name="customer_privacy"]';
    private readonly privacyPolicySelector: string  = 'input[name="psgdpr"]';
    private readonly submitButtonSelector: string  = '[data-link-action="save-customer"]';

    //Default values informations
    private readonly firstName: string = 'Test';
    private readonly lastName: string = 'Playwright';
    //private readonly email: string = 'dev@dev.com'; Use a random email
    private readonly password: string = 'p@sswOrd1234';

    //Success message
    private readonly successSelector: string = '.user-info';
    private readonly successMessage: string = 'Sign out';

    //Error message
    protected errorBoxSelector: string = '.container .alert-danger';

    /**
     * Fill the contact form with the default data
     * And submit the form
     */
    async fillAndSubmitForm()
    {
        const randomEmail: string = 'test-playwright' + Math.random() + '@yopmail.com';
        //Fill the registration form
        await this.page.locator('#field-id_gender-1').click();
        await this.page.locator(this.firstNameSelector).fill(this.firstName);
        await this.page.locator(this.lastNameSelector).fill(this.lastName);
        await this.page.locator(this.emailSelector).fill(randomEmail);
        await this.page.locator(this.passwordSelector).fill(this.password);
        await this.page.waitForTimeout(200);
        await this.page.locator(this.dataPrivacySelector).click();
        await this.page.locator(this.privacyPolicySelector).click();
        //Submit the contact form button
        await this.page.click(this.submitButtonSelector);
    }


    /**
     * Check that the user is logged in on the website after registration
     */
    async checkUserIsLogged()
    {
        const successMessageBlock = await this.page.locator(this.successSelector);
        await expect(successMessageBlock).toContainText(this.successMessage);
    }

}