---
theme: default
title: Checking if your app is accessible
info: |
  A web accessibility workshop.
  Tips and tricks on how to find the most common accessibility issues.
author: Hamza Mahjoubi, Grigory Vodyanov
favicon: /favicon.svg
highlighter: shiki
lineNumbers: false
transition: slide-left
drawings:
  persist: false
fonts:
  provider: none
  sans: Inter
  mono: JetBrains Mono
layout: cover
---

<div class="nc-eyebrow">Web accessibility workshop</div>

# Checking if your app is accessible

<p class="nc-lede">Tips and tricks on how to find the most common accessibility issues.</p>

<div class="nc-byline">
	<div><strong>Hamza Mahjoubi</strong><span>Nextcloud</span></div>
	<div><strong>Grigory Vodyanov</strong><span>Nextcloud</span></div>
</div>

<!--
Welcome. The session has two halves: a walk through the ten issues that account
for most of what goes wrong, and then a practical exercise where you go and find
those same issues in a real application.

Do not say what the exercise application is yet. It is meant to be a surprise.
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">How this works</div>

## Two parts

<div class="nc-grid nc-grid--even">
	<div class="nc-card nc-card--accent">
		<span class="nc-card__tag" style="color: var(--nc-blue-deep)">Part one</span>
		<h3>Ten issues, and how to catch each one</h3>
		<p class="nc-note">We will take one issue at a time and look at what it does to the interface, who it excludes, and the single check that reveals it.</p>
	</div>
	<div class="nc-card nc-card--accent">
		<span class="nc-card__tag" style="color: var(--nc-blue-deep)">Part two</span>
		<h3>Find the same issues yourself</h3>
		<p class="nc-note">You will get a working application with ten defects planted in it, drawn from the same categories. Your job is to find as many as you can.</p>
	</div>
</div>

<div class="nc-test">
	<span class="nc-test__icon"><NcIcon name="bulb" /></span>
	<div>
		None of this requires accessibility expertise. It requires a keyboard, a browser, and knowing where to look.
	</div>
</div>

<!--
Hamza and Grigory: split the stations between you however you like.

The framing that matters here is that this is a practical session rather than a
compliance session. We are not teaching the WCAG specification. We are teaching
ten habits that take about a minute each.
-->

---
layout: statement
---

<div class="nc-eyebrow">Automated checks and human testing</div>

## A clean scan is only the beginning

<p class="nc-lede" style="margin-top: 0.8rem">
	A scanner such as axe can flag contrast problems, missing names and some markup errors.
	You still need to try the complete task with a keyboard, a screen reader and zoom.
</p>

<!--
Run the scanner in each relevant state, including open dialogs and form errors.
The result depends on the tool, its rules and the state it can inspect. Missing
form labels can also be detected, so do not promise an exact three-out-of-ten count.
Have participants compare their scan with what they found while using the app.

Sources:
https://www.w3.org/WAI/test-evaluate/tools/selecting/
https://dequeuniversity.com/rules/axe/4.10/label
-->

---
layout: default
class: nc-fill
---

<div class="nc-eyebrow">The list</div>

## Ten things worth checking on every change

<IconGrid
	:cols="5"
	:items="[
		{ icon: 'contrast', title: 'Color contrast', sub: 'Can the text be read?' },
		{ icon: 'textsize', title: 'Text size', sub: 'Does it grow when asked?' },
		{ icon: 'eye', title: 'Non-text content', sub: 'Does it have a name?' },
		{ icon: 'tree', title: 'ARIA', sub: 'Role, name and state' },
		{ icon: 'motion', title: 'Reduced motion', sub: 'Can the animation be turned off?' },
		{ icon: 'keyboard', title: 'Keyboard', sub: 'Can every control be reached?' },
		{ icon: 'label', title: 'Form labels', sub: 'Is the label connected?' },
		{ icon: 'magnify', title: 'Zoom', sub: 'Does it survive 400%?' },
		{ icon: 'translate', title: 'Right to left', sub: 'Does the layout mirror?' },
		{ icon: 'phone', title: 'Mobile', sub: 'Reflow and touch targets' },
	]" />

<p class="nc-note">
	Each of these can be checked in under a minute once you know what you are looking for.
</p>

<!--
If time runs short, the four that surface the most real problems in practice are
keyboard access, color contrast, accessible names, and zooming to 400%.
-->

---
layout: section
num: "01"
---

