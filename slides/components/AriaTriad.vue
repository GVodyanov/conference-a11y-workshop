<template>
	<div class="triad">
		<div class="triad__source">
			<code>&lt;button aria-label="Settings" aria-expanded="false"&gt;</code>
		</div>
		<div class="triad__arrow" aria-hidden="true">
			<NcIcon name="arrowRight" />
		</div>
		<div class="triad__cards">
			<div class="triad__card">
				<span class="triad__key">Role</span>
				<span class="triad__val">button</span>
				<span class="triad__src">from the element</span>
			</div>
			<div class="triad__card">
				<span class="triad__key">Name</span>
				<span class="triad__val">Settings</span>
				<span class="triad__src">from <code>aria-label</code></span>
			</div>
			<div class="triad__card">
				<span class="triad__key">State</span>
				<span class="triad__val">collapsed</span>
				<span class="triad__src">from <code>aria-expanded</code></span>
			</div>
		</div>
	</div>
</template>

<style scoped>
.triad {
	display: grid;
	grid-template-columns: auto auto 1fr;
	gap: 0.8rem;
	align-items: center;
}

.triad__source {
	max-width: 13rem;
}

.triad__source code {
	display: block;
	line-height: 1.6;
	padding: 0.5rem 0.6rem;
	font-size: 0.72rem;
	word-break: break-word;
}

.triad__arrow svg {
	width: 1.4rem;
	height: 1.4rem;
	color: var(--nc-blue);
}

.triad__cards {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 0.6rem;
}

.triad__card {
	display: flex;
	flex-direction: column;
	gap: 0.1rem;
	background: var(--nc-tint);
	border-radius: var(--nc-radius-sm);
	padding: 0.5rem 0.65rem 0.6rem;
}

.triad__key {
	font-size: 0.64rem;
	font-weight: 800;
	letter-spacing: 0.09em;
	text-transform: uppercase;
	color: var(--nc-blue-deep);
}

.triad__val {
	font-family: 'JetBrains Mono', monospace;
	font-size: 0.95rem;
	font-weight: 700;
	color: var(--nc-text);
}

.triad__src {
	font-size: 0.66rem;
	color: var(--nc-text-muted);
}

.triad__src code {
	font-size: 0.9em;
	padding: 0 0.2em;
	background: rgba(255, 255, 255, 0.7);
}
</style>
