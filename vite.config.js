import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    resolve: {
        alias: {
            '@System': fileURLToPath(new URL('./resources/js/System', import.meta.url)),
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/System/app.css',
                'resources/js/System/app.js',

                // Guest Pages
                'resources/js/Guest/Pages/home/main.js',
                'resources/js/Guest/Pages/book_complaints/main.js',
                'resources/js/Guest/Pages/tracking_attendances/main.js',

                // System Pages - Assets
                'resources/js/System/Pages/Assets/assets/main.js',
                'resources/js/System/Pages/Assets/assets_management/main.js',

                // System Pages - Catalogs
                'resources/js/System/Pages/Catalogs/brands/main.js',
                'resources/js/System/Pages/Catalogs/categories/main.js',
                'resources/js/System/Pages/Catalogs/products/main.js',
                'resources/js/System/Pages/Catalogs/services/main.js',
                'resources/js/System/Pages/Catalogs/subscriptions/main.js',

                // System Pages - Customers
                'resources/js/System/Pages/Customers/customers/main.js',
                'resources/js/System/Pages/Customers/tracking_attendances/main.js',
                'resources/js/System/Pages/Customers/tracking_customers/main.js',
                'resources/js/System/Pages/Customers/tracking_subscriptions/main.js',

                // System Pages - Essentials
                'resources/js/System/Pages/Essentials/dashboard/main.js',
                'resources/js/System/Pages/Essentials/home/main.js',
                'resources/js/System/Pages/Essentials/reports/main.js',

                // System Pages - Customers (Tracking Notifications)
                'resources/js/System/Pages/Customers/tracking_notifications/main.js',

                // System Pages - Organizations
                'resources/js/System/Pages/Organizations/book_complaints/main.js',
                'resources/js/System/Pages/Organizations/branches/main.js',
                'resources/js/System/Pages/Organizations/companies/main.js',
                'resources/js/System/Pages/Organizations/roles/main.js',
                'resources/js/System/Pages/Organizations/users/main.js',

                // System Pages - Sales
                'resources/js/System/Pages/Sales/sales/list.js',
                'resources/js/System/Pages/Sales/sales/main.js',

                // System Pages - Warehouses
                'resources/js/System/Pages/Warehouses/stocks_management/main.js',

                // System Pages - Devices
                'resources/js/System/Pages/Devices/biometric_devices/main.js',

                // System Helpers
                'resources/js/System/Helpers/Alerts.js',
                'resources/js/System/Helpers/Constants.js',
                'resources/js/System/Helpers/Requests.js',
                'resources/js/System/Helpers/Utils.js',
            ],
            refresh: true,
        }),
    ],
});
