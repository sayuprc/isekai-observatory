interface ImportMetaEnv {
  readonly API_URL: string;
  readonly APP_ENV?: string;
  readonly SITE_NOINDEX?: string;
  readonly PUBLIC_API_DOCS_URL?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
