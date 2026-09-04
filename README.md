# Chaotic file cleaner

The least responsible way to tidy up your Nextcloud Files.

Press one button. A wheel loaded with your own files starts spinning. Whichever
file it lands on is deleted. No filters, no sorting, no mercy.

Deleted files go to the trash bin (as long as the *Deleted files* app is
enabled), so fate is at least reversible.

## How it works

- **`GET /ocs/v2.php/apps/chaotic_file_cleaner/api/wheel?limit=12`** walks the
  user's home folder, keeps only files they are allowed to delete, shuffles
  them server side and returns a random handful plus the total count.
- **`DELETE /ocs/v2.php/apps/chaotic_file_cleaner/api/files/{fileId}`** deletes
  one file and records it in the user's history. The lookup goes through the
  user's own folder, so a user can never reach a file they do not have access to.
- **`GET /ocs/v2.php/apps/chaotic_file_cleaner/api/history`** returns the files
  this user has already lost, newest first.
- **`GET`/`PUT /ocs/v2.php/apps/chaotic_file_cleaner/api/settings`** read and
  write the cleaning rules.
- **`POST /ocs/v2.php/apps/chaotic_file_cleaner/api/restore`** pulls one file
  back out of the trash bin.
- The frontend spins an SVG wheel to the chosen segment, then calls the delete
  endpoint and draws a fresh set of files.

The wheel holds a random selection rather than every single file, because a
few thousand unreadable slivers make for a poor wheel. Every file the user owns
is still a possible pick — the draw is reshuffled server side on every round.

## Cleaning rules

The cog button opens a dialog with two ways to narrow the wheel down:

- **Folder to clean from**, chosen with the Nextcloud file picker (directories
  only). The wheel then walks just that folder and its subfolders.
- **Only file names containing**, a plain text filter matched against the file
  name, ignoring case.

Both are stored per user and applied server side, so the wheel endpoint can
never hand back a file the rules exclude. The rules in force are shown under
the tagline, so an empty-looking wheel is never a mystery.

Paths are normalised to bare segments relative to the user's home, and any
path trying to climb out of it with `..` is refused rather than normalised
away. The folder is checked to exist and to really be a folder before the rules
are saved, and if it is moved or deleted later the wheel says so and offers to
reopen the rules.

## Second chance

After a deletion the result offers a **Restore** button, which does not restore
anything. It opens a second wheel of six alternating *Restore* and *Too bad*
slices. Only landing on *Restore* actually calls the restore endpoint; landing
on *Too bad* leaves the file where it is and offers another spin, for as long
as the user keeps wanting one.

The outcome is drawn first and the wheel is then turned to it, so what the
pointer shows is always what happened — the same approach as the main wheel.

Six slices rather than two because a two-slice wheel barely looks like it is
turning. The odds are the same 50/50.

Restoring goes through `ITrashManager`, which belongs to the *Deleted files*
app rather than to the public API, so it is an optional dependency: when that
app is disabled the container hands the service `null`, the delete response
reports `canRestore: false`, and the Restore button is not offered at all. This
is the same pattern core's own `files` app uses for its delete command.

A restored file is dropped from the history, since it is no longer a casualty.

## History

The **History** button opens a dialog listing everything the wheel has taken,
newest first, with how long ago it went and how big it was.

The list lives in a single per-user config value as JSON and is capped at the
50 most recent entries — it is short, always read as a whole, and never
queried, so it does not warrant a table of its own. If you ever want unbounded
history or paging, that cap is the point to replace with a real table.

The history records what the wheel deleted. Winning a file back through
*Second chance* removes its entry, but restoring a file from the Files app
yourself does not — the app never sees that happen.

### Accessibility

The wheel is a picture, so it is `aria-hidden` and backed by real text:

- the files currently on the wheel are exposed as a visually hidden list,
- spin start and outcome are announced through an `aria-live` region,
- `prefers-reduced-motion` replaces the five-turn spin with an instant result,
- segment colours come from the Nextcloud theme's paired background/text
  variables, and slices are separated by strokes rather than by colour alone.

Both dialogs are real modals: they are labelled by their title, take focus on
open, and return focus to the button that opened them when dismissed with
Escape. The cog button carries an `aria-label`, since it shows only an icon.

## Development

Requires Node ^24 and npm ^11 (see `.nvmrc`), plus PHP 8.1+ and Composer.

```bash
composer install
npm ci

npm run build      # production bundle into js/ and css/
npm run watch      # rebuild on change
npm run lint       # eslint
npm run stylelint  # stylelint
npm run typecheck  # vue-tsc
```

Backend checks:

```bash
composer cs:check  # php-cs-fixer
composer psalm     # static analysis
composer openapi   # regenerate openapi.json from the controller annotations
composer test:unit # phpunit
```

`composer test:unit` and `composer psalm` expect the app to sit inside a
Nextcloud server checkout (`server/apps/chaotic_file_cleaner`), because the
tests boot the server and the OCP stubs are analysis-only.

## Resources

### Documentation for developers:

- General documentation and tutorials: https://nextcloud.com/developer
- Technical documentation: https://docs.nextcloud.com/server/latest/developer_manual
- Component library: https://nextcloud-vue-components.netlify.app

### Help for developers:

- Official community chat: https://cloud.nextcloud.com/call/xs25tz5y
- Official community forum: https://help.nextcloud.com/c/dev/11
