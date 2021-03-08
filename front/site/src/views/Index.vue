<template>
    <div class="indexPage appContainer">
        <div class="indexPage__platforms boxContainer">
            <div class="indexPage__platforms__title">Популярные платформы</div>
            <div class="indexPage__platforms__items">
                <div @click="categorySelect(61)"
                     class="indexPage__platforms__items__item"
                     style="background:#1C1C1C"
                >
                    <img src="../assets/img/platforms/steam.svg" alt="Steam"/>
                    <div class="indexPage__platforms__items__item__name">steam</div>
                </div>
                <div @click="categorySelect(76)"
                     class="indexPage__platforms__items__item"
                     style="background:#db121e"
                >
                    <img src="../assets/img/platforms/nintendo.svg" alt="Nintendo"/>
                    <div class="indexPage__platforms__items__item__name">nintendo</div>
                </div>
                <div @click="categorySelect(67)"
                     class="indexPage__platforms__items__item"
                     style="background:#5FBE28"
                >
                    <img src="../assets/img/platforms/xbox.svg" alt="Xbox"/>
                    <div class="indexPage__platforms__items__item__name">xbox</div>
                </div>
                <div @click="categorySelect(73)"
                     class="indexPage__platforms__items__item"
                     style="background:#F15A23"
                >
                    <img src="../assets/img/platforms/ea.svg" alt="EA Origin"/>
                    <div class="indexPage__platforms__items__item__name">ea origin</div>
                </div>
                <div @click="categorySelect(100)"
                     class="indexPage__platforms__items__item"
                     style="background:#545454"
                >
                    <img src="../assets/img/platforms/eg.svg" alt="Epic Games"/>
                    <div class="indexPage__platforms__items__item__name">epic games</div>
                </div>
            </div>
        </div>

        <product-section-header>
            <template v-if="filters.query !== '' && filters.query !== undefined">
                Поиск "{{filters.query}}"
            </template>
            <template v-slot:right>
                <a @click="resetFilters">
                    Все товары
                </a>
                <a v-if="filters.productCategoryId">
                    {{productCategoryName}}
                </a>
            </template>
        </product-section-header>

        <div v-if="skeleton" class="indexPage__products">
            <product v-for="i in this.listing.itemsPerPage" :key="i" skeleton/>
        </div>
        <div v-else-if="items.length > 0" class="indexPage__products">
            <product v-for="(product, i) in items" :key="i" :product="product"/>
        </div>
        <v-sheet v-else class="indexPage__nothingFound">К сожалению, ничего не найдено</v-sheet>

        <v-btn v-if="!noHaveMore"
               @click="loadMore"
               class="indexPage__showMore"
               color="#e9e9e9"
               elevation="0"
               :disabled="skeleton"
               :loading="listing.loading"
               x-large
        >
            <span>Показать больше</span>
        </v-btn>
    </div>
</template>

