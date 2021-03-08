<template>
    <button :disabled="disabled"
            @click="$emit('click')"
            class="button"
            :style="{'background':computedBackground,'color':color}"
    >
        <slot/>
    </button>
</template>

<script>
    export default {
        props: {
            background: {
                type: String,
                default: '#0177fd'
            },
            color: {
                type: String,
                default: '#fff'
            },
            disabled: {
              type: Boolean,
              default: false,
            }
        },
        computed: {
            computedBackground() {
                return true === this.disabled ? '#ccc' : this.background;
            }
        },
    };
</script>

<style lang="scss" scoped>
    .button {
        display: block;
        width: 100%;
        padding: 16px 0;

        background: #0177fd;

        border: none;
        border-radius: 6px;

        text-align: center;
        color: #fff;
        font-size: 16px;
        font-weight: 600;

        cursor: pointer;
    }
</style>
