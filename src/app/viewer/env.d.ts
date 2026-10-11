interface ImportMetaEnv {
  readonly API_URL: string;
  readonly APP_ENV?: string;
  readonly SITE_NOINDEX?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
