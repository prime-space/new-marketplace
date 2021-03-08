<template>
    <div class="cardLoading">
        <v-progress-linear v-if="noCard" style="margin:20px 0" indeterminate/>
        <v-card v-else>
            <v-card-text>
                <v-progress-linear style="margin:20px 0" indeterminate/>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
    export default {
        props: {
            noCard: Boolean,
        },
    }
</script>
<style>
    .cardLoading {
        width: 100%;
    }
</style>
