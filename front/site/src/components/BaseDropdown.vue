<template>
    <div class="dropdown">
        <div class="title" v-click-outside="hide" @click="active = !active">
            <span>{{ title }}</span>
            <i class="pl-1 mdi mdi-chevron-down"></i>
        </div>

        <div class="options" :class="{ active: active }">
            <div class="option" v-for="(option, i) in options" :key="i" @click="$emit('select', option.value)">
                {{ option.text }}
            </div>
        </div>
    </div>
</template>

<script>
    import ClickOutside from 'vue-click-outside'

    export default {
        directives: {
            ClickOutside
        },
        props: {
            options: {
                type: Array,
                required: true
            },
            title: {
                type: String,
                required: true
            }
        },
        data() {
            return {
                active: false
            };
        },
        methods: {
            hide() {
                this.active = false;
            },
        }
    };
</script>

<style lang="scss" scoped>
    .dropdown {
        position: relative;
        z-index: 10;
        color: #1c1c1c;
        cursor: pointer;
        border-bottom: 2px solid rgba(#1c1c1c, 0.05);
        border-radius: 4px;
        user-select: none;
        height: 30px;

        .title {
            span {
                font-size: 16px;
            }

            i {
                color: rgba(#1c1c1c, 0.5);
            }
        }

        .options {
            margin-top: 20px;
            position: absolute;
            background: #fff;

            padding: 10px 10px;
            top: 100%;
            opacity: 0;
            visibility: hidden;
            transition: 0.3s;
            color: #000;
            border-radius: 4px;

            .option {
                margin: 10px;
                font-size: 16px;
                white-space: nowrap;

                &:hover {
                    text-decoration: underline;
                }
            }
        }

        .active {
            top: calc(100% + 10px);
            opacity: 1;
            visibility: visible;
        }
    }
</style>
