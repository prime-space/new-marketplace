<template>
    <div>
        <p>
            Входная точка API: <kbd>https://{{config.domainApi}}</kbd>
        </p>
        <p>
            Тип авторизации: <kbd>Bearer</kbd>
            <br/>Формат ключа <code>userId.apiKey</code>
            <br/><code>userId</code> можно найти в <a :href="'https://'+config.domainPrivate+'/account#info'" target="_blank">настройках
            аккаунта</a>
            <br/><code>apiKey</code> можно установить в <a :href="'https://'+config.domainPrivate+'/account#api'" target="_blank">настройках
            API</a>
        </p>
        <p>
            Формат ответа <kbd>JSON</kbd>
        </p>
        <span class="title">Коды ответа</span>
        <v-simple-table>
            <template v-slot:default>
                <thead>
                <tr>
                    <th>Код</th>
                    <th>Описание</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, i) in codes" :key="i">
                    <td>{{ item.code }}</td>
                    <td v-html="item.description"></td>
                </tr>
                </tbody>
            </template>
        </v-simple-table>
    </div>
</template>

<script>
    import {page} from './../../mixin/page';

    export default {
        mixins: [page],
        data() {
            return {
                config: config,
                codes: [
                    {code: 200, description: 'Запрос успешно выполнен',},
                    {
                        code: 400,
                        description: 'Ошибка входящих данных.<br/>Список ошибок будет перечисленн массивом в формате <code>parameterName => errorMessage</code>',
                    },
                    {code: 401, description: 'Не авторизован',},
                    {code: 404, description: 'Не найдено',},
                    {code: 423, description: 'API заблокировано, обратитесь в поддержку',},
                    {code: 424, description: 'API отключено в настройках',},
                    {code: 500, description: 'Ошибка сервера, повторите запрос позже',},
                ],

            }
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
