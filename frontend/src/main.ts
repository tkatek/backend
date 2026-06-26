import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router' // Make sure this path points to your router folder
import './style.css'

const app = createApp(App)

app.use(createPinia())
app.use(router) // This registers <RouterView /> globally!

app.mount('#app')