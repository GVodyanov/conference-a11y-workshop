<script setup lang="ts">
import { computed, ref } from 'vue'

const broken = ref(true)
const step = ref(-1)

/** Everything on the page, in document order. */
const order = computed(() => broken.value
	? ['nav', 'footer']
	: ['nav', 'spin', 'history', 'cog', 'footer'])

const focused = computed(() => (step.value < 0 ? null : order.value[step.value] ?? 'out'))

function tab() {
	step.value = step.value + 1 >= order.value.length ? -1 : step.value + 1
}

function toggle() {
	broken.value = !broken.value
	step.value = -1
}
</script>

<template>
	<div>
		<div class="nc-frame">
			<div class="nc-frame__bar">
				<span class="nc-frame__dot" /><span class="nc-frame__dot" /><span class="nc-frame__dot" />
				<span class="nc-frame__title">Project dashboard</span>
			</div>
			<div class="nc-frame__body kb__body">
				<span class="kb__nav" :class="{ 'is-focused': focused === 'nav' }">Overview</span>

				<div class="kb__actions">
					<span
						class="kb__btn kb__btn--primary"
						:class="{ 'is-focused': focused === 'spin' }">
						New project
					</span>
					<span class="kb__btn" :class="{ 'is-focused': focused === 'history' }">
						Import
					</span>
					<span class="kb__btn kb__btn--icon" :class="{ 'is-focused': focused === 'cog' }">
						<NcIcon name="cog" />
					</span>
				</div>

				<p v-if="broken" class="kb__annotation">
					all three carry <code>tabindex="-1"</code>
				</p>

				<span class="kb__nav" :class="{ 'is-focused': focused === 'footer' }">Help and feedback</span>
			</div>
		</div>

		<div class="nc-controls">
			<button class="nc-btn nc-btn--on" type="button" @click="tab">
				Press <span class="nc-kbd kb__key">Tab</span>
			</button>
			<button class="nc-btn" type="button" @click="toggle">
				{{ broken ? 'Remove the tabindex' : 'Put tabindex="-1" back' }}
			</button>
			<span class="kb__readout" :class="broken ? 'is-bad' : 'is-good'">
				<NcIcon :name="broken ? 'alert' : 'check'" />
				{{ order.length }} stops on this page
			</span>
		</div>
	</div>
</template>

<style scoped>
.kb__body {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 0.7rem;
	padding: 0.9rem;
}

.kb__nav {
	font-size: 0.8rem;
	font-weight: 600;
	color: var(--nc-blue-deep);
	text-decoration: underline;
	text-decoration-style: solid;
	padding: 0.15rem 0.35rem;
	border-radius: 5px;
}

.kb__annotation {
	display: flex;
	align-items: center;
	gap: 0.3rem;
	margin: 0;
	font-size: 0.72rem;
	font-weight: 600;
	color: var(--nc-bad);
}

.kb__annotation code {
	font-size: 0.66rem;
	background: var(--nc-bad-bg);
	border-color: var(--nc-bad-border);
	color: var(--nc-bad);
}

.kb__actions {
	display: flex;
	align-items: center;
	gap: 0.6rem;
}

.kb__btn {
	position: relative;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 0.35rem;
	height: 2.4rem;
	padding: 0 1rem;
	border-radius: 999px;
	background: #f2f1f6;
	color: var(--nc-text);
	font-size: 0.85rem;
	font-weight: 600;
	border: 1px solid var(--nc-border);
}

.kb__btn--primary {
	background: var(--nc-gradient);
	color: #fff;
	border-color: transparent;
}

.kb__btn--icon {
	width: 2.4rem;
	padding: 0;
}

.kb__btn--icon svg {
	width: 1.1rem;
	height: 1.1rem;
}

.is-focused {
	outline: 3px solid var(--nc-navy);
	outline-offset: 2px;
}

.kb__key {
	box-shadow: none;
	background: rgba(255, 255, 255, 0.85);
	border-color: transparent;
}

.kb__readout {
	display: inline-flex;
	align-items: center;
	gap: 0.3rem;
	font-size: 0.8rem;
	font-weight: 700;
}

.kb__readout svg {
	width: 0.95rem;
	height: 0.95rem;
}

.kb__readout.is-bad {
	color: var(--nc-bad);
}

.kb__readout.is-good {
	color: var(--nc-good);
}
</style>
