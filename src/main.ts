import { createApp } from 'vue'
import App from './App.vue'

// Toast notifications from @nextcloud/dialogs bring their own styles.
import '@nextcloud/dialogs/style.css'

// The wheel is sized off the viewport, so keep the viewport predictable.
document
	.querySelector('meta[name="viewport"]')
	?.setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no')

const app = createApp(App)
app.mount('#chaotic_file_cleaner')
