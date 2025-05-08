module.exports = {
    root: true,
    env: {
      browser: true,
      node: true,
      es2021: true,
    },
    extends: [
      'eslint:recommended',
      'plugin:vue/vue3-recommended', // or 'plugin:react/recommended' if React
    ],
    parserOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
    },
    plugins: ['unused-imports'],
    rules: {
    'unused-imports/no-unused-imports': 'error',
    'unused-imports/no-unused-vars': [
        'warn',
        {
        vars: 'all',
        varsIgnorePattern: '^_',
        args: 'after-used',
        argsIgnorePattern: '^_',
        },
    ],
    'no-unused-vars': 'off',
    }

  };
  