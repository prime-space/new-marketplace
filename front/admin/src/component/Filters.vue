<template>
    <div class="tableFilters">
        <v-toolbar flat dense>
            <v-text-field v-if="null !== searchKey"
                          v-model="value_[searchKey]"
                          @input="change"
                          v-on:keyup.enter="find"
                          hide-details
                          append-icon="mdi-magnify"
                          label="Поиск"
                          style="width:100%"
                          single-line
            />
            <v-spacer style="margin-left:15px"/>
            <v-menu :close-on-content-click="false" offset-y left>
                <template v-slot:activator="{on: menu}">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on: tooltip }">
                            <v-btn v-on="{...tooltip, ...menu}" small :disabled="isCardDisabled" tile icon>
                                <v-badge :value="isInsideFiltersUsed" color="primary" dot overlap bordered>
                                    <v-icon>mdi-filter</v-icon>
                                </v-badge>
                            </v-btn>
                        </template>
                        <span>Фильтры</span>
                    </v-tooltip>
                </template>
                <v-card>
                    <v-card-text>
                        <div style="margin-bottom:20px">Фильтры</div>
                        <div v-for="(field, i) in filtersWithoutSearch" :key="i">
                            <v-select v-if="field.type === 'choice'"
                                      v-model="value_[i]"
                                      @change="find"
                                      :items="field.choices"
                                      :label="field.label"
                                      dense
                            >
                                <template v-slot:append-outer>
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on: tooltip }">
                                            <v-btn v-on="{...tooltip}"
                                                   :style="{visibility: undefined === value_[i] ? 'hidden' : 'visible'}"
                                                   @click="cancelFilter(i)"
                                                   text
                                                   tile
                                                   icon
                                                   small
                                            >
                                                <v-icon small>mdi-close</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>Сбросить фильтр</span>
                                    </v-tooltip>
                                </template>
                            </v-select>
                            <v-text-field v-else-if="['integer', 'text'].includes(field.type)"
                                          v-model="value_[i]"
                                          :label="field.label"
                                          @input="change"
                                          @click:clear="cancelFilter(i)"
                                          dense
                                          clearable
                            />
                            <div v-else>FIELD {{field.label}} NOT REALISED!</div>
                        </div>
                    </v-card-text>
                    <v-card-actions v-if="isInsideFiltersUsed">
                        <v-spacer/>
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on: tooltip }">
                                <v-btn v-on="{...tooltip}" @click="cancelFilters" text tile icon>
                                    <v-icon>mdi-filter-remove</v-icon>
                                </v-btn>
                            </template>
                            <span>Сбросить фильтры</span>
                        </v-tooltip>
                    </v-card-actions>
                </v-card>
            </v-menu>
            <slot name="right"/>
        </v-toolbar>
        <div class="tableFilters__chips">
            <template v-for="(field, i) in value_">
                <v-chip v-if="data.fields[i].type !== 'search' && field !== null && field !== ''"
                        :key="i"
                        @click:close="cancelFilter(i)"
                        class="tableFilters__chips__item"
                        color="primary"
                        small
                        close
                >
                    <span>{{data.fields[i].label}}: <span class="bold">{{field}}</span></span>
                </v-chip>
            </template>
        </div>
    </div>
</template>

<script>
    import FormBody from '../form/FormBody';

    export default {
        comments: {FormBody},
        props: {
            data: Object,
            value: {},
        },
        data() {
            return {
                value_: {},
                searchKey: null,
                timer: null,
                state: null,
            }
        },
        computed: {
            filtersWithoutSearch() {
                let filters = {};
                for (let i in this.data.fields) {
                    let field = this.data.fields[i];
                    if (field.type !== 'search') {
                        filters[i] = field;
                    }
                }

                return filters;
            },
            isCardDisabled() {
                return Object.keys(this.filtersWithoutSearch).length === 0;
            },
            isInsideFiltersUsed() {
                for (let i in this.filtersWithoutSearch) {
                    if (this.filtersWithoutSearch[i].type !== 'search' && undefined !== this.$route.query['f_' + i]) {
                        return true;
                    }
                }

                return false;
            },
        },
        created() {
            this.value_ = this.value;
            for (let i in this.data.fields) {
                let queryName = 'f_' + i;
                let haveRouteQuery = undefined !== this.$route.query[queryName];
                if (haveRouteQuery && null !== this.$route.query[queryName]) {
                    this.value_[i] = haveRouteQuery ? this.$route.query[queryName].toString() : null;
                }
                if (this.data.fields[i].type === 'search') {
                    this.searchKey = i;
                }
            }
            this.sync();
            this.$emit('find');
        },
        methods: {
            change() {
                this.clearTimer();
                this.timer = setTimeout(function () {
                    this.find();
                }.bind(this), 1200);
            },
            find() {
                this.clearTimer();
                let queryFilters = {};
                for (let i in this.value_) {
                    if ('' !== this.value_[i] && null !== this.value_[i]) {
                        queryFilters['f_' + i] = this.value_[i];
                    }
                }
                let state = JSON.stringify(queryFilters);
                if (state !== this.state) {
                    this.state = state;
                    //@TODO удаляет все остальные query параметры
                    this.$router.replace({name: this.$route.name, query: queryFilters, hash: this.$route.hash});
                    this.$emit('find');
                }
            },
            clearTimer() {
                if (null !== this.timer) {
                    clearTimeout(this.timer);
                }
            },
            cancelFilters() {
                this.value_ = {};
                this.sync();
                this.find();
            },
            cancelFilter(i) {
                delete this.value_[i];
                this.sync();
                // if (this.data.fields[i].type === 'choice') {
                this.find();
                // }
            },
            sync() {
                this.$emit('input', this.value_);
            },

        },
    }
</script>

<style type="scss">
    .tableFilters__chips {
        padding: 0 16px;
    }

    .tableFilters__chips__item {
        margin: 0 4px;
    }

    .tableFilters__chips__item:first-child {
        margin-left: 0;
    }

    .tableFilters__chips__item:last-child {
        margin-right: 0;
    }
</style>
