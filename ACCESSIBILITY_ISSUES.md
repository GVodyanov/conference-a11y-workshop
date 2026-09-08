# Accessibility workshop — planted issues

> [!CAUTION]
> **This branch is deliberately broken. Never merge it, never open a pull request from it.**
>
> `workshop/a11y-hunt` exists so that workshop participants have something real to
> find. Every issue listed below was introduced on purpose, on top of the accessible
> implementation that lives on `main`. The code comments around the planted issues
> are written the way a well-meaning developer would have written them, so the
> comments are *not* hints — they are part of the exercise.
>
> `main` is the answer key: `git diff main..workshop/a11y-hunt`.

The app itself still works. Every feature is reachable with a mouse, on a desktop
window, at 100% zoom. The damage is confined to the things this workshop is about.

---

## Contents

| # | Concept | Issues | Where to look |
|---|---------|--------|---------------|
| 1 | [Colour contrast](#1-colour-contrast) | `CON-1` | main page — the rules pill |
| 2 | [Text size](#2-text-size) | `TXT-1` | cleaning rules dialog |
| 3 | [Accessible names](#3-accessible-names) | `ALT-1` | main page — the cog button |
| 4 | [Reduced motion](#4-reduced-motion) | `MOT-1` | the wheel |
| 5 | [Keyboard navigation](#5-keyboard-navigation) | `KBD-1` | main page — the button row |
| 6 | [Labels for forms](#6-labels-for-forms) | `LBL-1` | cleaning rules dialog |
| 7 | [Zoom](#7-zoom) | `ZOM-1` | main page — the tagline |
| 8 | [Mobile](#8-mobile) | `MOB-1` … `MOB-3` | page layout, viewport meta, cog button |

**10 planted issues across 8 concepts.**

---

## Before you start

### Run it

```bash
npm ci
npm run build     # or: npm run watch
```

Then open **Chaotic file cleaner** in Nextcloud. Make sure the account has a
handful of deletable files, otherwise the wheel never appears and the reduced-motion
station is unreachable.

### The toolbox

| Concept | How to test it |
|---|---|
| Colour contrast | axe DevTools, Lighthouse, WAVE, or Firefox DevTools → *Accessibility → Check for issues → Contrast*. Needs a folder or a name filter saved, so the pill is on screen. |
| Text size | Firefox → *Settings → Fonts → Advanced → Default size* = 24. Chrome → *Settings → Appearance → Font size* = Very large. This is **not** zoom — it only moves text sized in `rem`/`em`/`%`. |
| Accessible names | A screen reader (NVDA, Orca, VoiceOver), or the accessibility tree in DevTools. |
| Reduced motion | DevTools → *Rendering → Emulate CSS `prefers-reduced-motion: reduce`*, or the real OS setting (GNOME *Accessibility → Reduce animation*, macOS *Display → Reduce motion*, Windows *Visual effects → Animation effects*). |
| Keyboard | Put the mouse away. <kbd>Tab</kbd>, <kbd>Shift</kbd>+<kbd>Tab</kbd>, <kbd>Enter</kbd>, <kbd>Space</kbd>, <kbd>Esc</kbd>. |
| Form labels | Screen reader, or DevTools accessibility tree — check every control has a *name*. |
| Zoom | Window at 1280px wide, then <kbd>Ctrl</kbd>/<kbd>Cmd</kbd> + <kbd>+</kbd> to 200% and on to **400%** (that is the 320px reflow target). |
| Mobile | DevTools device toolbar at 375×667, and a real phone if you have one. Try to pinch-zoom. |

### A point worth making early

`npm run lint`, `npm run stylelint` and `npm run typecheck` all pass on this branch.
So does `npm run build`. Of the 10 planted issues, an automated scanner such as axe
finds roughly **three** — the contrast failure, the button with no name, and
`user-scalable=no`.

The other seven need a person with a keyboard, a screen reader, a zoom level and a
phone. That gap *is* the lesson.

---

## 1. Colour contrast

> **Issue:** Text is too faint against its own background to be read reliably.
>
> **Steps to reproduce:** Open **Cleaning rules**, type `a` into *Only file names containing*, **Save**. The rules pill now shows under the tagline. Run axe DevTools, or Firefox DevTools → *Accessibility → Check for issues → Contrast*.
> **Expected:** every piece of text clears 4.5:1 against what sits behind it.
> **Actual:** the pill's text sits at 1.9:1.
>
> **WCAG:** 1.4.3 Contrast (Minimum) (AA)

### `CON-1` — the rules pill is grey on grey

- **Where:** `src/App.vue` — `.cleaner__scope`
- **Was:** `background-color: var(--color-background-hover); color: var(--color-text-maxcontrast)`
- **Now:** `background-color: #f0f0f0; color: #b0b0b0`
- **Measured:** **1.9:1**. AA wants 4.5:1 for body text, 3:1 even for large text.
- **Symptom:** the one line of UI that explains *why the wheel looks empty* is the
  hardest line on the page to read. It also ignores the theme entirely, so it stays
  a light pill in dark mode.

<details><summary>Fix</summary>

```css
.cleaner__scope {
	background-color: var(--color-background-hover);
	color: var(--color-text-maxcontrast);
}
```

Two things went wrong here, and both are worth naming separately:

1. **Hard-coded hex.** Greys pasted out of a mockup cannot follow the user's theme,
   their high-contrast setting, or dark mode. Use the paired Nextcloud variables —
   `--color-text-maxcontrast` is defined as the *faintest* text still allowed on a
   background, so it is the floor, not a suggestion.
2. **"Secondary" is a visual weight, not a licence to fade out.** De-emphasise with
   size, weight, position or whitespace. Contrast is the one budget you cannot spend.
</details>

---

## 2. Text size

> **Issue:** Text is pinned to a pixel size below what anyone configured.
>
> **Steps to reproduce:** Open **Cleaning rules** and look under the *Only file names containing* field. Then set the browser's default font size to 24px (see toolbox) and look again.
> **Expected:** the note grows with every other piece of text.
> **Actual:** it is 8px, and it stays 8px.
>
> **WCAG:** 1.4.4 Resize Text (AA)

### `TXT-1` — the filter hint is pinned to 8px

- **Where:** `src/components/SettingsDialog.vue` — `.settings__hint { font-size: 8px }`
- **Was:** the same sentence passed to `NcTextField` as `:helper-text`, which sizes
  it relative to its context.
- **Symptom:** "Leave this empty to put every file name in danger." is rendered at
  8px — about half Nextcloud's ~15px base — and a reader who has set a 24px default
  font size still gets 8px. On a hi-dpi laptop it is unreadable at arm's length, and
  it is the only place the app explains what an empty filter does.

<details><summary>Fix</summary>

```css
.settings__hint {
	font-size: 0.85em;
}
```

Better still, put the sentence back where the component can own it — see the fix for
[`LBL-1`](#lbl-1--the-filter-input-has-no-label-only-a-paragraph-near-it), which
restores `NcTextField` and its `helper-text` in one move.

Size text in `em`, `rem` or `%`. Reserve `px` for borders and hairlines — things that
should *not* grow. And note that the browser's minimum-font-size setting will not
save you: it is off by default in Chrome.
</details>

---

## 3. Accessible names

> **Issue:** An icon-only control has no name, so it is announced as nothing at all.
>
> **Steps to reproduce:** With a screen reader on, <kbd>Tab</kbd> to the button row on the main page — or, since [`KBD-1`](#5-keyboard-navigation) has taken it out of the tab order, find the cog in the DevTools accessibility tree.
> **Expected:** "Cleaning rules, button".
> **Actual:** "button". Nothing else.
>
> **WCAG:** 4.1.2 Name, Role, Value (A), 1.1.1 Non-text Content (A), 2.4.4 Link Purpose / 2.4.6 Headings and Labels (AA)

### `ALT-1` — the cog button has no accessible name

- **Where:** `src/App.vue` — the third `NcButton` in `.cleaner__actions`
- **Was:** `:aria-label="t('chaotic_file_cleaner', 'Cleaning rules')"`
- **Now:** the attribute is gone. The button's only content is an inline `<svg>` that
  is correctly marked `aria-hidden="true"` — which is right for a decorative icon,
  but here it was the *only* thing in the button, so hiding it empties the button out.
- **Symptom:** the entire cleaning-rules feature — folder scope and name filter — is
  behind a control a screen reader user cannot identify. The dialog it opens *is*
  properly labelled, which makes this worse, not better: the feature is fine once you
  are in it, and unreachable until then.

<details><summary>Fix</summary>

```html
<NcButton
	variant="tertiary"
	size="large"
	:aria-label="t('chaotic_file_cleaner', 'Cleaning rules')"
	@click="settingsOpen = true">
```

The rule for icon-only controls: the icon is decorative (`aria-hidden="true"`, which
this one already had), and the *control* carries the name. Either an `aria-label` on
the button, or visible text hidden from sight but not from the tree.

Worth pointing out during the exercise: `aria-hidden="true"` on the `<svg>` is *not*
the bug. Removing it would make the button announce as its path data or as nothing
useful. The bug is that nothing replaced the name it was hiding.
</details>

---

## 4. Reduced motion

> **Issue:** A long, large-scale animation runs regardless of the user's stated preference.
>
> **Steps to reproduce:** Turn on *Reduce motion* at the OS level, or emulate `prefers-reduced-motion: reduce` in DevTools → *Rendering*. Reload the app and press **Clean a file**.
> **Expected:** the result appears at once, with no spin.
> **Actual:** the wheel turns five full revolutions over 4.8 seconds, every time.
>
> **WCAG:** 2.3.3 Animation from Interactions (AAA)

### `MOT-1` — the wheel ignores `prefers-reduced-motion`

- **Where:** `src/components/SpinWheel.vue` — `const durationMs = SPIN_DURATION`
- **Was:** a `matchMedia('(prefers-reduced-motion: reduce)')` listener, kept in a
  `ref`, with `durationMs` computed as `prefersReducedMotion ? 0 : SPIN_DURATION`.
  A duration of `0` skipped the transition and jumped straight to the outcome.
- **Now:** the media query, its listener, its `onMounted`/`onBeforeUnmount` hooks and
  the `durationMs === 0` branch in `spin()` are all gone. `durationMs` is a constant.
- **Symptom:** a full-viewport disc spinning for nearly five seconds is exactly the
  motion that triggers nausea and dizziness for people with vestibular disorders.
  It is also unskippable, and it stands between the user and the only action the app
  offers.

<details><summary>Fix</summary>

Restore the preference, and keep honouring changes made while the app is open:

```ts
const prefersReducedMotion = ref(false)
let mediaQuery: MediaQueryList | null = null

function onMotionPreferenceChange(event: MediaQueryListEvent) {
	prefersReducedMotion.value = event.matches
}

onMounted(() => {
	mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
	prefersReducedMotion.value = mediaQuery.matches
	mediaQuery.addEventListener('change', onMotionPreferenceChange)
})

onBeforeUnmount(() => {
	mediaQuery?.removeEventListener('change', onMotionPreferenceChange)
})

const durationMs = computed(() => (prefersReducedMotion.value ? 0 : SPIN_DURATION))
```

and put the early return back in `spin()` so a zero duration resolves immediately
instead of waiting on a `transitionend` that will never fire.

Two points worth drawing out:

- **Reduced motion is not "no animation".** It means: no large-scale, non-essential
  motion. A 150ms fade is fine. Five revolutions of a half-screen disc is not.
- **Do it in JS *and* CSS.** This component drives its transition from a `ref`, so JS
  is the right place. Where a transition is declared in a stylesheet, guard it with
  `@media (prefers-reduced-motion: reduce)` too — otherwise the two disagree.
</details>

---

## 5. Keyboard navigation

> **Issue:** The primary controls cannot be reached with a keyboard.
>
> **Steps to reproduce:** Put the mouse away. Load the app and press <kbd>Tab</kbd> repeatedly.
> **Expected:** focus moves through **Clean a file**, **History** and the cog.
> **Actual:** focus skips the whole row and leaves the app. There is no way to reach any of the three.
>
> **WCAG:** 2.1.1 Keyboard (A), 2.4.3 Focus Order (A)

### `KBD-1` — all three hero buttons carry `tabindex="-1"`

- **Where:** `src/App.vue` — every `NcButton` inside `.cleaner__actions`
- **Now:** each has `tabindex="-1"`, which Vue passes straight through to the
  underlying `<button>`.
- **Symptom:** the app is mouse-only. Not "awkward with a keyboard" — **unusable**.
  Spinning the wheel, opening the history and opening the cleaning rules are the only
  three things the app does, and a keyboard reaches none of them. This also locks out
  switch access, voice control and screen readers, all of which drive the page through
  the focus order.
- **Note:** the buttons *inside* the two dialogs are untouched, so once a dialog is
  open with the mouse the keyboard works normally. That contrast is the point: the
  defect is in one row, and it is total.

<details><summary>Fix</summary>

Delete all three `tabindex="-1"` attributes. A native `<button>` is focusable by
default; that is the whole reason to reach for one.

The general rule:

- **Never** put `tabindex="-1"` on something a user is meant to activate. Its only
  legitimate use is a target you want to move focus to *programmatically* — a dialog
  container, a skip-link destination — that should not appear in the tab order itself.
- **Never** put a positive `tabindex` on anything. It jumps the element ahead of
  everything in the document and the order becomes impossible to reason about.
- Test it. Load the page, put the mouse down, and <kbd>Tab</kbd> to every control.
  Anything you cannot reach, or cannot see the focus ring on, is a bug.
</details>

---

## 6. Labels for forms

> **Issue:** A text input has no programmatic label — only a paragraph sitting near it.
>
> **Steps to reproduce:** Open **Cleaning rules** and inspect the *Only file names containing* input in the DevTools accessibility tree, or tab into it with a screen reader.
> **Expected:** "Only file names containing, edit text".
> **Actual:** "edit text", with no name. The text above it is a plain `<p>` with no relationship to the field.
>
> **WCAG:** 1.3.1 Info and Relationships (A), 3.3.2 Labels or Instructions (A), 4.1.2 Name, Role, Value (A)

### `LBL-1` — the filter input has no label, only a paragraph near it

- **Where:** `src/components/SettingsDialog.vue` — `.settings__field` holding
  `.settings__label`, `.settings__input` and `.settings__hint`
- **Was:** an `NcTextField` with `:label` and `:helper-text`, which wires up a real
  `<label for>` and an `aria-describedby` pointing at the hint.
- **Now:** a hand-rolled `<p class="settings__label">` + bare `<input>` +
  `<p class="settings__hint">`. It looks identical. Nothing connects the three.
- **Symptom:** a screen reader reads the paragraph as it passes, then arrives at an
  input with no name and no description. The user is asked to type something with no
  idea what. Clicking the "label" does not focus the field either, which is a small
  loss for everyone and a real one for anyone with a motor impairment aiming at a
  larger target than a thin input.
- **Note:** the *Folder to clean from* group above it is still correct — it uses
  `role="group"` with `aria-labelledby` pointing at a real `<h3>`. Comparing the two
  in the accessibility tree makes the difference concrete.

<details><summary>Fix</summary>

Use the component that already does this properly:

```html
<NcTextField
	v-model="nameFilter"
	:label="t('chaotic_file_cleaner', 'Only file names containing')"
	:placeholder="t('chaotic_file_cleaner', 'e.g. screenshot')"
	:helper-text="t('chaotic_file_cleaner', 'Leave this empty to put every file name in danger.')" />
```

That one change also removes [`TXT-1`](#txt-1--the-filter-hint-is-pinned-to-8px),
since the helper text goes back to being sized by the component.

If you must hand-roll a field, the label has to be a real `<label>` bound to the
input, and the hint has to be referenced:

```html
<label class="settings__label" for="cfc-name-filter">
	{{ t('chaotic_file_cleaner', 'Only file names containing') }}
</label>
<input
	id="cfc-name-filter"
	v-model="nameFilter"
	aria-describedby="cfc-name-filter-hint"
	class="settings__input"
	type="text">
<p id="cfc-name-filter-hint" class="settings__hint">…</p>
```

And a placeholder is never a label. It vanishes the moment the user types, it is
usually too faint to pass 1.4.3, and support for announcing it is inconsistent.
</details>

---

## 7. Zoom

> **Issue:** Text sized in viewport units does not grow when the page is zoomed.
>
> **Steps to reproduce:** Window at 1280px wide. Note the size of the tagline under the title. Now press <kbd>Ctrl</kbd>/<kbd>Cmd</kbd> + <kbd>+</kbd> up to 200%, then 400%.
> **Expected:** the tagline grows with the title and the buttons.
> **Actual:** the title and buttons grow. The tagline stays exactly the same physical size — at 400% it is a sliver next to everything around it.
>
> **WCAG:** 1.4.4 Resize Text (AA)

### `ZOM-1` — the tagline is sized in `vw`

- **Where:** `src/App.vue` — `.cleaner__tagline { font-size: 1.2vw }`
- **Was:** no `font-size` at all, so it inherited the body's.
- **Symptom:** it looks fine at 1280px (15.4px) and it is *wrong at every other
  width*. Measured: **15.4px at 1280px, 12.3px at 1024px, 9.6px at 800px, 4.7px on a
  390px phone.** And because browser zoom shrinks the CSS viewport by the same factor
  it magnifies each CSS pixel, `1.2vw` resolves to the same number of device pixels at
  every zoom level — the text is pinned to the screen while the rest of the page grows
  away from it.
- **Why this one is worth a whole station:** it is the counter-intuitive case. `px`
  is the usual suspect for "text that will not resize", but `px` *does* respond to
  browser zoom — it only ignores the browser's font-size setting. `vw` defeats **both**.

<details><summary>Fix</summary>

```css
.cleaner__tagline {
	margin: 0;
	max-width: 26rem;
	color: var(--color-text-maxcontrast);
}
```

Let it inherit. If it genuinely needs its own size, use `rem` or `em`.

Never size body text in `vw`, `vh`, `vmin` or `vmax`. If you want a heading that
flexes with the viewport but still answers to zoom, put a font-relative floor in the
mix — `clamp()` with a `rem` minimum, the way `.cleaner__title` does it:

```css
font-size: clamp(1.9rem, 5.5vw, 3rem);
```

The `rem` bound means zoom always has something to act on. The success criterion is
200% with no loss of content or function; `vw` alone fails it outright.
</details>

---

## 8. Mobile

> **Issue:** The layout does not survive a small screen, and the user is not allowed to zoom out of it.
>
> **Steps to reproduce:** DevTools device toolbar at 375×667 (or a real phone). Load the app. Try to read the page, then try to pinch-zoom, then try to hit the cog.
> **Expected:** content reflows into one column with no horizontal scrolling, pinch-zoom works, and every target is at least 24×24px.
> **Actual:** the page is 900px wide inside a 375px window, so it scrolls sideways and the button row runs off the screen. Pinch-zoom is disabled. The cog is 24px in a row of 44px buttons.
>
> **WCAG:** 1.4.10 Reflow (AA), 1.4.4 Resize Text (AA), 2.5.8 Target Size (Minimum) (AA)

### `MOB-1` — the layout has a 900px floor

- **Where:** `src/App.vue` — `.cleaner { min-width: 900px }`
- **Symptom:** the grid refuses to go below 900px, so on any phone the page gains a
  horizontal scrollbar and the centred content is pushed off to the right. The user
  has to scroll in two directions to read one sentence — which is the exact failure
  1.4.10 exists to name. It also breaks a narrow desktop window and a split screen,
  not only phones.

<details><summary>Fix</summary>

Delete `min-width`. The grid was already fluid: `grid-template-columns: minmax(0, 1fr)`
and `--wheel-size: min(94vw, 46rem, 60vh)` were both written to cope with any width.

Reflow's target is 320px wide (that is 1280px at 400% zoom). Anywhere you are tempted
by a pixel floor, reach for `min-width: min(900px, 100%)`, or a `max-width` plus
`width: 100%`, or a media query that stacks the layout instead of insisting on room
it does not have.
</details>

### `MOB-2` — pinch-zoom is disabled

- **Where:** `src/main.ts` — the app rewrites the page's viewport meta to
  `maximum-scale=1, user-scalable=no`
- **Symptom:** the one workaround a user has for `MOB-1` and `ZOM-1` — pinch to zoom —
  is taken away, on the whole Nextcloud page, not just this app. Anyone with low
  vision is left with a 4.7px tagline and no way to magnify it. iOS Safari has ignored
  `user-scalable=no` for years, so this mostly punishes Android and desktop users.
- **Aggravating factor:** this is a global side effect. The app reaches out of its own
  mount point and edits a tag it does not own, and the setting outlives navigation
  inside the SPA.

<details><summary>Fix</summary>

Delete the whole block. The app has no business touching the viewport meta.

```ts
const app = createApp(App)
app.mount('#chaotic_file_cleaner')
```

`user-scalable=no` and `maximum-scale` below 2 are never acceptable in production. If
a layout only holds together at a fixed scale, fix the layout. And if you catch
yourself reaching for the viewport meta to make a component behave, that is a signal
the component is sized wrong — here, `--wheel-size` was already clamped to the
viewport and needed no help.
</details>

### `MOB-3` — the cog is a 24px target, and the row will not wrap

- **Where:** `src/App.vue` — `.cleaner__cog { width: 24px; height: 24px }` and
  `.cleaner__actions { flex-wrap: nowrap }`
- **Was:** the cog was a full `size="large"` `NcButton` (44px), and the row was
  `flex-wrap: wrap`.
- **Symptom:** two problems in one place. The cog is now a 24px touch target in a row
  of 44px ones — at the AA floor with no spacing to spare, and well under the 44px
  that both Apple and Google ask for. And `nowrap` means that instead of stacking on a
  narrow screen, the three buttons squeeze and overflow their container.

<details><summary>Fix</summary>

```css
.cleaner__actions {
	flex-wrap: wrap;
}
```

and drop the `.cleaner__cog` rule entirely, letting `NcButton size="large"` keep its
own 44px box.

Two rules to take away:

- **Touch targets:** 24×24px is the AA minimum (2.5.8) and 44×44px the AAA one
  (2.5.5). Ship 44 unless you have a reason. A visually small icon can still sit in a
  large hit area — pad the button, do not shrink it.
- **Let rows wrap.** `flex-wrap: wrap` is the cheapest reflow you will ever get. Any
  time you write `nowrap`, ask what happens at 320px.
</details>

---

## Scoring

| Found | |
|---|---|
| 3 | You ran a scanner. That is the easy third. |
| 6 | You picked up a keyboard. |
| 8 | You turned on a screen reader. |
| 10 | You also opened the device toolbar and pushed zoom to 400%. |

The distribution is the lesson: automated tooling is a floor, not a finish line.
