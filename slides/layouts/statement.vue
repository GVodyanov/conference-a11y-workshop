<template>
	<div class="slidev-layout nc-statement nc-dark">
		<div class="nc-blobs">
			<span style="width: 26rem; height: 26rem; left: 50%; top: 50%; transform: translate(-50%, -50%)" />
			<span style="width: 38rem; height: 38rem; left: 50%; top: 50%; transform: translate(-50%, -50%)" />
		</div>
		<div class="nc-statement__content">
			<slot />
		</div>
		<NcFooter />
	</div>
</template>

<style scoped>
.nc-statement {
	display: flex;
	align-items: center;
	justify-content: center;
	text-align: center;
	position: relative;
	overflow: hidden;
}

.nc-statement__content {
	position: relative;
	z-index: 1;
	max-width: 44rem;
}

.nc-statement__content :deep(h2) {
	font-size: 2.4rem;
	padding-bottom: 0;
}

.nc-statement__content :deep(h2::after) {
	display: none;
}

.nc-statement__content :deep(ul) {
	text-align: left;
	display: inline-block;
}
</style>
