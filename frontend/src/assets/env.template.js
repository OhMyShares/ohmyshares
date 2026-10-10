(function (window) {
  window['env'] = window['env'] || {};

  // Environment variables
  window['env']['API_ENDPOINT'] = '${API_ENDPOINT}';
  window['env']['H_CAPTCHA_SITE_KEY'] = '${H_CAPTCHA_SITE_KEY}';
  window['env']['SIGNUP_ENABLED'] = '${SIGNUP_ENABLED}';
  window['env']['PRIVACY_POLICY_URL'] = '${PRIVACY_POLICY_URL}';
  window['env']['USER_AGREEMENT_URL'] = '${USER_AGREEMENT_URL}';
})(this);
