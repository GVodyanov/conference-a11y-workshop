<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcContent from '@nextcloud/vue/components/NcContent'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import { showError } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { imagePath } from '@nextcloud/router'
import SpinWheel, { type WheelSegment } from './components/SpinWheel.vue'
import HistoryDialog from './components/HistoryDialog.vue'
import SettingsDialog from './components/SettingsDialog.vue'
import RestoreGambleDialog from './components/RestoreGambleDialog.vue'
import { deleteFile, fetchWheel, isNotFound, type CleanableFile } from './api'
import { getWheelSize } from './preferences'

const heroImage = imagePath('chaotic_file_cleaner', 'hero-wheel.svg')
const goneImage = imagePath('chaotic_file_cleaner', 'gone.svg')

const wheel = ref<InstanceType<typeof SpinWheel> | null>(null)

const files = ref<CleanableFile[]>([])
const totalFiles = ref(0)
const loading = ref(true)
const loadError = ref<'' | 'generic' | 'folder'>('')
const activeFolder = ref('')
const activeFilter = ref('')
const phase = ref<'idle' | 'spinning' | 'deleting'>('idle')
const lastDeleted = ref<CleanableFile | null>(null)
const announcement = ref('')
const historyOpen = ref(false)
const settingsOpen = ref(false)
const gambleOpen = ref(false)
/**
 * Path being gambled for, held separately from `lastDeleted` so that winning
 * the spin — which clears the result card — does not tear the dialog down
 * before it can say so.
 */
const gamblePath = ref('')
const canRestore = ref(false)

const busy = computed(() => phase.value !== 'idle')
const isEmpty = computed(() => !loading.value && loadError.value === '' && files.value.length === 0)
const canSpin = computed(() => !loading.value && loadError.value === '' && !busy.value && files.value.length > 0)
const isNarrowed = computed(() => activeFolder.value !== '' || activeFilter.value !== '')

/** The wheel only needs a key and something to write on each slice. */
const wheelSegments = computed<WheelSegment[]>(() =>
	files.value.map((file) => ({ key: String(file.id), label: file.name })),
)

/** A short description of the rules currently in force, if any. */
const scopeLabel = computed(() => {
	if (activeFolder.value !== '' && activeFilter.value !== '') {
		return t('chaotic_file_cleaner', 'Only in {folder}, and only names containing “{filter}”', {
			folder: activeFolder.value,
			filter: activeFilter.value,
		})
	}
	if (activeFolder.value !== '') {
		return t('chaotic_file_cleaner', 'Only in {folder}', { folder: activeFolder.value })
	}
	if (activeFilter.value !== '') {
		return t('chaotic_file_cleaner', 'Only names containing “{filter}”', { filter: activeFilter.value })
	}
	return ''
})

const buttonLabel = computed(() => {
	if (phase.value === 'spinning') {
		return t('chaotic_file_cleaner', 'Spinning…')
	}
	if (phase.value === 'deleting') {
		return t('chaotic_file_cleaner', 'Deleting…')
	}
	return t('chaotic_file_cleaner', 'Clean a file')
})

/**
 * Draw a fresh handful of files for the wheel.
 *
 * @param quiet Refill without showing the initial loading state
 */
async function load(quiet = false): Promise<void> {
	if (!quiet) {
		loading.value = true
	}
	loadError.value = ''

	try {
		const result = await fetchWheel(getWheelSize())
		files.value = result.files
		totalFiles.value = result.total
		activeFolder.value = result.folder
		activeFilter.value = result.nameFilter
	} catch (error) {
		// A 404 here means the folder in the settings has been moved or deleted.
		loadError.value = isNotFound(error) ? 'folder' : 'generic'
		files.value = []
		console.error('[chaotic_file_cleaner] Could not load the wheel', error)
	} finally {
		loading.value = false
	}
}

/**
 * Spin the wheel and delete whichever file it lands on.
 */
