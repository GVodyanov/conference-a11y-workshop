<script setup lang="ts">
export interface GridItem {
	icon: string
	title: string
	sub?: string
	num?: string
}

withDefaults(defineProps<{ items: GridItem[], cols?: number, dense?: boolean }>(), {
	cols: 5,
	dense: false,
})
</script>

<template>
	<div
		class="ig"
		:class="{ 'ig--dense': dense }"
		:style="{ gridTemplateColumns: `repeat(${cols}, minmax(0, 1fr))` }">
		<div v-for="item in items" :key="item.title" class="ig__item">
			<span class="ig__icon"><NcIcon :name="item.icon" /></span>
			<span class="ig__title">{{ item.title }}</span>
			<span v-if="item.sub" class="ig__sub">{{ item.sub }}</span>
		</div>
	</div>
</template>

<style scoped>
.ig {
	display: grid;
	gap: 0.7rem;
}

.ig__item {
	display: flex;
	flex-direction: column;
	gap: 0.15rem;
	background: #fff;
	border: 1px solid var(--nc-border);
	border-radius: var(--nc-radius);
	padding: 0.65rem 0.7rem 0.7rem;
	box-shadow: var(--nc-shadow);
}

.ig--dense .ig__item {
	padding: 0.5rem 0.6rem 0.55rem;
}

.ig__icon {
	display: grid;
	place-items: center;
	width: 1.9rem;
	height: 1.9rem;
	border-radius: 9px;
	background: var(--nc-tint);
	color: var(--nc-blue-deep);
	margin-bottom: 0.25rem;
}

.ig__icon svg {
	width: 1.1rem;
	height: 1.1rem;
}

.ig__title {
	font-size: 0.82rem;
	font-weight: 700;
	line-height: 1.2;
}

.ig__sub {
	font-size: 0.7rem;
	line-height: 1.35;
	color: var(--nc-text-muted);
}
</style>
