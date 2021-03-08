<template>
    <div class="langs">
        <span class="selected" v-click-outside="hide" @click="isOpen = !isOpen">{{languages[selected].title}}</span>
        <div class="all" :class="{all_visible:isOpen}">
            <span v-for="(language,i) in languages" :key="i" @click="changeLanguage(i, language.value)">{{language.title}}</span>
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
            languages: Array,
            selected: Number,
        },

        data() {
            return {
                isOpen: false
            };
        },

        methods: {
            changeLanguage(i, value) {
                this.isOpen = false;
                if (i !== this.selected) {
                    this.$emit('select', value);
                }
            },
            hide() {
                this.isOpen = false;
            }
        }
    };
</script>

<style lang="scss" scoped>
    .langs {
        margin-right: 5px;
        position: relative;

        .all {
            color: #000;
            background: #fff;
            padding: 5px;
            border-radius: 8px;
            position: absolute;
            left: 50%;
            top: 40px;
            transform: translateY(-10px) translateX(-50%);
            transition: 0.3s;
            opacity: 0;
            visibility: hidden;

            span {
                display: block;
                padding: 0 15px;
                margin: 10px 0;

                &:hover {
                    color: #0177fd;
                    cursor: pointer;
                }
            }
        }

        .all_visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .selected {
            font-weight: bold;
            background: rgba(#ffffff, 0.1);
            /*background: rgba(#fff, 0.7);*/
            padding: 10px;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            transition: 0.3s;

            &:hover {
                background: rgba($color: #fff, $alpha: 1);
                color: #0177fd;
            }
        }
    }
</style>
