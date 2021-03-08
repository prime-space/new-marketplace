<template>
    <v-flex xs12 class="accountBalance">
        <v-card color="primary" class="white--text">
            <v-card-title primary-title>
                <div class="accountBalance__content">
                    <div class="headline">{{balance}}<span v-if="sign"> {{sign}}</span></div>
                    <div class="subtitle-2">{{name}}<span v-if="id" class="accountBalance__id">#{{id}}</span></div>
                </div>
            </v-card-title>
        </v-card>
    </v-flex>
</template>

<script>
    export default {
        props: ['id', 'name', 'balance', 'sign'],
    }
</script>

<style lang="scss">
    .accountBalance {
        width: 400px;
        display: inline-block;
        margin: 0 5px 5px 0;

        &__content {
            width: 100%;
        }

        &__id {
            float: right;
        }
    }

    .accountBalance:last-child {
        margin: 0;
    }

    .accountBalance:first-child {
        margin-top: 5px;
    }
</style>