<script>
    import ProductSectionHeader from '../components/index/ProductSectionHeader';
    import Product from '../components/index/Product';
    import VueScrollTo from 'vue-scrollto';
    import {page} from './../mixin/page';

    export default {
        mixins: [page],
        components: {
            ProductSectionHeader,
            Product,
        },
        data() {
            return {
                config: config,
                skeleton: true,
                listing: null,
                items: [],
                noHaveMore: false,
                filters: {},
                isAppendloadItemsMode: true,
                productImagesLoaded: 0,
            };
        },
        beforeCreate() {
            let filterFields = ['query', 'productCategoryId'];
            for (let i in filterFields) {
                let fieldName = filterFields[i];
                let queryName = 'f_' + fieldName;
                let haveRouteQuery = undefined !== this.$route.query[queryName];
                let filters = {};
                if (haveRouteQuery && null !== this.$route.query[queryName]) {
                    filters[fieldName] = this.$route.query[queryName].toString();
                }
                // global.console.log(filters);
                this.$store.commit('search/set', filters);
            }
        },
        created() {
            let itemsPerPage = 16;
            this.listing = Main.initListingForm(this, {
                url: '/crud/siteProduct',
                itemsPerPage: itemsPerPage,
                sort: {salesNum: 'desc'},
                success: function (response) {
                    let items = response.body.items;
                    if (items.length < itemsPerPage) {
                        this.noHaveMore = 1;
                    }
                    for (let i in items) {
                        let img = new Image();
                        img.src = config.cdn + '/' + items[i].image;
                        img.onload = this.onProductImageLoad;
                    }
                    if (this.isAppendloadItemsMode) {
                        this.items = [...this.items, ...items];
                    } else {
                        this.items = items;
                    }
                    if (this.items.length === 0) {
                        this.skeleton = false;
                    }
                    this.$store.commit('search/loaded');
                }.bind(this),
                error: function () {
                    this.$store.commit('search/loaded');
                }.bind(this),
            });
        },
        mounted() {
            this.init();
        },
        watch: {
            '$store.state.search.counter': {
                handler: function () {
                    this.isAppendloadItemsMode = false;
                    this.loadItems();
                    VueScrollTo.scrollTo('.productSectionHeader', 300, {offset: -133});
                },
            },
        },
        computed: {
            productCategoryName() {
                return config.productCategoryTop.find(x => x.id === this.filters.productCategoryId - 0).name
            }
        },
        methods: {
            init() {
                this.loadItems();
            },
            loadItems() {
                this.prepareFilters();
                if (!this.isAppendloadItemsMode) {
                    this.listing.pagination.page = 1;
                }
                this.listing.submit({filters: this.filters});
            },
            loadMore() {
                this.isAppendloadItemsMode = true;
                this.listing.pagination.page++;
                this.loadItems();
            },
            prepareFilters() {
                let queryFilters = {};
                this.filters = {};
                for (let i in this.$store.state.search.filters) {
                    let filter = this.$store.state.search.filters[i];
                    if ('' !== filter && null !== filter) {
                        queryFilters['f_' + i] = filter;
                        this.filters[i] = filter;
                    }
                }
                //@TODO удаляет все остальные query параметры
                this.$router.replace({
                    name: this.$route.name,
                    query: queryFilters,
                    hash: this.$route.hash
                }).catch(() => {//navigationduplicated
                });
            },
            onProductImageLoad() {
                this.productImagesLoaded++
                if (this.productImagesLoaded === this.items.length) {
                    this.skeleton = false;
                }
            },
            resetFilters() {
                this.$store.commit('search/reset');
                this.isAppendloadItemsMode = false;
                this.loadItems();
            },
            categorySelect(categoryId) {
                this.$store.commit('search/search', {
                    productCategoryId: categoryId,
                });
            },
        },
    };
</script>

<style lang="scss">
    .indexPage {
        &__platforms {
            margin-bottom: 15px;

            &__title {
                position: relative;
                margin: 0;
                padding: 0 0 0 17px;
                font-weight: 500;
                margin-bottom: 15px;

                &:before {
                    content: '';
                    display: block;
                    width: 7px;
                    height: 7px;
                    border: 2px solid #2b7bfe;
                    border-radius: 50%;
                    position: absolute;
                    left: 0;
                    top: 50%;
                    transform: translateY(-50%);
                }
            }

            &__items {
                display: flex;
                justify-content: space-between;

                @media screen and (max-width: 991px) {
                    flex-direction: column;
                }

                &__item {
                    cursor: pointer;
                    user-select: none;
                    color: #fff;
                    text-align: center;
                    padding: 25px 25px;
                    border-radius: 8px;

                    flex: 2;
                    max-width: 196px;

                    @media screen and (max-width: 1199px) {
                        max-width: 160px;
                    }

                    @media screen and (max-width: 991px) {
                        width: 100%;
                        max-width: none;
                        margin-bottom: 10px;
                        display: flex;
                    }

                    img {
                        width: 70px;
                        height: 70px;
                        @media screen and (max-width: 1199px) {
                            width: 50px;
                            height: 50px;
                        }
                    }

                    &__name {
                        padding: 17px 0 0;
                        font-size: 17px;
                        font-weight: 500;
                        text-transform: uppercase;

                        @media screen and (max-width: 991px) {
                            text-align: center;
                            width: 100%;
                        }
                    }
                }
            }
        }

        &__products {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
        }

        &__showMore {
            width: 100%;
            color: #555 !important;
        }

        &__nothingFound {
            margin-top: 12px;
            height: 400px;
            text-align: center;
            color: rgba(28, 28, 28, 0.5) !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>
