<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcContent from '@nextcloud/vue/components/NcContent'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import { showError } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import FileWheel from './components/FileWheel.vue'
import { deleteFile, fetchWheel, type CleanableFile } from './api'

/** How many files fate gets to choose between in one round. */
const WHEEL_SIZE = 12

const wheel = ref<InstanceType<typeof FileWheel> | null>(null)

const files = ref<CleanableFile[]>([])
const totalFiles = ref(0)
const loading = ref(true)
const loadError = ref(false)
const phase = ref<'idle' | 'spinning' | 'deleting'>('idle')
const lastDeleted = ref<CleanableFile | null>(null)
const announcement = ref('')

const busy = computed(() => phase.value !== 'idle')
const isEmpty = computed(() => !loading.value && !loadError.value && files.value.length === 0)
const canSpin = computed(() => !loading.value && !loadError.value && !busy.value && files.value.length > 0)

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
	loadError.value = false

	try {
		const result = await fetchWheel(WHEEL_SIZE)
		files.value = result.files
		totalFiles.value = result.total
	} catch (error) {
		loadError.value = true
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

	const index = Math.floor(Math.random() * files.value.length)
	const doomed = files.value[index]

	phase.value = 'spinning'
	announcement.value = t('chaotic_file_cleaner', 'The wheel is spinning…')

	await wheel.value?.spin(index)

	phase.value = 'deleting'

	try {
		lastDeleted.value = await deleteFile(doomed.id)
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

onMounted(() => load())
</script>

<template>
	<NcContent app-name="chaotic_file_cleaner">
		<NcAppContent>
			<div class="cleaner">
				<div class="cleaner__stage">
					<h1 class="cleaner__title">
						{{ t('chaotic_file_cleaner', 'Chaotic file cleaner') }}
					</h1>
					<p class="cleaner__tagline">
						{{ t('chaotic_file_cleaner', 'Press the button. The wheel picks one of your files, and that file is gone.') }}
					</p>

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
				</div>

				<div class="cleaner__feedback">
					<p v-if="loading" class="cleaner__hint">
						<NcLoadingIcon :size="20" />
						{{ t('chaotic_file_cleaner', 'Rounding up your files…') }}
					</p>

					<NcNoteCard
						v-else-if="loadError"
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
						{{ t('chaotic_file_cleaner', 'You have no files that can be deleted. Impressively tidy.') }}
					</NcNoteCard>

					<NcNoteCard
						v-else-if="lastDeleted"
						type="warning"
						:heading="t('chaotic_file_cleaner', 'The wheel has spoken')">
						<span class="cleaner__victim">{{ lastDeleted.path }}</span>
						<span class="cleaner__victim-note">
							{{ t('chaotic_file_cleaner', 'Deleted. Check the trash bin if you regret this.') }}
						</span>
					</NcNoteCard>

					<p v-else-if="totalFiles > files.length" class="cleaner__hint">
						{{ t('chaotic_file_cleaner', '{count} of your {total} files are on the wheel.', {
							count: files.length,
							total: totalFiles,
						}) }}
					</p>
				</div>

				<!-- Screen reader users get the candidates as plain text, since the wheel is a picture. -->
				<div v-if="files.length > 0" class="cleaner__sr-only">
					<h2>{{ t('chaotic_file_cleaner', 'Files currently on the wheel') }}</h2>
					<ul>
						<li v-for="file in files" :key="file.id">
							{{ file.path }}
						</li>
					</ul>
				</div>

				<p class="cleaner__sr-only" role="status" aria-live="polite">
					{{ announcement }}
				</p>

				<FileWheel
					v-if="files.length > 0"
					ref="wheel"
					class="cleaner__wheel"
					:files="files" />
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
	grid-template-rows: 1fr 8rem var(--wheel-visible);
	/* One explicit column, otherwise the rows get auto-placed side by side. */
	grid-template-columns: minmax(0, 1fr);
	box-sizing: border-box;
	width: 100%;
	height: 100%;
	overflow: hidden;
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
	align-items: center;
	gap: 12px;
	box-sizing: border-box;
	width: 100%;
	max-width: 32rem;
	padding: 16px;
	text-align: center;
}

.cleaner__title {
	margin: 0;
	font-size: clamp(1.9rem, 5.5vw, 3rem);
	font-weight: 700;
	line-height: 1.1;
}

.cleaner__tagline {
	margin: 0;
	max-width: 26rem;
	color: var(--color-text-maxcontrast);
}

.cleaner__button {
	margin-top: 8px;
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
