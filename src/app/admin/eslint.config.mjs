import eslint from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier/flat';
import eslintPluginAstro from 'eslint-plugin-astro';
import { importX } from 'eslint-plugin-import-x';
import tsEslint from 'typescript-eslint';

const defaultRules = {
  'import-x/order': [
    'error',
    {
      alphabetize: {
        order: 'asc',
        caseInsensitive: true,
      },
    },
  ],
  '@typescript-eslint/consistent-type-imports': [
    'error',
    {
      fixStyle: 'separate-type-imports',
    },
  ],
};

export default [
  {
    plugins: {
      'import-x': importX,
    },
  },
  eslint.configs.recommended,
  ...tsEslint.configs.recommended,
  ...eslintPluginAstro.configs.recommended,
  {
    ignores: ['.astro/**', 'dist/**', 'src/generated/**'],
  },
  {
    files: ['**/*.{js,mjs,ts,jsx,tsx,astro}'],
    rules: defaultRules,
  },
  eslintConfigPrettier,
];
