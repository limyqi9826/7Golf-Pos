import { createRouter, createWebHistory } from 'vue-router'
import Register from '@/components/Register.vue'
import Login from '@/components/Login.vue'
import Profile from '@/components/Profile.vue'
import ProShop from '@/views/ProShopView.vue'
import Home from '@/views/Home.vue'
import MainPOS from '@/components/POS/MainPOS.vue'
import Receipt from '@/components/POS/ReceiptPreview.vue'
import TeeTime from '@/views/TeeTimesView.vue'
import Rental from '@/views/RentalsView.vue'
import Report from '@/views/ReportsView.vue'
import ReportDetails from '@/views/ReportsDetailsView.vue'
import Customer from '@/views/CustomersView.vue'
import PaymentProcessing from '@/components/POS/PaymentProcessing.vue'

const routes = [
    {
        path: '/',
        name: 'login',
        component: Login
    },
    {
        path: '/register',
        name: 'register',
        component: Register
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile
    },
    {
        path: '/home',
        name: 'home',
        component: Home
    },
    {
        path: '/pos',
        name: 'pos',
        component: MainPOS
    },
    {
        path: '/pro-shop',
        name: 'pro-shop',
        component: ProShop
    },
    {
        path: '/rentals',
        name: 'rental',
        component: Rental
    },
    {
        path: '/tee-time',
        name: 'teetime',
        component: TeeTime
    },
    {
        path: '/reports',
        name: 'report',
        component: Report
    },
    {
        path: '/reports/details/:id',  // Add this new route
        name: 'report-details',
        component: ReportDetails,
        props: true
    },
    {
        path: '/customers',
        name: 'customers',
        component: Customer
    },
    {
        path: '/receipt',
        name: 'receipt',
        component: Receipt
    },
    {
        path: '/payment',
        name: 'payment',
        component: PaymentProcessing
    }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
