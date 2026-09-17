<script setup lang="ts">
defineProps<{
	/** Big watermark numeral, e.g. the station number. */
	num?: string | number
}>()
</script>

<template>
	<div class="slidev-layout nc-section nc-dark">
		<div class="nc-blobs">
			<span style="width: 30rem; height: 30rem; right: -9rem; bottom: -13rem" />
			<span style="width: 18rem; height: 18rem; right: -2rem; bottom: -6rem" />
		</div>
		<div v-if="num" class="nc-section__num" aria-hidden="true">
			{{ num }}
		</div>
		<div class="nc-section__content">
			<slot />
		</div>
		<NcFooter />
	</div>
</template>

<style scoped>
.nc-section__num {
	position: absolute;
	right: 2.6rem;
	top: 50%;
	transform: translateY(-50%);
	font-size: 13rem;
	font-weight: 800;
	line-height: 1;
	letter-spacing: -0.05em;
	color: rgba(255, 255, 255, 0.11);
	user-select: none;
}

.nc-section__content {
	position: relative;
	z-index: 1;
	max-width: 36rem;
}

.nc-section__content :deep(h1) {
	font-size: 2.9rem;
}

.nc-section__content :deep(h2) {
	font-size: 2.6rem;
	padding-bottom: 0;
}

.nc-section__content :deep(h2::after) {
	display: none;
}
</style>
