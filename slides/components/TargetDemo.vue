<script setup lang="ts">
import { ref } from 'vue'

const hits = ref(0)
const misses = ref(0)

function hit() {
	hits.value++
}

function miss() {
	misses.value++
}

function reset() {
	hits.value = 0
	misses.value = 0
}
</script>

<template>
	<div class="tgt">
		<div class="tgt__stage" @click="miss">
			<p class="tgt__caption">
				Try to tap each cog. The ring is roughly a fingertip.
			</p>
			<div class="tgt__row">
				<div class="tgt__slot">
					<button class="tgt__cog tgt__cog--small" type="button" @click.stop="hit">
						<NcIcon name="cog" />
						<span class="nc-sr-only">Settings, 24px target</span>
					</button>
					<span class="tgt__finger" aria-hidden="true" />
					<span class="tgt__size is-bad">24 × 24</span>
				</div>

				<div class="tgt__slot">
					<button class="tgt__cog tgt__cog--large" type="button" @click.stop="hit">
						<NcIcon name="cog" />
						<span class="nc-sr-only">Settings, 44px target</span>
					</button>
					<span class="tgt__finger" aria-hidden="true" />
					<span class="tgt__size is-good">44 × 44</span>
				</div>
			</div>
			<div class="nc-controls">
				<span class="tgt__score is-good">Hits {{ hits }}</span>
				<span class="tgt__score is-bad">Misses {{ misses }}</span>
				<button class="nc-btn" type="button" @click.stop="reset">
					Reset
				</button>
			</div>
		</div>

		<div class="tgt__notes">
			<ul>
				<li><strong>24 × 24px</strong> is the AA floor (2.5.8).</li>
				<li><strong>44 × 44px</strong> is AAA, and what Apple and Google both ask for.</li>
				<li>A small icon can still sit in a large hit area. Pad the button, do not shrink it.</li>
			</ul>
		</div>
	</div>
</template>

<style scoped>
.tgt {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 1.2rem;
	align-items: center;
}

.tgt__stage {
	background: var(--nc-tint-soft);
	border: 1px solid var(--nc-border);
	border-radius: var(--nc-radius);
	padding: 0.8rem 1rem 0.9rem;
}

.tgt__caption {
	font-size: 0.75rem;
	font-weight: 600;
	color: var(--nc-text-muted);
	margin: 0 0 0.6rem;
}

.tgt__row {
	display: flex;
	gap: 3rem;
	justify-content: center;
	padding: 0.8rem 0 0.4rem;
}

.tgt__slot {
	position: relative;
	display: grid;
	place-items: center;
	width: 3.5rem;
	height: 3.5rem;
}

.tgt__cog {
	position: relative;
	z-index: 2;
	display: grid;
	place-items: center;
	border: none;
	border-radius: 50%;
	background: var(--nc-gradient);
	color: #fff;
	cursor: pointer;
	padding: 0;
}

.tgt__cog svg {
	width: 16px;
	height: 16px;
}

.tgt__cog--small {
	width: 24px;
	height: 24px;
}

.tgt__cog--large {
	width: 44px;
	height: 44px;
}

.tgt__cog:focus-visible {
	outline: 3px solid var(--nc-navy);
	outline-offset: 2px;
}

.tgt__finger {
	position: absolute;
	width: 44px;
	height: 44px;
	border-radius: 50%;
	border: 2px dashed var(--nc-text-muted);
	opacity: 0.55;
	pointer-events: none;
}

.tgt__size {
	position: absolute;
	bottom: -1.1rem;
	font-size: 0.66rem;
	font-weight: 700;
	white-space: nowrap;
}

.tgt__size.is-bad {
	color: var(--nc-bad);
}

.tgt__size.is-good {
	color: var(--nc-good);
}

.tgt__score {
	font-size: 0.78rem;
	font-weight: 700;
}

.tgt__score.is-bad {
	color: var(--nc-bad);
}

.tgt__score.is-good {
	color: var(--nc-good);
}

.tgt__notes ul {
	font-size: 0.9rem;
}
</style>
