<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

/** One slice of the wheel. The key only has to be unique within the wheel. */
export interface WheelSegment {
	key: string
	label: string
}

const props = defineProps<{
	segments: WheelSegment[]
}>()

/** Radius of the wheel, in viewBox units. */
const RADIUS = 94
/** Innermost and outermost point a label may occupy. */
const LABEL_INNER = 30
const LABEL_OUTER = 87
/** How long a spin lasts, in milliseconds. */
const SPIN_DURATION = 4800
/** Whole turns taken before settling, so the outcome is never obvious up front. */
const SPIN_TURNS = 5

/** Cumulative rotation of the wheel in degrees. It only ever grows. */
const rotation = ref(0)
const disc = ref<SVGGElement | null>(null)
const prefersReducedMotion = ref(false)

let mediaQuery: MediaQueryList | null = null

function onMotionPreferenceChange(event: MediaQueryListEvent) {
	prefersReducedMotion.value = event.matches
}

onMounted(() => {
	mediaQuery = window.matchMedia('(prefers-reduced-motion: reduced)')
	prefersReducedMotion.value = mediaQuery.matches
	mediaQuery.addEventListener('change', onMotionPreferenceChange)
})

onBeforeUnmount(() => {
	mediaQuery?.removeEventListener('change', onMotionPreferenceChange)
})

const count = computed(() => props.segments.length)
const segmentAngle = computed(() => 360 / Math.max(1, count.value))
const durationMs = computed(() => (prefersReducedMotion.value ? 0 : SPIN_DURATION))

// Roomy slices get bigger text; the cap only bites on wheels of about seven
// slices or fewer, which is where there is space to spare.
const fontSize = computed(() => Math.min(6, Math.max(2, 40 / Math.max(1, count.value))))
const maxLabelChars = computed(() => Math.floor((LABEL_OUTER - LABEL_INNER) / (fontSize.value * 0.52)))

/**
 * Convert polar coordinates to the cartesian ones SVG wants.
 * 0 degrees points right, angles grow clockwise.
 *
 * @param radius Distance from the hub
 * @param degrees Angle in degrees
 */
function polar(radius: number, degrees: number): { x: number, y: number } {
	const radians = (degrees * Math.PI) / 180
	return {
		x: radius * Math.cos(radians),
		y: radius * Math.sin(radians),
	}
}

/**
 * The angle the middle of a segment sits at while the wheel is at rest.
 *
 * @param index Segment index
 */
function centreAngle(index: number): number {
	return index * segmentAngle.value - 90
}

/**
 * Build the pie slice for one segment.
 *
 * @param index Segment index
 */
function segmentPath(index: number): string {
	// A lone file owns the whole disc; an arc of exactly 360 degrees would collapse.
	if (count.value === 1) {
		return `M 0 ${-RADIUS} A ${RADIUS} ${RADIUS} 0 1 1 0 ${RADIUS} `
			+ `A ${RADIUS} ${RADIUS} 0 1 1 0 ${-RADIUS} Z`
	}

	const half = segmentAngle.value / 2
	const start = polar(RADIUS, centreAngle(index) - half)
	const end = polar(RADIUS, centreAngle(index) + half)
	const largeArc = segmentAngle.value > 180 ? 1 : 0

	return `M 0 0 L ${start.x.toFixed(3)} ${start.y.toFixed(3)} `
		+ `A ${RADIUS} ${RADIUS} 0 ${largeArc} 1 ${end.x.toFixed(3)} ${end.y.toFixed(3)} Z`
}

/**
 * Whether a label would end up on its head and needs turning around.
 *
 * The test uses the angle the segment currently points at — its resting angle
 * plus however far the wheel has been turned — because a label that reads fine
 * at rest is upside down once the wheel carries it across to the other side.
 *
 * @param index Segment index
 */
function isUpsideDown(index: number): boolean {
	return Math.cos(((centreAngle(index) + rotation.value) * Math.PI) / 180) < 0
}

/**
 * Place a label along its segment's radius, turning around the ones that would
 * otherwise be read on their head.
 *
 * Both branches land on the same point: rotating by a further 180 degrees and
 * stepping backwards is the same position, just approached from the far side.
 *
 * @param index Segment index
 */
function labelTransform(index: number): string {
	const angle = centreAngle(index)

	return isUpsideDown(index)
		? `rotate(${(angle + 180).toFixed(3)}) translate(${-LABEL_OUTER} 0)`
		: `rotate(${angle.toFixed(3)}) translate(${LABEL_OUTER} 0)`
}

