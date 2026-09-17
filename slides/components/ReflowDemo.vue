<script setup lang="ts">
// Two real 375px viewports. The left page carries min-width: 900px and
// flex-wrap: nowrap, so its overflow is genuine, not drawn.
</script>

<template>
	<div class="reflow">
		<figure class="reflow__item">
			<div class="phone">
				<div class="phone__screen">
					<div class="page page--rigid">
						<h4 class="page__title">
							Project dashboard
						</h4>
						<p class="page__tagline">
							Everything your team shipped this week.
						</p>
						<div class="page__actions page__actions--nowrap">
							<span class="page__btn page__btn--primary">New project</span>
							<span class="page__btn">Import</span>
							<span class="page__btn page__btn--cog"><NcIcon name="cog" /></span>
						</div>
						<div class="page__rows">
							<span v-for="n in 3" :key="n" class="page__row">
								<span class="page__avatar" />
								<span class="page__lines">
									<span class="page__line" />
									<span class="page__line page__line--short" />
								</span>
							</span>
						</div>
					</div>
				</div>
			</div>
			<figcaption class="reflow__cap is-bad">
				<NcIcon name="alert" /> <code>min-width: 900px</code> in a 375px window
			</figcaption>
		</figure>

		<figure class="reflow__item">
			<div class="phone">
				<div class="phone__screen">
					<div class="page">
						<h4 class="page__title">
							Project dashboard
						</h4>
						<p class="page__tagline">
							Everything your team shipped this week.
						</p>
						<div class="page__actions">
							<span class="page__btn page__btn--primary">New project</span>
							<span class="page__btn">Import</span>
							<span class="page__btn page__btn--cog"><NcIcon name="cog" /></span>
						</div>
						<div class="page__rows">
							<span v-for="n in 3" :key="n" class="page__row">
								<span class="page__avatar" />
								<span class="page__lines">
									<span class="page__line" />
									<span class="page__line page__line--short" />
								</span>
							</span>
						</div>
					</div>
				</div>
			</div>
			<figcaption class="reflow__cap is-good">
				<NcIcon name="check" /> fluid width, <code>flex-wrap: wrap</code>
			</figcaption>
		</figure>

		<div class="reflow__notes">
			<h3>What Reflow asks for</h3>
			<ul>
				<li>Content readable in one column at <strong>320px</strong> wide.</li>
				<li>No scrolling in two directions to read a single sentence.</li>
				<li>320px is also what 1280px becomes at 400% zoom, so fixing this fixes two success criteria at once.</li>
			</ul>
			<p class="nc-note">
				A pixel floor is almost never what was intended. A <code>max-width</code> with
				<code>width: 100%</code>, or simply letting the row wrap, does the same job without
				breaking small screens.
			</p>
		</div>
	</div>
</template>

<style scoped>
.reflow {
	display: grid;
	grid-template-columns: auto auto 1fr;
	gap: 1rem;
	align-items: start;
}

.reflow__item {
	margin: 0;
}

.phone {
	width: 190px;
	border: 6px solid var(--nc-navy);
	border-radius: 18px;
	overflow: hidden;
	background: var(--nc-navy);
	box-shadow: var(--nc-shadow);
}

.phone__screen {
	height: 252px;
	background: #fff;
	overflow: auto;
}

.page {
	padding: 10px 12px;
	min-height: 100%;
	box-sizing: border-box;
}

/* the defect, running for real */
.page--rigid {
	min-width: 900px;
}

.page__title {
	font-size: 15px;
	font-weight: 800;
	margin: 0 0 4px;
	letter-spacing: -0.02em;
}

.page__tagline {
	font-size: 11px;
	color: var(--nc-text-muted);
	margin: 0 0 10px;
}

.page__actions {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
	margin-bottom: 12px;
}

.page__actions--nowrap {
	flex-wrap: nowrap;
}

.page__btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	height: 28px;
	padding: 0 12px;
	border-radius: 999px;
	background: #f2f1f6;
	border: 1px solid var(--nc-border);
	font-size: 11px;
	font-weight: 600;
	white-space: nowrap;
}

.page__btn--primary {
	background: var(--nc-gradient);
	color: #fff;
	border-color: transparent;
}

.page__btn--cog {
	width: 28px;
	padding: 0;
}

.page__btn--cog svg {
	width: 14px;
	height: 14px;
}

.page__rows {
	display: flex;
	flex-direction: column;
	gap: 9px;
}

.page__row {
	display: flex;
	align-items: center;
	gap: 8px;
}

.page__avatar {
	flex: none;
	width: 22px;
	height: 22px;
	border-radius: 50%;
	background: var(--nc-gradient);
}

.page__lines {
	display: flex;
	flex-direction: column;
	gap: 4px;
	flex: 1;
}

.page__line {
	height: 6px;
	border-radius: 3px;
	background: #e6e4ed;
}

.page__line--short {
	width: 55%;
}

.reflow__cap {
	display: flex;
	align-items: center;
	gap: 0.3rem;
	font-size: 0.7rem;
	font-weight: 600;
	margin-top: 0.45rem;
	line-height: 1.3;
}

.reflow__cap svg {
	width: 0.9rem;
	height: 0.9rem;
	flex: none;
}

.reflow__cap.is-bad {
	color: var(--nc-bad);
}

.reflow__cap.is-good {
	color: var(--nc-good);
}

.reflow__notes h3 {
	font-size: 0.95rem;
	margin-bottom: 0.35rem;
}

.reflow__notes ul {
	font-size: 0.85rem;
}
</style>
