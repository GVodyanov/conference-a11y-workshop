<script setup lang="ts">
import { ref, watch } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import { FilePickerClosed, getFilePickerBuilder } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { fetchSettings, saveSettings } from '../api'

const open = defineModel<boolean>({ required: true })
const emit = defineEmits<{ saved: [] }>()

const folder = ref('')
const nameFilter = ref('')
const loading = ref(false)
const saving = ref(false)
const loadFailed = ref(false)
const saveError = ref('')

/**
 * Read the stored rules into the form.
 */
async function load(): Promise<void> {
	loading.value = true
	loadFailed.value = false
	saveError.value = ''

	try {
		const settings = await fetchSettings()
		folder.value = settings.folder
		nameFilter.value = settings.nameFilter
	} catch (error) {
		loadFailed.value = true
		console.error('[chaotic_file_cleaner] Could not load the settings', error)
	} finally {
		loading.value = false
	}
}

// Start from what is actually stored every time the dialog opens, so a
// cancelled edit is never carried over into the next one.
watch(open, (isOpen) => {
	if (isOpen) {
		load()
	}
})

/**
 * Let the user choose a folder with the Nextcloud file picker.
 *
 * The picker is a dialog of its own opened on top of this one. Both use the
 * same focus-trap instance, so the trap stack pauses this dialog while the
 * picker is up.
 */
async function pickFolder(): Promise<void> {
	const picker = getFilePickerBuilder(t('chaotic_file_cleaner', 'Choose a folder to clean from'))
		.setMultiSelect(false)
		// Directories only: the wheel walks a folder, it cannot start at a file.
		.setMimeTypeFilter(['httpd/unix-directory'])
		.allowDirectories(true)
		.addButton({
			label: t('chaotic_file_cleaner', 'Clean from here'),
			variant: 'primary',
			callback: () => {},
		})
		.startAt(`/${folder.value}`)
		.build()

	try {
		const picked = await picker.pick()
		folder.value = picked.replace(/^\/+/, '').replace(/\/+$/, '')
	} catch (error) {
		if (error instanceof FilePickerClosed) {
			// The user changed their mind; leave the current folder alone.
			return
		}

		console.error('[chaotic_file_cleaner] The folder picker failed', error)
	}
}

/**
 * Store the rules, then let the parent refill the wheel.
 */
async function save(): Promise<void> {
	saving.value = true
	saveError.value = ''

	try {
		const stored = await saveSettings({ folder: folder.value, nameFilter: nameFilter.value })
		folder.value = stored.folder
		nameFilter.value = stored.nameFilter
		emit('saved')
		open.value = false
	} catch (error) {
		saveError.value = t('chaotic_file_cleaner', 'Those rules could not be saved. Does the folder still exist?')
		console.error('[chaotic_file_cleaner] Could not save the settings', error)
	} finally {
		saving.value = false
	}
}
</script>

<template>
	<NcDialog
		v-model:open="open"
		size="normal"
		:name="t('chaotic_file_cleaner', 'Cleaning rules')">
		<p v-if="loading" class="settings__state">
			<NcLoadingIcon :size="20" />
			{{ t('chaotic_file_cleaner', 'Reading your rules…') }}
		</p>

		<NcNoteCard
			v-else-if="loadFailed"
			type="error"
			:text="t('chaotic_file_cleaner', 'Your rules could not be loaded.')" />

		<div v-else class="settings">
			<div
				class="settings__field"
				role="group"
				aria-labelledby="cfc-folder-heading">
				<h3 id="cfc-folder-heading" class="settings__label">
					{{ t('chaotic_file_cleaner', 'Folder to clean from') }}
				</h3>
				<p class="settings__value">
					{{ folder === '' ? t('chaotic_file_cleaner', 'All of your files') : folder }}
				</p>
				<div class="settings__row">
					<NcButton variant="secondary" @click="pickFolder">
						{{ t('chaotic_file_cleaner', 'Choose folder…') }}
					</NcButton>
					<NcButton
						v-if="folder !== ''"
						variant="tertiary"
						@click="folder = ''">
						{{ t('chaotic_file_cleaner', 'Use all files') }}
					</NcButton>
				</div>
			</div>

			<div class="settings__field">
				<p class="settings__label">
					{{ t('chaotic_file_cleaner', 'Only file names containing') }}
				</p>
				<input
					v-model="nameFilter"
					class="settings__input"
					type="text"
					:placeholder="t('chaotic_file_cleaner', 'e.g. screenshot')">
				<p class="settings__hint">
					{{ t('chaotic_file_cleaner', 'Leave this empty to put every file name in danger.') }}
				</p>
			</div>

			<NcNoteCard v-if="saveError !== ''" type="error" :text="saveError" />
		</div>

		<template #actions>
			<NcButton variant="tertiary" @click="open = false">
				{{ t('chaotic_file_cleaner', 'Cancel') }}
			</NcButton>
			<NcButton
				variant="primary"
				:disabled="loading || saving || loadFailed"
				@click="save">
				<template v-if="saving" #icon>
					<NcLoadingIcon :size="20" />
				</template>
				{{ t('chaotic_file_cleaner', 'Save') }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<style scoped>
.settings__state {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	margin: 0;
	padding: 24px 0;
	color: var(--color-text-maxcontrast);
}

.settings {
	display: flex;
	flex-direction: column;
	gap: 20px;
	padding-bottom: 8px;
}

.settings__field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.settings__label {
	margin: 0;
	font-size: 1rem;
	font-weight: 600;
}

.settings__value {
	margin: 0;
	padding: 8px 12px;
	border-radius: var(--border-radius-element, 8px);
	background-color: var(--color-background-hover);
	font-family: var(--font-face-monospace, monospace);
	overflow-wrap: anywhere;
}

.settings__row {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	margin-top: 4px;
}

.settings__input {
	box-sizing: border-box;
	width: 100%;
	margin: 0;
	padding: 8px 12px;
	border: 2px solid var(--color-border-maxcontrast);
	border-radius: var(--border-radius-element, 8px);
	background-color: var(--color-main-background);
	color: var(--color-main-text);
	font-size: 1rem;
}

/* Kept small so the note sits under the field without competing with it. */
.settings__hint {
	margin: 0;
	color: var(--color-text-maxcontrast);
	font-size: 8px;
}
</style>
