---
name: eightshift-forms-security-harnesses
description: "Use when running, extending, or perf-testing the PDF security harnesses in eightshift-forms tests/security/ (verify-c2pa, verify-structured-xml, verify-pdf-scanner); they are not in composer test or CI."
---

# Eightshift Forms security harnesses

`tests/security/verify-*.php` are dependency-free PHP harnesses for the PDF scanner code in `src/Validation/FileSecurity/`. They are NOT run by `composer test` (PHPStan/PHPCS exclude `tests/`) and NOT run in CI (`.github/workflows/ci.yml` is composer lint + node lint only). Run them manually; `php -l` alone proves nothing.

## Run

```fish
php tests/security/verify-c2pa.php            # optional fixture dir arg, defaults tests/security/test-files
php tests/security/verify-structured-xml.php
php tests/security/verify-pdf-scanner.php
```

Each prints `PASS`/`FAIL` per check and exits non-zero on failure; last line `All checks passed.`

## Requirements

- PHP CLI 8.4+, ext-dom, ext-xmlreader (xmlreader only needed for the Europass/XML validator checks).
- qpdf on PATH enables the qpdf-dependent acceptance checks; without it the scanner harness prints `SKIP` lines and still passes. Never assume a skip means verified.
- No vendor/ needed: harnesses `require` src files directly and stub `HooksHelpers`/`apply_filters` locally. Fixtures are synthetic (`structured-xml-fixtures.php`, `c2pa-fixtures.php`) so no real personal data is committed.

## Perf claims: reproduce old vs new side by side

Reviewer timing claims on the verifier are cheap to check exactly:

1. Extract the base version: `git show origin/release/<N>:src/Validation/FileSecurity/<Old>.php > /tmp/review-perf/<Old>.php` (write tool or shell, bash tool blocks redirection).
2. In one throwaway script require `Config.php`, `PdfTokens.php`, `PdfStrings.php`, the validator files, and both old + new classes (distinct class names coexist).
3. Build the suspect shape as an in-memory body (no qpdf needed when calling `EmbeddedFileVerifier` directly) and time both classes on the same bytes with `microtime(true)`.
4. Fixture gotcha: an indirect `/EF` must point at a `<< /F 7 0 R >>` filespec object, not at the stream object, or every check rejects.

Known shape that regressed in PR #658: N nested dictionaries each owning an `/EF` in one ~1 MB object region. Owner lookup is cached by `$ownerStart`, so each nesting level rescans the remaining region (`readDictionary` + `mask()` + 2 preg passes): 0.08s old vs 7.41s new at depth 100. Inline filespecs sharing one region stay fast; only owner-dicts-with-content are the multiplier.

## Conventions when adding checks

- Use the `$check(label, actual, expected)` and `$timed(label, body, expected, budget)` closures already in each harness; budgets are generous ceilings (0.5s/1.0s), not perf assertions.
- Fail closed is the rule: a new unknown shape should reject, and a skipped environment must not silently pass.
