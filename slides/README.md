# Checking if your app is accessible

Slides for the web accessibility workshop, by Hamza Mahjoubi and Grigory Vodyanov.
Built with [Slidev](https://sli.dev). 36 slides: roughly 30 minutes of walkthrough,
then the exercise, then the answer key.

## Before presenting

The presentation link is [accessibility.gvodyanov.ovh](https://accessibility.gvodyanov.ovh/).
Slide 32 directs participants to their email for the exercise application link and
sign-in details. Send that email before the exercise and announce the time limit.

The exercise application is deliberately never named in the deck. It is meant to be a
surprise, and none of the demos reuse its wording or its components, so nothing on
screen gives away what participants will be looking at.

## Running it

Needs Node 24 (see `.nvmrc`; the app itself pins Node 20, this is separate).

```bash
nvm use
npm install
npm run dev      # http://localhost:3030
```

Press <kbd>P</kbd> in the browser, or open `/presenter/`, for presenter mode with the
speaker notes. Every slide has notes, and they carry the detail that is deliberately
kept off the slide.

```bash
npm run build    # static site into dist/
npm run export   # PDF (needs playwright-chromium installed)
```

The deck is fully self-contained. Inter, JetBrains Mono, Noto Sans Arabic and Noto
Sans Hebrew are bundled, so it renders identically with no network.

## Structure

| Slides | What |
|---|---|
| 1 to 4 | Cover, how the session runs, automated and manual testing, the list of stations |
| 5 to 27 | Ten stations, each a divider plus one demo slide (ARIA and mobile have two) |
| 28 to 29 | Complete task checks and reusing Nextcloud Vue components |
| 30 | The toolbox, the slide people photograph |
| 31 to 32 | Part two: the exercise brief and the scoring |
| 33 to 36 | The answer key and the close |

The ten stations are color contrast, text size, non-text content, ARIA, reduced
motion, keyboard, form labels, zoom, right to left, and mobile.

## Live demos

Every station carries a working demo rather than a screenshot, so it can be driven in
front of the room. All of them use a generic sample interface, a "Project dashboard"
with a display name field and a settings button, so none of them resemble the
application used in part two.

| Slide | Demo | What to do |
|---|---|---|
| 6 | `ContrastDemo` | Drag the slider and watch the ratio cross 4.5:1 |
| 8 | `TextSizeDemo` | Click 16 / 20 / 24px; the 8px hint never moves |
| 16 | `MotionDemo` | Toggle the preference, then open the report panel |
| 18 | `KeyboardDemo` | Press *Tab* repeatedly, then remove the tabindex |
| 20 | `LabelDemo` | Click either label; only the connected one takes focus |
| 22 | `ZoomDemo` | Click 100 / 200 / 400%; the `vw` line stays the same size |
| 24 | `RtlDemo` | Switch to `dir="rtl"`; the left card keeps its left border |
| 26 | `ReflowDemo` | The left phone really does scroll sideways |
| 27 | `TargetDemo` | Try to click the 24px settings button |

`MotionDemo` reads the real `prefers-reduced-motion` on load, so if the presenting
machine has reduced motion enabled, the toggle starts in the honoring state.

## Coverage against the exercise

The deck covers all ten planted defects listed in `../ACCESSIBILITY_ISSUES.md`:

| Station | Defect |
|---|---|
| Color contrast | `CON-1` |
| Text size | `TXT-1` |
| Non-text content | `ALT-1` |
| Reduced motion | `MOT-1` |
| Keyboard | `KBD-1` |
| Form labels | `LBL-1` |
| Zoom | `ZOM-1` |
| Mobile | `MOB-1`, `MOB-2`, `MOB-3` |

Right to left has no planted defect. It is in the deck as a station because it is the
item on the list that gets broken most reliably in practice, but there is nothing to
find for it in part two.

ARIA is station four and underpins both `ALT-1` and `LBL-1`.

The additional checks on slide 28 are discussion points for real apps, not extra
planted exercise defects. The answer key distinguishes usability findings from
WCAG failures: a 24px target needs its actual hit area and spacing checked.
Scanner coverage depends on tool rules and the state of the interface, so the
deck does not claim a fixed number of automatically detected defects.

## Styling

Nextcloud brand values taken from nextcloud.com: Inter, `#0082c9` primary,
`linear-gradient(45deg, #0082c9 20%, #1cafff)` as the signature gradient, and
`#0f0833` for dark surfaces. Copy uses American spelling throughout.

- `styles/nextcloud.css` holds the tokens and the shared classes (`nc-card`, `nc-test`,
  `nc-chip`, `nc-eyebrow` and so on).
- `layouts/` overrides `default`, `cover`, `section` and `statement`.
- `components/` holds the demos. `components/icons.ts` is Material Design Icons path
  data extracted from `@mdi/js`.

Useful classes when editing slides:

- `nc-fill` on a slide centers its content between the heading and the footer, and
  spaces the blocks below the heading evenly. Leave it off for slides that need the
  full height.
- `nc-grid--even` makes side-by-side cards share a height.