<div class="nc-eyebrow">Station one</div>

## Color contrast

<p class="nc-lede">Text needs enough separation from its background to stay readable for people with low vision, on a dim screen, or in daylight.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">01 · Color contrast</div>

## Text that fades into its own background

<ContrastDemo />

<TestIt icon="contrast">
	<strong>How to find it:</strong> run axe DevTools, Lighthouse or WAVE, or open the
	<em>Accessibility</em> panel in Firefox and choose <em>Check for issues, Contrast</em>.
	The color picker in any browser's DevTools also reports the ratio as you inspect a value.
</TestIt>

<div class="nc-chips">
	<span class="nc-chip">WCAG 1.4.3 Contrast (Minimum), AA</span>
	<span class="nc-chip nc-chip--plain">4.5:1 for body text</span>
	<span class="nc-chip nc-chip--plain">3:1 for large text</span>
</div>

<!--
Drag the slider live and point out where it crosses 4.5.

Meaningful control boundaries and graphics have a separate 3:1 requirement
under 1.4.11. Errors and states also need a text or shape cue, so color is
never the only way to tell them apart (1.4.1).

Two separate mistakes usually hide behind a failure like this one.

The first is hard-coded color values. A gray copied out of a mockup cannot
follow the user's theme, their high-contrast mode, or dark mode. Use the paired
variables your design system already provides, and remember that a variable
named for the faintest permitted text is a floor rather than a suggestion.

The second is treating "secondary" as permission to fade text out. Secondary is
a matter of visual weight, and there are four other ways to express it: size,
weight, position and whitespace. Contrast is the one budget you cannot spend.
-->

---
layout: section
num: "02"
---

<div class="nc-eyebrow">Station two</div>

## Text size

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">02 · Text size</div>

## Text that cannot follow the reader's font size

<TextSizeDemo />

<TestIt icon="textsize">
	<strong>How to find it:</strong> in Firefox, set <em>Settings, Fonts, Advanced, Default size</em>
	to 24. In Chrome, set <em>Settings, Appearance, Font size</em> to <em>Very large</em>.
	This differs from zoom. Text in <code>rem</code>, <code>em</code> or <code>%</code>
	can follow it when root and parent sizes follow the browser default.
</TestIt>

<!--
Click through 16, 20 and 24 and let the audience watch the left panel refuse to
move.

The rule is to size text in em, rem or percentages, and to reserve pixels for
things that genuinely should not grow, such as borders and hairlines.

Do not fix the root font size in pixels and expect rem to follow the browser
default. A minimum font size setting can enlarge text, but clipping or fixed
containers can still make the result unusable. Check the rendered result.
-->

---
layout: section
num: "03"
---

<div class="nc-eyebrow">Station three</div>

## Non-text content

<p class="nc-lede">Informative images, icons and charts need an equivalent text alternative. Decorative images should stay silent.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">03 · Non-text content</div>

## Three kinds of image, three different answers

<AltTextDemo />

<TestIt icon="screenreader">
	<strong>How to find it:</strong> use a screen reader, or read the accessibility tree in DevTools.
	A scanner will tell you an alternative is missing, but only a person can tell you that the one
	you wrote is useless.
</TestIt>

<!--
The middle case is the one that gets handled wrongly in both directions. An
empty alt attribute is a deliberate decision and frequently the correct one.
Omitting the attribute entirely is not the same thing, because the screen reader
then falls back to announcing the file name.

The third case is the one that shows up most often in real code. It is worth
saying clearly that marking the icon as hidden is not the mistake. Removing that
can expose an unnamed graphic instead. The mistake is that
nothing was put in place of the name the icon was hiding.
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">03 · Non-text content</div>

## What the accessibility tree reports

<div class="nc-grid">
	<div>
		<A11yTree
			title="Icon button with no label"
			:rows="[
				{ role: 'main', name: 'Project dashboard' },
				{ role: 'button', name: 'New project', depth: 1 },
				{ role: 'button', name: 'Import', depth: 1 },
				{ role: 'button', name: '', depth: 1, bad: true },
			]" />
		<SrSays bad>button</SrSays>
	</div>
	<div>
		<A11yTree
			title="The same button with aria-label"
			:rows="[
				{ role: 'main', name: 'Project dashboard' },
				{ role: 'button', name: 'New project', depth: 1 },
				{ role: 'button', name: 'Import', depth: 1 },
				{ role: 'button', name: 'Settings', depth: 1 },
			]" />
		<SrSays good>Settings, button</SrSays>
	</div>
