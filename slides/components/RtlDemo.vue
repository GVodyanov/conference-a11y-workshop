<script setup lang="ts">
import { ref } from 'vue'

const rtl = ref(false)
</script>

<template>
	<div>
		<div class="nc-controls" style="margin-top: 0">
			<span class="rtl__legend">Page direction</span>
			<button
				class="nc-btn"
				:class="{ 'nc-btn--on': !rtl }"
				type="button"
				@click="rtl = false">
				dir="ltr"
			</button>
			<button
				class="nc-btn"
				:class="{ 'nc-btn--on': rtl }"
				type="button"
				@click="rtl = true">
				dir="rtl" (العربية)
			</button>
		</div>

		<div class="nc-grid" style="margin-top: 0.8rem">
			<div class="nc-card nc-card--bad rtl__card">
				<span class="nc-card__tag"><NcIcon name="alert" /> margin-left, border-left, →</span>
				<div class="rtl__panel rtl__panel--physical" :dir="rtl ? 'rtl' : 'ltr'">
					<p class="rtl__title">
						{{ rtl ? 'الإعدادات' : 'Settings' }}
					</p>
					<p class="rtl__row">
						<span class="rtl__badge">3</span>
						{{ rtl ? 'عناصر محددة' : 'items selected' }}
					</p>
					<span class="rtl__next">{{ rtl ? 'التالي' : 'Next' }} →</span>
				</div>
			</div>

			<div class="nc-card nc-card--good rtl__card">
				<span class="nc-card__tag"><NcIcon name="check" /> margin-inline-start, logical border</span>
				<div class="rtl__panel rtl__panel--logical" :dir="rtl ? 'rtl' : 'ltr'">
					<p class="rtl__title">
						{{ rtl ? 'الإعدادات' : 'Settings' }}
					</p>
					<p class="rtl__row">
						<span class="rtl__badge">3</span>
						{{ rtl ? 'عناصر محددة' : 'items selected' }}
					</p>
					<span class="rtl__next">{{ rtl ? 'التالي' : 'Next' }} <span class="rtl__arrow">→</span></span>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.rtl__legend {
	font-size: 0.78rem;
	font-weight: 600;
	color: var(--nc-text-muted);
}

.rtl__card {
	padding: 0.8rem 0.9rem 0.9rem;
}

.rtl__panel {
	background: #fff;
	border-radius: var(--nc-radius-sm);
	padding: 0.6rem 0.75rem;
	font-size: 0.9rem;
}

.rtl__title {
	font-weight: 700;
	margin: 0 0 0.35rem;
}

.rtl__row {
	display: flex;
	align-items: center;
	margin: 0 0 0.45rem;
}

.rtl__badge {
	display: inline-grid;
	place-items: center;
	width: 1.4rem;
	height: 1.4rem;
	border-radius: 50%;
	background: var(--nc-gradient);
	color: #fff;
	font-size: 0.75rem;
	font-weight: 700;
}

.rtl__next {
	display: inline-block;
	font-size: 0.8rem;
	font-weight: 600;
	color: var(--nc-blue-deep);
}

/* physical properties: pinned to the left whatever the direction */
.rtl__panel--physical {
	border-left: 4px solid var(--nc-blue);
	text-align: left;
}

.rtl__panel--physical .rtl__badge {
	margin-right: 0.5rem;
}

.rtl__panel--physical .rtl__title {
	padding-left: 0.2rem;
}

/* logical properties: they follow the direction */
.rtl__panel--logical {
	border-inline-start: 4px solid var(--nc-blue);
	text-align: start;
}

.rtl__panel--logical .rtl__badge {
	margin-inline-end: 0.5rem;
}

.rtl__panel--logical .rtl__title {
	padding-inline-start: 0.2rem;
}

.rtl__panel--logical .rtl__arrow {
	display: inline-block;
}

.rtl__panel--logical[dir='rtl'] .rtl__arrow {
	transform: scaleX(-1);
}
</style>
