<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import { t } from '@nextcloud/l10n'
import SpinWheel, { type WheelSegment } from './SpinWheel.vue'
import { restoreFile } from '../api'

const props = defineProps<{
	/** Path of the file the user is gambling for. */
	path: string
}>()

const open = defineModel<boolean>({ required: true })
const emit = defineEmits<{ restored: [path: string] }>()

/**
 * Alternating slices rather than two halves: the odds are the same 50/50, but
 * a wheel with a single slice per outcome barely looks like it is turning.
 */
const SLICES = 6

const wheel = ref<InstanceType<typeof SpinWheel> | null>(null)
const phase = ref<'ready' | 'spinning' | 'restoring' | 'won' | 'lost'>('ready')
const failed = ref('')

/** Even slices restore, odd slices do not. */
const segments = computed<WheelSegment[]>(() =>
	Array.from({ length: SLICES }, (_, index) => ({
		key: `slice-${index}`,
		label: index % 2 === 0
			? t('chaotic_file_cleaner', 'Restore')
			: t('chaotic_file_cleaner', 'Too bad'),
	})),
)

const busy = computed(() => phase.value === 'spinning' || phase.value === 'restoring')

const spinLabel = computed(() => {
	if (phase.value === 'spinning') {
		return t('chaotic_file_cleaner', 'Spinning…')
	}
	if (phase.value === 'restoring') {
		return t('chaotic_file_cleaner', 'Restoring…')
	}
	if (phase.value === 'lost') {
		return t('chaotic_file_cleaner', 'Spin again')
	}
	return t('chaotic_file_cleaner', 'Spin for it')
})

// Every visit starts from a clean slate, so a previous loss is not still showing.
watch(open, (isOpen) => {
	if (isOpen) {
		phase.value = 'ready'
		failed.value = ''
	}
})

/**
 * Spin the wheel, and put the file back only if fate says so.
 */
async function spin(): Promise<void> {
	if (busy.value) {
		return
	}

	failed.value = ''
	phase.value = 'spinning'

	// Pick first, then turn the wheel to it, so what is shown is what happened.
	const index = Math.floor(Math.random() * SLICES)
	await wheel.value?.spin(index)

	if (index % 2 !== 0) {
		phase.value = 'lost'
		return
	}

	phase.value = 'restoring'

	try {
		await restoreFile(props.path)
		phase.value = 'won'
		emit('restored', props.path)
	} catch (error) {
		// Winning the spin but losing the restore is a different kind of sad.
		failed.value = t('chaotic_file_cleaner', 'Fate said yes, but the file could not be brought back.')
		phase.value = 'lost'
		console.error('[chaotic_file_cleaner] Could not restore the file', error)
	}
}
</script>

<template>
	<NcDialog
		v-model:open="open"
		size="normal"
		:name="t('chaotic_file_cleaner', 'Second chance')">
		<div class="gamble">
			<p class="gamble__intro">
				{{ t('chaotic_file_cleaner', 'Spin again to decide whether {name} comes back.', { name: path }) }}
			</p>

			<SpinWheel
				ref="wheel"
				class="gamble__wheel"
				:segments="segments" />

			<p class="gamble__status" role="status" aria-live="polite">
				<span v-if="phase === 'won'" class="gamble__won">
					{{ t('chaotic_file_cleaner', 'Restored. Fate was merciful.') }}
				</span>
				<span v-else-if="phase === 'lost' && failed === ''">
					{{ t('chaotic_file_cleaner', 'Too bad. It stays in the trash, unless you try again.') }}
				</span>
				<span v-else-if="phase === 'restoring'">
					{{ t('chaotic_file_cleaner', 'Putting it back…') }}
				</span>
			</p>

			<NcNoteCard v-if="failed !== ''" type="error" :text="failed" />
		</div>

		<template #actions>
			<NcButton variant="tertiary" @click="open = false">
				{{ phase === 'won' ? t('chaotic_file_cleaner', 'Close') : t('chaotic_file_cleaner', 'Give up') }}
			</NcButton>
			<NcButton
				v-if="phase !== 'won'"
				variant="primary"
				:disabled="busy"
				@click="spin">
				<template v-if="busy" #icon>
					<NcLoadingIcon :size="20" />
				</template>
				{{ spinLabel }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<style scoped>
.gamble {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 12px;
	padding-block-end: 12px;
	text-align: center;
}

.gamble__intro {
	margin: 0;
	color: var(--color-text-maxcontrast);
	overflow-wrap: anywhere;
}

/* A full disc here, unlike the half-sunken wheel on the page behind. */
.gamble__wheel {
	--wheel-size: min(70vw, 17rem);
}

.gamble__status {
	min-height: 1.5em;
	margin: 0;
	font-weight: 500;
}

.gamble__won {
	color: #7ec8a0;
}
</style>