</div>

<TestIt icon="devtools">
	<strong>Try this now:</strong> in Chrome open <em>Elements</em> and then the <em>Accessibility</em>
	pane, or in Firefox open the <em>Accessibility</em> tab. Every interactive control should have
	a meaningful name. Structural elements do not all need names.
</TestIt>

<!--
If you would rather show the real thing, this is the slide to replace with a
screenshot of the Chrome accessibility pane. The mock is here so that the deck
works without a network and stays legible from the back of the room.

The point to make is that an entire feature can end up behind a single unnamed
control, and that the panel it opens is often perfectly labeled, which makes
the problem easy to miss in review.
-->

---
layout: section
num: "04"
---

<div class="nc-eyebrow">Station four</div>

## ARIA

<p class="nc-lede">A set of HTML attributes that describe an element to assistive technology when the markup alone does not say enough.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">04 · ARIA</div>

## Every control owes three answers

<AriaTriad />

<div class="nc-grid nc-grid--even">
	<div class="nc-card nc-card--flat">
		<h3>What the attributes do</h3>
		<p class="nc-note" style="margin: 0">
			<code>role</code> says what kind of thing this is. <code>aria-label</code> and
			<code>aria-labelledby</code> give it a name. <code>aria-expanded</code>,
			<code>aria-checked</code> and the rest report its current state.
		</p>
	</div>
	<div class="nc-card nc-card--flat">
		<h3>What they do not do</h3>
		<p class="nc-note" style="margin: 0">
			ARIA changes only what gets announced. It adds no behavior, no styling, no focus
			handling and no keyboard support. A <code>&lt;div role="button"&gt;</code> is still
			a division that ignores <kbd>Enter</kbd>.
		</p>
	</div>
</div>

<!--
Keep the framing simple. Assistive technology does not read your CSS; it reads a
parallel structure the browser builds from your markup, called the accessibility
tree. Every node in it has a role, a name and a set of states, and ARIA is how
you correct those three when the HTML gets them wrong.

The sentence worth repeating is that ARIA is a promise, not an implementation.
If you declare role="button" you have just told the user that Enter and Space
will work, and you now owe them tabindex, key handlers and a focus style, all of
which a real button provides for free.
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">04 · ARIA</div>

## When an aria-label is the right answer

<div class="nc-grid nc-grid--even">
	<div class="nc-card nc-card--good">
		<span class="nc-card__tag"><NcIcon name="check" /> Reach for it when</span>
		<ul style="margin: 0; font-size: 0.86rem">
			<li>The control has no visible text, such as an icon-only button.</li>
			<li>The visible text only makes sense in context, such as five links all reading <em>Read more</em>.</li>
			<li>A region needs distinguishing, such as two <code>&lt;nav&gt;</code> elements on one page.</li>
		</ul>
	</div>
	<div class="nc-card nc-card--bad">
		<span class="nc-card__tag"><NcIcon name="alert" /> Leave it alone when</span>
		<ul style="margin: 0; font-size: 0.86rem">
			<li>Native text or a connected label already names the control. Use <code>aria-labelledby</code> when you need to reference text elsewhere.</li>
			<li>The element has no role, such as a plain <code>&lt;div&gt;</code> or <code>&lt;span&gt;</code>. The label is simply ignored.</li>
			<li>Your label disagrees with the visible text. Voice control users say what they see, and the command stops working.</li>
		</ul>
	</div>
</div>

<div class="nc-test">
	<span class="nc-test__icon"><NcIcon name="bulb" /></span>
	<div>
		Start with native HTML. A <code>&lt;button&gt;</code> brings keyboard behavior,
		a connected <code>&lt;label&gt;</code> names a field, and <code>&lt;nav&gt;</code> identifies navigation.
	</div>
</div>

<!--
The accessible name should contain the visible label. "Save document" contains
"Save" and meets that requirement. Replacing "Save" with "Submit changes" does
not, and can make voice control harder. Prefer the visible words at the start.

Source: https://www.w3.org/WAI/WCAG22/Understanding/label-in-name.html

Also worth a sentence: aria-label does not translate unless you translate it. It
is a string in your source, so it needs to go through the same translation
pipeline as everything else on screen.
-->

---
layout: section
num: "05"
---

<div class="nc-eyebrow">Station five</div>

