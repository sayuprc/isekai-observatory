/** @type {import('stylelint').Config} */
export default {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-html/html',
    'stylelint-config-html/astro',
    'stylelint-config-recess-order',
  ],
  plugins: ['stylelint-order'],
  rules: {
    'order/order': ['custom-properties', 'declarations'],
    'alpha-value-notation': null,
    'color-function-alias-notation': null,
    'color-function-notation': null,
    'selector-class-pattern': ['^[a-z][a-zA-Z0-9-]+$'],
  },
};
