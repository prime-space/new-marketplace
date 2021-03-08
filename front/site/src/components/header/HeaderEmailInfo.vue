<template>
    <div class="headerEmailInfo">
        <a :href="'mailto:'+config.emailInfo">
            <i class="mdi mdi-email"></i>
            <span>{{config.emailInfo}}</span>
        </a>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                config: config,
            };
        },
    }
</script>
<style lang="scss">
    .headerEmailInfo {
        a {
            color: #fff!important;
            text-decoration: none;
            display: flex;
            align-items: center;

            i {
                font-size: 20px;
            }
        }
    }
</style>
