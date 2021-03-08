import Vue from 'vue'
import VueRouter from 'vue-router'
import HighLevelPage from '../views/HighLevelPage'
import Product from '../views/Product'
import Index from '../views/Index'

Vue.use(VueRouter)

const routes = [
    // {
    //     path: '/',
    //     name: 'main',
    //     component: Index,
    //     meta: {name: 'Главная'},
    // },
    {
        path: '/',
        component: HighLevelPage,
        meta: {name: 'Главная'},
        children: [
            {
                path: '',
                component: Index,
                name: 'main',
                meta: {name: 'Главная'},
            },
            {
                path: 'product/',
                component: HighLevelPage,
                meta: {name: 'Товары', isNotPage: true},
                children: [
                    {
                        path: ':productId',
                        name: 'product',
                        component: Product,
                        meta: {name: '{productName}'}

                    },
                ]
            },
        ]
    },


]

const router = new VueRouter({
    // mode: 'history',
    base: '/',
    routes,
    scrollBehavior() {
        return {x: 0, y: 0};
    }
})

export default router
