<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const PANEL_DURATION = 1200

/** Starts from the real OS preference, then the presenter drives it. */
const reduce = ref(false)
const open = ref(false)

let query: MediaQueryList | null = null
function sync(event: MediaQueryListEvent) {
	reduce.value = event.matches
}

onMounted(() => {
	query = window.matchMedia('(prefers-reduced-motion: reduce)')
	reduce.value = query.matches
	query.addEventListener('change', sync)
})

onBeforeUnmount(() => query?.removeEventListener('change', sync))

const duration = computed(() => (reduce.value ? 0 : PANEL_DURATION))

const panelStyle = computed(() => ({
	transition: duration.value
		? `transform ${duration.value}ms cubic-bezier(.16,.84,.26,1), opacity ${duration.value}ms ease`
		: 'none',
	transform: open.value
		? 'translateX(0) scale(1) rotate(0deg)'
		: 'translateX(150%) scale(0.3) rotate(10deg)',
	opacity: open.value ? 1 : 0,
}))
</script>

<template>
	<div class="motion">
		<div class="motion__stage">
			<div class="motion__app" aria-hidden="true">
				<span class="motion__line" />
				<span class="motion__line motion__line--short" />
				<span class="motion__line" />
				<span class="motion__line motion__line--short" />
			</div>
			<div class="motion__panel" :style="panelStyle">
				<span class="motion__panel-title">Weekly report</span>
				<span class="motion__bars">
					<i style="height: 38%" /><i style="height: 64%" /><i style="height: 47%" />
					<i style="height: 82%" /><i style="height: 56%" />
				</span>
			</div>
		</div>

		<div class="motion__side">
			<button
				class="nc-btn"
				:class="{ 'nc-btn--on': reduce }"
				type="button"
				:aria-pressed="reduce"
				@click="reduce = !reduce">
				prefers-reduced-motion: {{ reduce ? 'reduce' : 'no-preference' }}
			</button>

			<button class="nc-btn motion__go" type="button" @click="open = !open">
				{{ open ? 'Close report' : 'Open report' }}
			</button>

			<dl class="motion__facts">
				<div>
					<dt>Transition</dt>
					<dd>{{ duration }}ms</dd>
				</div>
				<div>
					<dt>Travel</dt>
					<dd>{{ reduce ? 'none' : '150% + rotate' }}</dd>
				</div>
			</dl>

			<p class="nc-note">
				The report arrives either way. Honoring the preference removes the journey,
				not the destination.
			</p>
		</div>
	</div>
</template>

<style scoped>
.motion {
	display: grid;
	grid-template-columns: 19rem minmax(0, 1fr);
	gap: 1.5rem;
	align-items: center;
}

.motion__stage {
	position: relative;
	height: 10rem;
	border: 1px solid var(--nc-border);
	border-radius: var(--nc-radius);
	background: var(--nc-tint-soft);
	overflow: hidden;
	box-shadow: var(--nc-shadow);
}

.motion__app {
	position: absolute;
	inset: 0;
	display: flex;
	flex-direction: column;
	justify-content: center;
	gap: 0.55rem;
	padding: 0 1.1rem;
}

.motion__line {
	height: 9px;
	border-radius: 5px;
	background: #e2e0ea;
}

.motion__line--short {
	width: 62%;
}

.motion__panel {
	position: absolute;
	inset: 0.9rem;
	border-radius: var(--nc-radius);
	background: var(--nc-gradient);
	color: #fff;
	box-shadow: 0 6px 20px rgba(15, 8, 51, 0.25);
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	padding: 0.7rem 0.85rem 0.8rem;
}

.motion__panel-title {
	font-size: 0.9rem;
	font-weight: 700;
}

.motion__bars {
	display: flex;
	align-items: flex-end;
	gap: 0.5rem;
	height: 4rem;
}

.motion__bars i {
	flex: 1;
	border-radius: 4px 4px 0 0;
	background: rgba(255, 255, 255, 0.75);
}

.motion__side {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	gap: 0.5rem;
}

.motion__go {
	background: var(--nc-gradient);
	border-color: transparent;
	color: #fff;
	font-weight: 700;
}

.motion__go:hover {
	background: var(--nc-blue-deep);
}

.motion__facts {
	display: flex;
	gap: 1.4rem;
	margin: 0.2rem 0 0;
}

.motion__facts dt {
	font-size: 0.68rem;
	text-transform: uppercase;
	letter-spacing: 0.07em;
	font-weight: 700;
	color: var(--nc-text-muted);
}

.motion__facts dd {
	margin: 0;
	font-family: 'JetBrains Mono', monospace;
	font-size: 0.95rem;
	font-weight: 700;
	color: var(--nc-blue-deep);
}
</style>
