<template>
    <footer class="appFooter">
        <div class="appFooter__top appContainer">
            <router-link to="/" class="appFooter__top__logo">
                <img src="@/assets/img/footer-logo.png" alt=""/>
            </router-link>
            <img src="@/assets/img/partners.png" class="appFooter__top__methods" alt="methods"/>
            <span style="" class="appFooter__top__moreMethods">и еще более 36 способов оплаты</span>
        </div>

        <div class="appFooter__middle">
            <div class="appContainer appFooter__middle__box">
                <div class="appFooter__middle__box__item">
                    <h6>Разделы</h6>
                    <div class="links">
                        <a :href="'https://'+config.domainAuth">Кабинет продавца</a>
                        <br><a :href="'https://'+config.domainCustomer">Кабинет покупателя</a>
                    </div>
                </div>
                <div class="appFooter__middle__box__item">
                    <h6>Связаться</h6>
                    <span>По всем вопросам</span>
                    <br/>
                    <a href="mailto:support@crocuspay.com">
                        <i class="mdi mdi-email"></i>
                        support@crocuspay.com
                    </a>
                </div>
            </div>
        </div>

        <div class="appFooter__bottom">
            <div class="appContainer appFooter__bottom__box">
                <span class="appFooter__bottom__box__copyright">© 2019-{{config.year}} {{config.siteName}}</span>
            </div>
        </div>
    </footer>
</template>

<script>
    export default {
        data() {
            return {
                config: config,
            };
        },
    };
</script>

<style lang="scss" scoped>
    .appFooter {
        background: #fff;

        &__top {
            display: flex;
            align-items: center;
            padding-top: 23px;
            padding-bottom: 23px;

            &__methods {
                margin: -10px 0 0 46px;
            }

            &__moreMethods {
                padding-left: 16px;
            }

            @media screen and (max-width: 767px) {
                justify-content: center;
                &__logo {
                    display: none;
                }
                &__methods {
                    margin: 0;
                }
                &__moreMethods {
                    display: none;
                }
            }
        }

        &__middle {
            padding: 25px 0 45px;
            background: #2774f0;
            color: #fff;
            font-size: 13px;

            h6 {
                padding: 0 0 10px;
                font-weight: 500;
                font-size: 16px;
            }

            a {
                color: #fff;
            }

            &__box {
                display: flex;
                align-items: center;
                justify-content: flex-end;

                &__item {
                    margin: 0 25px;
                }
            }
        }

        &__bottom {
            padding: 18px 0;
            background: #2b7bfe;

            &__box {
                &__copyright {
                    color: rgba(#ffffff, 0.75);
                }
            }
        }
    }
</style>
