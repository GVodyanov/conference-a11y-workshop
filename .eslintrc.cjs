module.exports = {
	// Self-contained: never inherit config from a parent directory. The app
	// lives inside the server checkout, which has its own eslint setup.
	root: true,
	// This app writes its Vue components with `<script setup lang="ts">`,
	// which needs the Typescript flavour of the shared config.
	extends: [
		'@nextcloud/eslint-config/typescript',
	],
	rules: {
		'jsdoc/require-jsdoc': 'off',
		'vue/first-attribute-linebreak': 'off',
	},
}
