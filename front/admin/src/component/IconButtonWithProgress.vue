<template>
    <div>
        <v-progress-circular v-if="loading"
                             :size="size" :width="3"
                             :color="progressColor"
                             indeterminate
        />
        <v-tooltip v-else bottom>
            <template v-slot:activator="{ on: tooltip }">
                <v-btn v-on="{ ...tooltip, ...on }"
                       @click="$emit('action')"
                       :disabled="disabled"
                       :small="small"
                       :href="href"
                       :target="p_target"
                       text
                       tile
                       icon
                >
                    <v-icon>{{icon}}</v-icon>
                </v-btn>
            </template>
            <span>{{tooltip}}</span>
        </v-tooltip>
    </div>
</template>

<script>
    export default {
        props: {
            icon: String,
            tooltip: String,
            loading: Boolean,
            action: {
                type: Function, default: function () {
                }
            },
            href: {type: String, default: null},
            target: {type: String, default: null},
            small: {type: Boolean, default: false},
            progressColor: {type: String, default: 'primary'},
            on: {type: Object, default: null},
            disabled: {type: Boolean, default: false},
        },
        data() {
            return {
                p_target: null,
            }
        },
        created() {
            if (null !== this.href) {
                this.p_target = this.target !== null ? this.target : '_self';
            }
        },
        computed: {
            size() {
                return this.small ? 28 : 36;
            },
        },
    };
</script>
<style>
</style>
