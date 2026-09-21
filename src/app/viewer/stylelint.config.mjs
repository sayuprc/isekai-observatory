/** @type {import('stylelint').Config} */
export default {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-html/html',
    'stylelint-config-html/astro',
    'stylelint-config-recess-order',
  ],
  plugins: ['stylelint-order', '@stylistic/stylelint-plugin'],
  rules: {
    'order/order': ['custom-properties', 'declarations'],
    'rule-empty-line-before': ['always'],
    '@stylistic/max-empty-lines': [1],
    '@stylistic/no-empty-first-line': [true],
    'alpha-value-notation': null,
    'color-function-alias-notation': null,
    'color-function-notation': null,
    'selector-class-pattern': ['^[a-z][a-zA-Z0-9-]+$'],
  },
};