## Reduced motion

<p class="nc-lede">Large movement on screen causes nausea and dizziness for people with vestibular disorders, and the operating system already offers them a way to ask for less of it.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">05 · Reduced motion</div>

## Animation that runs regardless of the setting

<MotionDemo />

<TestIt icon="motion">
	<strong>How to find it:</strong> open DevTools, go to <em>Rendering</em> and emulate
	<code>prefers-reduced-motion: reduce</code>. The real setting lives in GNOME under
	<em>Accessibility, Reduce animation</em>, in macOS under <em>Display, Reduce motion</em>,
	and in Windows under <em>Visual effects, Animation effects</em>.
</TestIt>

<div class="nc-chips">
	<span class="nc-chip">WCAG 2.3.3 Animation from Interactions, AAA</span>
</div>

<!--
Toggle the preference and then open the report with each setting, so the
audience sees that the outcome is identical and only the journey differs.

Two things are worth drawing out.

Reduced motion does not mean no animation. It means no large-scale,
non-essential movement. A 150 millisecond fade is fine. A panel that travels
across the viewport while rotating and scaling is not.

And the preference has to be honored in JavaScript as well as CSS. A component
that drives its own transition from state belongs in matchMedia, while a
transition declared in a stylesheet needs a prefers-reduced-motion media query,
or the two will disagree with each other.
-->

---
layout: section
num: "06"
---

<div class="nc-eyebrow">Station six</div>

## Keyboard

<p class="nc-lede">A quick accessibility test you can do on every change. Put the mouse down and try to use the feature you just built.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">06 · Keyboard</div>

## Controls the keyboard cannot reach

<KeyboardDemo />

<TestIt icon="keyboard">
	<strong>How to find it:</strong> put the mouse away and work through the page with
	<span class="nc-kbd">Tab</span>, <span class="nc-kbd">Shift + Tab</span>,
	<span class="nc-kbd">Enter</span>, <span class="nc-kbd">Space</span> and
	<span class="nc-kbd">Esc</span>. Anything you cannot reach, or cannot see a focus ring on,
	is a defect.
</TestIt>

<!--
Press Tab a few times with the defect in place, then remove it and press again.

Do not remove ordinary buttons from the Tab order. tabindex="-1" is useful for
programmatic focus and within composite widgets that use arrow keys to move
between items. Avoid positive tabindex values and keep a logical reading order.

Try arrow keys in menus, tabs and other composite widgets. Keyboard support
includes activating controls and leaving them again, with visible focus that
sticky headers or overlays do not obscure. Screen readers also navigate by
headings and landmarks, beyond the Tab order.

Source: https://www.w3.org/WAI/ARIA/apg/practices/keyboard-interface/
-->

---
layout: section
num: "07"
---

<div class="nc-eyebrow">Station seven</div>

## Form labels

<p class="nc-lede">A field needs a label that is programmatically connected to it, not merely sitting nearby on screen.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">07 · Form labels</div>

## Text beside a field is not a label

<LabelDemo />

<TestIt icon="label">
	<strong>How to find it:</strong> click the label text. It should focus its field.
	Then check the accessibility tree for a meaningful name: an ARIA name alone does not
	provide the same click behavior.
</TestIt>

<div class="nc-chips">
	<span class="nc-chip">1.3.1 Info and Relationships, A</span>
	<span class="nc-chip">3.3.2 Labels or Instructions, A</span>
	<span class="nc-chip">4.1.2 Name, Role, Value, A</span>
</div>

<!--
Do the click test live, because it is the fastest demonstration in the deck.

The click behavior is not only a way of detecting the bug. Losing it costs
everybody a little and costs users with motor impairments a lot, since they were
aiming at a large target rather than a thin input.

Also worth saying: a placeholder is never a label. It disappears as soon as the
user types, it is usually too faint to pass the contrast requirement, and
support for announcing it is inconsistent across screen readers.

Nextcloud Vue provides NcTextField. Use its documented label and helper-text
options, then check the rendered name and description in your app.
-->

---
layout: section
num: "08"
---

<div class="nc-eyebrow">Station eight</div>

## Zoom

<p class="nc-lede">Magnifying the page is the most common adaptation there is, and some perfectly ordinary CSS quietly defeats it.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">08 · Zoom</div>

## Why <span class="nc-h-mono">px</span> survives zoom and <span class="nc-h-mono">vw</span> does not

