<template>
    <div>
        <p>Запрос позволяет получить список товаров партнерской программы.</p>
        <p>
            Адрес: <kbd>/partner/products</kbd>
            <br>Метод <kbd>GET</kbd>
        </p>
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
                curl https://{{config.domainApi}}/partner/products -G \<br/>
                <span style="margin-left:50px">-H "Authorization: Bearer {userId}.{apiKey}"</span><br/>
                <span style="margin-left:50px">-d query="Counter Strike key" \</span><br/>
                <span style="margin-left:50px">-d showMissing=1</span>
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
                config:config,
                inputParameters: [
                    {key: 'query', isRequired: false, format: 'string', description: 'Поисковая строка',},
                    {key: 'sellerIds', isRequired: false, format: 'array', description: 'IDs продавцов',},
                    {key: 'groupId', isRequired: false, format: 'integer', description: 'ID группы товаров',},
                    {
                        key: 'showMissing',
                        isRequired: false,
                        format: '1/0',
                        default: 0,
                        description: 'Отображать отсутсвующие к продаже товары ',
                    },
                    {
                        key: 'itemsPerPage',
                        isRequired: false,
                        format: 'integer(5-100)',
                        default: 15,
                        description: 'Количество товаров в ответе',
                    },
                    {
                        key: 'page',
                        isRequired: false,
                        format: 'integer',
                        default: 1,
                        description: 'Номер страницы',
                    },
                ],
                outputParameters: [
                    {key: 'items', format: 'array', description: 'Массив товаров',},
                    {key: 'items.id', format: 'integer', description: 'ID',},
                    {key: 'items.name', format: 'string', description: 'Название',},
                    {key: 'items.description', format: 'string', description: 'Описание',},
                    {key: 'items.price', format: 'decimal', description: 'Цена',},
                    {key: 'items.currencyId', format: 'integer', description: 'Код валюты',},
                    {key: 'items.inStock', format: 'bool', description: 'Наличие',},
                    {key: 'items.sellerId', format: 'integer', description: 'ID продавца',},
                    {key: 'items.categoryId', format: 'integer', description: 'ID категории',},
                    {key: 'items.pictureUrl', format: 'string', description: 'Адрес картинки',},
                    {key: 'items.url', format: 'string', description: 'Ссылка для покупки',},
                    {key: 'itemsNum', format: 'integer', description: 'Всего товаров',},
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
