<template>
    <div>
        <v-tabs v-model="tab"
                style="margin-bottom:5px"
                @change="change"
                :style="{display: invisible ? 'none' : 'block'}"
                centered
        >
            <v-tabs-slider/>
            <v-tab v-for="(tab,i) in tabs"
                   :key="i"
                   :href="'#'+tab.hash"
                   :disabled="tab.disabled===true"
            > {{tab.name}}
            </v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab">
            <slot v-bind:tab="tab"/>
        </v-tabs-items>
    </div>
</template>

<script>
    export default {
        props: {
            tabs: Array,
            invisible: {type: Boolean, default: false},
        },
        data() {
            return {
                tab: this.getStartTab(),
            }
        },
        watch: {
            $route(to) {
                if (undefined !== to.hash) {
                    this.tab = to.hash.substr(1);
                }
            }
        },
        methods: {
            change(e) {
                this.$router.replace({name: this.$route.name, query: {}, hash: '#' + e});
            },
            getStartTab() {
                let hash = this.$route.hash;
                return '' === hash ? this.tabs[0].hash : hash.substr(1);
            },
        }
    };
</script>
<style>
</style>
