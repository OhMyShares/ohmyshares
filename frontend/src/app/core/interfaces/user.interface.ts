export enum UserRole {
  User = 0,
  Admin = 20,
}

export interface User {
  id: number;
  name: string;
  email: string;
  role?: UserRole;
  locale?: string | null;
  is_test?: boolean;
  is_ignore_funds_sufficiency?: boolean;
  password?: string | null;
  created_at?: string;
  updated_at?: string;
}
