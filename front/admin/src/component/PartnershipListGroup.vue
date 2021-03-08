<template>
    <v-list-group color="white" :value="isOpened()" eager>
        <template v-slot:activator>
            <v-list-item-action>
                <v-icon>mdi-handshake</v-icon>
            </v-list-item-action>
            <v-list-item-content>
                <v-list-item-title>Партнерство</v-list-item-title>
            </v-list-item-content>
        </template>
        <v-tabs v-model="tab" background-color="transparent" centered grow hide-slider>
            <v-tab href="#agent">Агент</v-tab>
            <v-tab href="#seller">Продавец</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" style="background-color:transparent">
            <v-tab-item value="agent">
                <v-list-item to="/partnership/agent/sellers" link dark>
                    <v-list-item-action>
                        <v-icon>mdi-human-male-male</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Продавцы</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item to="/partnership/agent/products" link dark>
                    <v-list-item-action>
                        <v-icon>mdi-dolly</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Товары</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item to="/partnership/agent/settings" link dark>
                    <v-list-item-action>
                        <v-icon>mdi-cog</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Настройки</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-tab-item>
            <v-tab-item value="seller">
                <v-list-item to="/partnership/seller/agents" link dark>
                    <v-list-item-action>
                        <v-icon>mdi-human-male-male</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Агенты</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-list-item to="/partnership/seller/settings" link dark>
                    <v-list-item-action>
                        <v-icon>mdi-cog</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Настройки</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </v-tab-item>
        </v-tabs-items>
    </v-list-group>
</template>

<script>
    export default {
        data() {
            return {
                tab: this.getStartTab(),
            }
        },
        methods: {
            isOpened() {
                let currentRoute = this.$router.currentRoute;
                let matchedRoutes = currentRoute.matched;
                for (let i in matchedRoutes) {
                    if (matchedRoutes[i].path === '/partnership') {
                        return true;
                    }
                }
                return false;
            },
            getStartTab() {
                let currentRoute = this.$router.currentRoute;
                let matchedRoutes = currentRoute.matched;
                for (let i in matchedRoutes) {
                    if (matchedRoutes[i].path === '/partnership/seller') {
                        return 'seller';
                    }
                }
                return 'agent';
            },
        }
    };
</script>
<style>
</style>
