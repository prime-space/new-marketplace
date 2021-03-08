<template>
    <div>
        <v-dialog v-model="dialog" width="800" persistent fullscreen>
            <template v-slot:activator="{ on }">
                <v-layout justify-center>
                    <v-btn @click="onUploadButtonClick" :loading="uploading">
                        <v-icon left>mdi-upload-multiple</v-icon>
                        csv\txt
                    </v-btn>
                    <input ref="uploader"
                           class="d-none"
                           type="file"
                           @change="onFileChanged"
                    >
                </v-layout>
                <v-layout justify-center style="margin-top:10px">
                    <v-btn v-on="on">
                        <v-icon left>mdi-format-textbox</v-icon>
                        Текст
                    </v-btn>
                </v-layout>
            </template>
            <v-card>
                <v-toolbar color="primary" style="position: sticky;top:0;z-index:999" dark>
                    <v-btn icon dark @click="dialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                    <v-toolbar-title>Загрузка товара</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark text @click="form.submit" :loading="form.loading">Загрузить</v-btn>
                    </v-toolbar-items>
                </v-toolbar>
                <v-card-text>
                    <p>
                        Вставьте исходный текст и укажите разделитель элементов.
                        <br/>В качестве такого разделителя как перенос строки, укажите <span class="bold">\n</span>.
                        <br/>Далее, в области <span class="bold">предпросмотр</span> проверьте, что исходный текст
                        разделился правильно и нажмите <span class="bold">Загрузить</span>.
                        <br/>Если после загрузки ничего не происходит, видимо, с одним из ваших элементов возникла
                        проблема. Пролистните предпросмотр, что бы увидеть ошибку.
                    </p>
                    <v-textarea v-model="text" label="Текст" :rows="7"/>
                    <v-text-field v-model="separator" label="Разделитель"/>
                    <v-subheader>Предпросмотр</v-subheader>
                    <form-error :error="form.getError('form')"/>
                    <form-type-collection :form="form" path="productObjects">
                        <div v-if="form.data.productObjects.length > 0">
                            <v-card v-for="(item,i) in form.data.productObjects"
                                    :key="i"
                                    style="margin-bottom:20px"
                                    outlined
                            >
                                <v-card-text>
                                    <v-textarea v-model="form.data.productObjects[i].data"
                                                :error-messages="form.getError('productObjects.'+i+'.data')"
                                                label="Содержимое"
                                                :rows="3"
                                                disabled
                                    />
                                </v-card-text>
                            </v-card>
                        </div>
                        <div v-else class="text-center">
                        <span class="font-weight-light">
                            Вставьте исходный текст и укажите разделитель элементов.
                        </span>
                        </div>
                    </form-type-collection>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    import FormError from "../../../form/FormError";
    import FormTypeCollection from "../../../form/FormTypeCollection";

    export default {
        components: {FormError, FormTypeCollection},
        props: {},
        data() {
            return {
                uploading: false,
                dialog: false,
                text: '',
                separator: '\\n',
                form: null,
                reader: null,
            }
        },
        created() {
            this.form = this.$ewll.initForm(this, {
                url: '/crud/product/' + this.$route.params.id + '/objectsAdd',
                snackSuccessMessage: 'Сохранено',
                data: {productObjects: []},
                success: function () {
                    this.dialog = false;
                    this.$emit('onUploaded');
                }.bind(this)
            });
            this.reader = new FileReader();
            this.reader.onload = function (e) {
                if (e.target.readyState !== 2) {
                    return;
                }
                this.$refs.uploader.value = '';
                this.uploading = false;
                if (e.target.error) {
                    this.$snack.danger({text: 'Ошибка загрузки файла'});
                    return;
                }
                this.text = e.target.result;
                this.dialog = true;
            }.bind(this);
        },
        watch: {
            text: function () {
                this.parse();
            },
            separator: function () {
                this.parse();
            },
        },
        methods: {
            onUploadButtonClick() {
                this.$refs.uploader.click();
            },
            onFileChanged(e) {
                if (e.target.files.length > 0) {
                    this.uploading = true;
                    this.reader.readAsText(e.target.files[0]);
                } else {
                    this.uploading = false;
                }
            },
            parse() {
                this.form.data.productObjects = [];
                if (this.separator !== '') {
                    let separator = this.separator.replace('\\n', "\n");
                    let exploded = this.text.split(separator);
                    for (let i in exploded) {
                        this.form.data.productObjects.push({
                            data: exploded[i],
                        });
                    }
                }
            },
        },
    }
</script>

<style>
</style>
