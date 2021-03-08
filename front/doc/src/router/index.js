import Vue from 'vue'
import VueRouter from 'vue-router'
import TelegramIdPage from "../page/telegramId/TelegramIdPage";
import ApiPage from "../page/api/ApiPage";
import HighLevelPage from "../../../admin/src/page/HighLevelPage";
import ProductsPage from "../page/api/partner/products/ProductsPage";
import ProductPage from "../page/api/partner/products/ProductPage";

Vue.use(VueRouter);

const routes = [
    {
        path: '/',
        redirect: {name: 'apiInfo'},
    },
    {
        path: '/api',
        meta: {name: 'API', isNotPage: true},
        component: HighLevelPage,
        children: [
            {path: '', redirect: {name: 'apiInfo'}},
            {path: 'info', name: 'apiInfo', component: ApiPage, meta: {name: 'Информация'}},
            {
                path: 'partner/products',
                name: 'apiPartnerProducts',
                component: ProductsPage,
                meta: {name: 'partner.products'}
            },
            {
                path: 'partner/product',
                name: 'apiPartnerProduct',
                component: ProductPage,
                meta: {name: 'partner.product'}
            },
        ]
    },
    {
        path: '/telegramId',
        name: 'telegramId',
        component: TelegramIdPage,
        meta: {name: 'Узнать Telegram ID'}
    },
];

const router = new VueRouter({
    mode: 'history',
    base: '/',
    routes
});

export default router
