<template>
    <div class="productEditCommonTab">
        <div>
            <!--                        <v-card class="imageInput" outlined>-->
            <!--                            <v-card-text-->
            <!--                                    :class="[{'imageInput__imageCard__error': form.errors.imageStorageFileId},'imageInput__imageCard','lighten-5']">-->
            <!--                                <img v-if="form.data.imageStorageFileId!==null" style="width:247px" :src="form.data.imageStorageFileId" width="100%">-->
            <!--                                <v-icon v-else style="margin-top: 42px;" x-large>mdi-panorama</v-icon>-->
            <!--                                <v-btn-->
            <!--                                        absolute-->
            <!--                                        dark-->
            <!--                                        fab-->
            <!--                                        bottom-->
            <!--                                        right-->
            <!--                                        small-->
            <!--                                        color="primary"-->
            <!--                                >-->
            <!--                                    <v-icon small>mdi-pencil</v-icon>-->
            <!--                                </v-btn>-->
            <!--                            </v-card-text>-->
            <!--                        </v-card>-->
            <image-input @apply="form.data.imageStorageFileId=$event;form.errors.imageStorageFileId=null"
                         :value="form.data.imageStorageFileId"
                         :error="form.errors.imageStorageFileId"
                         class="productEditCommonTab__imageInput"
                         :width="300"
                         :height="160"
                         :previewWidth="251"
                         :previewHeight="136"
            />
            <tree-picker @apply="form.data.productCategoryId=$event;form.errors.productCategoryId=null"
                         :tree="config.productCategoryTree"
                         :error="form.errors.productCategoryId"
                         :value="form.data.productCategoryId"
            />
        </div>
        <v-radio-group v-model="form.data.typeId" label="Тип" dense disabled>
            <v-radio v-for="(item,i) in form.definition.fields.typeId.choices"
                     :key="i"
                     :label="item.text"
                     :value="item.value"
            />
        </v-radio-group>
        <v-text-field :error-messages="form.errors.name" v-model="form.data.name" label="Название"/>
        <v-text-field :error-messages="form.errors.price" v-model="form.data.price"
                      class="productEditPage__price" label="Цена">
            <template v-slot:append-outer>
                <v-select class="productEditPage__currency"
                          :error-messages="form.errors.currencyId"
                          v-model="form.data.currencyId"
                          :items="form.definition.fields.currencyId.choices"
                          label="Валюта"
                />
            </template>
        </v-text-field>
        <v-text-field :error-messages="form.errors.partnershipFee"
                      v-model="form.data.partnershipFee"
                      label="Комиссия партнера(%)"
        />
        <v-textarea v-model="form.data.description" :error-messages="form.errors.description"
                    label="Описание"/>
        <div v-if="form.errors.form" class="error--text">{{ form.errors.form }}</div>
    </div>
</template>

<script>
    import ImageInput from './../../ImageInput';
    import TreePicker from './../../TreePicker';

    export default {
        components: {ImageInput, TreePicker},
        props: {
            form: Object,
        },
        data() {
            return {
                config: config,
            }
        },
    }
</script>

<style>
    .productEditCommonTab__imageInput {
        margin-right: 16px;
        display: table-cell;
        vertical-align: top;
    }

    .treePicker {
        display: table-cell;
        vertical-align: top;
    }

    .productEditPage__price .v-input__append-outer {
        margin-top: 0;
    }

    .productEditPage__currency {
        padding-top: 0;
        margin-top: 0;
    }

    @media screen and (max-width: 600px) {
        .productEditCommonTab__imageInput {
            display: block;
        }

        .treePicker {
            margin-top: 16px;
            display: block;
        }
    }
</style>
