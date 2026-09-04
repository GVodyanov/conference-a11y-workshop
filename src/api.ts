import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

/**
 * A file that is eligible to be devoured by the wheel.
 */
export interface CleanableFile {
	id: number
	name: string
	/** Path relative to the user's home folder, e.g. `Photos/cat.jpg` */
	path: string
	size: number
}

/**
 * A file the wheel has already claimed.
 */
export interface HistoryEntry {
	name: string
	/** Path the file had, relative to the user's home folder */
	path: string
	size: number
	/** Unix timestamp in seconds, the unit PHP reports it in */
	deletedAt: number
}

/**
 * The rules limiting what the wheel may choose from.
 */
export interface Settings {
	/** Folder to clean from, relative to the user's home. Empty means everything. */
	folder: string
	/** Only names containing this text are eligible. Empty means every name. */
	nameFilter: string
}

/** What the wheel endpoint returns: the candidates plus the rules behind them. */
export interface Wheel extends Settings {
	files: CleanableFile[]
	total: number
}

interface OcsEnvelope<T> {
	ocs: {
		data: T
	}
}

/**
 * Fetch a fresh random selection of files to put on the wheel.
 *
 * @param limit How many files the wheel should hold
 */
export async function fetchWheel(limit: number): Promise<Wheel> {
	const { data } = await axios.get<OcsEnvelope<Wheel>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/wheel'),
		{ params: { limit } },
	)

	return data.ocs.data
}

/** What happened to the file the wheel landed on. */
export interface Deletion {
	deleted: CleanableFile
	/** Whether a trash bin exists to gamble the file back out of. */
	canRestore: boolean
}

/**
 * Delete the file the wheel landed on.
 *
 * @param fileId Id of the doomed file
 */
export async function deleteFile(fileId: number): Promise<Deletion> {
	const { data } = await axios.delete<OcsEnvelope<Deletion>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/files/{fileId}', { fileId }),
	)

	return data.ocs.data
}

/**
 * Fetch the files this user has already fed to the wheel, newest first.
 */
export async function fetchHistory(): Promise<HistoryEntry[]> {
	const { data } = await axios.get<OcsEnvelope<{ entries: HistoryEntry[] }>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/history'),
	)

	return data.ocs.data.entries
}

/**
 * Read the current cleaning rules.
 */
export async function fetchSettings(): Promise<Settings> {
	const { data } = await axios.get<OcsEnvelope<Settings>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/settings'),
	)

	return data.ocs.data
}

/**
 * Store new cleaning rules and return them as the server normalised them.
 *
 * @param settings The folder and name filter to save
 */
export async function saveSettings(settings: Settings): Promise<Settings> {
	const { data } = await axios.put<OcsEnvelope<Settings>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/settings'),
		settings,
	)

	return data.ocs.data
}

/**
 * Whether a failed request came back as "not found".
 *
 * Used to tell "the folder you picked is gone" apart from a generic failure.
 *
 * @param error The error thrown by a request
 */
export function isNotFound(error: unknown): boolean {
	return (error as { response?: { status?: number } })?.response?.status === 404
}

/**
 * Ask the trash bin to put a file back where it was.
 *
 * @param path The path the file had, relative to the user's home
 */
export async function restoreFile(path: string): Promise<string> {
	const { data } = await axios.post<OcsEnvelope<{ restored: string }>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/restore'),
		{ path },
	)

	return data.ocs.data.restored
}