async function clean(): Promise<void> {
	if (!canSpin.value) {
		return
	}

	lastDeleted.value = null
	canRestore.value = false

	const index = Math.floor(Math.random() * files.value.length)
	const doomed = files.value[index]

	phase.value = 'spinning'
	announcement.value = t('chaotic_file_cleaner', 'The wheel is spinning…')

	await wheel.value?.spin(index)

	phase.value = 'deleting'

	try {
		const outcome = await deleteFile(doomed.id)
		lastDeleted.value = outcome.deleted
		canRestore.value = outcome.canRestore
		announcement.value = t(
			'chaotic_file_cleaner',
			'The wheel landed on {name}. It has been deleted.',
			{ name: doomed.name },
		)
	} catch (error) {
		console.error('[chaotic_file_cleaner] Could not delete the chosen file', error)
		showError(t('chaotic_file_cleaner', 'Could not delete {name}. It survived.', { name: doomed.name }))
		announcement.value = t('chaotic_file_cleaner', 'Could not delete {name}. It survived.', { name: doomed.name })
	} finally {
		phase.value = 'idle'
	}

	// Reload so the wheel never offers a file that is already gone.
	await load(true)
}

/**
 * Offer fate a second opinion on the file that was just deleted.
 */
function openGamble(): void {
	if (lastDeleted.value === null) {
		return
	}

	gamblePath.value = lastDeleted.value.path
	gambleOpen.value = true
}

// Only let go of the dialog once it has actually been dismissed.
watch(gambleOpen, (isOpen) => {
	if (!isOpen) {
		gamblePath.value = ''
	}
})

/**
 * Fate relented: drop the result card and put the file back on the wheel.
 *
 * @param path The path that came back
 */
async function onRestored(path: string): Promise<void> {
	announcement.value = t('chaotic_file_cleaner', '{name} was restored.', { name: path })
	lastDeleted.value = null
	canRestore.value = false
	await load(true)
}

onMounted(() => load())
</script>

