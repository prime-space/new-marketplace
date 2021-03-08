import Vue from 'vue';
import VueI18n from 'vue-i18n';

Vue.use(VueI18n);

const messages = {
    ru: {
        cart: {
            myCart: 'Корзина',
            guarantee: 'гарантия',
            moneyBack: 'возврата денег',
            emailDelivery: 'Email доставки товара',
            email: 'Email',
            totalToPay: 'Итого к оплате',
            continueToPay: 'Оплатить',
            subscribeAgreement: 'Я хочу получать еженедельную подборку специальных акций. Мы ценим вашу подписку — только по факту 1-2 сообщения, никакого спама!',
            product: {
                seller: 'Продавец',
                sales: 'Продано раз',
                amount: 'Кол-во',
                remove: 'Удалить из корзины',
            },
            promocode: {
                iHave: 'У меня есть промокод',
                promocode: 'Промокод',
                apply: 'Активировать',
            },
            addingProduct: 'Добавляем товар',
        },
        customer: {
            loading: 'Загрузка',
            login: {
                auth: 'Авторизация в личный кабинет',
                dear: 'Уважаемый покупатель!',
                forAuth1: 'Для получения доступа к вашим покупкам просим ввести ваш адрес электронной почты!',
                forAuth2: 'Укажите e-mail, который вы вводили при оплате',
                continue: 'Продолжить',
                sent: 'Ссылка для доступа в кабинет отправлена вам на email.',
                tokenNotFound: 'Токен не найден или устарел. Воспользуйтесь процедурой получение нового токена.',
            },
            order: {
                getting: 'Получение товара',
                notFound: 'Заказ не найден или токен устарел',
                seekIn: 'Оплаченные заказы вы можете найти в',
                inCustomerSpace: 'кабинете покупателя',
                waiting: 'Ожидание подтверждения оплаты счета',
                pageAutoRefresh: 'Страница перезагрузится автоматически',
                ifNotPaid: 'Если вы не оплатили товар, то можете вернуться в',
                paymentSystem: 'платежную систему',
                wait: 'Пожалуйста, подождите.',
            },
            orderList: {
                allOrders: 'Ко всем покупкам',
                orderNumber: 'Номер счета',
                created: 'Создан',
                amount: 'На сумму',
                outOfStock: 'К сожалению, один или несколько товаров в момент оплаты стал недоступен.',
                contectSupport: 'Обратитесь в поддержку для возврата средств.',
                myOrders: 'Мои заказы',
            },
            product: {
                seller: 'Продавец',
                yourGood: 'Оплаченный товар',
                back: 'Назад',
                details: 'Подробная информация',
                productName: 'Название товара',
                aboutSeller: 'Продавец',
                contactSeller: 'Связаться с продавцом',
            },
            inside: {
                header: 'Кабинет покупателя',
            },
            cartItem: {
                order: 'Заказ',
                date: 'Дата',
            },
            cartItemList: {
                allOrders: 'Все заказы',
            },
            review: {
                title: 'Отзыв',
                good: 'Все хорошо',
                bad: 'Все плохо',
                text: 'Текст вашего отзыва',
                public: 'Внимание! Текст отзыва является публичной информацией и он будет размещен в открытом доступе.',
                timeLimit: 'Отзыв можно оставить\\изменить\\удалить только в течение 14 дней после покупки.',
                send: 'Опубликовать',
                sellerAnswer: 'Ответ продавца',
                delete: 'Удалить',
                edit: 'Редактировать',
                edited: 'Отредактировано',
                published: 'Опубликовано',
                deleted: 'Удалено',
            },
            sellerMessaging: {
                title: 'Переписка с продавцом',
                send: 'Отправить',
                seller: 'Продавец',
                you: 'Вы',
                messageText: 'Текст сообщения',
            },
            cartItemEmailNotification: {
                subscribe: {
                    text: 'Вы отписаны от уведомлений о новых сообщениях по этому заказу посредством email.',
                    button: 'Подписаться',
                },
                unsubscribe: {
                    text: 'Вы подписаны на уведомления о новых сообщениях по этому заказу посредством email.',
                    button: 'Отписаться',
                },
            }
        },
    },
    en: {
        cart: {
            myCart: 'Cart',
            guarantee: 'guarantee',
            moneyBack: 'money back',
            emailDelivery: 'Email for products delivery',
            email: 'Email',
            totalToPay: 'Total',
            continueToPay: 'Continue',
            subscribeAgreement: 'I want to get special offers. We apreciate your subscribe - only 1-2 letters, no spam!',
            product: {
                seller: 'Seller',
                sales: 'Sales',
                amount: 'Amount',
                remove: 'Remove from cart',
            },
            promocode: {
                iHave: 'I have promocode',
                promocode: 'Promocode',
                apply: 'Apply',
            },
            addingProduct: 'Adding product',
        },
        customer: {
            loading: 'Loading',
            login: {
                auth: 'Authorization To Customer Space',
                dear: 'Dear customer!',
                forAuth1: 'For getting access to your purchases, please, input your email address.',
                forAuth2: 'Enter the email address you used in orders.',
                continue: 'Continue',
                sent: 'The link for access to customer space has sent to your email address.',
                tokenNotFound: 'Token not found or expired. Please, try to get new.',
            },
            order: {
                getting: 'Order',
                notFound: 'Order not found or token expired',
                seekIn: 'All you paid orders you can see in',
                inCustomerSpace: 'customer space',
                waiting: 'Waiting for payment confirmation',
                pageAutoRefresh: 'The page will auto refresh',
                ifNotPaid: 'If you did not pay turn back to',
                paymentSystem: 'payment system',
                wait: 'Please, wait.',
            },
            orderList: {
                allOrders: 'All orders',
                orderNumber: 'Order №',
                created: 'Created',
                amount: 'Amount',
                outOfStock: 'Unfortunately, one or more goods became not available during payment time',
                contectSupport: 'Please, contact support for money back',
                myOrders: 'My Purchases',
            },
            product: {
                seller: 'Seller',
                yourGood: 'Your purchase',
                back: 'Back',
                details: 'Details',
                productName: 'Product name',
                aboutSeller: 'Seller',
                contactSeller: 'Contact seller',
            },
            inside: {
                header: 'Customer Space',
            },
            cartItem: {
                order: 'Order',
                date: 'Date',
            },
            cartItemList: {
                allOrders: 'All orders',
            },
            review: {
                title: 'Review',
                good: 'Good',
                bad: 'Bad',
                text: 'Review text',
                public: 'Attention! The review text will be available publicly.',
                timeLimit: 'You can leave\\edit\\delete review within 14 days after purchase only.',
                send: 'Publish',
                sellerAnswer: 'Seller\'s answer',
                delete: 'Delete',
                edit: 'Edit',
                edited: 'Edited',
                published: 'Published',
                deleted: 'Deleted',
            },
            sellerMessaging: {
                title: 'Seller Chat',
                send: 'Send',
                seller: 'Seller',
                you: 'You',
                messageText: 'Text',
            },
            cartItemEmailNotification: {
                subscribe: {
                    text: 'You are unsubscribed from notifications of new messages for this order by email.',
                    button: 'Subscribe',
                },
                unsubscribe: {
                    text: 'You are subscribed to notifications about new messages for this order by email.',
                    button: 'Unsubscribe',
                },
            }
        },
    }
};

const i18n = new VueI18n({
    locale: config.locale,
    fallbackLocale: 'ru',
    messages,
});

export default i18n;