<ZoomDemo />

<TestIt icon="magnify">
	<strong>How to find it:</strong> size the window to 1280px wide, then press
	<span class="nc-kbd">Ctrl / Cmd</span> <span class="nc-kbd">+</span> up to 200% and on to 400%.
	Nothing may be lost and nothing may stop working.
</TestIt>

<!--
Click through 100, 200 and 400 and let the audience watch the tagline stay
exactly where it is while everything around it grows.

The mechanism is worth explaining, because it is genuinely counter-intuitive.
Browser zoom shrinks the CSS viewport by the same factor that it magnifies each
CSS pixel. A value expressed in vw therefore resolves to a smaller number at
higher zoom, and the two effects cancel out exactly. The text stays pinned to
the physical screen.

Pixels are the usual suspect for text that will not resize, but pixels do
respond to zoom. They only ignore the browser's font size setting, which was
station two. Viewport units defeat both.

If you want a heading that flexes with the viewport and still answers to zoom,
put a font-relative floor in the expression, for example clamp(1.9rem, 5.5vw,
3rem). A rem bound gives zoom something to act on, but a clamp is not a guarantee
of 200% text enlargement. Test the full zoom range and prefer a simple rem value
when fluid sizing would limit enlargement.
-->

---
layout: section
num: "09"
---

<div class="nc-eyebrow">Station nine</div>

## Right to left

<p class="nc-lede">Arabic, Hebrew, Persian and Urdu are read from right to left, and a layout built out of physical directions will not follow them.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">09 · Right to left</div>

## Layout has a direction, so describe it logically

<RtlDemo />

<TestIt icon="translate">
	<strong>How to find it:</strong> switch the interface language to Arabic or Hebrew. For a
	ten second check without translations, run <code>document.dir = 'rtl'</code> in the console
	and look for anything that stays where it was.
</TestIt>

<!--
Switch to right to left and point at the blue rule, the badge and the arrow on
the left-hand card. None of the three has moved.

The substitution list is short and mechanical:

  margin-left       becomes  margin-inline-start
  padding-right     becomes  padding-inline-end
  border-left       becomes  border-inline-start
  left and right    become   inset-inline-start and inset-inline-end
  text-align: left  becomes  text-align: start
  float: left       becomes  float: inline-start

Directional icons need mirroring as well: back and forward arrows, undo, and
indentation controls. Icons that are not directional, such as a play button or a
clock, must not be mirrored.
-->

---
layout: section
num: "10"
---

<div class="nc-eyebrow">Station ten</div>

## Mobile

<p class="nc-lede">Most people will meet your interface on a phone first, where a rigid layout, a blocked zoom and a small target each fail in their own way.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">10 · Mobile</div>

## A fixed minimum width in a 375px window

<ReflowDemo />

<TestIt icon="phone">
	<strong>How to find it:</strong> open the DevTools device toolbar at 320px wide, then try 375 × 667.
	If reading one sentence requires scrolling sideways, you have found it.
</TestIt>

<!--
The left-hand phone is real rather than illustrated. That page carries a 900
pixel minimum width and a row that refuses to wrap, so scroll it sideways in
front of the audience.

The target for reflow is 320 pixels, and that number is not arbitrary: 320
pixels is what a 1280 pixel window becomes at 400% zoom. The same layout fix
helps both phone users and people who zoom. Reflow and
text resizing still need separate checks. Tables and other content that genuinely
require two dimensions have exceptions under the reflow criterion.
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">10 · Mobile</div>

## Touch targets, and one tag to never ship

<TargetDemo />

<div class="nc-card nc-card--bad">
	<span class="nc-card__tag"><NcIcon name="alert" /> The viewport meta tag</span>
	<p style="margin: 0; font-size: 0.9rem">
		<code>&lt;meta name="viewport" content="… maximum-scale=1, user-scalable=no"&gt;</code>
	</p>
	<p class="nc-note" style="margin: 0.35rem 0 0">
		This can prevent people with low vision from magnifying content on a phone.
		Some browsers override it, so check pinch zoom on a real phone as well.
		If a layout only holds together at a fixed scale, the layout is what needs fixing.
	</p>
</div>

<!--
Let the audience try to click both cogs. The small one is awkward even with a
mouse, which is rather the point.

