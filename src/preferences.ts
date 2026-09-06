/** Key the per-device wheel size is stored under. */
const WHEEL_SIZE_KEY = 'chaotic_file_cleaner.wheel_size'

/** How many files fate gets to choose between when nothing is stored. */
export const DEFAULT_WHEEL_SIZE = 12
/** Fewer than three slices barely looks like a wheel. */
export const MIN_WHEEL_SIZE = 3
/** More than this and the labels stop being readable. */
export const MAX_WHEEL_SIZE = 24

/**
 * Clamp a wheel size into the range the wheel can actually draw.
 *
 * @param size The requested number of slices
 */
function clamp(size: number): number {
	if (!Number.isFinite(size)) {
		return DEFAULT_WHEEL_SIZE
	}

	return Math.min(MAX_WHEEL_SIZE, Math.max(MIN_WHEEL_SIZE, Math.round(size)))
}

/**
 * How many files the wheel should hold, as chosen on this device.
 */
export function getWheelSize(): number {
	try {
		const stored = window.localStorage.getItem(WHEEL_SIZE_KEY)
		return stored === null ? DEFAULT_WHEEL_SIZE : clamp(Number.parseInt(stored, 10))
	} catch (error) {
		// Private browsing modes can refuse storage entirely.
		console.error('[chaotic_file_cleaner] Could not read the wheel size', error)
		return DEFAULT_WHEEL_SIZE
	}
}

/**
 * Remember how many files the wheel should hold on this device.
 *
 * @param size The number of slices to store
 */
export function setWheelSize(size: number): void {
	try {
		window.localStorage.setItem(WHEEL_SIZE_KEY, String(clamp(size)))
	} catch (error) {
		console.error('[chaotic_file_cleaner] Could not store the wheel size', error)
	}
}
