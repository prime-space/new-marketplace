<template>
    <div class="headerSearch">
        <input v-on:keyup.enter="find"
               v-model="query"
               type="text"
               class="headerSearch__input"
               placeholder="Что будем искать?"
        />
        <div class="headerSearch__categories">
            <BaseDropdown title="Категории" :options="categories" @select="categorySelect"></BaseDropdown>
        </div>
        <v-btn @click="find"
               class="headerSearch__button"
               :elevation="0"
               color="#fee11b"
               :loading="this.$store.state.search.loading"
        >
            <v-icon class="mdi mdi-magnify"/>
        </v-btn>
    </div>
</template>

<script>
    import BaseDropdown from '@/components/BaseDropdown';

    export default {
        props: {
            loading: Boolean
        },
        components: {
            BaseDropdown
        },
        data() {
            return {
                config: config,
                query: null,
                categories: [{value: null, text: 'Все товары'}],
            };
        },
        created() {
            for (let i in config.productCategoryTop) {
                let category = config.productCategoryTop[i];
                this.categories.push({
                    value: category.id,
                    text: category.name,
                });
            }
        },
        mounted() {
            this.query = this.$store.state.search.filters.query;
        },
        methods: {
            find() {
                this.$store.commit('search/search', {
                    query: this.query,
                });
                if (this.$route.name !== 'main') {
                    this.$router.push({name: 'main'});
                }
            },
            categorySelect(categoryId) {
                this.$store.commit('search/search', {
                    productCategoryId: categoryId,
                });
                if (this.$route.name !== 'main') {
                    this.$router.push({name: 'main'});
                }
            }
        }
    };
</script>

<style lang="scss">
    .headerSearch {
        background: #ffffff;
        border-radius: 8px;
        height: 50px;
        width: 650px;
        display: flex;
        align-items: center;
        justify-content: space-between;

        @media screen and (max-width: 1100px) {
            width: auto;
            margin: 0 auto;
        }

        &__categories {
            margin-right: 25px;
            @media screen and (max-width: 1100px) {
                display: none;
            }
        }

        @media screen and (max-width: 1199px) {
            margin-top: 16px;
        }

        &__input {
            padding: 0 50px 0 25px;
            border: none;
            outline: none;
            border-radius: 8px;
            flex: 2;
            font-size: 16px;
            color: #000;

            @media screen and (max-width: 600px) {
                flex: 1;
                padding: 0 10px 0 10px;
            }
        }

        &__button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 65px !important;
            height: 100% !important;
            border-radius: 8px;
            color: #1c1c1c !important;
            font-size: 24px;
            text-decoration: none;

            @media screen and (max-width: 1100px) {
                flex: 2;
                width: 100px;
            }
        }
    }
</style>
