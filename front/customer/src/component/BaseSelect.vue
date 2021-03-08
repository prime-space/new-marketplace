<template>
    <div class="select">
        <div class="activator" v-click-outside="hide" @click="click">
            <slot name="pre"/>
            {{ selected.title }}
            <slot name="after"/>
        </div>

        <div class="options" :class="{ options_active: active }">
            <div
                    class="option"
                    v-for="(option, i) in optionsWithoutSelected"
                    :key="i"
                    @click="changeOption(option)"
            >
                <span>{{ option.title }}</span>
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
            value: {
                type: Number,
                default: 0
            }
        },
        created() {
            for (let i in this.options) {
                if (this.options[i].value - 0 === this.value) {
                    this.selectedIndex = i - 0;
                    break;
                }
            }
        },
        mounted() {
            this.selected = this.options[this.selectedIndex];
        },
        data() {
            return {
                selected: {},
                selectedIndex: 0,
                active: false
            };
        },
        computed: {
            optionsWithoutSelected() {
                return this.options.filter((option, i) => i !== this.selectedIndex);
            }
        },
        methods: {
            changeOption(option) {
                this.selected = option;
                this.selectedIndex = this.options.indexOf(option);
                this.active = false;
                this.$emit('change', option.value)
            },
            click() {
                this.active = !this.active;
            },
            hide() {
                this.active = false;
            },
        }
    };
</script>

<style lang="scss" scoped>
    .select {
        position: relative;
        /*z-index: 12;*/
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(#ffffff, 0.1);
        text-align: center;
        cursor: pointer;
        border-radius: 4px;
        user-select: none;
        transition: 0.3s;

        &:hover {
            background: rgba($color: #fff, $alpha: 1);
            color: #0177fd;
        }

        .activator {
            padding: 10px;
            font-size: 20px;
        }

        .options {
            position: absolute;
            top: 100%;
            left: 0;
            padding: 10px 5px;
            background: #fff;
            width: 100%;
            border-radius: 4px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.15);
            transition: 0.2s;
            opacity: 0;
            visibility: hidden;

            .option {
                color: #1c1c1c;
                margin: 2px 0;

                &:hover {
                    text-decoration: underline;
                }
            }
        }

        .options_active {
            top: calc(100% + 10px);
            opacity: 1;
            visibility: visible;
        }
    }
</style>
