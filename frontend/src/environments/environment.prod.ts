export const environment = {
  production: true,
  API_ENDPOINT: window['env']['API_ENDPOINT'] || '',
  SUPPORT_EMAIL: 'support@ohmyshares.com',
  H_CAPTCHA_SITE_KEY: window['env']['H_CAPTCHA_SITE_KEY'] || '',
  SIGNUP_ENABLED: window['env']['SIGNUP_ENABLED'] !== 'false',
  PRIVACY_POLICY_URL: window['env']['PRIVACY_POLICY_URL'] || '',
  USER_AGREEMENT_URL: window['env']['USER_AGREEMENT_URL'] || '',
};
