<template>
    <v-app id="app">
        <confirm-dialog/>
        <v-snackbar v-model="snack.isShow">{{ snack.text }}</v-snackbar>
        <div class="appContainer">
            <div class="content__block">
                <router-view/>
            </div>
            <BaseFooter/>
        </div>
    </v-app>
</template>

<script>
    import BaseFooter from './component/BaseFooter';
    import ConfirmDialog from './component/ConfirmDialog'

    export default {
        name: "app",
        components: {BaseFooter, ConfirmDialog},
        data: () => ({
            snack: {isShow: false, text: ''},
        }),
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
    #app,
    html {
        margin: 0;
        background: linear-gradient(120deg, #0177fd 0%, #4dd29b 100%);
        min-height: 100vh;
        font-family: "Rubik", sans-serif;
    }

    .appContainer {
        width: 1140px;
        margin: 0 auto;
        padding: 80px 0 0;

        @media screen and (max-width: 1200px) {
            width: 100%;
            padding: 80px 10px 0;
            box-sizing: border-box;
        }
    }

    .content__block {
        position: relative;
        width: 920px;
        margin: 0 auto;
        padding: 35px;
        box-sizing: border-box;
        background: #fff;
        border-radius: 30px;
        z-index: 3;

        @media screen and (max-width: 1000px) {
            width: 100%;
        }

        @media screen and (max-width: 400px) {
            padding: 18px;
        }

        &:before,
        &:after {
            content: "";
            display: block;
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            opacity: 0.5;
            width: 90%;
            border-radius: 30px;
            height: 70px;
            z-index: -1;

            @media screen and (max-width: 400px) {
                top: -35px;
            }
        }

        &:after {
            top: -70px;
            opacity: 0.25;
            width: 80%;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;

            @media screen and (max-width: 400px) {
                top: calc(-35px * 2);
            }
        }
    }
</style>

