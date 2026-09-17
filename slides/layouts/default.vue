<template>
	<div class="slidev-layout default nc-default">
		<div class="nc-default__body">
			<slot />
		</div>
		<NcFooter />
	</div>
</template>

<style scoped>
.nc-default {
	display: flex;
	flex-direction: column;
}

.nc-default__body {
	flex: 1;
	min-height: 0;
}
</style>