<template>
	<NcContent app-name="chaotic_file_cleaner">
		<NcAppContent>
			<div class="cleaner">
				<div class="cleaner__stage">
					<img
						class="cleaner__hero"
						:src="heroImage"
						width="64"
						height="64">

					<h1 class="cleaner__title">
						{{ t('chaotic_file_cleaner', 'Chaotic file cleaner') }}
					</h1>
					<p class="cleaner__tagline">
						{{ t('chaotic_file_cleaner', 'Press the button. The wheel picks one of your files, and that file is gone.') }}
					</p>

					<p v-if="scopeLabel !== ''" class="cleaner__scope">
						{{ scopeLabel }}
					</p>

					<div class="cleaner__actions">
						<NcButton
							class="cleaner__button"
							variant="primary"
							size="large"
							:disabled="!canSpin"
							@click="clean">
							<template v-if="busy" #icon>
								<NcLoadingIcon :size="20" />
							</template>
							{{ buttonLabel }}
						</NcButton>

						<div
							class="cleaner__history-button"
							role="button"
							:aria-label="t('chaotic_file_cleaner', 'Casualty log')"
							@click="historyOpen = true">
							{{ t('chaotic_file_cleaner', 'History') }}
						</div>

						<NcButton
							variant="tertiary"
							size="large"
							tabindex="3"
							:aria-label="t('chaotic_file_cleaner', 'Cog')"
							@click="settingsOpen = true">
							<template #icon>
								<svg
									viewBox="0 0 24 24"
									width="20"
									height="20"
									aria-hidden="true">
									<path fill="currentColor" d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
								</svg>
							</template>
						</NcButton>
					</div>
				</div>

				<div class="cleaner__feedback">
					<p v-if="loading" class="cleaner__hint">
						<NcLoadingIcon :size="20" />
						{{ t('chaotic_file_cleaner', 'Rounding up your files…') }}
					</p>

					<NcNoteCard
						v-else-if="loadError === 'folder'"
						type="error"
						:heading="t('chaotic_file_cleaner', 'That folder is gone')">
						{{ t('chaotic_file_cleaner', 'The folder in your cleaning rules no longer exists. Pick another one.') }}
						<NcButton class="cleaner__retry" variant="secondary" @click="settingsOpen = true">
							{{ t('chaotic_file_cleaner', 'Open cleaning rules') }}
						</NcButton>
					</NcNoteCard>

					<NcNoteCard
						v-else-if="loadError === 'generic'"
						type="error"
						:heading="t('chaotic_file_cleaner', 'The wheel is stuck')">
						{{ t('chaotic_file_cleaner', 'Your files could not be loaded.') }}
						<NcButton class="cleaner__retry" variant="secondary" @click="load()">
							{{ t('chaotic_file_cleaner', 'Try again') }}
						</NcButton>
					</NcNoteCard>

					<NcNoteCard
						v-else-if="isEmpty"
						type="info"
						:heading="t('chaotic_file_cleaner', 'Nothing left to lose')">
						<template v-if="isNarrowed">
							{{ t('chaotic_file_cleaner', 'No file matches your cleaning rules.') }}
							<NcButton class="cleaner__retry" variant="secondary" @click="settingsOpen = true">
								{{ t('chaotic_file_cleaner', 'Open cleaning rules') }}
							</NcButton>
						</template>
						<template v-else>
							{{ t('chaotic_file_cleaner', 'You have no files that can be deleted. Impressively tidy.') }}
						</template>
					</NcNoteCard>

					<NcNoteCard
						v-else-if="lastDeleted"
						type="warning"
						:heading="t('chaotic_file_cleaner', 'The wheel has spoken')">
						<span class="cleaner__victim">
							<img
								class="cleaner__victim-icon"
								:src="goneImage"
								alt="gone.svg"
								width="24"
								height="24">
							{{ lastDeleted.path }}
						</span>
						<span class="cleaner__victim-note">
							{{ t('chaotic_file_cleaner', 'Deleted. Check the trash bin if you regret this.') }}
						</span>
						<NcButton
							v-if="canRestore"
							class="cleaner__retry"
							variant="secondary"
							@click="openGamble">
							{{ t('chaotic_file_cleaner', 'Restore') }}
						</NcButton>
					</NcNoteCard>

					<p v-else-if="totalFiles > files.length" class="cleaner__hint">
						<!-- Once rules are in force the total is the matching count, not everything. -->
						{{ isNarrowed
							? t('chaotic_file_cleaner', '{count} of the {total} matching files are on the wheel.', {
								count: files.length,
								total: totalFiles,
							})
							: t('chaotic_file_cleaner', '{count} of your {total} files are on the wheel.', {
								count: files.length,
								total: totalFiles,
							}) }}
					</p>
				</div>

				<!-- Screen reader users get the candidates as plain text, since the wheel is a picture. -->
				<div
					v-if="files.length > 0"
					class="cleaner__sr-only"
					aria-hidden="true">
					<h2>{{ t('chaotic_file_cleaner', 'Files currently on the wheel') }}</h2>
					<ul>
						<li v-for="file in files" :key="file.id">
							{{ file.path }}
						</li>
					</ul>
				</div>

				<p class="cleaner__sr-only" role="status" aria-live="off">
					{{ announcement }}
				</p>

				<HistoryDialog v-model="historyOpen" />

				<SettingsDialog v-model="settingsOpen" @saved="load()" />

				<RestoreGambleDialog
					v-if="gamblePath !== ''"
					v-model="gambleOpen"
					:path="gamblePath"
					@restored="onRestored" />

				<SpinWheel
					v-if="files.length > 0"
					ref="wheel"
					class="cleaner__wheel"
					role="button"
					:aria-label="t('chaotic_file_cleaner', 'Wheel')"
					:segments="wheelSegments"
					@click="clean" />
			</div>
		</NcAppContent>
	</NcContent>
</template>

<style scoped>
.cleaner {
	/* Only the top half of the wheel is on screen, so this is twice the visible height. */
	--wheel-size: min(94vw, 46rem, 60vh);
	--wheel-visible: calc(var(--wheel-size) / 2);

	position: relative;
	display: grid;
	/*
	 * A row each for the hero, any message, and the visible half of the wheel,
	 * so none of the three can ever collide. The message row is a fixed height
	 * that a taller card grows out of upwards into the slack above it, which
	 * keeps the title still no matter which message is showing.
	 */
	grid-template-rows: 1fr 128px var(--wheel-visible);
	/* One explicit column, otherwise the rows get auto-placed side by side. */
	grid-template-columns: minmax(0, 1fr);
	box-sizing: border-box;
	/* The wheel and the hero need room side by side without the two colliding. */
	min-width: 960px;
	width: 100%;
	height: 100%;
	overflow: hidden;
}

