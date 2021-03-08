<template>
    <header class="appHeader">
        <div class="appHeader__top appContainer">
            <header-email-info class="appHeader__top__email"/>
            <BaseSelect :options="currencies"
                        :value="config.currency"
                        @change="selectCurrency"
                        class="appHeader__top__currency"
            >
            </BaseSelect>
        </div>
        <div class="appHeader__middle">
            <div class="appContainer appHeader__middle__box">
                <div class="appHeader__middle__box__logo">
                    <a @click="logoClick">
                        <img src="@/assets/img/logo.png" :alt="config.siteName"/>
                    </a>
                </div>
                <header-search class="appHeader__middle__box__search"/>
            </div>
        </div>
        <div :class="['appHeader__bottom', {'appHeader__bottom--fixed': isMenuFixed}]">
            <div class="appContainer appHeader__bottom__box">
                <header-categories/>
                <v-spacer/>
                <header-user-links/>
                <header-cart/>
            </div>
        </div>
    </header>
</template>
<script>
    import BaseSelect from '@/components/BaseSelect';
    import HeaderSearch from './header/HeaderSearch';
    import HeaderEmailInfo from './header/HeaderEmailInfo';
    import HeaderCart from './header/HeaderCart';
    import HeaderCategories from './header/HeaderCategories';
    import HeaderUserLinks from './header/HeaderUserLinks';

    export default {
        components: {
            BaseSelect,
            HeaderSearch,
            HeaderEmailInfo,
            HeaderCart,
            HeaderCategories,
            HeaderUserLinks
        },

        data() {
            return {
                config: config,
                currencies: [],
                setCurrencyForm: null,
                scrollY: 0,
            };
        },
        created() {
            this.setCurrencyForm = Main.initForm(this, {url: '/setCurrency',});
            for (let i in config.currencies) {
                let currency = config.currencies[i];
                this.currencies.push({title: currency.short.toUpperCase(), value: i});
            }
        },

        mounted() {
            document.addEventListener('scroll', () => {
                this.scrollY = window.scrollY;
            });
        },

        methods: {
            selectCurrency(currencyId) {
                currencyId = currencyId - 0;
                this.setCurrencyForm.submit({
                    data: {currencyId},
                    success: function () {
                        this.config.currency = currencyId;
                        location.reload();
                    }.bind(this),
                });
            },
            logoClick() {
                this.$store.commit('search/search', {
                    productCategoryId: null,
                });
                if (this.$route.name !== 'main') {
                    this.$router.push({name: 'main'});
                }
            }
        },

        computed: {
            isMenuFixed() {
                return this.scrollY > 400;
            }
        }
    };
</script>
<style lang="scss">
    .appHeader {
        color: #fff;
        background: #2b7bfe;

        &__top {
            padding-top: 20px;
            padding-bottom: 20px;
            display: flex;
            justify-content: center;
            @media screen and (min-width: 1200px) {
                height: 45px;
                padding-top: 0;
                padding-bottom: 0;
                display: flex;
                justify-content: flex-end;
            }

            &__email {
                display: flex;
                margin-right: 15px;

                a {
                    font-size: 14px;
                }

                span {
                    padding-left: 10px;
                }
            }

            &__currency {
                display: flex;
                height: 34px;
                margin: 5px 0;

                .activator {
                    font-size: 16px !important;
                }
            }

        }


        &__middle {
            padding: 67px 0;
            background: #2774f0;
            @media screen and (max-width: 600px) {
                padding: 10px 0;
            }

            @media screen and (min-width: 1200px) {
                height: 101px;
                padding-top: 0;
                padding-bottom: 0;
                display: flex;
                align-items: center;

                img {
                    width: 250px;
                }
            }

            &__box {
                margin: auto;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                width: 100%;

                @media screen and (min-width: 1200px) {
                    flex-direction: row;
                }

                &__logo {
                    @media screen and (max-width: 600px) {
                        text-align: center;

                        img {
                            width: 90%;
                        }
                    }
                }

                &__search {
                }
            }
        }

        &__bottom {
            background: #2b7bfe;

            &__box {
                padding: 15px 20px 15px 5px;
                background: #2b7bfe;
                transition: 0.3s;
                transform: translateY(0);
                display: flex;
                justify-content: flex-end;

                @media screen and (max-width: 1200px) {
                    position: relative;
                    z-index: 9;
                }

                @media screen and (min-width: 1200px) {
                    height: 61px;
                    padding-top: 0;
                    padding-bottom: 0;
                    display: flex;
                    align-items: center;
                    position: relative;
                    z-index: 9;
                }
            }

            @media screen and (min-width: 1200px) {
                &--fixed {
                    position: fixed;
                    top: -20px;
                    left: 0;
                    width: 100%;
                    z-index: 5;
                    transform: translateY(20px);
                }
            }
        }
    }
</style>
