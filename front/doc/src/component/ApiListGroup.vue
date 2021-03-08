<template>
    <v-list-group :value="isOpened()" color="#000" no-action>
        <template v-slot:activator>
            <v-list-item-action>
                <v-icon>mdi-api</v-icon>
            </v-list-item-action>
            <v-list-item-content>
                <v-list-item-title>Api</v-list-item-title>
            </v-list-item-content>
        </template>
        <v-list-item to="/api/info" link>
            <v-list-item-content>
                <v-list-item-title>Информация</v-list-item-title>
            </v-list-item-content>
        </v-list-item>
        <v-list-item to="/api/partner/products" link>
            <v-list-item-content>
                <v-list-item-title>partner.products</v-list-item-title>
            </v-list-item-content>
        </v-list-item>
        <v-list-item to="/api/partner/product" link>
            <v-list-item-content>
                <v-list-item-title>partner.product</v-list-item-title>
            </v-list-item-content>
        </v-list-item>
    </v-list-group>
</template>

<script>
    export default {
        data() {
            return {}
        },
        methods: {
            isOpened() {
                return true;
                // let currentRoute = this.$router.currentRoute;
                // let matchedRoutes = currentRoute.matched;
                // for (let i in matchedRoutes) {
                //     if (matchedRoutes[i].path === '/partnership') {
                //         return true;
                //     }
                // }
                // return false;
            },
        }
    };
</script>
