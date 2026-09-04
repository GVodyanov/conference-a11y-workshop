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
export async function fetchWheel(limit: number): Promise<{ files: CleanableFile[], total: number }> {
	const { data } = await axios.get<OcsEnvelope<{ files: CleanableFile[], total: number }>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/wheel'),
		{ params: { limit } },
	)

	return data.ocs.data
}

/**
 * Delete the file the wheel landed on.
 *
 * @param fileId Id of the doomed file
 */
export async function deleteFile(fileId: number): Promise<CleanableFile> {
	const { data } = await axios.delete<OcsEnvelope<{ deleted: CleanableFile }>>(
		generateOcsUrl('/apps/chaotic_file_cleaner/api/files/{fileId}', { fileId }),
	)

	return data.ocs.data.deleted
}
