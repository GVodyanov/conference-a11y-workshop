import { createApp } from 'vue'
import App from './App.vue'

// Toast notifications from @nextcloud/dialogs bring their own styles.
import '@nextcloud/dialogs/style.css'

const app = createApp(App)
app.mount('#chaotic_file_cleaner')
