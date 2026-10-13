---
name: eightshift-forms-real-render-style-smoke
description: "Browser-check cz-theme Eightshift Forms styling (Choices, Flatpickr ranges, hover states) on real plugin-rendered markup without switching the shared stack"
---

# Eightshift Forms real-render style smoke

Use when you change the cz-theme Forms styling (`src/Plugins/EightshiftForms.php`, `src/Blocks/assets/styles/eightshift-forms.css`) and need browser proof. The shared stack may be serving another worktree. Never switch it, never submit the form, and never touch the database.

## 1. Render the real markup in the stack CLI container

- Find the container: `docker ps --format '{{.ID}} {{.Names}}' | rg 'cz-stack.*cli'`.
- Copy this worktree's `src/Plugins/EightshiftForms.php` into the container as `/tmp/cz188-EightshiftForms.php`, along with a render script. The script does the following, in order:
  - Sets `$_SERVER` HTTP_HOST, SERVER_NAME, HTTPS and REQUEST_URI, then requires `/var/www/html/wp-load.php`.
  - Sets `new WP_Query(['page_id' => <page>])` and calls `the_post()`.
  - Requires the copied service, then runs `(new \EightshiftUIKit\Plugins\EightshiftForms())->register(); do_action('wp_enqueue_scripts');`.
  - Renders `do_blocks(get_post(<page>)->post_content)`.
  - Collects the `eightshift-forms*frontend*` scripts with their dependencies, recursively, plus each one's `wp_scripts()->get_data($h, 'before')`.
  - Collects the `wp_styles()->queue` entries for Forms and cz-theme, keeping their real load order: Forms mandatory, then Forms frontend, then the theme.
  - Writes everything as JSON.
- Run it with `docker exec <id> php /tmp/render.php 2>/dev/null`, `docker cp` the JSON out, then `unlink` the container files.
- On the CZ-188 test setup, page #131 embeds form #130.

## 2. Serve it read-only

- Use `bun` `Bun.serve` on `127.0.0.1`, port 0, and answer GET only. Start it with `bash` using `ready: {log: '"url"'}` and kill it through `proc://<name>/kill`.
- `/` serves the frontend bundle and `/editor` the editor bundle. Re-read `public/manifest.json` on every request so rebuilds show up without a restart.
- Serve `/wp-content/themes/cz-theme/public/*` from the worktree. Serve `/wp-content/plugins/eightshift-forms/public/*` from `~/.cache/centar-zlata/wp-plugins/eightshift-forms/10.4.1/<hash>/eightshift-forms` (find the path with `readlink apps/wp/wp-content/plugins/eightshift-forms`).
- Add a capture-phase `submit` listener that calls `preventDefault`. At the end, assert that `tab.requests({method:'POST'})` is empty.
- To compare old and new CSS, serve the old bundle copy from inside `public/` so its relative font URLs still resolve.

## 3. Drive state through APIs, not clicks

- Show a later step by toggling `es-form-is-active` and `aria-hidden` on `.es-step`.
- Flatpickr: the hidden input carries `_flatpickr`, and the visible field is `fp.altInput` (with `.id`). Use `fp.setDate([a, b], true)`, `fp.open()`, `fp.clear()`, and `fp.set('minDate'|'maxDate', …)` to create disabled days and a disabled year input. Real clicks often time out with `off-viewport` right after a step toggle. If you must click, wait about 400ms and call `tab.scrollIntoView` first.
- Choices: open it with a click on `.choices__inner` or the input, then click `[data-value=…]` in the dropdown to create a chip.
- Hover, focus and active: force them through CDP with `page.createCDPSession()`, `DOM.getDocument` / `DOM.querySelector`, then `CSS.forcePseudoState`. Mouse hover over the month arrows does not register. Read the child `svg` fill, not the anchor's.

## 4. Assert against tokens

- Resolve each `--es-form-*` token through a probe element (`probe.style.color = 'var(--x)'`, then `getComputedStyle(probe).color`). Computed values come out as `lab()`, so compare them against the resolved probe values, never against hex.
- Parse `box-shadow` colors with a regex, then compare each one.
- Grid check: for every `.dayContainer .flatpickr-day`, the nearest `.flatpickr-weekday` column must equal `new Date(aria-label).getDay()`, and each row must hold 7 days.
- Check both bundles and a 390px viewport, where `scrollWidth` must equal `innerWidth`.
- Screenshots: the calendar fades in, so wait about 600ms until its opacity is 1. Use a clip box with `scrollX`/`scrollY` and `captureBeyondViewport: true`.

## 5. Cleanup

Close the tabs, kill the proc, `rip` the `/tmp` scratch directory, and confirm that `git status` shows only the intended files.
