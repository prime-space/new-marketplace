<template>
    <div>
        <v-breadcrumbs :items="$store.state.breadcrumbs.data"
                       class="breadcrumbs breadcrumbs__full"
                       large
        >
            <template v-slot:divider>
                <v-icon>mdi-chevron-right</v-icon>
            </template>
        </v-breadcrumbs>
        <v-breadcrumbs :items="$store.state.breadcrumbs.shortData"
                       class="breadcrumbs breadcrumbs__short"
                       large
        >
            <template v-slot:divider>
                <v-icon>mdi-chevron-right</v-icon>
            </template>
        </v-breadcrumbs>
    </div>
</template>

<script>
    export default {}
</script>

<style>
    .breadcrumbs {
        padding-left: 6px !important;
        display: none !important;
    }

    .v-breadcrumbs__item--disabled {
        color: rgba(0, 0, 0, .87) !important;
    }

    @media screen and (max-width: 576px) {
        .breadcrumbs__short {
            display: flex !important;
        }

        .v-breadcrumbs__divider {
            padding: 5px !important;
        }
    }

    @media screen and (min-width: 577px) {
        .breadcrumbs__full {
            display: flex !important;
        }
    }
</style>
