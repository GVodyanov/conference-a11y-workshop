module.exports = {
	// Self-contained: never inherit config from a parent directory. The app
	// lives inside the server checkout, which has its own eslint setup.
	root: true,
	// Vue 3 + Typescript. The plain `typescript` entry of the shared config
	// still extends `plugin:vue/recommended`, which is the Vue 2 ruleset and
	// rejects Vue 3 syntax such as `v-model:open`.
	extends: [
		'@nextcloud/eslint-config/vue3',
	],
	rules: {
		'jsdoc/require-jsdoc': 'off',
		'vue/first-attribute-linebreak': 'off',
	},
}