WCAG 2.2 AA uses 24 by 24 CSS pixels, with spacing and other exceptions.
A 24px bounding box alone is not enough to judge an irregular or circular target:
check its actual hit area and spacing. AAA uses 44 by 44, also with exceptions.
Aim for a generous hit area. A visually small icon can still sit inside a
large hit area, so pad the button rather than shrinking it.

Sources:
https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html
https://www.w3.org/WAI/WCAG22/Understanding/target-size-enhanced.html
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">Beyond the ten stations</div>

## The complete task matters too

<div class="nc-grid nc-grid--even">
	<div class="nc-card nc-card--flat">
		<h3>Dialogs and focus</h3>
		<p class="nc-note">Open a dialog with the keyboard. Focus should enter it, stay inside while it is modal, and return sensibly when it closes.</p>
		<h3>Page structure</h3>
		<p class="nc-note">Use screen reader headings and landmarks to reach the main content. Check heading order and a skip link.</p>
	</div>
	<div class="nc-card nc-card--flat">
		<h3>Errors and updates</h3>
		<p class="nc-note">Submit an invalid form. Can you find the field and understand how to fix it? Can you hear when saving finishes?</p>
		<h3>More than color</h3>
		<p class="nc-note">Errors, selection and status need words or another visible cue alongside color.</p>
	</div>
</div>

<TestIt icon="screenreader">
	Try one complete task with a screen reader, including an error and a successful result.
	Include people with disabilities in usability testing.
</TestIt>

<!--
These are further checks for real apps, not extra planted exercise defects.
A modal should support Escape and provide a visible close control. Check focus
when the opener has disappeared too, such as after deleting a row.

Connect error text to its field, for example with aria-describedby, and mark an
invalid field with aria-invalid. Give a useful correction and preserve input.
For routine updates such as "Saved", use a status region that exists before the
message changes. Avoid announcing every keystroke or moving focus to a toast.

Headings should describe the content in a logical hierarchy. Landmarks and a
skip link help users bypass repeated navigation. Check the page language too.
If the app includes media, captions and transcripts need their own review.

Sources:
https://www.w3.org/WAI/ARIA/apg/patterns/dialog-modal/
https://www.w3.org/WAI/tutorials/page-structure/
https://www.w3.org/WAI/WCAG22/Understanding/error-identification.html
https://www.w3.org/WAI/WCAG22/Understanding/status-messages.html
https://www.w3.org/WAI/WCAG22/Understanding/use-of-color.html
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">Building Nextcloud apps</div>

## Nextcloud components give you a head start

<p class="nc-lede">
	A lot of accessibility work has already gone into <strong>@nextcloud/vue</strong>.
	Reusing its components brings that work into your app.
</p>

<div class="nc-grid nc-grid--even">
	<div class="nc-card nc-card--accent">
		<h3>Reuse the shared components</h3>
		<p class="nc-note">Start with components such as <code>NcButton</code>, <code>NcTextField</code> and <code>NcDialog</code>. Follow their documented options and keep compatible versions up to date.</p>
	</div>
	<div class="nc-card nc-card--flat">
		<h3>Check how they work in your app</h3>
		<p class="nc-note">Supply meaningful labels. Preserve keyboard behavior, focus styles and target sizes. Test your layout and the complete user journey.</p>
	</div>
</div>

<p class="nc-note">Documentation: <a href="https://nextcloud-vue-components.netlify.app/">Nextcloud Vue component library</a></p>

<!--
The library gives app developers the benefit of shared accessibility work.
It reduces how much interaction behavior each app has to implement and maintain.
This is a head start, not a guarantee that every combination is accessible.

For example, use the text field's label option instead of placing an unrelated
paragraph beside a bare input. An icon-only button still needs a meaningful
name from the app. Custom CSS can undo a component's focus or sizing behavior.
Report shared issues upstream so a fix can benefit other Nextcloud apps too.

Sources:
https://help.nextcloud.com/t/upcoming-changes-in-dependencies-nc-vue-dialogs/170771
https://github.com/nextcloud-libraries/nextcloud-vue
https://nextcloud-vue-components.netlify.app/
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">Worth keeping</div>

## The whole toolbox

