<template>
    <v-menu class="headerCategories" offset-y>
        <template v-slot:activator="{ on }">
            <v-btn class="headerCategories__btn" v-on="on" color="#2774f0" elevation="0" x-large>
                <v-icon left>mdi-menu</v-icon>
                <span>Категории</span>
            </v-btn>
        </template>
        <v-list>
            <v-list-item @click="categorySelect(null)">
                <v-list-item-title>Все товары</v-list-item-title>
            </v-list-item>
            <v-list-item v-for="(category, i) in config.productCategoryTop"
                         :key="i"
                         @click="categorySelect(category.id)"
            >
                <v-list-item-title>{{category.name}}</v-list-item-title>
            </v-list-item>
        </v-list>
    </v-menu>
</template>

<script>
    export default {
        data() {
            return {
                config: config,
            };
        },
        methods: {
            categorySelect(categoryId) {
                this.$store.commit('search/search', {
                    productCategoryId: categoryId,
                });
                if (this.$route.name !== 'main') {
                    this.$router.push({name: 'main'});
                }
            }
        }
    }
</script>

<style lang="scss">
    .headerCategories {
        &__btn {
            margin-left: 10px;
            color: #fff!important;
            border-radius: 8px !important;
            border: 1px solid transparent!important;
            &:hover {
                border: 1px solid #fff!important;
            }
        }
    }
</style>
