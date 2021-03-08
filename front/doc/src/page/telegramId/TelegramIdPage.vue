<template>
    <div>
        <p>1. Добавьте в список контактов бота <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>.</p>
        <p>2. Нажмите /start в окне чата.</p>
        <p>3. Обратным сообщением бот вернет вам ваш идентификатор.</p>
        <img src="/inc/img/telegramId.png" alt="Telegram ID" title="Telegram ID">
    </div>
</template>

<script>
    import {page} from './../../mixin/page';

    export default {
        mixins: [page],
        data() {
            return {}
        },
        mounted() {
            this.init()
        },
        methods: {
            init() {
            }
        }
    }
</script>

<style>
</style>