/**
 * Labels read outwards, so the anchor flips together with the transform.
 *
 * @param index Segment index
 */
function labelAnchor(index: number): 'start' | 'end' {
	return isUpsideDown(index) ? 'start' : 'end'
}

/**
 * Shorten a label to fit its slice, keeping any file extension visible.
 *
 * @param name The full label
 */
function truncate(name: string): string {
	const limit = maxLabelChars.value
	if (name.length <= limit) {
		return name
	}

	const dot = name.lastIndexOf('.')
	const extension = dot > 0 && name.length - dot <= 6 ? name.slice(dot) : ''
	const keep = Math.max(1, limit - extension.length - 1)

	return `${name.slice(0, keep)}…${extension}`
}

/**
 * Spin the wheel so that the given segment comes to rest under the pointer.
 *
 * @param index Index of the file that fate has chosen
 */
async function spin(index: number): Promise<void> {
	const segment = segmentAngle.value

	// Segment `index` sits under the pointer whenever the wheel's rotation is
	// congruent to -index * segment, so turn forward until we reach that.
	const target = ((-index * segment) % 360 + 360) % 360
	const current = ((rotation.value % 360) + 360) % 360
	let delta = target - current
	if (delta < 0) {
		delta += 360
	}

	// Settle somewhere inside the slice instead of always dead centre.
	const jitter = (Math.random() - 0.5) * segment * 0.6

	if (durationMs.value === 0) {
		rotation.value += delta + jitter
		return
	}

	rotation.value += delta + SPIN_TURNS * 360 + jitter

	await new Promise<void>((resolve) => {
		const element = disc.value
		if (element === null) {
			window.setTimeout(resolve, SPIN_DURATION)
			return
		}

		const finish = () => {
			element.removeEventListener('transitionend', finish)
			window.clearTimeout(safetyNet)
			resolve()
		}

		// If the transition never fires — a hidden tab, for instance — move on anyway.
		const safetyNet = window.setTimeout(finish, SPIN_DURATION + 500)
		element.addEventListener('transitionend', finish)
	})
}

defineExpose({ spin })
</script>

<template>
	<div class="wheel">
		<svg
			class="wheel__svg"
			viewBox="-105 -105 210 210"
			aria-hidden="true"
			focusable="false">
			<g
				ref="disc"
				class="wheel__disc"
				:style="{
					transform: `rotate(${rotation}deg)`,
					transitionDuration: `${durationMs}ms`,
				}">
				<path
					v-for="(segment, index) in segments"
					:key="`slice-${segment.key}`"
					class="wheel__segment"
					:class="index % 2 === 0 ? 'wheel__segment--a' : 'wheel__segment--b'"
					:d="segmentPath(index)" />

				<text
					v-for="(segment, index) in segments"
					:key="`label-${segment.key}`"
					class="wheel__label"
					:class="index % 2 === 0 ? 'wheel__label--a' : 'wheel__label--b'"
					:transform="labelTransform(index)"
					:text-anchor="labelAnchor(index)"
					:font-size="fontSize"
					dominant-baseline="middle">{{ truncate(segment.label) }}</text>
			</g>

			<circle
				class="wheel__hub"
				r="14"
				cx="0"
				cy="0" />
			<path class="wheel__pointer" d="M -9 -105 L 9 -105 L 0 -85 Z" />
		</svg>
	</div>
</template>

<style scoped>
.wheel {
	width: var(--wheel-size);
	height: var(--wheel-size);
	cursor: pointer;
}

.wheel__svg {
	display: block;
	width: 100%;
	height: 100%;
	overflow: visible;
}

.wheel__disc {
	transition-property: transform;
	transition-timing-function: cubic-bezier(0.16, 0.84, 0.3, 1);
}

.wheel__segment {
	stroke: none;
	stroke-width: 0;
	stroke-linejoin: round;
}

.wheel__segment--a {
	fill: var(--color-primary-element);
}

.wheel__segment--b {
	fill: #e8f0fb;
}

.wheel__label {
	font-family: var(--font-face, system-ui, sans-serif);
	font-weight: 500;
}

.wheel__label--a {
	fill: var(--color-primary-element-text);
}

.wheel__label--b {
	fill: #ffffff;
}

.wheel__hub {
	fill: var(--color-main-background);
	stroke: var(--color-border-dark);
	stroke-width: 1;
}

.wheel__pointer {
	fill: var(--color-error, #c9326b);
	stroke: var(--color-main-background);
	stroke-width: 1.5;
	stroke-linejoin: round;
}
</style>
