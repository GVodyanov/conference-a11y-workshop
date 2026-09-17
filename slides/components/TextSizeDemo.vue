<script setup lang="ts">
import { ref } from 'vue'

const sizes = [16, 20, 24]
const base = ref(16)
</script>

<template>
	<div>
		<div class="nc-controls" style="margin-top: 0">
			<span class="ts__legend">Browser default font size</span>
			<button
				v-for="size in sizes"
				:key="size"
				class="nc-btn"
				:class="{ 'nc-btn--on': base === size }"
				type="button"
				@click="base = size">
				{{ size }}px
			</button>
			<span class="nc-note">(a setting, not zoom)</span>
		</div>

		<div class="ts__grid" :style="{ fontSize: base + 'px' }">
			<div class="nc-card nc-card--bad ts__panel">
				<span class="nc-card__tag"><NcIcon name="alert" /> font-size: 8px</span>
				<label class="ts__label">Display name</label>
				<input class="ts__input" type="text" placeholder="e.g. Design team" disabled>
				<p class="ts__hint ts__hint--px">
					Shown to everyone who can see this project.
				</p>
			</div>

			<div class="nc-card nc-card--good ts__panel">
				<span class="nc-card__tag"><NcIcon name="check" /> font-size: 0.85em</span>
				<label class="ts__label">Display name</label>
				<input class="ts__input" type="text" placeholder="e.g. Design team" disabled>
				<p class="ts__hint ts__hint--em">
					Shown to everyone who can see this project.
				</p>
			</div>
		</div>
	</div>
</template>

<style scoped>
.ts__legend {
	font-size: 0.78rem;
	font-weight: 600;
	color: var(--nc-text-muted);
}

.ts__grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 1rem;
	margin-top: 0.8rem;
}

.ts__panel {
	padding: 0.8rem 0.9rem 0.9rem;
}

.ts__label {
	display: block;
	font-size: 0.85em;
	font-weight: 600;
	margin-bottom: 0.25em;
}

.ts__input {
	display: block;
	width: 100%;
	font: inherit;
	font-size: 0.95em;
	padding: 0.35em 0.6em;
	border: 2px solid #cfccdb;
	border-radius: 8px;
	background: #fff;
	color: var(--nc-text);
}

/* the bug: a pixel value the user's setting cannot reach */
.ts__hint--px {
	font-size: 8px;
}

.ts__hint--em {
	font-size: 0.85em;
}

.ts__hint {
	margin: 0.4em 0 0;
	color: var(--nc-text-muted);
	line-height: 1.4;
}
</style>
