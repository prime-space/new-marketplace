<template>
    <v-app id="app">
        <v-snackbar v-model="snack.isShow">{{ snack.text }}</v-snackbar>
        <Header/>
        <router-view class="page"/>
        <app-foter/>
    </v-app>
</template>

<script>
    import Header from '@/components/Header.vue';
    import appFoter from '@/components/AppFoter.vue';

    export default {
        components: {
            Header,
            appFoter
        },
        data: () => ({
            snack: {isShow: false, text: ''},
            config: config,
        }),
        created() {
            this.config.currencyShortView = this.config.currencies[this.config.currency].short.toUpperCase();
        },
        beforeMount() {
            this.$snack.listener = function (text) {
                this.snack.text = text;
                this.snack.isShow = false;
                this.snack.isShow = true;
            }.bind(this);
        },
    };
</script>

<style lang="scss">

    body,
    html {
        background: #f7f7f7;
        min-height: 100vh;
    }

    #app {
        font-family: 'Rubik';
        min-height: 100vh;
        background: #f7f7f7;
    }

    .page {
        padding-top: 16px;
    }

    .ps__rail-x {
        background: #eeeeee;
        border-radius: 5px;
        height: 5px !important;
    }

    .ps__rail-x:hover > .ps__thumb-x,
    .ps__rail-x:focus > .ps__thumb-x,
    .ps__rail-x.ps--clicking .ps__thumb-x {
        background: #eeeeee;
        height: 5px !important;
        border-radius: 5px;
    }

    .ps__rail-x {
        height: 5px !important;
        bottom: 0;
        opacity: 0.75 !important;
        display: flex;
        align-items: center;
    }

    .ps--active-x > .ps__rail-x,
    .ps--active-y > .ps__rail-y {
        background: #eeeeee !important;
    }

    .ps__thumb-x {
        background: #2b7bfe !important;
        border-radius: 5px !important;
        cursor: pointer;
    }

    .ps__thumb-x {
        transition: background-color 0.2s linear, height 0.2s ease-in-out;
        -webkit-transition: background-color 0.2s linear, height 0.2s ease-in-out;
        height: 5px !important;
        bottom: 0 !important;
    }

    .ps__rail-x,
    .ps__rail-y {
        display: block !important;
    }

    a {
        text-decoration: none;
    }
</style>
