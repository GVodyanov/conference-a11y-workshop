<template>
	<div class="slidev-layout nc-cover nc-dark">
		<div class="nc-blobs">
			<span style="width: 34rem; height: 34rem; right: -11rem; top: -13rem" />
			<span style="width: 22rem; height: 22rem; right: -4rem; top: -5rem" />
			<span style="width: 15rem; height: 15rem; left: -6rem; bottom: -6rem" />
		</div>
		<img
			class="nc-cover__logo"
			src="/nextcloud-logo-white.svg"
			alt=""
			aria-hidden="true">
		<div class="nc-cover__content">
			<slot />
		</div>
	</div>
</template>

<style scoped>
.nc-cover__logo {
	position: absolute;
	top: 2.4rem;
	right: 3rem;
	width: 5.6rem;
	opacity: 0.95;
}

.nc-cover__content {
	position: relative;
	z-index: 1;
	max-width: 42rem;
}
</style>
