<script setup lang="ts">
defineProps<{ bad?: boolean, good?: boolean }>()
</script>

<template>
	<p class="sr" :class="{ 'sr--bad': bad, 'sr--good': good }">
		<span class="sr__icon"><NcIcon name="screenreader" /></span>
		<span class="sr__quote">“<slot />”</span>
	</p>
</template>

<style scoped>
.sr {
	display: flex;
	align-items: center;
	gap: 0.45rem;
	margin: 0.5rem 0 0;
	font-size: 0.86rem;
	color: var(--nc-text-muted);
}

.sr__icon svg {
	width: 1rem;
	height: 1rem;
	display: block;
}

.sr__quote {
	font-weight: 600;
	font-style: italic;
}

.sr--bad {
	color: var(--nc-bad);
}

.sr--good {
	color: var(--nc-good);
}
</style>
