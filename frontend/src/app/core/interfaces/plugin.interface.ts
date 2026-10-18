export interface PluginConfigField {
  type?: 'string' | 'number' | 'boolean';
  label?: string;
  description?: string;
  required?: boolean;
  secret?: boolean;
  default?: string | number | boolean;
}

export interface Plugin {
  id: number;
  package: string;
  version: string;
  title: string;
  description: string | null;
  remote: string;
  source: string;
  is_enabled: boolean;
  is_loaded: boolean;
  load_error: string | null;
  is_approved: boolean;
  stars: number | null;
  is_popular: boolean;
  config: Record<string, any> | null;
  config_schema: Record<string, PluginConfigField>;
  created_at: string;
  updated_at: string;
}

export interface CatalogItem {
  name: string;
  title: string;
  description: string | null;
  url: string;
  version: string | null;
  stars: number | null;
  updated_at: string | null;
  source: string;
  installed_id: number | null;
  installed_version: string | null;
  is_approved: boolean;
  is_popular: boolean;
}

export type PluginInstallationStatus = 'pending' | 'running' | 'success' | 'failed';

export interface PluginInstallation {
  id: string;
  status: PluginInstallationStatus;
  remote: string;
  plugin_id: number | null;
  package: string | null;
  title: string | null;
  version: string | null;
  error: string | null;
  started_at: string;
  finished_at: string | null;
}
