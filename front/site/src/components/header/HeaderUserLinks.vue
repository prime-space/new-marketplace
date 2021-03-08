<template>
    <div class="headerUserLinks">
        <div class="headerUserLinks__btns">
            <a :href="'https://'+config.domainAuth" class="headerUserLinks__btns__item">
                <i class="mdi mdi-account-tie"></i>
                <span>Продавцам</span>
            </a>
            <a :href="'https://'+config.domainCustomer" class="headerUserLinks__btns__item">
                <i class="mdi mdi-account"></i>
                <span>Покупателям</span>
            </a>
        </div>
        <v-menu class="headerUserLinks__menu" offset-y>
            <template v-slot:activator="{ on }">
                <v-btn class="headerUserLinks__menu__btn" v-on="on" color="white" icon large>
                    <v-icon>mdi-account</v-icon>
                </v-btn>
            </template>
            <v-list>
                <v-list-item :href="'https://'+config.domainAuth">
                    <v-list-item-title>Продавцам</v-list-item-title>
                </v-list-item>
                <v-list-item :href="'https://'+config.domainCustomer">
                    <v-list-item-title>Покупателям</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-menu>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                config: config,
                showMenu: true,
            };
        },
    }
</script>

<style lang="scss">
    .headerUserLinks {
        &__btns {
            display: flex;
            @media screen and (max-width: 799px) {
                display: none;
            }

            &__item {
                position: relative;
                color: #fff !important;

                padding: 10px 20px;

                transition: 0.3s;
                text-decoration: none;

                span,
                i {
                    position: relative;
                    z-index: 2;
                }

                &:before {
                    content: '';
                    display: block;
                    position: absolute;
                    height: 100%;
                    width: 90%;
                    left: 50%;
                    top: 0;
                    transform: translateX(-50%);
                    opacity: 0;
                    transition: 0.3s;
                    background: #2469d9;
                    border-radius: 8px;
                }

                &:hover {
                    &:before {
                        opacity: 1;
                    }
                }

                &:not(:last-of-type) {
                    border-right: 2px solid rgba(#ffffff, 0.1);

                    @media screen and (max-width: 900px) {
                        /*border-left: 2px solid rgba(#ffffff, 0.1);*/
                    }
                }

                .shevron-icon {
                    opacity: 0.5;
                    font-size: 12px;
                }

                span {
                    padding-left: 10px;
                }
            }
        }

        &__menu {
            &__btn {
                display: none !important;
                margin-right: 8px;
                border-radius: 8px !important;
                width: 60px !important;
                height: 49px !important;
                @media screen and (max-width: 799px) {
                    display: inline-flex !important;
                }

                &:hover {
                    background: #2469d9;
                }
            }
        }
    }
</style>
