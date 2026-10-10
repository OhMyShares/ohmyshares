export interface AuthParams {
  email: string;
  password: string;
}

export interface SignUpParams {
  name: string;
  email: string;
  captcha: string;
}

export interface ChangePasswordParams {
  password: string;
  password_new: string;
}

export interface LoginResponse extends AuthParams {
  api_token: string;
}

export interface Me {
  days_remaining: number;
  email: string;
  is_pro: boolean;
  is_test: boolean;
  is_ignore_funds_sufficiency: boolean;
  name: string;
  locale: string;
  role: string;
}

export interface ForgotParams {
  email: string;
}

export interface ResetParams {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}
