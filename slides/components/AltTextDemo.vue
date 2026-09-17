<template>
	<div class="nc-grid-3 alt" style="align-items: stretch">
		<div class="nc-card alt__card">
			<span class="alt__kind">Informative</span>
			<div class="alt__art">
				<svg viewBox="0 0 120 70" aria-hidden="true">
					<rect width="120" height="70" rx="6" fill="#dbedf8" />
					<path d="M12 56 L38 26 L58 48 L74 34 L108 56 Z" fill="#0082c9" />
					<circle cx="92" cy="20" r="8" fill="#30b6ff" />
				</svg>
			</div>
			<p class="alt__code">
				<code>alt="Weekly active users, rising since June"</code>
			</p>
			<p class="nc-note">
				Carries meaning, so the alt carries the same meaning. Not
				<em>&ldquo;chart&rdquo;</em>, not the file name.
			</p>
		</div>

		<div class="nc-card alt__card">
			<span class="alt__kind">Decorative</span>
			<div class="alt__art">
				<svg viewBox="0 0 120 70" aria-hidden="true">
					<circle cx="24" cy="35" r="10" fill="#dbedf8" />
					<circle cx="52" cy="35" r="10" fill="#bfe1f5" />
					<circle cx="80" cy="35" r="10" fill="#dbedf8" />
					<circle cx="108" cy="35" r="6" fill="#bfe1f5" />
				</svg>
			</div>
			<p class="alt__code">
				<code>alt=""</code>
			</p>
			<p class="nc-note">
				Adds nothing a reader would miss. An empty alt is a decision, and it is
				the right one. Leaving it off is not the same thing.
			</p>
		</div>

		<div class="nc-card alt__card">
			<span class="alt__kind">Icon-only control</span>
			<div class="alt__art alt__art--button">
				<span class="alt__btn"><NcIcon name="cog" /></span>
			</div>
			<p class="alt__code">
				<code>&lt;button aria-label="Settings"&gt;</code>
			</p>
			<p class="nc-note">
				The icon stays <code>aria-hidden</code>. The <strong>control</strong>
				carries the name, or the button is empty.
			</p>
		</div>
	</div>
</template>

<style scoped>
.alt__card {
	padding: 0.7rem 0.8rem 0.8rem;
	height: 100%;
}

.alt__kind {
	display: block;
	font-size: 0.66rem;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--nc-blue-deep);
	margin-bottom: 0.45rem;
}

.alt__art {
	background: var(--nc-tint-soft);
	border-radius: var(--nc-radius-sm);
	padding: 0.45rem;
	margin-bottom: 0.5rem;
	display: grid;
	place-items: center;
	height: 4.2rem;
}

.alt__art svg {
	width: 100%;
	max-height: 100%;
}

.alt__art--button svg {
	width: 1.3rem;
	height: 1.3rem;
}

.alt__btn {
	display: grid;
	place-items: center;
	width: 2.75rem;
	height: 2.75rem;
	border-radius: 50%;
	background: var(--nc-gradient);
	color: #fff;
}

.alt__code {
	margin: 0 0 0.35rem;
}

.alt__code code {
	font-size: 0.68rem;
	word-break: break-word;
}

.alt .nc-note {
	font-size: 0.74rem;
}
</style>
