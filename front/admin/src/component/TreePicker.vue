<template>
    <div :class="[{'treePicker__error': error},'treePicker']">
        <div style="display:flex;align-items: center">
            <v-subheader style="display:inline-flex;height:30px">Категория</v-subheader>
            <v-spacer/>
            <div class="error--text" style="display:inline-flex;font-size:12px">{{error}}</div>
        </div>
        <div style="position:relative">
            <div class="treePicker__edit">
                <v-btn @click="dialog=true" color="primary" fab small dark>
                    <v-icon small>mdi-pencil</v-icon>
                </v-btn>
            </div>
            <div class="treePicker__current">
                <div v-if="applied" class="subtitle-1">
                    <v-treeview
                            :items="itemsShow"
                            :open="open"
                            item-key="id"
                            class="treePicker__tree"
                    ></v-treeview>
                </div>
                <div v-else class="subtitle-1">Не выбрана</div>
            </div>
        </div>

        <v-dialog v-model="dialog" content-class="treePicker__dialog">
            <v-card>
                <v-card-title class="grey lighten-2 treePicker__dialogTitle">Выбор категории</v-card-title>
                <v-card-text>
                    <v-treeview
                            :items="items"
                            :open="open"
                            :active.sync="active"
                            item-key="id"
                            class="treePicker__tree treePicker__treeSelector"
                            activatable
                            hoverable
                    ></v-treeview>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn @click="dialog = false" text>Отменить</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {
        props: {
            tree: Array,
            error: String,
            value: Number,
        },
        data: () => ({
            items: null,
            itemsShow: [],
            open: [],
            active: [],
            applied: null,
            dialog: false,
        }),
        created() {
            this.items = this.tree[0].children;
        },
        mounted() {
            if (this.value !== undefined && this.value !== null) {
                this.active = [this.value];
                this.apply(this.value);
            }
        },
        watch: {
            'active': {
                handler: function (newValue) {
                    let value = null;
                    if (newValue.length > 0) {
                        value = newValue[0];
                    }
                    this.apply(value);
                }
            },
            // 'value': {
            //     handler: function (newValue) {
            //         if (newValue === undefined || newValue === null) {
            //             this.applied = null;
            //         } else {
            //             this.active = [newValue];
            //             this.apply();
            //         }
            //     }
            // },
            // 'selected': {
            //     handler: function (newValue) {
            //         console.log(newValue);
            //         if (newValue !== undefined && newValue !== null) {
            //             this.apply();
            //         }
            //     }
            // },
        },
        // computed: {
        //     selected() {
        //         if (!this.active.length) {
        //             return undefined;
        //         }
        //         const id = this.active[0];
        //
        //         return this.findInTree(this.items, id)
        //     },
        // },
        methods: {
            findInTree(node, id) {
                if (node.id === id) {
                    return node;
                }
                for (let i in node.children) {
                    let result = this.findInTree(node.children[i], id);
                    if (result !== null) {
                        return result;
                    }
                }

                return null;
            },
            apply(id) {
                this.itemsShow = [];
                this.open = [];
                this.applied = null;
                if (null !== id && undefined !== id) {
                    this.applied = this.findInTree(this.tree[0], id);

                    this.itemsShow = [];
                    let el = Object.assign({}, this.applied);
                    el.children = [];
                    this.open.push(el.id);
                    while (el.parent.parent !== null) {
                        let parent = Object.assign({}, el.parent);
                        parent.children = [el];
                        el = parent;
                        this.open.push(el.id);
                    }
                    this.itemsShow = [el];
                }
                this.$emit('apply', id);
                this.dialog = false;
            },
        }
    };
</script>

<style>
    .treePicker {
        /*max-width: 500px;*/
        padding: 0 5px;
        width: 100%;
        border: 2px dashed transparent !important;
    }

    .treePicker__tree .v-treeview-node__root {
        min-height: unset !important;
    }

    .treePicker__treeSelector {
        overflow: auto;
        /*height: stretch;*/
    }

    .treePicker__treeSelector .v-treeview-node__root {
        cursor: pointer;
    }

    .treePicker__tree .v-treeview-node__label {
        white-space: normal !important;
        /*max-width: 450px;*/
    }

    .treePicker__current {
        padding: 6px 0 0 50px;
    }

    .treePicker__edit {
        position: absolute;
    }

    .treePicker__error {
        border: 2px dashed red !important;
    }
</style>
