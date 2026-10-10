// This file can be replaced during build by using the `fileReplacements` array.
// `ng build --prod` replaces `environment.ts` with `environment.prod.ts`.
// The list of file replacements can be found in `angular.json`.

export const environment = {
  production: false,
  API_ENDPOINT: window['env']['API_ENDPOINT'] || '',
  SUPPORT_EMAIL: 'support@ohmyshares.com',
  H_CAPTCHA_SITE_KEY: window['env']['H_CAPTCHA_SITE_KEY'] || '',
  SIGNUP_ENABLED: window['env']['SIGNUP_ENABLED'] !== 'false',
  PRIVACY_POLICY_URL: window['env']['PRIVACY_POLICY_URL'] || '',
  USER_AGREEMENT_URL: window['env']['USER_AGREEMENT_URL'] || '',
};

/*
 * For easier debugging in development mode, you can import the following file
 * to ignore zone related error stack frames such as `zone.run`, `zoneDelegate.invokeTask`.
 *
 * This import should be commented out in production mode because it will have a negative impact
 * on performance if an error is thrown.
 */
// import 'zone.js/dist/zone-error';  // Included with Angular CLI.
