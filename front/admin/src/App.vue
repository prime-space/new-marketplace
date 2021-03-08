<template>
    <v-app id="inspire">
        <v-snackbar v-model="snack.isShow">{{ snack.text }}</v-snackbar>
        <v-navigation-drawer v-model="drawer" app>
            <v-list dark>
                <v-list-item class="drawer__title">
                    <v-list-item-content>
                        <v-list-item-title class="text-center title">
                            {{config.siteName.toUpperCase()}}
                        </v-list-item-title>
                    </v-list-item-content>
                </v-list-item>

                <v-divider class="navigation__titleDivider"/>

                <div class="drawer__pageList" style="">
                    <v-list-item to="/" link>
                        <v-list-item-action>
                            <v-icon>mdi-gauge</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>Показатели</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item to="/products" link>
                        <v-list-item-action>
                            <v-icon>mdi-dolly</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>Мои товары</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item to="/orders" link>
                        <v-list-item-action>
                            <v-icon>mdi-notebook</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>Продажи</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item to="/payouts" link>
                        <v-list-item-action>
                            <v-icon>mdi-cash</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>Вывод средств</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-divider class="navigation__listDivider"/>
                    <partnership-list-group/>
                </div>
            </v-list>
        </v-navigation-drawer>

        <v-content>
            <confirm-dialog/>
            <v-toolbar class="mainToolbar" flat dense>
                <v-btn @click.stop="drawer = !drawer" text>
                    <v-icon>mdi-menu</v-icon>
                </v-btn>
                <breadcrumbs/>
                <v-spacer/>

                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-btn v-on="on" to="/events" text>
                            <v-badge v-if="config.haveUnreadEvent" color="red" dot overlap bordered>
                                <template v-slot:badge>&nbsp;</template>
                                <v-icon>mdi-bell</v-icon>
                            </v-badge>
                            <v-icon v-else>mdi-bell</v-icon>
                        </v-btn>
                    </template>
                    <span>События</span>
                </v-tooltip>

                <!--                <v-menu offset-y left>-->
                <!--                    <template v-slot:activator="{ on }">-->
                <!--                        <v-btn v-on="on" text>-->
                <!--                            <v-icon>mdi-bell</v-icon>-->
                <!--                        </v-btn>-->
                <!--                    </template>-->
                <!--                    <v-card>-->
                <!--                    <v-list dense>-->
                <!--                        <v-list-item>-->
                <!--                            <v-list-item-content>-->
                <!--                                <v-list-item-title class="font-weight-light">Уведомлений пока нет</v-list-item-title>-->
                <!--                            </v-list-item-content>-->
                <!--                        </v-list-item>-->
                <!--                    </v-list>-->
                <!--                        <v-card-actions style="padding-top:0">-->
                <!--                            <v-spacer></v-spacer>-->
                <!--                            <v-btn to="/events" x-small text>Все события</v-btn>-->
                <!--                        </v-card-actions>-->
                <!--                    </v-card>-->
                <!--                </v-menu>-->

                <v-menu offset-y left>
                    <template v-slot:activator="{ on }">
                        <v-btn v-on="on" text>
                            <v-icon>mdi-account</v-icon>
                        </v-btn>
                    </template>
                    <v-list dense>
                        <v-list-item>
                            <v-list-item-avatar>
                                <v-icon large>mdi-image-filter-center-focus-weak</v-icon>
                                <!--<v-img src="/inc/img/me.png"></v-img>-->
                            </v-list-item-avatar>
                            <v-list-item-content>
                                <v-list-item-title>{{config.user.name}}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>

                        <v-divider/>

                        <v-list-item :to="{name:'tariffs'}">
                            <v-list-item-icon>
                                <v-icon>mdi-rocket-launch</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Тарифы</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>

                        <v-list-item :to="{name:'account'}">
                            <v-list-item-icon>
                                <v-icon>mdi-cog</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Настройки</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>

                        <v-list-item :to="{name:'support'}">
                            <v-list-item-icon>
                                <v-icon>mdi-face-agent</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Поддержка</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>

                        <v-list-item :href="'//'+config.domainDoc" target="_blank">
                            <v-list-item-icon>
                                <v-icon>mdi-information</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Документация</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>

                        <v-divider/>

                        <v-list-item :href="'//'+config.domainAuth+'/exit'">
                            <v-list-item-icon>
                                <v-icon>mdi-logout</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>Выход</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-menu>

            </v-toolbar>
            <v-container style="padding-top:0" fluid>
                <router-view/>
                <!--<v-row align="center" justify="center">-->
                <!--<v-col>-->
                <!--</v-col>-->
                <!--</v-row>-->
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
    import Breadcrumbs from './component/Breadcrumbs'
    import PartnershipListGroup from './component/PartnershipListGroup'
    import ConfirmDialog from './component/ConfirmDialog'

    export default {
        components: {Breadcrumbs, PartnershipListGroup, ConfirmDialog},
        data: () => ({
            config: config,
            drawer: null,
            snack: {isShow: false, text: ''},
        }),
        beforeMount() {
            this.$snack.listener = function (text) {
                this.snack.text = text;
                this.snack.isShow = false;
                this.snack.isShow = true;
            }.bind(this);
        },
    }
</script>

<style>
    .mainToolbar .v-toolbar__content {
        padding: 4px 8px !important;
    }

    .v-navigation-drawer {
        background-position: center center;
        background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url(./assets/img/sidebar.jpg);
        background-size: cover;
    }

    .v-navigation-drawer .drawer__title .title {
        margin-bottom: 5px;
    }

    .v-navigation-drawer .v-divider {
        border-color: #a8a8a8 !important;
    }

    .v-navigation-drawer .v-divider.navigation__titleDivider {
        margin: 0 10px;
    }

    .v-navigation-drawer .v-divider.navigation__listDivider {
        margin: 10px 0;
    }

    .mainToolbar {
        background-color: #fafafa !important;
        border-color: #fafafa !important;
    }

    .mainToolbar .v-btn {
        min-width: 0 !important;
        width: 45px;
        margin-left: 2px;
    }

    .mainToolbar .v-icon {
        color: #777 !important
    }

    .v-menu__content .v-list-item__title {
        /*font-size: 15px;*/
    }

    .drawer__pageList {
        margin: 10px;
    }

    .drawer__pageList .v-list-item {
        margin-bottom: 3px;
    }
</style>