<IconGrid
	:cols="5"
	:dense="true"
	:items="[
		{ icon: 'contrast', title: 'Contrast', sub: 'axe, Lighthouse or WAVE, or the DevTools color picker' },
		{ icon: 'textsize', title: 'Text size', sub: 'Set the browser default font size to 24px' },
		{ icon: 'tree', title: 'Names and ARIA', sub: 'The DevTools accessibility tree, or a screen reader' },
		{ icon: 'motion', title: 'Motion', sub: 'DevTools Rendering, emulate reduced motion' },
		{ icon: 'keyboard', title: 'Keyboard', sub: 'Tab, Shift+Tab, Enter, Space and Esc' },
		{ icon: 'label', title: 'Labels', sub: 'Click the label and watch where focus lands' },
		{ icon: 'magnify', title: 'Zoom', sub: 'Ctrl and plus, up to 400% at 1280px wide' },
		{ icon: 'translate', title: 'Direction', sub: 'document.dir = rtl, or switch the language' },
		{ icon: 'phone', title: 'Mobile', sub: 'Device toolbar at 320px wide, then try pinch zoom on a phone' },
		{ icon: 'screenreader', title: 'Screen reader', sub: 'NVDA, Orca or VoiceOver, thirty minutes once' },
	]" />

<p class="nc-note">
	Almost all of this is already built into the browser you have open.
</p>

<!--
This is the slide people photograph, so pause on it.

If they take away a single habit, make it this one: Tab through your own feature
before you open the pull request. Combine that with automated checks, zoom
and a screen reader test of the complete task.
-->

---
layout: section
num: "?"
---

<div class="nc-eyebrow">Part two</div>

## Now go and find them

<p class="nc-lede">Ten defects have been planted in a working application, drawn from the same categories we have just been through.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">The exercise</div>

## How it works

<div class="nc-grid exercise">
	<div class="nc-stack">
		<div class="nc-card nc-card--accent">
			<h3>Check your email</h3>
			<p class="nc-note" style="margin: 0.4rem 0 0">
				We will email you the application link and sign-in details. Open the app from that
				email, then explore it as you would review anyone else's feature.
			</p>
		</div>
		<div class="nc-card nc-card--flat">
			<h3>These slides</h3>
			<p class="exercise__link"><a href="https://accessibility.gvodyanov.ovh/">accessibility.gvodyanov.ovh</a></p>
			<p class="nc-note" style="margin: 0.4rem 0 0">
				The toolbox slide is the one you will want open beside you.
			</p>
		</div>
	</div>
	<div class="nc-stack">
		<div class="nc-card">
			<h3>Write down, for each one</h3>
			<p style="margin: 0">What is wrong &middot; who it affects &middot; how you found it</p>
		</div>
		<div class="nc-card">
			<h3>Scoring</h3>
			<p class="nc-note" style="margin: 0.2rem 0 0">
				The first person to write down all ten wins. Everyone else is ranked by how many they
				found within the time we set.
			</p>
		</div>
	</div>
</div>

<!--
Say clearly that the application works. Every feature is reachable with a mouse,
in a desktop window, at 100% zoom. The damage is confined to the ten categories
we have just covered.

Also mention that the comments around the defects are written the way a
well-meaning developer would have written them, so they are not hints.

Confirm everyone has received the exercise email and announce the time limit here.
-->

---
layout: section
num: "10"
---

<div class="nc-eyebrow">Answers</div>

## What was actually in there

<p class="nc-lede">Ten defects across eight of the ten categories.</p>

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">Answers · 1 to 5</div>

## <span class="nc-muted" style="font-weight: 600">How many did you get?</span>

<div class="nc-grid-3" style="gap: 0.7rem">
	<AnswerCard id="CON-1" title="Secondary text is gray on gray" where="App.vue · .cleaner__scope" wcag="1.4.3 AA" :scanner="true">
		Hard-coded <code>#b0b0b0</code> on <code>#f0f0f0</code>, measuring 1.9:1. It also ignores the theme, so it stays light in dark mode.
	</AnswerCard>
	<AnswerCard id="TXT-1" title="The field hint is pinned to 8px" where="SettingsDialog.vue · .settings__hint" wcag="1.4.4 AA">
		Roughly half the base size, and a 24px browser setting still produces 8px. It is the only place the behavior is explained.
	</AnswerCard>
	<AnswerCard id="ALT-1" title="The settings button has no accessible name" where="App.vue · third NcButton" wcag="4.1.2 A · 1.1.1 A" :scanner="true">
		The <code>aria-label</code> was removed, leaving only an <code>aria-hidden</code> icon inside. An entire feature sits behind it.
	</AnswerCard>
	<AnswerCard id="MOT-1" title="The animation ignores prefers-reduced-motion" where="SpinWheel.vue · durationMs" wcag="2.3.3 AAA">
		The <code>matchMedia</code> listener is gone and the duration is a constant, so a long full-width animation runs every time.
	</AnswerCard>
	<AnswerCard id="KBD-1" title="All three main buttons carry tabindex=-1" where="App.vue · .cleaner__actions" wcag="2.1.1 A · 2.4.3 A">
		Not awkward with a keyboard but unusable. The buttons inside the dialogs still work, which is what makes the row easy to overlook.
	</AnswerCard>
	<div class="nc-card nc-card--accent" style="display: flex; flex-direction: column; justify-content: center">
		<p class="nc-note" style="margin: 0">
			<strong style="color: var(--nc-blue-deep)">Five more over the page</strong><br>
			Form labels, zoom, and three separate mobile defects.
		</p>
	</div>
