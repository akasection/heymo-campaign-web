import { defineConfig } from "oxlint";

export default defineConfig({
  categories: {
    correctness: "error",
    suspicious: "warn",
    perf: "error",
    style: "warn",
    pedantic: "off",
    restriction: "off",
  },
  plugins: ["import", "unicorn", "typescript", "vue", "oxc", "promise"],
  options: {
    typeAware: true,
    typeCheck: true,
  },
  rules: {
    "func-style": [
      "error",
      "declaration",
      {
        allowArrowFunctions: true,
        overrides: {
          namedExports: "declaration",
        },
      },
    ],
    "capitalized-comments": "off",
    "sort-imports": "off", // import sorting is handled by oxfmt
    "one-var": "off", // one binding per line (Vue script setup convention)
    "import/no-unassigned-import": "off", // allow side-effect imports
    "import/exports-last": "warn",
    "import/group-exports": "off",
    "import/no-named-export": "off",
    "max-statements": ["warn", 64],
    "max-lines": [
      "warn",
      { max: 750, skipBlankLines: true, skipComments: true },
    ],
    "max-lines-per-function": "off", // Noisy against Vue template/script blocks
    "sort-keys": "off",
    "sort-vars": "off",
    curly: ["warn", "multi-line", "consistent"],
    "unicorn/numeric-separators-style": [
      "warn",
      {
        hexadecimal: {
          minimumDigits: 8,
        },
      },
    ],
    "unicorn/number-literal-case": "off",
    "max-params": [
      "warn",
      {
        max: 4,
      },
    ],
    "no-ternary": "off",
    "no-nested-ternary": "warn",
    "no-unneeded-ternary": "warn",
    "unicorn/no-null": "off",
    "no-magic-numbers": "off",
    "id-length": "off",
    "typescript/consistent-type-definitions": ["error", "type"],
    "prefer-destructuring": [
      "error",
      {
        AssignmentExpression: { array: false, object: true },
        VariableDeclarator: { array: false, object: true },
      },
    ],
    "unicorn/filename-case": "off",
    "no-duplicate-imports": [
      "warn",
      {
        allowSeparateTypeImports: true,
        includeExports: false,
      },
    ],
    "no-implicit-coercion": [
      "warn",
      {
        boolean: false, // Allow !! due to Boolean(val) can't do type narrowing
        number: true,
        string: true,
      },
    ],
  },
  settings: {},
  ignorePatterns: [
    "node_modules/**",
    "vendor/**",
    "public/**",
    "tmp/**",
    "coverage/**",
    "dist/**",
    ".rspack-cache/**",
  ],
});
