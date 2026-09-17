<script setup lang="ts">
import { icons } from './icons'

defineProps<{ name: keyof typeof icons | string }>()
</script>

<template>
	<svg
		viewBox="0 0 24 24"
		fill="currentColor"
		aria-hidden="true"
		focusable="false">
		<path :d="icons[name]" />
	</svg>
</template>
