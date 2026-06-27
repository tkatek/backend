import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import './style.css' // Assuming Tailwind is set up here
import App from './App.vue'

// Import your views
import DashboardLayout from './layouts/DashboardLayout.vue'
import OcrWorkspace from './views/dashboard/OcrWorkspace.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/dashboard',
      component: DashboardLayout,
      children: [
        {
          path: '',
          name: 'overview',
          component: () => import('./views/dashboard/Overview.vue') // Placeholder for now
        },
        {
          path: 'ocr-import',
          name: 'ocr-import',
          component: OcrWorkspace
        }
      ]
    }
  ]
})

const app = createApp(App)
app.use(router)
app.mount('#app')