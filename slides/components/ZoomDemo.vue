<script setup lang="ts">
import { computed, ref } from 'vue'

/*
 * Browser zoom shrinks the CSS viewport by the zoom factor and magnifies every
 * CSS pixel by the same factor. So font-relative text grows on screen, while
 * `vw` text resolves to a smaller CSS value and lands back at the same physical
 * size. `vw` cannot be used literally here (it answers to the real window), so
 * the mock viewport is modeled and the value computed.
 */
const VIEWPORT = 800 // CSS pixels of the modeled window at 100%
const DISPLAY = 430 // how wide that window is drawn on the slide

const zoom = ref(1)
const levels = [1, 2, 4]

const cssViewport = computed(() => VIEWPORT / zoom.value)
const scale = computed(() => (DISPLAY / VIEWPORT) * zoom.value)

/** 1.2vw, resolved against the zoomed CSS viewport. */
const taglinePx = computed(() => 0.012 * cssViewport.value)
/** What the audience actually sees, in slide pixels. */
const taglineOnScreen = computed(() => taglinePx.value * scale.value)
const titleOnScreen = computed(() => 24 * scale.value)
</script>

<template>
	<div class="zoom">
		<div class="zoom__viewport" :style="{ width: DISPLAY + 'px' }">
			<div
				class="zoom__page"
				:style="{
					width: cssViewport + 'px',
					transform: `scale(${scale})`,
				}">
				<h3 class="zoom__title">
					Dashboard
				</h3>
				<p class="zoom__tagline" :style="{ fontSize: taglinePx + 'px' }">
					Everything your team shipped this week.
				</p>
				<span class="zoom__btn">New project</span>
			</div>
		</div>

		<div class="zoom__side">
			<div class="nc-controls" style="margin-top: 0">
				<span class="zoom__legend">Browser zoom</span>
				<button
					v-for="level in levels"
					:key="level"
					class="nc-btn"
					:class="{ 'nc-btn--on': zoom === level }"
					type="button"
					@click="zoom = level">
					{{ level * 100 }}%
				</button>
			</div>

			<table class="zoom__table">
				<thead>
					<tr><th>Element</th><th>Sized in</th><th>On screen</th></tr>
				</thead>
				<tbody>
					<tr class="is-good">
						<td>Title</td>
						<td><code>rem</code></td>
						<td>{{ titleOnScreen.toFixed(1) }}px</td>
					</tr>
					<tr class="is-bad">
						<td>Tagline</td>
						<td><code>1.2vw</code></td>
						<td>{{ taglineOnScreen.toFixed(1) }}px</td>
					</tr>
				</tbody>
			</table>

			<p class="nc-note">
				<code>px</code> is the usual suspect, but <code>px</code> answers to zoom.
				<code>vw</code> answers to nothing.
			</p>
		</div>
	</div>
</template>

<style scoped>
.zoom {
	display: grid;
	grid-template-columns: auto 1fr;
	gap: 1.2rem;
	align-items: start;
}

.zoom__viewport {
	height: 12rem;
	overflow: hidden;
	border: 1px solid var(--nc-border);
	border-radius: var(--nc-radius-sm);
	background: #fff;
	box-shadow: var(--nc-shadow);
}

.zoom__page {
	transform-origin: top left;
	padding: 10px 14px;
	font-family: 'Inter', sans-serif;
	color: var(--nc-text);
}

.zoom__title {
	font-size: 24px;
	font-weight: 800;
	margin: 0 0 6px;
	letter-spacing: -0.02em;
}

.zoom__tagline {
	margin: 0 0 10px;
	color: var(--nc-text-muted);
	white-space: nowrap;
}

.zoom__btn {
	display: inline-block;
	font-size: 14px;
	font-weight: 700;
	color: #fff;
	background: var(--nc-gradient);
	padding: 8px 18px;
	border-radius: 999px;
}

.zoom__side {
	display: flex;
	flex-direction: column;
	gap: 0.6rem;
}

.zoom__legend {
	font-size: 0.78rem;
	font-weight: 600;
	color: var(--nc-text-muted);
}

.zoom__table {
	width: 100%;
	border-collapse: collapse;
	font-size: 0.8rem;
}

.zoom__table th {
	text-align: left;
	font-size: 0.66rem;
	text-transform: uppercase;
	letter-spacing: 0.07em;
	color: var(--nc-text-muted);
	border-bottom: 1px solid var(--nc-border);
	padding: 0 0.5rem 0.25rem 0;
}

.zoom__table td {
	padding: 0.32rem 0.5rem 0.32rem 0;
	border-bottom: 1px solid var(--nc-border);
	font-weight: 600;
}

.zoom__table tr.is-bad td:last-child {
	color: var(--nc-bad);
}

.zoom__table tr.is-good td:last-child {
	color: var(--nc-good);
}
</style>
