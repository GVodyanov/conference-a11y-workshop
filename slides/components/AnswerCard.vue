<script setup lang="ts">
defineProps<{
	id: string
	title: string
	where: string
	wcag?: string
	scanner?: boolean
}>()
</script>

<template>
	<div class="ans">
		<div class="ans__head">
			<span class="ans__id">{{ id }}</span>
			<span v-if="scanner" class="ans__bot" title="automated checks can flag this, depending on rules and page state">
				<NcIcon name="check" /> scanner
			</span>
		</div>
		<h3 class="ans__title">
			{{ title }}
		</h3>
		<p class="ans__where">
			<code>{{ where }}</code>
		</p>
		<p class="ans__body">
			<slot />
		</p>
		<p v-if="wcag" class="ans__wcag">
			{{ wcag }}
		</p>
	</div>
</template>

<style scoped>
.ans {
	background: #fff;
	border: 1px solid var(--nc-border);
	border-radius: var(--nc-radius);
	border-top: 3px solid var(--nc-blue);
	padding: 0.55rem 0.7rem 0.65rem;
	box-shadow: var(--nc-shadow);
	height: 100%;
}

.ans__head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.4rem;
	margin-bottom: 0.2rem;
}

.ans__id {
	font-family: 'JetBrains Mono', monospace;
	font-size: 0.66rem;
	font-weight: 700;
	letter-spacing: 0.04em;
	color: var(--nc-blue-deep);
	background: var(--nc-tint);
	border-radius: 4px;
	padding: 0.1rem 0.32rem;
}

.ans__bot {
	display: inline-flex;
	align-items: center;
	gap: 0.15rem;
	font-size: 0.58rem;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	color: var(--nc-good);
}

.ans__bot svg {
	width: 0.7rem;
	height: 0.7rem;
}

.ans__title {
	font-size: 0.82rem;
	line-height: 1.25;
	margin: 0 0 0.2rem;
}

.ans__where {
	margin: 0 0 0.3rem;
}

.ans__where code {
	font-size: 0.62rem;
	padding: 0.05em 0.25em;
	word-break: break-word;
}

.ans__body {
	margin: 0;
	font-size: 0.72rem;
	line-height: 1.4;
	color: var(--nc-text-muted);
}

.ans__wcag {
	margin: 0.3rem 0 0;
	font-size: 0.62rem;
	font-weight: 700;
	color: var(--nc-blue-deep);
}
</style>
