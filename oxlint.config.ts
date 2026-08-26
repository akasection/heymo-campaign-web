import { defineConfig } from "oxlint";
// import reactHooksConfig from "./oxlint/react-hooks.config.ts";

// Glob is prefixed with **/ so it resolves correctly from either the package
// dir (CLI invocation) or the repo root (LSP invocation from VSCode). The
// leading wildcard absorbs the optional packages/boon-core/ prefix.
const PREFIX = "**/app/javascript/bundles/takeoff-v2/**";

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
    typeCheck: true,
    typeAware: true,
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
    "sort-imports": [
      "warn",
      {
        ignoreMemberSort: true,
        ignoreDeclarationSort: true,
        ignoreCase: true,
      },
    ],
    "import/exports-last": "warn",
    "import/group-exports": "off",
    "import/no-named-export": "off",
    "max-statements": ["warn", 64],
    "max-lines": [
      "warn",
      { max: 750, skipBlankLines: true, skipComments: true },
    ],
    // "max-lines-per-function": ["warn", { max: 64, skipBlankLines: true, skipComments: true }],
    "max-lines-per-function": "off", // Noisy against React jsx
    "sort-keys": "off",
    "sort-vars": "off",
    curly: ["warn", "multi-line", "consistent"],
    "react/react-in-jsx-scope": "off",
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
    // "no-magic-numbers": [
    //   "warn",
    //   {
    //     ignore: [-1, 0, 1, 2, 10, 100, 1000, 16, 32, 36, 64], // boolean flags, percentages, array out-of-bounds, radix
    //     ignoreArrayIndexes: true,
    //     ignoreDefaultValues: true,
    //     ignoreNumericLiteralTypes: true,
    //     ignoreClassFieldInitialValues: true,
    //     ignoreTypeIndexes: true,
    //     ignoreReadonlyClassProperties: true,
    //     enforceConst: true,
    //   },
    // ], // Broken rule: numbers in arrays cannot be ignored
    "react/jsx-max-depth": ["warn", { max: 7 }],
    "react/jsx-handler-names": "off",
    "react-perf/jsx-no-new-function-as-prop": "error",
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
    "**/*",
    `!${PREFIX}`,
    `!${PREFIX}/**`,
    "**/app/javascript/**/__mocks__/**/*.{cjs,mjs}",
    "**/app/javascript/bundles/__jest__/**/*.{cjs,mjs}",
  ],
});
