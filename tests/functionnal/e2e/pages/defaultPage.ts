import {expect, Page} from '@playwright/test';

/**
 * Default page
 */
export abstract class DefaultPage {

    //Page information
    protected readonly page: Page;
    protected pageUrl: string;

    //Login Page
    protected readonly loginPageUrl: string = 'en/login?back=my-account';
    protected readonly loginEmailSelector: string = '#field-email';
    protected readonly loginPasswordSelector: string = '#field-password';
    protected readonly loginSubmitSelector: string = '#submit-login';
    protected readonly loginEmail:string = 'dev@dev.com';
    protected readonly loginPassword:string = 'dev1234';

    //Configuration page
    protected configurationPageUrl: string = 'modules/eicaptcha/tests/functionnal/apply_case.php?test_case=';

    //Success messages
    protected readonly successBoxSelector: string = '#content .alert-success';

    //Errors messages
    protected errorBoxSelector: string = '#content .alert-danger';
    protected readonly errorMessageCaptchaRequired: string  = 'Please validate the captcha field before submitting your request';

    //Recaptcha V2
    protected recaptchaV2IframeSelector: string = '#captcha-box iframe[src*="google.com/recaptcha"]';
    protected readonly recaptchaV2LabelSelector: string =  '#recaptcha-anchor-label';
    protected readonly recaptchaV2GermanLabel: string = 'Ich bin kein Roboter.';

    /**
     *
     * @param page
     */
    constructor(page) {
        this.page = page;
    }

    /**
     * Go to the contact page url
     */
    public async goto(){
        if (null !== this.pageUrl) {
            await this.page.goto(this.pageUrl);
        } else {
            throw new Error('Please define a pageUrl property in the subclass');
        }
    }

    /**
     * Login as customer to test cases which require to be logged
     */
    public async loginAsCustomer()
    {
        await this.page.goto(this.loginPageUrl);
        await this.page.locator(this.loginEmailSelector).fill(this.loginEmail);
        await this.page.locator(this.loginPasswordSelector).fill(this.loginPassword);
        await this.page.waitForTimeout(1000);
        await this.page.locator(this.loginSubmitSelector).click();

        //Check that the customer is well loggued
        const breadCrumb = await this.page.locator('.breadcrumb');
        await expect(breadCrumb).toContainText('Your account');
    }

    /**
     * Check that an error is displayed when the captcha is not filled
     */
    public async hasCaptchaErrorMessage()
    {
        const errorMessageBlock = this.page.locator(this.errorBoxSelector);
        await expect(errorMessageBlock).toContainText(this.errorMessageCaptchaRequired);
    }

    /**
     * Apply the required configuration before the execution of the test
     * @param configurationCode
     */
    public async applyConfiguration(configurationCode:string){
        await this.page.goto(this.configurationPageUrl+configurationCode);
        //Check that no warning are displayed on the configuration page
        await expect(await this.page.locator('.warning').count()).toEqual(0);
    }

    /**
     * Check that the label of the captcha box is in german
     */
    async checkCaptchaV2Label()
    {
        const captchaBlock = await this.page.frameLocator(this.recaptchaV2IframeSelector)
        await expect(captchaBlock.locator(this.recaptchaV2LabelSelector)).toContainText(this.recaptchaV2GermanLabel);
    }

    /**
     * Check if darkmode is applied to the captcha theme
     * ( By checking src attribute )
     */
    async checkDarkModeApplied()
    {
        const captchaIframe = await this.page.locator(this.recaptchaV2IframeSelector).first();
        await expect(captchaIframe).toHaveAttribute('src', /theme=dark/)
    }

}