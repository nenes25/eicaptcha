import {expect, Page} from '@playwright/test';
import {DefaultPage} from "./defaultPage";

export class ContactFormPage extends DefaultPage {


    //Page url
    protected readonly pageUrl = 'en/contact-us';

    //Selectors information
    private readonly emailSelector: string = '#email';
    private readonly messageSelector: string  = '#contactform-message';
    private readonly submitButtonSelector: string  = 'input[name="submitMessage"]';

    //Default values informations
    private readonly email: string = 'test-playwright@yopmail.com';
    private readonly message: string  = 'We are testing the contact form with playwright';

    //Success message
    private readonly successMessage: string = 'Your message has been successfully sent to our team.';


    /**
     * Fill the contact form with the default data
     * And submit the form
     */
    async fillAndSubmitForm()
    {
        //Add an email and a message in the contact form
        await this.page.locator(this.emailSelector).fill(this.email);
        await this.page.locator(this.messageSelector).fill(this.message);
        await this.page.waitForTimeout(200);//Add a Timeout for V3 version to display the content
        //Submit the contact form button
        await this.page.click(this.submitButtonSelector);
    }

    /**
     * Assert that the success message is well displayed
     */
    async assertSuccessMessage()
    {
        const successMessageBlock = this.page.locator(this.successBoxSelector);
        await expect(successMessageBlock).toContainText(this.successMessage);
    }

   /* /!**
     * Check that the label of the captcha box is in german
     *!/
    async checkCaptchaV2Label()
    {
        const captchaBlock = await this.page.frameLocator(this.recaptchaV2IframeSelector)
        await expect(captchaBlock.locator(this.recaptchaV2LabelSelector)).toContainText(this.recaptchaV2GermanLabel);
    }

    /!**
     * Check if darkmode is applied to the captcha theme
     * ( By checking src attribute )
     *!/
    async checkDarkModeApplied()
    {
        const captchaIframe = await this.page.locator(this.recaptchaV2IframeSelector).first();
        await expect(captchaIframe).toHaveAttribute('src', /theme=dark/)
    }*/
}