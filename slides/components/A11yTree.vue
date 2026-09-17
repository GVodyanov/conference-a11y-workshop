<script setup lang="ts">
export interface TreeRow {
	role: string
	name: string
	depth?: number
	state?: string
	bad?: boolean
}

defineProps<{ title?: string, rows: TreeRow[] }>()
</script>

<template>
	<div class="nc-panel">
		<div class="nc-panel__bar">
			<NcIcon name="tree" class="tree__bar-icon" />
			{{ title ?? 'Accessibility tree' }}
		</div>
		<div class="nc-panel__body">
			<div
				v-for="(row, i) in rows"
				:key="i"
				class="tree__row"
				:class="{ 'is-bad': row.bad }"
				:style="{ paddingLeft: (row.depth ?? 0) * 1.1 + 'rem' }">
				<span class="tree__role">{{ row.role }}</span>
				<span class="tree__name" :class="{ 'is-empty': !row.name }">
					{{ row.name ? `“${row.name}”` : '(no name)' }}
				</span>
				<span v-if="row.state" class="tree__state">{{ row.state }}</span>
			</div>
		</div>
	</div>
</template>

<style scoped>
.tree__bar-icon {
	width: 0.9rem;
	height: 0.9rem;
}

.tree__row {
	display: flex;
	align-items: baseline;
	gap: 0.5rem;
	padding: 0.05rem 0;
}

.tree__role {
	color: var(--nc-blue-deep);
	font-weight: 700;
}

.tree__name {
	color: var(--nc-text);
}

.tree__name.is-empty {
	color: var(--nc-bad);
	font-weight: 700;
}

.tree__state {
	color: var(--nc-text-muted);
}

.tree__row.is-bad {
	background: var(--nc-bad-bg);
	border-radius: 4px;
	margin-inline: -0.25rem;
	padding-right: 0.25rem;
}
</style>
