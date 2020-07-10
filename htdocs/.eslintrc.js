module.exports = {
  "env": {
    "browser": true,
    "es6": true,
    "commonjs": true
  },
  "extends": [
    "eslint:recommended",
    "plugin:vue/essential"
  ],
  "parserOptions": {
    "parser": "babel-eslint",
    "ecmaVersion": 2017,
    "sourceType": "module"
  },
  "plugins": [
    "vue"
  ],
  "rules": {
    "indent": ["error", 4],
    "semi":["error", "always"],
    "no-unused-vars": 2,
    "no-var": ["error"],
    "camelcase": 0,
    "eqeqeq": 0,
    "node/no-deprecated-api": 0,
    "no-extra-boolean-cast": 0,
    "valid-typeof": 0,
    "no-trailing-spaces": 0,
    "no-eval": 0
  },
  "globals": {
    "GESHOP_PIPELINE": false,
    "GESHOP_SITECODE": false,
    "GESHOP_STORE": false,
    "GESHOP_PLATFORM": false
  }
};
