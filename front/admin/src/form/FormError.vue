<template>
    <div class="formError" :style="{fontSize: small ? '13px' : '16px'}">
        <div v-if="error">
            {{error}}
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            error: {
                type: [String, Object],
                default: null
            },
            small: {
                type: Boolean,
                default: false,
            },
        },
    };
</script>

<style>
    .formError {
        color: #cc0f30;
    }
</style>