/* The theme's focus ring fights with the wheel behind the buttons. */
.cleaner :deep(button:focus),
.cleaner :deep(button:focus-visible),
.cleaner :deep(a:focus),
.cleaner :deep(a:focus-visible) {
	outline: none;
	box-shadow: none;
}

/* Centred in the space above the wheel, and independent of any message below. */
.cleaner__stage {
	grid-row: 1;
	grid-column: 1;
	align-self: center;
	justify-self: center;
	position: relative;
	/* Sit above the wheel so the two never fight over the same pixels. */
	z-index: 1;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	gap: 12px;
	box-sizing: border-box;
	width: 100%;
	max-width: 32rem;
	padding: 16px;
	padding-left: 48px;
	text-align: left;
}

.cleaner__hero {
	width: 64px;
	height: 64px;
}

.cleaner__title {
	margin: 0;
	font-size: clamp(1.9rem, 5.5vw, 3rem);
	font-weight: 700;
	line-height: 1.1;
}

/* One line of slack under the title, and no more. */
.cleaner__tagline {
	margin: 0;
	max-width: 26rem;
	height: 34px;
	overflow: hidden;
	color: #b3b3b3;
}

/* The rules in force, so it is never a mystery why the wheel looks empty. */
.cleaner__scope {
	margin: 0;
	margin-left: 24px;
	padding: 4px 12px;
	max-width: 22rem;
	border-radius: var(--border-radius-pill, 100px);
	background-color: #f5f5f5;
	color: #9b9b9b;
	font-size: 12px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.cleaner__actions {
	display: flex;
	flex-wrap: nowrap;
	align-items: center;
	justify-content: center;
	gap: 12px;
	margin-top: 8px;
}

/* Draw the eye to the one button that matters. */
.cleaner__button {
	animation: cfc-pulse 1.4s ease-in-out infinite;
}

@keyframes cfc-pulse {
	0%, 100% {
		transform: scale(1);
	}

	50% {
		transform: scale(1.06);
	}
}

.cleaner__history-button {
	display: inline-flex;
	align-items: center;
	height: 44px;
	padding: 0 16px;
	border-radius: var(--border-radius-element, 8px);
	background-color: var(--color-background-dark);
	color: var(--color-main-text);
	font-weight: bold;
	cursor: pointer;
	user-select: none;
}

.cleaner__history-button:hover {
	background-color: var(--color-background-hover);
}

/*
 * Pinned to the bottom of its own reserved row, so a message grows upwards
 * into the gap above the wheel instead of pushing the title around.
 */
.cleaner__feedback {
	grid-row: 2;
	grid-column: 1;
	align-self: end;
	justify-self: center;
	position: relative;
	z-index: 1;
	display: flex;
	align-items: flex-end;
	justify-content: center;
	box-sizing: border-box;
	width: 100%;
	max-width: 32rem;
	padding: 0 16px 16px;
}

.cleaner__hint {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	margin: 0;
	color: var(--color-text-maxcontrast);
}

.cleaner__retry {
	margin-top: 8px;
}

/* Clamped so that a deeply nested path cannot grow the card without bound. */
.cleaner__victim {
	display: -webkit-box;
	-webkit-line-clamp: 2;
	line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;
	font-weight: 700;
	overflow-wrap: anywhere;
	animation: cfc-fade 1.2s ease-in-out infinite;
}

@keyframes cfc-fade {
	0%, 100% {
		opacity: 1;
	}

	50% {
		opacity: 0.45;
	}
}

.cleaner__victim-icon {
	width: 24px;
	height: 24px;
	vertical-align: text-bottom;
}

.cleaner__victim-note {
	display: block;
	color: var(--color-text-maxcontrast);
}

/*
 * Centred with a negative margin rather than a transform, because FileWheel
 * already owns this element's transform to drop its hub onto the bottom edge.
 */
.cleaner__wheel {
	position: absolute;
	bottom: 0;
	left: 50%;
	margin-left: calc(var(--wheel-size) / -2);
	/* Drop the hub onto the bottom edge so only the upper half stays visible. */
	transform: translateY(50%);
}

.cleaner__sr-only {
	position: absolute;
	width: 1px;
	height: 1px;
	margin: -1px;
	padding: 0;
	border: 0;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
}
</style>
