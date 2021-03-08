<template>
    <div>
        <p>Запрос позволяет получить подробные данные о товаре.</p>
        <p>
            Адрес: <kbd>/partner/product/<code>id</code></kbd>
            <br>Метод <kbd>GET</kbd>
        </p>
        <div v-if="inputParameters.length > 0">
            <span class="title">Входящие парметры</span>
            <v-simple-table>
                <template v-slot:default>
                    <thead>
                    <tr>
                        <th>Ключ</th>
                        <th></th>
                        <th>Формат</th>
                        <th>По-умолчанию</th>
                        <th>Описание</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, i) in inputParameters" :key="i">
                        <td>{{ item.key }}</td>
                        <td>
                            <v-chip v-if="!item.isRequired">Опционально</v-chip>
                        </td>
                        <td>{{ item.format }}</td>
                        <td>{{ item.default }}</td>
                        <td v-html="item.description"/>
                    </tr>
                    </tbody>
                </template>
            </v-simple-table>
            <br/>
        </div>
        <span class="title">Ответ</span>
        <v-simple-table>
            <template v-slot:default>
                <thead>
                <tr>
                    <th>Ключ</th>
                    <th>Формат</th>
                    <th>Описание</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, i) in outputParameters" :key="i">
                    <td>{{ item.key }}</td>
                    <td>{{ item.format }}</td>
                    <td v-html="item.description"/>
                </tr>
                </tbody>
            </template>
        </v-simple-table>
        <br/>
        <span class="title">Пример</span>
        <div>
            <kbd>
                curl https://{{config.domainApi}}/partner/product/{productId} -G \<br/>
                <span style="margin-left:50px">-H "Authorization: Bearer {userId}.{apiKey}"</span>
            </kbd>
        </div>
    </div>
</template>

<script>
    import {page} from './../../../../mixin/page';

    export default {
        mixins: [page],
        data() {
            return {
                config: config,
                inputParameters: [],
                outputParameters: [
                    {key: 'id', format: 'integer', description: 'ID',},
                    {key: 'name', format: 'string', description: 'Название',},
                    {key: 'description', format: 'string', description: 'Описание',},
                    {key: 'price', format: 'decimal', description: 'Цена',},
                    {key: 'currencyId', format: 'integer', description: 'Код валюты',},
                    {key: 'inStock', format: 'bool', description: 'Наличие',},
                    {key: 'sellerId', format: 'integer', description: 'ID продавца',},
                    {key: 'categoryId', format: 'integer', description: 'ID категории',},
                    {key: 'pictureUrl', format: 'string', description: 'Адрес картинки',},
                    {key: 'url', format: 'string', description: 'Ссылка для покупки',},
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

<style>
</style>
