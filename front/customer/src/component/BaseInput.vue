<template>
    <div class="baseInput">
        <div :class="[{'baseInput__error': error}, 'baseInput__content']" :style="{background:background}">
            <div class="icon">
                <slot name="icon"/>
            </div>
            <input :type="type"
                   :placeholder="placeholder"
                   :class="{'baseInput__light':light}"
                   :value="value"
                   :style="[{lineHeight: height}]"
                   @input="input($event)"
            />
        </div>
        <form-error :error="error" style="margin-top:7px" small/>
    </div>
</template>

<script>
    import FormError from './FormError.vue';

    export default {
        components: {FormError,},
        props: {
            type: {
                type: String,
                default: "text"
            },
            placeholder: {
                type: String
            },
            background: {
                type: String,
                default: 'rgba(#000000, 0.02)'
            },
            light: {
                type: Boolean,
                default: false
            },
            error: {
                type: String,
            },
            value: {
                type: String
            },
            height: {
                type: String,
                default: '40px',
            }
        },

        methods: {
            input(e) {
                this.$emit('input', e.target.value);
            },
        },
    };
</script>

<style lang="scss" scoped>
    .baseInput__content {
        border: 1px solid transparent;
        background: rgba(#000000, 0.02);
        padding: 0 25px;
        display: flex;
        align-items: center;
        justify-content: flex-start;

        border-radius: 6px;

        img {
            width: 24px;
            height: 24px;
        }

        input {
            padding-left: 15px;
            flex: 2;
            background: transparent;
            border: none;
            outline: none;
            color: #1c1c1c;
            font-size: 14px;
            font-weight: 500;
            &::placeholder {
                color: #a8a8a8;
            }
        }

        .baseInput__light {
            &::placeholder {
                color: rgba(#fff, 0.5);
            }
        }
    }

    .baseInput__error {
        border: 1px solid #cc0f30;
    }
</style>
