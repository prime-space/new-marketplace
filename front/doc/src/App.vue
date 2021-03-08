<template>
    <v-app id="app">
        <v-navigation-drawer v-model="drawer" app>
            <v-list>

                <api-list-group/>

                <v-list-item to="/telegramId" link>
                    <v-list-item-action>
                        <v-icon>mdi-telegram</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Узнать Telegram ID</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar app>
            <v-app-bar-nav-icon @click.stop="drawer = !drawer"/>
            <v-toolbar-title><breadcrumbs/></v-toolbar-title>
        </v-app-bar>

        <v-content>
            <v-container fluid>
                <router-view/>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
    import Breadcrumbs from './component/Breadcrumbs'
    import ApiListGroup from './component/ApiListGroup'

    export default {
        components: {Breadcrumbs, ApiListGroup},
        data() {
            return {
                drawer: null,
            }
        },
    }
</script>

<style>
</style>
