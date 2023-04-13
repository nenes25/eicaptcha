import {expect, Page} from '@playwright/test';
import {DefaultPage} from "./defaultPage";

export class NewsletterPage extends DefaultPage {

    //Page url
    protected readonly pageUrl = 'en/3-clothes';

    //Selectors information
    private readonly emailSelector: string = '#blockEmailSubscription_displayFooterBefore input[name="email"]';
    private readonly submitButtonSelector: string  = '#blockEmailSubscription_displayFooterBefore input[name="submitNewsletter"]';

    //Errors
    private readonly errorMessageBlock: string = '#blockEmailSubscription_displayFooterBefore p.alert-danger';

    //Success
    private readonly successMessageBlock: string = '#blockEmailSubscription_displayFooterBefore p.alert-success';
    private readonly successMessage: string = '';

    //Recaptcha V2
    protected recaptchaV2IframeSelector: string = '#captcha-box-newsletter iframe[src*="google.com/recaptcha"]';


    /**
     * Fill the newsletter form with the default data
     * And submit the form
     */
    async fillAndSubmitForm()
    {
        //Add an email and a message in the newsletter form
        await this.page.locator(this.emailSelector).fill('test-playwright' + Math.random() + '@yopmail.com');
        //Submit the contact form button
        await this.page.click(this.submitButtonSelector);
    }

    /**
     * Check error message
     */
    async assertErrorMessage()
    {
        const messageBlock = await this.page.locator(this.errorMessageBlock);
        await expect(messageBlock).toContainText(this.errorMessageCaptchaRequired);
    }

    /**
     * Check success Message
     */
    async assertSuccessMessage()
    {
        const messageBlock = await this.page.locator(this.successMessageBlock);
        await expect(messageBlock).toContainText(this.successMessage);
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