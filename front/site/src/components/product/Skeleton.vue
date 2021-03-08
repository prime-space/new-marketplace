<template>
    <div class="productPageSkeleton">
        <v-sheet :color="`grey lighten-4`">
            <v-skeleton-loader width="100%" max-width="500px" type="list-item"/>
        </v-sheet>
        <v-sheet :color="`grey lighten-4`" style="margin-top: 16px">
            <v-skeleton-loader width="300px" height="170px" type="image" style="margin:auto"/>
        </v-sheet>
        <v-sheet :color="`grey lighten-4`" style="margin-top: 16px">
            <v-skeleton-loader width="100%" type="article, table-heading"/>
        </v-sheet>
    </div>
</template>

<script>
    export default {}
</script>

<style lang="scss">
    .productPageSkeleton {

    }
</style>
