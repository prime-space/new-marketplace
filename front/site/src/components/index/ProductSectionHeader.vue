<template>
    <div class="productSectionHeader">
        <div>
            <slot name="default"></slot>
        </div>
        <div class="productSectionHeader__right">
            <slot name="right"></slot>
        </div>
    </div>
</template>

<script>
    export default {}
</script>

<style lang="scss">
    .productSectionHeader {
        background: #fff;
        border-radius: 8px;
        padding: 25px;
        display: flex;
        justify-content: space-between;

        &__right {
            a {
                color: rgba(28, 28, 28, .5) !important;
            }

            a::after {
                content: "/";
                padding: 0 10px;
                color: rgba(28, 28, 28, .5);
            }
        }
    }
</style>
