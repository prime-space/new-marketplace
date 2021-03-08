<template>
    <div class="notification"
         :class="{'notification__error':error,'notification__success':success,'notification__bordered':bordered}"
    >
        <div class="notification__message">
            <slot/>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            error: {
                type: Boolean,
                default: false
            },
            success: {
                type: Boolean,
                default: false
            },

            bordered: {
                type: Boolean,
                default: false
            }
        }
    }
</script>

<style lang="scss" scoped>
    .notification {
        padding: 18px 15px;
        background: transparent;
        font-size: 15px;
        border-radius: 8px;
        font-weight: 500;
        white-space: pre-wrap;
    }

    .notification__message {
        opacity: .85;

    }

    .notification__bordered {
        border: 4px solid transparent;
    }

    .notification__error {
        background: rgba(#FF3737, 0.1);
        color: #FF3737;
        border-color: rgba(#FF3737, 0.4);
    }

    .notification__success {
        background: rgba(#4CD09E, 0.1);
        border-color: #4CD09E;
        color: #1c1c1c;
    }
</style>
