<script setup lang="ts">
import { ref, watch } from 'vue'
import NcDateTime from '@nextcloud/vue/components/NcDateTime'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcEmptyContent from '@nextcloud/vue/components/NcEmptyContent'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import { formatFileSize } from '@nextcloud/files'
import { t } from '@nextcloud/l10n'
import { fetchHistory, type HistoryEntry } from '../api'

const open = defineModel<boolean>({ required: true })

const entries = ref<HistoryEntry[]>([])
const loading = ref(false)
const failed = ref(false)

/**
 * Fetch the history from the server.
 */
async function load(): Promise<void> {
	loading.value = true
	failed.value = false

	try {
		entries.value = await fetchHistory()
	} catch (error) {
		failed.value = true
		console.error('[chaotic_file_cleaner] Could not load the history', error)
	} finally {
		loading.value = false
	}
}

// Refetch every time the dialog opens, so it never shows a stale list.
watch(open, (isOpen) => {
	if (isOpen) {
		load()
	}
})
</script>

<template>
	<NcDialog
		v-model:open="open"
		size="normal"
		:name="t('chaotic_file_cleaner', 'Previously cleaned files')">
		<div class="history-dialog">
			<p v-if="loading" class="history__state">
				<NcLoadingIcon :size="20" />
				{{ t('chaotic_file_cleaner', 'Digging through the wreckage…') }}
			</p>

			<NcNoteCard
				v-else-if="failed"
				type="error"
				:text="t('chaotic_file_cleaner', 'The history could not be loaded.')" />

			<NcEmptyContent
				v-else-if="entries.length === 0"
				:name="t('chaotic_file_cleaner', 'No casualties yet')"
				:description="t('chaotic_file_cleaner', 'Files you clean with the wheel show up here.')">
				<template #icon>
					<svg viewBox="0 0 24 24"
						width="20"
						height="20"
						aria-hidden="true">
						<path fill="currentColor" d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.51,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3Z" />
					</svg>
				</template>
			</NcEmptyContent>

			<ul v-else class="history">
				<li
					v-for="(entry, index) in entries"
					:key="`${entry.deletedAt}-${index}`"
					class="history__item">
					<span class="history__name">{{ entry.name }}</span>
					<!-- For a file that sat in the root the path is just the name again. -->
					<span v-if="entry.path !== entry.name" class="history__path">{{ entry.path }}</span>
					<span class="history__meta">
						<!-- Stored in seconds, but NcDateTime expects milliseconds. -->
						<NcDateTime :timestamp="entry.deletedAt * 1000" relative-time="long" />
						<span aria-hidden="true">·</span>
						<span>{{ formatFileSize(entry.size) }}</span>
					</span>
				</li>
			</ul>
		</div>
	</NcDialog>
</template>

<style scoped>
/*
 * NcDialog's content wrapper only pads its inline end, and this dialog has no
 * action buttons underneath to space the content off the bottom edge.
 */
.history-dialog {
	padding-block-end: 12px;
}

.history__state {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	margin: 0;
	padding: 24px 0;
	color: var(--color-text-maxcontrast);
}

.history {
	list-style: none;
	margin: 0;
	padding: 0;
	/* Keep long histories inside the dialog instead of stretching it. */
	max-height: 50vh;
	overflow-y: auto;
}

.history__item {
	display: flex;
	flex-direction: column;
	gap: 2px;
	padding: 10px 12px;
	border-radius: var(--border-radius-element, 8px);
}

.history__item:nth-child(odd) {
	background-color: var(--color-background-hover);
}

.history__name {
	font-weight: 500;
	overflow-wrap: anywhere;
}

.history__path,
.history__meta {
	color: var(--color-text-maxcontrast);
	font-size: 0.9em;
	overflow-wrap: anywhere;
}

.history__meta {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
}
</style>
