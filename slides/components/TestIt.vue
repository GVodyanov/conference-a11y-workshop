<script setup lang="ts">
withDefaults(defineProps<{ icon?: string }>(), { icon: 'tools' })
</script>

<template>
	<div class="nc-test">
		<span class="nc-test__icon"><NcIcon :name="icon" /></span>
		<div><slot /></div>
	</div>
</template>
