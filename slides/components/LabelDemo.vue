<script setup lang="ts">
// Both fields look identical. Only the right one is wired up, so only its
// label moves focus when clicked. Try it: click either piece of label text.
</script>

<template>
	<div class="nc-grid">
		<div class="nc-card nc-card--bad lbl__card">
			<span class="nc-card__tag"><NcIcon name="alert" /> A paragraph near an input</span>
			<p class="lbl__label">
				Display name
			</p>
			<input class="lbl__input" type="text" placeholder="e.g. Design team">
			<p class="lbl__hint">
				Shown to everyone who can see this project.
			</p>
			<SrSays bad>edit text, blank</SrSays>
		</div>

		<div class="nc-card nc-card--good lbl__card">
			<span class="nc-card__tag"><NcIcon name="check" /> label for + aria-describedby</span>
			<label class="lbl__label" for="lbl-good">Display name</label>
			<input
				id="lbl-good"
				class="lbl__input"
				type="text"
				placeholder="e.g. Design team"
				aria-describedby="lbl-good-hint">
			<p id="lbl-good-hint" class="lbl__hint">
				Shown to everyone who can see this project.
			</p>
			<SrSays good>Display name, edit text. Shown to everyone who can see…</SrSays>
		</div>
	</div>
</template>

<style scoped>
.lbl__card {
	padding: 0.8rem 0.9rem 0.85rem;
}

.lbl__label {
	display: block;
	font-size: 0.85rem;
	font-weight: 600;
	margin: 0 0 0.25rem;
	cursor: pointer;
}

.lbl__input {
	display: block;
	width: 100%;
	font: inherit;
	font-size: 0.9rem;
	padding: 0.35rem 0.6rem;
	border: 2px solid #cfccdb;
	border-radius: 8px;
	background: #fff;
	color: var(--nc-text);
}

.lbl__input:focus {
	outline: 3px solid var(--nc-navy);
	outline-offset: 1px;
	border-color: var(--nc-blue);
}

.lbl__hint {
	margin: 0.35rem 0 0;
	font-size: 0.76rem;
	color: var(--nc-text-muted);
	line-height: 1.4;
}
</style>
