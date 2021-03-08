import Vue from 'vue'
import VueRouter from 'vue-router'
import Login from './../page/Login';
import Cart from './../page/Cart';
import AddingProduct from './../page/AddingProduct';
import Customer from './../page/Customer';
import OrderOnly from "../page/OrderOnly";
import OrderOnlyList from "../page/OrderOnlyList";
import OrderOnlyCartItem from "../page/OrderOnlyCartItem";
import CustomerOrderList from "../page/CustomerOrderList";
import CustomerCart from "../page/CustomerCart";
import CustomerCartItem from "../page/CustomerCartItem";

const NotFound = { render: (createElement) => createElement('h1', 'Not Found') };

Vue.use(VueRouter);

let routes = [
    {
        path: '*',
        component: NotFound
    },
    {
        name: 'orderOnly',
        path: '/order-only/:cartId/:tokenKey',
        component: OrderOnly,
        children: [
            {
                name: 'orderOnlyList',
                path: '',
                component: OrderOnlyList,
            },
            {
                name: 'orderOnlyCartItem',
                path: ':cartItemId',
                component: OrderOnlyCartItem,
            },
        ],
    },
];

if (config.subApp === 'cart') {
    routes = routes.concat([
        {
            path: '/',
            component: Cart,
            name: 'cart'
        },
        {
            path: '/add/:productId/:partnerUserId?',
            component: AddingProduct
        },
    ]);
} else if (config.subApp === 'customer_login') {
    routes = routes.concat([
        {
            path: '/',
            component: Login,
        },
    ]);
} else if (config.subApp === 'customer') {
    routes = routes.concat([
        {
            name: 'customer',
            path: '/',
            component: Customer,
            children: [
                {
                    name: 'customerOrderList',
                    path: '',
                    component: CustomerOrderList,
                },
                {
                    name: 'customerCartItemList',
                    path: 'order/:cartId',
                    component: CustomerCart,
                },
                {
                    name: 'customerCartItem',
                    path: 'order/:cartId/:cartItemId',
                    component: CustomerCartItem,
                },
            ],
        },
    ]);
}

const router = new VueRouter({
    mode: 'history',
    base: '/',
    routes
});

export default router;
