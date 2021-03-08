<template>
    <a class="headerCart" :href="'https://'+config.domainBuy" target="_blank">
        <i class="mdi mdi-basket"></i>
        <span class="headerCart__counter">{{config.cart.itemsAmount}}</span>
    </a>
</template>

<script>
    export default {
        data() {
            return {
                config: config,
            };
        }
    };
</script>

<style lang="scss">
    .headerCart {
        position: relative;
        padding: 5px 18px 5px 18px;
        cursor: pointer;

        display: flex;
        align-items: center;
        justify-content: center;

        transition: 0.3s;

        i {
            color: #fff;
        }

        &:hover {
            background: #2469d9;
            border-radius: 8px;
        }

        &__counter {
            width: 18px;
            height: 18px;
            border-radius: 4px 4px 4px 0;
            background: #fee11b;

            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1c1c1c;
            font-weight: bold;
            top: -3px;
            right: 0;
        }

        i {
            font-size: 24px;
        }
    }
</style>
