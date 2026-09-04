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
  one file. The lookup goes through the user's own folder, so a user can never
  reach a file they do not have access to.
- The frontend spins an SVG wheel to the chosen segment, then calls the delete
  endpoint and draws a fresh set of files.

The wheel holds a random selection rather than every single file, because a
few thousand unreadable slivers make for a poor wheel. Every file the user owns
is still a possible pick — the draw is reshuffled server side on every round.

### Accessibility

The wheel is a picture, so it is `aria-hidden` and backed by real text:

- the files currently on the wheel are exposed as a visually hidden list,
- spin start and outcome are announced through an `aria-live` region,
- `prefers-reduced-motion` replaces the five-turn spin with an instant result,
- segment colours come from the Nextcloud theme's paired background/text
  variables, and slices are separated by strokes rather than by colour alone.

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
