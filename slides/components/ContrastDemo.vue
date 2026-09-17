<script setup lang="ts">
import { computed, ref } from 'vue'

/** Relative luminance, WCAG 2.x definition. */
function luminance(hex: string): number {
	const n = parseInt(hex.slice(1), 16)
	const channels = [(n >> 16) & 255, (n >> 8) & 255, n & 255]
	const [r, g, b] = channels.map((c) => {
		const s = c / 255
		return s <= 0.03928 ? s / 12.92 : ((s + 0.055) / 1.055) ** 2.4
	})
	return 0.2126 * r + 0.7152 * g + 0.0722 * b
}

function ratio(fg: string, bg: string): number {
	const a = luminance(fg)
	const b = luminance(bg)
	return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05)
}

const background = '#f0f0f0'
const lightness = ref(176) // 0xb0, the planted value

const foreground = computed(() => {
	const v = lightness.value.toString(16).padStart(2, '0')
	return `#${v}${v}${v}`
})

const current = computed(() => ratio(foreground.value, background))
const passes = computed(() => current.value >= 4.5)
</script>

<template>
	<div class="contrast">
		<div class="contrast__stage">
			<p class="contrast__caption">
				Secondary text on a card
			</p>
			<span
				class="contrast__pill"
				:style="{ backgroundColor: background, color: foreground }">
				Draft saved a moment ago
			</span>

			<div class="nc-controls">
				<label class="contrast__label" for="contrast-range">Text lightness</label>
				<input
					id="contrast-range"
					v-model.number="lightness"
					type="range"
					min="0"
					max="220"
					step="4">
				<code>{{ foreground }}</code>
			</div>
		</div>

		<div class="contrast__readout" :class="passes ? 'is-pass' : 'is-fail'">
			<span class="contrast__ratio">{{ current.toFixed(1) }}<span>:1</span></span>
			<span class="contrast__verdict">
				<NcIcon :name="passes ? 'check' : 'alert'" />
				{{ passes ? 'Passes AA' : 'Fails AA' }}
			</span>
			<span class="contrast__target">AA body text needs 4.5:1</span>
		</div>
	</div>
</template>

<style scoped>
.contrast {
	display: grid;
	grid-template-columns: 1fr 12rem;
	gap: 1rem;
	align-items: stretch;
}

.contrast__stage {
	background: var(--nc-tint-soft);
	border: 1px solid var(--nc-border);
	border-radius: var(--nc-radius);
	padding: 0.85rem 1rem 1rem;
}

.contrast__caption {
	font-size: 0.75rem;
	font-weight: 600;
	color: var(--nc-text-muted);
	margin: 0 0 0.5rem;
}

.contrast__pill {
	display: inline-block;
	padding: 0.35rem 0.8rem;
	border-radius: 999px;
	font-size: 1rem;
	font-weight: 500;
}

.contrast__label {
	font-size: 0.78rem;
	font-weight: 600;
	color: var(--nc-text-muted);
}

.contrast input[type='range'] {
	flex: 1;
	min-width: 6rem;
	accent-color: var(--nc-blue);
}

.contrast__readout {
	border-radius: var(--nc-radius);
	padding: 0.8rem;
	display: flex;
	flex-direction: column;
	justify-content: center;
	gap: 0.3rem;
	text-align: center;
	border: 1px solid;
}

.contrast__readout.is-fail {
	background: var(--nc-bad-bg);
	border-color: var(--nc-bad-border);
	color: var(--nc-bad);
}

.contrast__readout.is-pass {
	background: var(--nc-good-bg);
	border-color: var(--nc-good-border);
	color: var(--nc-good);
}

.contrast__ratio {
	font-size: 2.1rem;
	font-weight: 800;
	line-height: 1;
	letter-spacing: -0.03em;
}

.contrast__ratio span {
	font-size: 1rem;
	font-weight: 600;
}

.contrast__verdict {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 0.3rem;
	font-size: 0.82rem;
	font-weight: 700;
}

.contrast__verdict svg {
	width: 0.95rem;
	height: 0.95rem;
}

.contrast__target {
	font-size: 0.68rem;
	color: var(--nc-text-muted);
}
</style>
