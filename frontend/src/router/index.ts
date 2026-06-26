import { createRouter, createWebHistory } from 'vue-router';
import DashboardHome from '../views/dashboard/DashboardHome.vue';
import MenuManagement from '../views/dashboard/MenuManagement.vue';
import OcrWorkspace from '../views/dashboard/OcrWorkspace.vue';
import PublicMenuDisplay from '../views/public/PublicMenuDisplay.vue';

const routes = [
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardHome,
  },
  {
    path: '/dashboard/menu',
    name: 'menu-manager',
    component: MenuManagement,
  },
  {
    path: '/dashboard/ocr-import',
    name: 'ocr-import',
    component: OcrWorkspace,
  },
  {
    path: '/menu/:slug',
    name: 'public-menu',
    component: PublicMenuDisplay,
    props: true,
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;