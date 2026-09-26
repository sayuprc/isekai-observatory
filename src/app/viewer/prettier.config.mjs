/** @type {import('prettier').Config} */
export default {
  printWidth: 120,
  singleQuote: true,
  experimentalOperatorPosition: 'start',
  plugins: ['prettier-plugin-astro'],
  overrides: [
    {
      files: '*.astro',
      options: {
        parser: 'astro',
      },
    },
  ],
};
