import { defineConfig } from "oxfmt";

export default defineConfig({
  sortImports: {
    newlinesBetween: false,
    groups: [
      ["value-builtin", "value-external"],
      "type-import",
      "value-internal",
      "type-internal",
      ["value-parent", "value-sibling", "value-index"],
      ["type-parent", "type-sibling", "type-index"],
      "unknown",
    ],
  },
  printWidth: 150,
  semi: true,
  singleQuote: false,
  trailingComma: "all",
  endOfLine: "lf",
  tabWidth: 2,
  useTabs: false,
  jsxSingleQuote: false,
  objectWrap: "preserve",
  arrowParens: "avoid",
  insertFinalNewline: true,
  proseWrap: "preserve",
  bracketSpacing: true,
  singleAttributePerLine: false,
});
