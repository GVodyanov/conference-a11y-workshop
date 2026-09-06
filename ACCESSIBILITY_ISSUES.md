# Accessibility workshop — planted issues

> [!CAUTION]
> **This branch is deliberately broken. Never merge it, never open a pull request from it.**
>
> `workshop/accessibility-issues` exists so that workshop participants have something
> real to find. Every issue listed below was introduced on purpose, on top of the
> accessible implementation that lives on `main`. The code comments around the
> planted issues are written the way a well-meaning developer would have written
> them, so the comments are *not* hints — they are part of the exercise.
>
> `main` is the answer key: `git diff main..workshop/accessibility-issues`.

The app itself still works. Everything is operable with a mouse, at a normal window
size, in English. The damage is confined to the things this workshop is about.

---

## Contents

| # | Concept | Issues | Where to look |
|---|---------|--------|---------------|
| 1 | [Text size](#1-text-size) | `TXT-1` … `TXT-5` | main page, history dialog, wheel |
| 2 | [Colour contrast](#2-colour-contrast) | `CON-1` … `CON-6` | main page, wheel, history, second chance |
| 3 | [Alt text for images](#3-alt-text-for-images) | `ALT-1` … `ALT-3` | hero, result card, empty history |
| 4 | [Reduced motion](#4-reduced-motion) | `MOT-1` … `MOT-3` | wheel, primary button, result card |
| 5 | [Keyboard navigation](#5-keyboard-navigation) | `KBD-1` … `KBD-4` | button row, wheel |
| 6 | [Right-to-left text](#6-right-to-left-text) | `RTL-1` … `RTL-5` | app root, main page, both dialogs |
| 7 | [Labels for forms](#7-labels-for-forms) | `LBL-1` … `LBL-4` | cleaning rules dialog |
| 8 | [Zoom](#8-zoom) | `ZOM-1` … `ZOM-4` | page layout, viewport meta |
| 9 | [Incorrect ARIA](#9-incorrect-aria) | `ARIA-1` … `ARIA-6` | button row, wheel, live region, rules dialog |

**40 planted issues across 9 concepts.**

---

## Before you start

### Run it

```bash
npm ci
npm run build     # or: npm run watch
```

Then open **Chaotic file cleaner** in Nextcloud. Make sure the account has a
handful of deletable files, otherwise the wheel never appears and half the
stations are unreachable.

### The toolbox

| Concept | How to test it |
|---|---|
| Text size | Firefox → *Settings → Fonts → Advanced → Default size* = 24. Chrome → *Settings → Appearance → Font size* = Very large. This is **not** zoom — it only moves text that is sized in `rem`/`em`/`%`. |
| Colour contrast | axe DevTools, Lighthouse, WAVE, or Firefox DevTools → *Accessibility → Check for issues → Contrast*. |
| Alt text | A screen reader (NVDA, Orca, VoiceOver), or the accessibility tree in DevTools. |
| Reduced motion | DevTools → *Rendering → Emulate CSS `prefers-reduced-motion: reduce`*, or the real OS setting (GNOME *Accessibility → Reduce animation*, macOS *Display → Reduce motion*, Windows *Visual effects → Animation effects*). |
| Keyboard | Put the mouse away. <kbd>Tab</kbd>, <kbd>Shift</kbd>+<kbd>Tab</kbd>, <kbd>Enter</kbd>, <kbd>Space</kbd>, <kbd>Esc</kbd>. |
| Right-to-left | Switch the Nextcloud account language to **العربية** or **עברית**. Faster: set `dir="rtl"` on `<html>` in DevTools. |
| Form labels | Screen reader, or DevTools accessibility tree — check every control has a *name*. |
| Zoom | Window at 1280px wide, then <kbd>Ctrl</kbd>/<kbd>Cmd</kbd> + <kbd>+</kbd> to **400%** (that is the 320px reflow target). Plus the device toolbar for pinch-zoom. |
| Incorrect ARIA | Screen reader plus the accessibility tree. The DOM lies; the tree tells the truth. |

### A point worth making early

`npm run lint`, `npm run stylelint` and `npm run typecheck` all pass on this branch.
So does the build. Of the 40 planted issues, an automated scanner such as axe finds
roughly **nine** — the missing `alt`, four of the six contrast failures, the two
unlabelled inputs, the dangling `aria-labelledby`, and `user-scalable=no`.

The other ~31 need a person with a keyboard, a screen reader, a zoom level and a
second language. That gap *is* the lesson.

---

## 1. Text size

> **Issue:** Text does not survive the reader's own font size.
>
> **Steps to reproduce:** Set the browser's default font size to 24px (see toolbox above). Open the app, spin once, open **History**.
> **Expected:** every piece of text grows, and its container grows with it.
> **Actual:** some text does not grow at all, and some of the text that does grow is cut off by a container that does not.
>
> **WCAG:** 1.4.4 Resize Text (AA), 1.4.12 Text Spacing (AA)

### `TXT-1` — history metadata is pinned to 11px

- **Where:** `src/components/HistoryDialog.vue:146` — `.history__path, .history__meta { font-size: 11px }`
- **Was:** `font-size: 0.9em`
- **Symptom:** the path and the "3 days ago · 1.2 MB" line stay at 11px no matter what the reader has configured. It is already below Nextcloud's ~15px base and it never moves.

<details><summary>Fix</summary>

```css
.history__path,
.history__meta {
	font-size: 0.9em;
}
```
Size text relative to its context (`em`, `rem`, `%`). Reserve `px` for borders and hairlines.
</details>

### `TXT-2` — the tagline is clipped by a fixed height

- **Where:** `src/App.vue:433-439` — `.cleaner__tagline { height: 34px; overflow: hidden }`
- **Symptom:** at a larger font size the tagline wraps to a second line, and the second line is chopped off mid-glyph. The sentence stops making sense.

<details><summary>Fix</summary>

Delete `height` and `overflow`. If a bound is genuinely needed, bound it in text units and let it scroll or wrap:

```css
.cleaner__tagline {
	max-width: 26rem;
}
```
</details>

### `TXT-3` — the rules pill truncates instead of wrapping

- **Where:** `src/App.vue:442-454` — `.cleaner__scope { font-size: 12px; max-width: 22rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis }`
- **Symptom:** set a folder and a name filter in **Cleaning rules**, and the pill that explains why the wheel is narrow reads `Only in Photos, and only names conta…`. It never recovers, at any size.

<details><summary>Fix</summary>

```css
.cleaner__scope {
	font-size: 0.9em;
	overflow-wrap: anywhere;
}
```
Let the pill wrap. Truncating the only explanation of the app's current state is a content loss, not a layout choice.
</details>

### `TXT-4` — the message row is a fixed pixel height inside `overflow: hidden`

- **Where:** `src/App.vue:379` — `grid-template-rows: 1fr 128px var(--wheel-visible)`
- **Was:** `1fr 8rem var(--wheel-visible)`
- **Symptom:** the row that holds the result card and the error cards no longer grows with the text. At a large font size the card's own text overflows the row, and `.cleaner { overflow: hidden }` clips it away. The **Restore** button can disappear entirely.

<details><summary>Fix</summary>

Restore the `rem` track — `grid-template-rows: 1fr 8rem var(--wheel-visible)` — so the reserved row scales with the root font size along with everything in it.
</details>

### `TXT-5` — the wheel labels ignore text size entirely, and got smaller

- **Where:** `src/components/SpinWheel.vue:51` — `Math.min(6, Math.max(2, 40 / count))`
- **Was:** `Math.min(9, Math.max(2.6, 60 / count))`
- **Symptom:** file names on the wheel are about a third smaller than they were. Worse, they are sized in SVG viewBox units, so they are *unaffected* by the reader's font size setting — the one piece of text on the page that can never be made bigger. On a 12-file wheel they land around 11px.
- **Compounding:** on `main` this was survivable because the same file names were also exposed as real text for assistive tech. `ARIA-3` hides that list, so the tiny SVG text is now the only copy.

<details><summary>Fix</summary>

Restore the larger scale, and keep the visually hidden text list (`ARIA-3`) as the real, resizable, selectable copy of the same information. Decorative SVG text is a picture of text — it is never the accessible version.
</details>

---

## 2. Colour contrast

> **Issue:** Several pieces of text and one graphic fall below the required contrast, and hard-coded hex values ignore the theme.
>
> **Steps to reproduce:** Run axe DevTools or Lighthouse on the main page. Then switch Nextcloud to the dark theme and to the high-contrast theme.
> **Expected:** body text ≥ 4.5:1, large text and meaningful graphics ≥ 3:1, in every theme.
> **Actual:** six elements fail, and because their colours are hard-coded hex rather than theme variables, no theme can rescue them.
>
> **WCAG:** 1.4.3 Contrast Minimum (AA), 1.4.11 Non-text Contrast (AA), 1.4.1 Use of Colour (A)

Every one of these replaced a Nextcloud theme variable — `--color-text-maxcontrast`,
`--color-primary-element-light`, `--color-success-text` — with a fixed hex. That is
the underlying bug in all six: **the theme already had the right answer.**

### `CON-1` — the tagline, ~2.1:1

- **Where:** `src/App.vue:438` — `.cleaner__tagline { color: #b3b3b3 }` (was `var(--color-text-maxcontrast)`)
- `#b3b3b3` on `#ffffff` ≈ **2.1:1**. Needs 4.5:1.

### `CON-2` — the rules pill, ~2.5:1

- **Where:** `src/App.vue:448-449` — `background-color: #f5f5f5; color: #9b9b9b`
- ≈ **2.5:1**, at 12px (`TXT-3`), so it needs the full 4.5:1.

### `CON-3` — wheel labels on the pale slices, ~1.15:1

- **Where:** `src/components/SpinWheel.vue:277,290` — `.wheel__segment--b { fill: #e8f0fb }` with `.wheel__label--b { fill: #ffffff }`
- **Was:** the paired `--color-primary-element-light` / `--color-primary-element-light-text` theme variables, which are designed as a legible pair.
- White on `#e8f0fb` ≈ **1.15:1**. Every second file name on the wheel is effectively invisible. Squint at the wheel — half the slices look blank.

### `CON-4` — slices are separated by colour alone

- **Where:** `src/components/SpinWheel.vue:267-268` — `.wheel__segment { stroke: none; stroke-width: 0 }` (was `stroke: var(--color-main-background); stroke-width: 0.5`)
- **Symptom:** the boundary between two adjacent slices is now nothing but a hue change. Under greyscale, or for a reader with a colour vision deficiency, the wheel reads as one disc and it is impossible to tell where the pointer has landed.
- **WCAG:** 1.4.1 (information conveyed by colour alone) and 1.4.11 (a meaningful graphical boundary needs 3:1).

### `CON-5` — history metadata, ~2.7:1

- **Where:** `src/components/HistoryDialog.vue:145` — `color: #9e9e9e` (was `var(--color-text-maxcontrast)`)
- ≈ **2.7:1** on white, at 11px (`TXT-1`).

### `CON-6` — the "Restored" message, ~2.0:1

- **Where:** `src/components/RestoreGambleDialog.vue:173` — `.gamble__won { color: #7ec8a0 }` (was `var(--color-success-text, var(--color-success))`)
- ≈ **2.0:1**. The single most important sentence in the second-chance dialog — *"Restored. Fate was merciful."* — is the hardest one on the page to read.

<details><summary>Fix (all six)</summary>

Put every one of them back on the theme variable it replaced:

| Issue | Restore to |
|---|---|
| `CON-1` | `var(--color-text-maxcontrast)` |
| `CON-2` | `background-color: var(--color-background-hover); color: var(--color-text-maxcontrast)` |
| `CON-3` | `fill: var(--color-primary-element-light)` / `fill: var(--color-primary-element-light-text)` |
| `CON-4` | `stroke: var(--color-main-background); stroke-width: 0.5` |
| `CON-5` | `var(--color-text-maxcontrast)` |
| `CON-6` | `var(--color-success-text, var(--color-success))` |

Nextcloud's variables come in tested background/text pairs and follow the active
theme, including the dark and high-contrast ones. A hard-coded hex opts out of all
of that, silently.
</details>

---

## 3. Alt text for images

> **Issue:** Three `<img>` elements, three different ways of getting the text alternative wrong.
>
> **Steps to reproduce:** Turn on a screen reader. Load the main page, spin once, then open **History** on an account that has never deleted anything.
> **Expected:** decorative images are skipped silently; informative images are described.
> **Actual:** one image is announced as a file path, one as a filename, one as the word "image".
>
> **WCAG:** 1.1.1 Non-text Content (A)

All three images are **decorative** — they repeat information that is already in the
adjacent text. The correct alternative for all three is therefore `alt=""`, not a
better description.

### `ALT-1` — no `alt` attribute at all

- **Where:** `src/App.vue:188-192` — the hero wheel graphic
- **Symptom:** with no `alt` attribute, a screen reader falls back to announcing the image's source. The page starts with *"graphic, hero-wheel"* before it reaches the `<h1>`.
- **Note the distinction:** *missing* `alt` and `alt=""` are completely different. Only the second one means "skip this".

<details><summary>Fix</summary>

```html
<img class="cleaner__hero" :src="heroImage" alt="" width="64" height="64">
```
</details>

### `ALT-2` — the filename as the alt text

- **Where:** `src/App.vue:294` — `alt="gone.svg"` on the bin icon in the result card
- **Symptom:** announced as *"gone dot ess vee gee"*, immediately before the path of the file that was deleted. The icon repeats what the card already says in words.

<details><summary>Fix</summary>

`alt=""`. The `<span class="cleaner__victim">` next to it already carries the meaning.
</details>

### `ALT-3` — `alt="image"`

- **Where:** `src/components/HistoryDialog.vue:69` — the empty-state illustration
- **Symptom:** announced as *"image"*, which is exactly what the reader already knew. `NcEmptyContent` supplies the real message ("No casualties yet") right beside it, so the illustration adds nothing.
- **On `main`** this was an inline `<svg aria-hidden="true">`, which is the same idea done correctly.

<details><summary>Fix</summary>

```html
<img :src="emptyImage" alt="" width="64" height="64">
```
</details>

---

## 4. Reduced motion

> **Issue:** `prefers-reduced-motion` is respected nowhere on this branch, and two animations run forever with no way to stop them.
>
> **Steps to reproduce:** Turn on *Reduce motion* in the OS, or emulate `prefers-reduced-motion: reduce` in DevTools. Reload. Press **Clean a file**.
> **Expected:** the spin resolves instantly; nothing loops.
> **Actual:** the wheel still takes five full turns over 4.8 seconds, the primary button pulses continuously, and the deleted path fades in and out.
>
> **WCAG:** 2.3.3 Animation from Interactions (AAA), 2.2.2 Pause Stop Hide (A)

### `MOT-1` — one letter breaks the whole feature

- **Where:** `src/components/SpinWheel.vue:36`
  ```js
  mediaQuery = window.matchMedia('(prefers-reduced-motion: reduced)')
  ```
- **The bug:** the valid value is `reduce`, not `reduced`. An unrecognised media feature value simply never matches, so `prefersReducedMotion` is permanently `false`, `durationMs` is permanently 4800, and the instant-result path at `SpinWheel.vue:176` is dead code.
- **Why it is worth a whole station:** every line of the handling is still there — the listener, the cleanup, the computed duration. Code review passes it. It only fails when someone actually turns the setting on. **Media queries fail silently; there is no console warning, no type error, nothing.**

<details><summary>Fix</summary>

```js
mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
```

And test it with the setting on, every time. A unit test that asserts
`matchMedia` was called with the exact string catches this class of typo.
</details>

### `MOT-2` — the primary button pulses forever

- **Where:** `src/App.vue:465-478` — `.cleaner__button { animation: cfc-pulse 1.4s ease-in-out infinite }`
- **Symptom:** the main call to action never stops moving. There is no pause control and no reduced-motion guard. For a reader with a vestibular disorder this is the kind of thing that ends the session.
- **WCAG 2.2.2** applies because it moves for more than five seconds, runs in parallel with other content, and cannot be stopped.

### `MOT-3` — the deleted path fades in and out

- **Where:** `src/App.vue:539-550` — `.cleaner__victim { animation: cfc-fade 1.2s ease-in-out infinite }`
- **Symptom:** the name of the file that was just deleted — the one thing the reader needs to read carefully — pulses between full and 45% opacity forever. It also drags that text below the contrast threshold for half of every cycle.

<details><summary>Fix (MOT-2 and MOT-3)</summary>

Drop both animations; neither carries information. If an emphasis animation is
genuinely wanted, make it finite and gate it:

```css
@media (prefers-reduced-motion: reduce) {
	.cleaner__button,
	.cleaner__victim {
		animation: none;
	}
}
```

Better still, gate it the other way round — no animation by default, animation only
inside `@media (prefers-reduced-motion: no-preference)` — so the accessible
behaviour is what you get when you forget.

Note that there is **no** `prefers-reduced-motion` block anywhere in this branch's
CSS. Grepping for it is a fair way to find `MOT-2` and `MOT-3` at once.
</details>

---

## 5. Keyboard navigation

> **Issue:** One control cannot be reached at all, one cannot be seen when reached, one is reached in the wrong order, and one whole interaction is mouse-only.
>
> **Steps to reproduce:** Reload the main page and press <kbd>Tab</kbd> repeatedly without touching the mouse. Try to open the history. Try to spin the wheel by clicking it, then try the same with the keyboard.
> **Expected:** every control is reachable in document order, visibly focused, and operable with <kbd>Enter</kbd> or <kbd>Space</kbd>.
> **Actual:** see below.
>
> **WCAG:** 2.1.1 Keyboard (A), 2.4.3 Focus Order (A), 2.4.7 Focus Visible (AA), 4.1.2 Name Role Value (A)

### `KBD-1` — the History button cannot be reached or activated

- **Where:** `src/App.vue:218-224`
  ```html
  <div class="cleaner__history-button" role="button" :aria-label="…" @click="historyOpen = true">
  ```
- **Was:** a real `<NcButton>`, i.e. a `<button>`.
- **Three separate failures in one element:**
  1. a `<div>` is not focusable, so <kbd>Tab</kbd> skips straight past it;
  2. even if it were focused, `@click` alone does not fire on <kbd>Enter</kbd> or <kbd>Space</kbd> — native buttons synthesise that, `<div>`s do not;
  3. `role="button"` promises assistive tech a button that keyboard users cannot use, which is worse than no role at all.
- **Result:** the entire history feature is unreachable without a pointing device.

<details><summary>Fix</summary>

Use the real thing:

```html
<NcButton variant="secondary" size="large" @click="historyOpen = true">
	{{ t('chaotic_file_cleaner', 'History') }}
</NcButton>
```

A native `<button>` is focusable, activates on both <kbd>Enter</kbd> and
<kbd>Space</kbd>, exposes the right role, and inherits the theme's focus ring — for
free. `role="button"` + `tabindex="0"` + a `keydown` handler is the *reimplementation*
of a button, and it is almost always the wrong call.
</details>

### `KBD-2` — the focus indicator is switched off

- **Where:** `src/App.vue:390-397`
  ```css
  .cleaner :deep(button:focus),
  .cleaner :deep(button:focus-visible),
  .cleaner :deep(a:focus),
  .cleaner :deep(a:focus-visible) {
  	outline: none;
  	box-shadow: none;
  }
  ```
- **Symptom:** tabbing across the main page produces no visible change anywhere. A sighted keyboard user has no way to know what <kbd>Enter</kbd> will press — and on this page, <kbd>Enter</kbd> deletes a file.
- Both `outline` and `box-shadow` are killed, because Nextcloud's own focus style uses the latter. Removing only one would have left the ring intact.

<details><summary>Fix</summary>

Delete the whole rule. If a custom indicator is really needed, replace rather
than remove, and keep it at 3:1 against both the button and the page:

```css
.cleaner :deep(button:focus-visible) {
	outline: 2px solid var(--color-main-text);
	outline-offset: 2px;
}
```
</details>

### `KBD-3` — a positive `tabindex` scrambles the tab order

- **Where:** `src/App.vue:229` — `tabindex="3"` on the cleaning-rules cog
- **Symptom:** any `tabindex` greater than 0 jumps the element to the front of the **whole document's** tab order — ahead of Nextcloud's own header, navigation and search. Tab into the page and the third stop is the cog, before anything else on the screen. Then focus jumps back to the top of the page.
- Combined with `KBD-2` this is invisible: focus is somewhere unexpected *and* unmarked.

<details><summary>Fix</summary>

Remove the attribute. The only two legitimate values are `0` (focusable, in document
order) and `-1` (focusable by script only). Anything positive is a bug — fix the DOM
order instead.
</details>

### `KBD-4` — the wheel is click-only

- **Where:** `src/App.vue:352-359` (`@click="clean"` on `<SpinWheel>`) and `src/components/SpinWheel.vue:248-252` (`pointer-events: none` replaced with `cursor: pointer`)
- **Symptom:** clicking the wheel spins and deletes a file. The wheel is a `<div>` with no `tabindex`, so the keyboard cannot reach it and this shortcut simply does not exist for keyboard users.
- This one is *survivable* — the **Clean a file** button does the same thing — which makes it a good discussion: a mouse-only path is acceptable only when an equivalent keyboard path genuinely exists and is discoverable. Here it does. But the `role="button"` on it (`ARIA-5`) advertises the wheel as a control, so assistive tech users are told about an affordance they cannot use.

<details><summary>Fix</summary>

Either restore `pointer-events: none` and drop the `@click` — the button is the
control — or make the wheel a real `<button>` wrapping the SVG. Do not leave a
`<div>` claiming to be a button.
</details>

---

## 6. Right-to-left text

> **Issue:** The app never mirrors. In Arabic, Hebrew or Farsi the interface stays laid out for a left-to-right reader.
>
> **Steps to reproduce:** Set the Nextcloud account language to **العربية**. Open the app, open **Cleaning rules**, open **History**.
> **Expected:** the whole interface mirrors — text aligns to the right, indents and accent bars move to the right edge.
> **Actual:** nothing mirrors, and even if the root direction is forced, four separate CSS rules stay stuck on the left.
>
> **WCAG:** 1.3.2 Meaningful Sequence (A), 1.3.1 Info and Relationships (A) — and a large amount of plain usability that no success criterion covers.

### `RTL-1` — the app root is hard-coded to `dir="ltr"`

- **Where:** `templates/index.php:12`
  ```html
  <div id="chaotic_file_cleaner" dir="ltr"></div>
  ```
- **Symptom:** Nextcloud sets `dir="rtl"` on `<html>` for RTL languages, and this attribute overrides it for the whole app. Every bidi algorithm decision inside the app is now wrong: punctuation lands on the wrong side of sentences, mixed Arabic/Latin file names come out in the wrong order, and the entire layout stays mirrored the wrong way.
- **This is the master switch.** Remove it first, then the four issues below become visible.

<details><summary>Fix</summary>

```html
<div id="chaotic_file_cleaner"></div>
```

Never set `dir` on a container unless that container's content is genuinely in a
fixed, known direction that differs from the page.
</details>

### `RTL-2` — the hero uses physical left properties

- **Where:** `src/App.vue:410,416-417` — `align-items: flex-start; padding-left: 48px; text-align: left`
- **Was:** `align-items: center; text-align: center` with symmetric padding.
- **Symptom:** with `RTL-1` removed, the title, tagline and rules pill still hug the left edge with a 48px gap on the left, while the rest of the mirrored page runs to the right. The hero looks detached from its own page.

### `RTL-3` — the rules pill is offset to the left

- **Where:** `src/App.vue:444` — `.cleaner__scope { margin-left: 24px }`
- **Symptom:** the offset stays on the left in RTL, so the pill drifts away from the text it belongs to instead of lining up with it.

### `RTL-4` — the folder path is forced left-to-right

- **Where:** `src/components/SettingsDialog.vue:233-234` — `.settings__value { direction: ltr; text-align: left }`
- **Symptom:** the chosen folder in **Cleaning rules** is pinned LTR and left-aligned in the middle of an otherwise mirrored dialog. An Arabic folder name displays with its segments in the wrong order.
- **The nuance worth teaching:** forcing `ltr` on a *path* is not automatically wrong — `/` separators are read left to right even in RTL locales. What is wrong is (a) doing it with `direction` rather than `unicode-bidi: isolate` / `<bdi>`, and (b) also forcing `text-align: left`, which is a pure layout decision with no bidi justification.

### `RTL-5` — the history accent bar is on the wrong side

- **Where:** `src/components/HistoryDialog.vue:128-131` — `padding-left: 16px; border-left: 4px solid …; text-align: left`
- **Symptom:** in RTL every history row is left-aligned with its coloured accent bar on the left, i.e. at the *end* of each row rather than at its start. This is the classic physical-vs-logical mistake, and the easiest one to spot once the root direction is fixed.

<details><summary>Fix (RTL-2 … RTL-5)</summary>

Use logical properties everywhere. They resolve against the current writing direction,
so one declaration is correct in both:

| Physical | Logical |
|---|---|
| `margin-left` | `margin-inline-start` |
| `padding-left` | `padding-inline-start` |
| `border-left` | `border-inline-start` |
| `text-align: left` | `text-align: start` |
| `left: 0` | `inset-inline-start: 0` |

Note that `main` already does this correctly elsewhere — `padding-block-end` in
`HistoryDialog.vue`, for instance. The convention was there; these changes broke it.

A quick audit: `grep -nE '(margin|padding|border)-(left|right)|text-align: *(left|right)' src/`
</details>

---

## 7. Labels for forms

> **Issue:** Neither input in the cleaning-rules dialog has an accessible name.
>
> **Steps to reproduce:** Open **Cleaning rules**. Tab to each control with a screen reader running, and check the accessibility tree in DevTools.
> **Expected:** every control announces a name, a role and its current value.
> **Actual:** the text field announces *"edit, blank"*, the slider announces *"slider, 12"*.
>
> **WCAG:** 1.3.1 Info and Relationships (A), 3.3.2 Labels or Instructions (A), 4.1.2 Name Role Value (A), 2.4.6 Headings and Labels (AA)

### `LBL-1` — the name filter has a placeholder instead of a label

- **Where:** `src/components/SettingsDialog.vue:150-155`
  ```html
  <input v-model="nameFilter" class="settings__input" type="text"
  	:placeholder="t('chaotic_file_cleaner', 'Only file names containing, e.g. screenshot')">
  ```
- **Was:** `<NcTextField :label="…" :placeholder="…" :helper-text="…" />`, which renders a real, persistently visible `<label>`.
- **Three problems with placeholder-as-label:**
  1. it disappears the moment the reader types, so there is nothing left to check the entry against;
  2. placeholder text is styled at low contrast by every browser (see also `CON-*`);
  3. support for exposing it as an accessible name is inconsistent, and it is never a *label* — clicking it does not focus the field.

<details><summary>Fix</summary>

Put `NcTextField` back. It handles the label, the description and the id wiring:

```html
<NcTextField
	v-model="nameFilter"
	:label="t('chaotic_file_cleaner', 'Only file names containing')"
	:placeholder="t('chaotic_file_cleaner', 'e.g. screenshot')"
	:helper-text="t('chaotic_file_cleaner', 'Leave this empty to put every file name in danger.')" />
```

The general rule: a placeholder is a *hint*, never a name.
</details>

### `LBL-2` — the slider's `<label for>` points at nothing

- **Where:** `src/components/SettingsDialog.vue:161` (`for="wheel-size"`) versus `:166` (`id="cfc-wheel-size"`)
- **Symptom:** the ids do not match, so the association silently does not exist. The label is *visible*, which is exactly what makes this hard to catch by eye — it looks correct on screen and is broken in the tree. The slider announces *"slider, 12, minimum 3, maximum 24"* with no indication of what it controls. Clicking the label does not focus the slider either — a useful quick manual check.

<details><summary>Fix</summary>

Make them match:

```html
<label class="settings__label" for="cfc-wheel-size">…</label>
<input id="cfc-wheel-size" type="range" …>
```

Or nest the input inside the `<label>` and skip the ids entirely.
</details>

### `LBL-3` — the helper text is not connected to its field

- **Where:** `src/components/SettingsDialog.vue:155-157` — the `<span class="settings__hint">` after the filter input
- **Symptom:** *"Leave this empty to put every file name in danger."* sits next to the field visually but is not referenced by it, so a screen reader user tabbing through the form never hears it. `NcTextField`'s `helper-text` did this wiring on `main`.

<details><summary>Fix</summary>

`aria-describedby` — or, again, let `NcTextField` do it:

```html
<input aria-describedby="cfc-filter-hint" …>
<span id="cfc-filter-hint" class="settings__hint">…</span>
```
</details>

### `LBL-4` — the folder group's heading was demoted to a `<div>`

- **Where:** `src/components/SettingsDialog.vue:130-132` — a `<div class="settings__label">` (was `<h3 id="cfc-folder-heading">`)
- **Symptom:** "Folder to clean from" still *looks* like a heading — 1rem, weight 600 — but is no longer one. It vanishes from the heading list a screen reader user navigates by, and the dialog's structure flattens.
- See also `ARIA-6`: removing the `id` also broke the `aria-labelledby` that pointed at it, so the `role="group"` lost its name at the same time.

<details><summary>Fix</summary>

```html
<h3 id="cfc-folder-heading" class="settings__label">
	{{ t('chaotic_file_cleaner', 'Folder to clean from') }}
</h3>
```

Style headings to look how you want; don't replace them with styled `<div>`s.
</details>

---

## 8. Zoom

> **Issue:** The page does not reflow, and pinch-zoom is switched off.
>
> **Steps to reproduce:** Size the window to 1280px wide and zoom to **400%** (the equivalent of a 320px viewport). Then open the device toolbar and try to pinch-zoom.
> **Expected:** content reflows into a single column with vertical scrolling only, and pinch-zoom works up to at least 500%.
> **Actual:** the page scrolls horizontally, the button row overflows, and pinch-zoom is blocked.
>
> **WCAG:** 1.4.10 Reflow (AA), 1.4.4 Resize Text (AA)

### `ZOM-1` — a 960px minimum width

- **Where:** `src/App.vue:383-384` — `.cleaner { min-width: 960px }`
- **Symptom:** at 400% zoom the viewport is ~320 CSS px wide but the app insists on 960. The result is horizontal scrolling on every line of text — reading requires scrolling left and right for each one, which is precisely what 1.4.10 exists to prevent. The same failure shows on any phone in portrait.
- **The comment above it** ("The wheel and the hero need room side by side") is the kind of rationale that gets these merged. The wheel is decorative; it should shrink or go.

<details><summary>Fix</summary>

Delete `min-width`. The layout already uses `min(94vw, …)` for the wheel and
`max-width` for the text columns, so it reflows on its own once the floor is gone.
</details>

### `ZOM-2` — pinch-zoom is disabled

- **Where:** `src/main.ts:7-10`
  ```js
  document.querySelector('meta[name="viewport"]')
  	?.setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no')
  ```
- **Symptom:** the app rewrites the viewport meta tag that Nextcloud's own template set, and blocks pinch-zoom on mobile for the whole page. A reader who needs 300% magnification on a phone simply cannot get it.
- **Worth knowing:** iOS Safari has ignored `user-scalable=no` since iOS 10, and Chrome's *Force enable zoom* setting overrides it — but Android Chrome honours it by default, so most users are affected. "Some browsers ignore it" is not a defence.
- **Also worth knowing:** an app should never touch a global `<meta>` tag it does not own.

<details><summary>Fix</summary>

Delete the whole statement. Nextcloud's `<meta name="viewport">` is already correct.
If a viewport really must be set, never use `maximum-scale` or `user-scalable=no`:

```html
<meta name="viewport" content="width=device-width, initial-scale=1">
```
</details>

### `ZOM-3` — the button row refuses to wrap

- **Where:** `src/App.vue:458` — `.cleaner__actions { flex-wrap: nowrap }` (was `wrap`)
- **Symptom:** **Clean a file**, **History** and the cog are forced onto one line. Once the row is wider than the viewport — at zoom, on a phone, or in a language with longer words — they overflow instead of stacking. In German or Finnish this happens well before 400%.

<details><summary>Fix</summary>

`flex-wrap: wrap`. It was already there, and it is the correct default for any row
of controls whose combined width depends on translated text.
</details>

### `ZOM-4` — clipped content cannot be scrolled to

- **Where:** `src/App.vue:387` — `.cleaner { overflow: hidden }`
- **Note:** this one is inherited from `main`, where it was harmless — it exists to hide the bottom half of the wheel, and nothing else ever overflowed.
- **Symptom now:** combined with `TXT-4` (the 128px message row) and `ZOM-1` (the 960px floor), anything that overflows is not merely clipped but **unreachable** — there is no scrollbar to recover it. At 400% zoom the result card, and the **Restore** button inside it, can be entirely inaccessible.
- **The lesson:** `overflow: hidden` is a promise that nothing will ever overflow. That promise is broken by the next change to font size, translation length or zoom level, and it fails destructively — silently swallowing content rather than showing a scrollbar.

<details><summary>Fix</summary>

Clip only the wheel, not the whole page:

```css
.cleaner__wheel {
	overflow: hidden;
}
```

Better still, use an SVG `viewBox` that only draws the visible half, and let the
page scroll normally.
</details>

---

## 9. Incorrect ARIA

> **Issue:** Six ARIA attributes that are present, look deliberate, and are wrong. Every one of them makes the app *worse* than having no ARIA at all.
>
> **Steps to reproduce:** Open the accessibility tree in DevTools alongside a screen reader. Compare what each control looks like on screen with what it is announced as.
> **Expected:** the accessible name matches the visible label; roles match behaviour; the live region announces outcomes.
> **Actual:** two controls are misnamed, the wheel's text alternative is hidden, the live region is muted, one role is a lie, and one reference points at nothing.
>
> **WCAG:** 4.1.2 Name Role Value (A), 2.5.3 Label in Name (A), 4.1.3 Status Messages (AA), 1.1.1 Non-text Content (A)

> **The first rule of ARIA:** no ARIA is better than bad ARIA. Every issue in this
> section is *worse* than the plain HTML it replaced, because it overrides what the
> browser would otherwise have worked out correctly on its own.

### `ARIA-1` — the label names the icon, not the action

- **Where:** `src/App.vue:230` — `:aria-label="t('chaotic_file_cleaner', 'Cog')"` (was `'Cleaning rules'`)
- **Symptom:** the icon-only settings button announces as *"Cog, button"*. It describes the picture. A reader who cannot see the picture learns nothing about what pressing it does.
- **The rule:** an accessible name says what the control *does*, not what it *looks like*. "Cog", "Gear", "Three dots", "Hamburger" are all the same mistake.

<details><summary>Fix</summary>

```html
:aria-label="t('chaotic_file_cleaner', 'Cleaning rules')"
```
— which is also the name of the dialog it opens, so the reader can tell they arrived.
</details>

### `ARIA-2` — the accessible name does not contain the visible label

- **Where:** `src/App.vue:219-224` — visible text `History`, `aria-label="Casualty log"`
- **Symptom:** two failures at once.
  1. **Voice control** (Dragon, Voice Access, Voice Control): the user says *"click History"* — the words they can see — and nothing happens, because the accessible name is "Casualty log". The control is effectively invisible to them.
  2. **Screen reader** users hear "Casualty log" while a sighted colleague reads "History" over their shoulder. They cannot talk about the same interface.
- **WCAG 2.5.3 Label in Name** requires the accessible name to *contain* the visible text, in the same order.

<details><summary>Fix</summary>

Drop the `aria-label` entirely — the visible text becomes the name automatically.
`aria-label` on an element that already has visible text is nearly always a bug: it
silently *replaces* what the reader can see.
</details>

### `ARIA-3` — `aria-hidden` on the wheel's only text alternative

- **Where:** `src/App.vue:326-336` — `aria-hidden="true"` on the visually hidden list of files
- **Symptom:** on `main` this block was the accessible counterpart to the wheel: the wheel is `aria-hidden` because it is a picture, and this list carries the same information as real text. `aria-hidden="true"` here hides the *replacement* as well. The result is that assistive tech users are told nothing at all about what is on the wheel.
- **The distinction that matters:** `.cleaner__sr-only` (clip-rect) hides content **visually but not from assistive tech** — that is the whole point of the class. `aria-hidden="true"` hides it **from assistive tech but not visually**. Applying both leaves content that no one can perceive by any means. Note the same class is used on the live region below it *without* `aria-hidden`, which is the correct usage sitting ten lines away.

<details><summary>Fix</summary>

Remove the attribute. `aria-hidden="true"` belongs on decoration that is *duplicated*
elsewhere — like the wheel SVG itself — never on the duplicate.
</details>

### `ARIA-4` — the live region is muted

- **Where:** `src/App.vue:338` — `<p class="cleaner__sr-only" role="status" aria-live="off">`
- **Symptom:** the app writes "The wheel is spinning…", then "The wheel landed on X. It has been deleted." into this region. `role="status"` carries an implicit `aria-live="polite"`, but the explicit `aria-live="off"` **overrides it**, so nothing is ever announced.
- **Why it is a good station:** the region exists, it has a role, it is being updated correctly by the JavaScript, and it is completely silent. The DOM looks right. Only the accessibility tree — or a screen reader — shows that `live: off`.
- **Combined with `ARIA-3` and `KBD-2`,** a screen reader user can press the button and get no feedback whatsoever that a file has been deleted.

<details><summary>Fix</summary>

```html
<p class="cleaner__sr-only" role="status" aria-live="polite">
```

Or just `role="status"` and let the implicit value stand. Do not set `aria-live="off"`
on something whose entire purpose is to announce.
</details>

### `ARIA-5` — a role the element cannot honour

- **Where:** `src/App.vue:352-359` — `role="button" :aria-label="'Wheel'"` on `<SpinWheel>`
- **Symptom:** the wheel wrapper claims to be a button. It is a `<div>`: not focusable, no keyboard handler (`KBD-4`), and named "Wheel" — a noun that says nothing about what activating it does (which is *delete a file*). A screen reader user is told there is a button called "Wheel", cannot reach it, and could not have guessed the consequence if they had.
- **Three ways to be wrong in one attribute:** role does not match behaviour, name does not describe the action, and the name overrides content that was deliberately hidden.

<details><summary>Fix</summary>

Remove `role`, `aria-label` and `@click`, and restore `pointer-events: none` on
`.wheel`. The wheel is a picture of the app's state; the button next to it is the
control. If it must be clickable, make it a real `<button>` named for what it does
("Clean a file").
</details>

### `ARIA-6` — `aria-labelledby` pointing at an id that does not exist

- **Where:** `src/components/SettingsDialog.vue:126-132` — `aria-labelledby="cfc-folder-heading"` on the `role="group"`, while the element that carried `id="cfc-folder-heading"` is now an id-less `<div>` (`LBL-4`)
- **Symptom:** the reference dangles. A dangling `aria-labelledby` does not fall back to anything — the group is simply unnamed, so the folder controls are announced with no indication of what they configure.
- **This is the one class of ARIA bug that automated tools reliably catch** (axe: *"aria-labelledby attribute must point to an element that exists"*), which makes it a good contrast with `ARIA-1`, `ARIA-2` and `ARIA-4` in the same app, which no tool will catch.

<details><summary>Fix</summary>

Restore the heading with its id (see `LBL-4`), so the reference resolves again.
</details>

---

## What is still correct on this branch

Worth pointing out, so participants have a baseline and don't chase ghosts:

- **The dialogs are still real modals.** `NcDialog` labels them by their title, moves
  focus in on open, traps it, and returns it to the opener on <kbd>Esc</kbd>. Nothing
  in this branch touched that.
- **The wheel SVG is still `aria-hidden="true"` with `focusable="false"`.** That part
  was always right — the problem is `ARIA-3` hiding its replacement, not this.
- **The second-chance dialog's live region still works** (`role="status" aria-live="polite"`
  in `RestoreGambleDialog.vue`). Only the main page's region was muted, which makes a
  useful side-by-side comparison with `ARIA-4`.
- **The `.cleaner__sr-only` clip-rect technique itself is correct.** It is the modern
  visually-hidden pattern — no `display: none`, no `visibility: hidden`, no negative
  text-indent.
- **The backend is untouched.** No permission, routing or data changes on this branch.

---

## Facilitator notes

### The answer key

```bash
git diff main..workshop/accessibility-issues -- src templates
```

Roughly 40 issues in ~250 lines of diff across seven files. Do not show this until
after the exercise — the diff gives everything away at once.

### Files touched

| File | Issues |
|---|---|
| `src/App.vue` | `TXT-2` `TXT-3` `TXT-4` · `CON-1` `CON-2` · `ALT-1` `ALT-2` · `MOT-2` `MOT-3` · `KBD-1` `KBD-2` `KBD-3` `KBD-4` · `RTL-2` `RTL-3` · `ZOM-1` `ZOM-3` `ZOM-4` · `ARIA-1` `ARIA-2` `ARIA-3` `ARIA-4` `ARIA-5` |
| `src/components/SpinWheel.vue` | `TXT-5` · `CON-3` `CON-4` · `MOT-1` · `KBD-4` |
| `src/components/SettingsDialog.vue` | `RTL-4` · `LBL-1` `LBL-2` `LBL-3` `LBL-4` · `ARIA-6` |
| `src/components/HistoryDialog.vue` | `TXT-1` · `CON-5` · `ALT-3` · `RTL-5` |
| `src/components/RestoreGambleDialog.vue` | `CON-6` |
| `src/main.ts` | `ZOM-2` |
| `templates/index.php` | `RTL-1` |

Also added, to give the stations something to work with: `img/hero-wheel.svg`,
`img/gone.svg`, `img/no-casualties.svg`, and `src/preferences.ts` (a per-device
wheel size, which is what the unlabelled slider in `LBL-2` controls).

### Suggested station order

The concepts are not independent, and some issues mask others. This order avoids
participants hitting a wall:

1. **Colour contrast** and **alt text** — quickest wins, and the automated tools
   find most of them, which sets up the "tools only get you so far" point.
2. **Keyboard** — needs no tooling at all beyond putting the mouse down.
3. **Zoom** and **text size** — related but genuinely different; `ZOM-1` is obvious,
   `TXT-1` needs the browser font setting, and confusing the two is the most common
   misconception in the room.
4. **Reduced motion** — `MOT-2` and `MOT-3` are visible immediately; `MOT-1` is a
   one-character typo and rewards careful reading. Good as a stretch task.
5. **Right-to-left** — start by removing `RTL-1`, otherwise the other four are
   invisible. Say so if the room gets stuck.
6. **Form labels** and **incorrect ARIA** — best last, because they need the
   accessibility tree and, ideally, a screen reader. `ARIA-4` and `LBL-2` are the
   two that look perfectly fine in the DOM.

### Discussion prompts that these issues set up

- `MOT-1`, `LBL-2` and `ARIA-4` all *look correct in the source*. What kind of test
  would have caught each one?
- `KBD-1` reimplements a `<button>` out of a `<div>` and gets three things wrong.
  What did the native element give away for free?
- `CON-1` … `CON-6` all replaced a theme variable with a hex value. What else broke
  besides contrast? (Dark theme, high-contrast theme, custom branding.)
- `ZOM-4` was harmless on `main` and destructive here. Which of your own
  `overflow: hidden` rules are one font-size change away from the same thing?
- Nine of these 40 are caught by axe. Which nine, and what do the other 31 have in
  common?

### Resetting between sessions

Nothing is persisted server-side. The one piece of client state is the wheel size in
`localStorage` under `chaotic_file_cleaner.wheel_size`; clear site data to reset it.
Deleted files go to the trash bin as usual and can be restored from the Files app.

---

## Compliance note

This branch is a teaching artefact, not a contribution. It deliberately violates the
[Nextcloud Contribution Guidelines](https://github.com/nextcloud/.github/blob/master/CONTRIBUTING.md)
and the [AI Contribution Policy](https://github.com/nextcloud/.github/blob/master/AI_POLICY.md):
it ships known defects, it has no tests for the changed code, and its code comments
describe intent that is not the real intent.

Keep it off `main` and out of any pull request. If the workshop material is ever
published, publish it as a standalone repository clearly marked as such, so nobody
can mistake it for the app.