</div>

<!--
Go one card at a time and ask who found it before explaining it.

CON-1 and ALT-1 are examples that automated checks can flag. Results depend on
the rendered state and the rules enabled in the tool.
-->

---
layout: default
class: nc-tight nc-fill
---

<div class="nc-eyebrow">Answers · 6 to 10</div>

## <span class="nc-muted" style="font-weight: 600">The remaining five findings</span>

<div class="nc-grid-3" style="gap: 0.7rem">
	<AnswerCard id="LBL-1" title="A text input has no label, only a paragraph near it" where="SettingsDialog.vue · .settings__field" wcag="1.3.1 A · 3.3.2 A">
		A paragraph and bare input replaced the library component. The visible label and hint have no programmatic connection.
	</AnswerCard>
	<AnswerCard id="ZOM-1" title="A line of text is sized in vw" where="App.vue · .cleaner__tagline" wcag="1.4.4 AA">
		<code>1.2vw</code>. Acceptable at 1280px, wrong at every other width, and completely immune to zoom.
	</AnswerCard>
	<AnswerCard id="MOB-1" title="The layout has a 900px floor" where="App.vue · .cleaner" wcag="1.4.10 AA">
		The grid was already fluid. The minimum width forces sideways scrolling on any phone, and in any narrow desktop window.
	</AnswerCard>
	<AnswerCard id="MOB-2" title="Pinch to zoom is disabled" where="main.ts · viewport meta" wcag="1.4.4 AA" :scanner="true">
		<code>user-scalable=no</code>, written onto the whole page. It can block magnification on browsers that honor it.
	</AnswerCard>
	<AnswerCard id="MOB-3" title="A 24px target in a row of 44px ones" where="App.vue · .cleaner__cog" wcag="1.4.10 AA · target: verify 2.5.8 AA">
		A smaller target is harder to tap. AA depends on its hit area and spacing. The row also overflows because of <code>flex-wrap: nowrap</code>.
	</AnswerCard>
	<div class="nc-card nc-card--accent" style="display: flex; flex-direction: column; justify-content: center">
		<p class="nc-note" style="margin: 0">
			<strong style="color: var(--nc-blue-deep)">Compare both kinds of check</strong><br>
			Which findings came from a scanner, and which appeared when you used the app?
		</p>
	</div>
</div>

<!--
MOB-2 can be flagged by a viewport rule. LBL-1 may also be flagged, depending on
the accessible name the browser computes. Open the settings dialog before scanning.

MOB-1 is worth dwelling on, because it also breaks a narrow desktop window and a
split screen. It tends to get filed as a phone bug when it is not one.
-->

---
layout: cover
---

<div class="nc-eyebrow">Thank you</div>

# Go break your own app

<div class="nc-byline">
	<div><strong>Hamza Mahjoubi</strong><span>Nextcloud</span></div>
	<div><strong>Grigory Vodyanov</strong><span>Nextcloud</span></div>
</div>

<p class="nc-note" style="margin-top: 1.4rem; color: rgba(255,255,255,0.75)">
	Slides: <a href="https://accessibility.gvodyanov.ovh/" style="color: inherit; text-decoration: underline">accessibility.gvodyanov.ovh</a><br>
	WCAG quick reference: <a href="https://www.w3.org/WAI/WCAG22/quickref/" style="color: inherit; text-decoration: underline">w3.org/WAI/WCAG22/quickref</a>
</p>

<!--
Questions.

For anyone who wants to go further, point them at the WAI quick reference, and
offer to sit down with anyone who wants help setting up a screen reader once.
-->
