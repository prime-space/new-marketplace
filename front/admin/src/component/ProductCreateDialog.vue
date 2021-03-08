<template>
    <v-dialog v-model="dialog" width="500" content-class="productCreateDialog" persistent>
        <template v-slot:activator="{ on }">
            <v-btn v-on="on" @click="form.reset" color="primary" small dark>Добавить</v-btn>
        </template>
        <v-form @submit.prevent="submit" style="display:contents">
            <v-card>
                <v-card-title class="grey lighten-2">Новый товар</v-card-title>
                <v-card-text>
                    <v-radio-group v-model="form.data.typeId" :error-messages="form.errors.typeId" dense>
                        <v-radio label="Уникальный (продается один раз)" value="2" class="productCreateDialog__radio"/>
                        <div class="caption productCreateDialog__typeCaption">
                            Каждый экземпляр этого товара продается только один раз, без возможности
                            тиражирования: ПИН-код, пароль, реквизиты доступа к чему-либо и т.д. При этом, чтобы
                            продать 100 пин-кодов пополнения мобильного телефона, вам понадобится загрузить все
                            100 кодов, и каждый покупатель получит свой уникальный код.
                        </div>
                        <v-radio label="Универсальный (продается многократно)" value="1"
                                 class="productCreateDialog__radio"/>
                        <div class="caption">
                            Загружается в одном экземпляре, а продается бесконечное число раз.
                            Типичные примеры универсальных товаров: программа, электронная книга, база данных.
                            Так, чтобы продать электронную книгу 100 раз, достаточно загрузить ее лишь единожды.
                        </div>
                    </v-radio-group>
                    <div class="error--text">
                        <span v-if="form.errors.form">{{ form.errors.form }}</span>
                    </div>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-btn @click="dialog = false" text>Отменить</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn type="submit" color="primary" :disabled="form.loading">Создать</v-btn>
                </v-card-actions>
            </v-card>
        </v-form>
    </v-dialog>
</template>

<script>
    export default {
        data: () => ({
            config: config,
            dialog: false,
            form: null,
        }),
        created() {
            this.form = this.$ewll.initForm(this, {
                url: '/crud/product',
                success: function (response) {
                    this.$router.push({name: 'productEdit', params: {id: response.body.id}});
                }.bind(this),
            });
        },
        methods: {
            submit() {
                this.form.submit();
            },
        }
    };
</script>
<style>
    .productCreateDialog__radio {
        margin-bottom: 0 !important;
    }

    .productCreateDialog .caption {
        padding-left: 33px;
    }

    .error--text .productCreateDialog__typeCaption {
        color: inherit!important;
        caret-color: inherit!important;
    }
</style>
