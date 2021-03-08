import Vue from 'vue'
import VueRouter from 'vue-router'
import store from './../store';
import DashboardPage from "../page/DashboardPage";
import TariffsPage from "../page/TariffsPage";
import HighLevelPage from "../page/HighLevelPage";
import ProductsPage from "../page/product/ProductsPage";
import ProductEditPage from "../page/product/ProductEditPage";
import ProductFillPage from "../page/product/ProductFillPage";
import OrdersPage from "../page/order/OrdersPage";
import OrderPage from "../page/order/OrderPage";
import SupportPage from "../page/SupportPage";
import SupportTicketPage from "../page/SupportTicketPage";
import PayoutsPage from "../page/PayoutsPage";
import EventsPage from "../page/EventsPage";
import AccountPage from "../page/AccountPage";
import PartnershipAgentSellersPage from "../page/partnership/agent/SellersPage";
import PartnershipAgentSellerPage from "../page/partnership/agent/SellerPage";
import PartnershipAgentProductsPage from "../page/partnership/agent/ProductsPage";
import PartnershipAgentSettingsPage from "../page/partnership/agent/SettingsPage";
import PartnershipSellerAgentsPage from "../page/partnership/seller/AgentsPage";
import PartnershipSellerAgentPage from "../page/partnership/seller/AgentPage";
import PartnershipSellerSettingsPage from "../page/partnership/seller/SettingsPage";

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    base: '/',
    routes: [
        {
            path: '/',
            name: 'dashboard',
            component: DashboardPage,
            meta: {name: 'Показатели'}
        },
        {
            path: '/tariffs',
            name: 'tariffs',
            component: TariffsPage,
            meta: {name: 'Тарифы'}
        },
        {
            path: '/products',
            component: HighLevelPage,
            meta: {name: 'Товары'},
            children: [
                {path: '', name: 'products', component: ProductsPage},
                {
                    path: 'edit/:id',
                    name: 'productEdit',
                    component: ProductEditPage,
                    meta: {name: 'Редактирование товара #{id}'}
                },
                {
                    path: 'fill/:id',
                    name: 'productFill',
                    component: ProductFillPage,
                    meta: {name: 'Наполнение товара #{id}'}
                },
            ]
        },
        {
            path: '/orders',
            component: HighLevelPage,
            meta: {name: 'Продажи'},
            children: [
                {path: '', name: 'orders', component: OrdersPage},
                {
                    path: ':id',
                    name: 'order',
                    meta: {name: 'Заказ #{id}',},
                    component: OrderPage,
                }
            ],
        },
        {
            path: '/support',
            component: HighLevelPage,
            meta: {name: 'Поддержка'},
            children: [
                {path: '', name: 'support', component: SupportPage},
                {
                    path: ':ticketId',
                    name: 'supportTicket',
                    component: SupportTicketPage,
                    meta: {name: 'Тикет #{ticketId}'}
                },
            ]
        },
        {
            path: '/payouts',
            name: 'payouts',
            component: PayoutsPage,
            meta: {name: 'Выплаты'}
        },
        {
            path: '/events',
            name: 'events',
            component: EventsPage,
            meta: {name: 'События'}
        },
        {
            path: '/account',
            name: 'account',
            component: AccountPage,
            meta: {name: 'Аккаунт'}
        },
        {
            path: '/partnership',
            meta: {name: 'Партнерство', isNotPage: true},
            component: HighLevelPage,
            children: [
                {path: '', name: 'partnership'},
                {
                    path: 'agent',
                    meta: {name: 'Агент', isNotPage: true},
                    component: HighLevelPage,
                    children: [
                        {path: '', name: 'partnershipAgent'},
                        {
                            path: 'sellers',
                            meta: {name: 'Продавцы'},
                            component: HighLevelPage,
                            children: [
                                {path: '', name: 'partnershipAgentSellers', component: PartnershipAgentSellersPage},
                                {
                                    path: ':id',
                                    name: 'partnershipAgentSeller',
                                    meta: {name: '{sellerName}'},
                                    component: PartnershipAgentSellerPage
                                },
                            ],
                        },
                        {
                            path: 'products',
                            name: 'partnershipAgentProducts',
                            meta: {name: 'Товары'},
                            component: PartnershipAgentProductsPage
                        },
                        {
                            path: 'settings',
                            name: 'partnershipAgentSettings',
                            meta: {name: 'Настройки'},
                            component: PartnershipAgentSettingsPage
                        },
                    ]
                },
                {
                    path: 'seller',
                    meta: {name: 'Продавец', isNotPage: true},
                    component: HighLevelPage,
                    children: [
                        {path: '', name: 'partnershipSeller'},
                        {
                            path: 'agents',
                            meta: {name: 'Агенты',},
                            component: HighLevelPage,
                            children: [
                                {path: '', name: 'partnershipSellerAgents', component: PartnershipSellerAgentsPage},
                                {
                                    path: ':id',
                                    name: 'partnershipSellerAgent',
                                    meta: {name: '{agentName}'},
                                    component: PartnershipSellerAgentPage
                                },
                            ],
                        },
                        {
                            path: 'settings',
                            name: 'partnershipSellerSettings',
                            meta: {name: 'Настройки'},
                            component: PartnershipSellerSettingsPage
                        },
                    ]
                },
            ]
        },
    ]
});

router.beforeEach((to, from, next) => {
    if (store.state.unsaved.isUnsaved) {
        store.dispatch('confirmer/ask', {
            title: 'Несохраненные изменения',
            body: 'Покинуть страницу?',
        }).then(confirmation => {
            if (confirmation) {
                next();
            } else {
                next(false);
            }
        });
    } else {
        next();
    }
});

export default router;
